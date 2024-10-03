<?php

namespace Drupal\ins_payment\Services;

use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentMethod;
use GuzzleHttp\Exception\RequestException;

/**
 * Class InsPaymentTransactionManager
 *
 * @package Drupal\ins_payment\Services
 */
class InsPaymentTransactionManager implements InsPaymentTransactionManagerInterface {

  /**
   *
   * @param \Drupal\commerce_payment\Entity\Payment $payment
   * @param \Drupal\commerce_payment\Entity\PaymentMethod $payment_method
   *
   * @return |null
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  function saleTransaction(Payment $payment, PaymentMethod $payment_method) {
    $transaction_code = NULL;
    $owner = $payment_method->getOwner();
    $order = $payment->getOrder();
    // Get cvv and number.
    $tempstore = \Drupal::service('user.private_tempstore')->get('ins_payment');
    $data_encrypted_locally = $tempstore->get('data');
    // Build all card information to be send in the api
    $data = [
      'montoTransaccion' => number_format($payment->getAmount()->getNumber(),2),
      'mesExpiracion' => $payment_method->card_exp_month->value,
      'anioExpiracion' => $payment_method->card_exp_year->value,
      'moneda' => $payment->getAmount()->getCurrencyCode(),
      'detalleCargo' => 'Orden #' . $order->id() . ' desde Víctor Hugo Castro Coto',
      'correoElectronico' => $owner->getEmail(),
    ];

    // get configuration from payment gateway configuration form.
    $config = \Drupal::service('ins_payment.config_onsite')->getConfig();
    // Call service to encrypt data.
    $data_encrypted = \Drupal::service('ins_payment.prepare_data')
      ->prepareEncryptedData($data, $config);
    // Build card number and cvv.
    $data_encrypted['numeroTarjeta'] = $data_encrypted_locally['number'];
    $data_encrypted['cvv'] = $data_encrypted_locally['number_code'];
    // Choose API path.
    if (!empty($data_encrypted['numeroTarjeta'])) {

      if ($config['mode'] === 'SANDBOX') {
        $api_path = $config['api_base_test'];
      }
      else {
        $api_path = $config['api_base_live'];
      }
      // Get authorization token from INS api.
      $authorization = $this->getInsApiAuthorization($config, $api_path);

      if ($authorization['status'] == 200) {
        // Convert to json.
        // $data_json = json_encode($data_encrypted);
        // Apply the charge by INS api sending all encrypted data.
        $card_charged = $this->insApplyCardCharge($data_encrypted, $api_path, $authorization['response']['access_token']);
        if (isset($card_charged['response']['EsAprobada']) && $card_charged['response']['EsAprobada'] == 1) {
          // Get authorization number.
          $transaction_code = $card_charged['response']['NumeroAutorizacion'];
        }
        else {
          \Drupal::logger('INS PAYMENT')
            ->error('<pre>'. print_r($card_charged, TRUE) .'</pre>');
        }
      }
    }

    return $transaction_code;
  }

  /**
   * Get authorization token from api.
   *
   * @param $config
   * @param $api_path
   *
   * @return array|\Psr\Http\Message\ResponseInterface
   */
  private function getInsApiAuthorization($config, $api_path) {
    // Build Body with credentials and ids.
    $response = [];
    $body = [
      'grant_type' => 'client_credentials',
      'client_id' => $config['client_id'],
      'client_secret' => $config['client_secret'],
      'allowed_scopes' => ''
    ];

    try {
      // Creat a call to INS API access token.
      $response = \Drupal::httpClient()
        ->post($api_path . '/connect/token', [
          'verify' => FALSE,
          'form_params' => $body,
          'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
          ],
        ]);
      // Build call response.
      $response = [
        'status' => $response->getStatusCode(),
        'response' => json_decode($response->getBody()->getContents(), TRUE),
      ];

    } catch (RequestException $e) {
      // Manage Exceptions.
      \Drupal::logger('INS PAYMENT')
        ->error('<pre>'. print_r($e, TRUE) .'</pre>');
    }

    return $response;
  }

  /**
   * Get response from apply charge to api.
   *
   * @param $data
   * @param $api_path
   * @param $access_token
   *
   * @return array|\Psr\Http\Message\ResponseInterface
   */
  private function insApplyCardCharge($data, $api_path, $access_token) {
    $response = [];

    $url = $api_path . '/pagos/cargotarjeta';
    $data = json_encode($data);

    try {
      // Init curl execution.
      $cURLConnection = curl_init($url);
      // Add body data.
      curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $data);
      curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, 1);
      curl_setopt($cURLConnection, CURLOPT_POST, 1);
      // Set headers.
      curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
      ));
      curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, TRUE);
      // Get response.
      $apiResponse = curl_exec($cURLConnection);
      curl_close($cURLConnection);
      // Build response.
      $response = [
        'response' => json_decode($apiResponse, TRUE),
      ];
      \Drupal::logger('INS PAYMENT')
        ->info('<pre>'. print_r($response, 1) .'</pre>');

    } catch (RequestException $e) {
      // Manage Exceptions.
      \Drupal::logger('INS PAYMENT')
        ->error('<pre>'. print_r($e, 1) .'</pre>');
    }
    return $response;
  }

}
