<?php

namespace Drupal\klap_payment\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Class KlapCardManager.
 */
class KlapCardManager implements KlapCardManagerInterface {

  /**
   * Users Data.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Information about the Configuration API.
   *
   * @var ConfigFactoryInterface
   */
  private $mainConfig;

  /**
   * Provides an interface for entity type managers.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * KlapCardManager constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The account viewing the form.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $currentUser,
                              ConfigFactoryInterface $config,
                              EntityTypeManagerInterface $entityTypeManager) {
    $this->mainConfig = $config->get('klap.main_settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * Function get Api Mode url.
   *
   * @param \Drupal\Core\Plugin\PluginBase $payment
   *    Base class for plugins supporting metadata inspection and translation.
   *
   * @return:array \Drupal\Core\Config\ImmutableConfig|mixed|null
   */
  public function getApiModeUrl(PluginBase $payment = NULL) {
    $api_url = NULL;
    if ($payment) {
      $config = $payment->getConfiguration();
    }
    else {
      $config = $this->getKlapPluginDefinition();
    }
    // Get api url form configuration file.
    if ($config) {
      if ('test' == $config['mode']) {
        $api_url = $config['app_test_url'];
      }
      else {
        $api_url = $config['app_live_url'];
      }
    }
    return $api_url;
  }

  /**
   * Get Plugin definition klap_onsite.
   *
   * @return:boolean
   */
  public function getKlapPluginDefinition() {
    // Get plugin type commerce payment gateway.
    $gateway = \Drupal::entityTypeManager()
      ->getStorage('commerce_payment_gateway')->loadByProperties([
        'plugin' => 'klap_onsite',
      ]);
    $plugin = reset($gateway);
    // Get plugin.
    $plugin = $plugin->getPlugin();
    if ($config = $plugin->getConfiguration()) {
      return $config;
    }
    return FALSE;
  }

  /**
   * Get user cards.
   *
   * @return:mixed|string
   */
  public function getUserCard() {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $user->name,
      "userPassword" => ucfirst($user->name . $user->id() . '*'),
    ];
    // Post method to get cards.
    $response = \Drupal::httpClient()->post($api_base . 'UserRequestCards', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * Add user cards.
   *
   * @return:mixed|string
   */
  public function addUserCard($dataCardObject) {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $user->name,
      "userPassword" => ucfirst($user->name . $user->id() . '*'),
      "cardDescription" => $dataCardObject['cardDescription'],
      "primaryAccountNumber" => $dataCardObject['primaryAccountNumber'],
      "expirationMonth" => $dataCardObject['expirationMonth'],
      "expirationYear" => $dataCardObject['expirationYear'],
      "verificationValue" => $dataCardObject['verificationValue'],
    ];
    // Post method to add card.
    $response = \Drupal::httpClient()->post($api_base . 'UserIncludeCard', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * User Set Default Card.
   *
   * @return:mixed|string
   */
  public function addFavoriteCard($dataCardTokenId) {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $user->name,
      "userPassword" => ucfirst($user->name . $user->id() . '*'),
      "cardTokenId" => $dataCardTokenId,
    ];
    // Post method to add favorite card.
    $response = \Drupal::httpClient()->post($api_base . 'UserSetDefaultCard', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * Get user default card.
   *
   * @return:mixed|string
   */
  public function getDefaultUserCard() {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $user->name,
      "userPassword" => ucfirst($user->name . $user->id() . '*'),
    ];
    // Post method to default card.
    $response = \Drupal::httpClient()->post($api_base . 'UserRequestDefaultCard', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * Create and apply a charge to user.
   */
  public function applyPayment() {
    // Load current user.
    $user = $this->currentUser->getAccount();
    // Load user in the API.
    $userVerify = \Drupal::service('klap_payment.user')->logOnUser();
    if ($userVerify['isApproved']) {
      // Load the app in the API.
      $appVerify = \Drupal::service('klap_payment.user')->LogOnApp();
      if ($appVerify['isApproved']) {
        // Get order.
          $order = \Drupal::routeMatch()->getParameter('commerce_order');
        // Create charge.
        $appIncludeCharge = $this->appIncludeCharge($order->total_price->getValue()[0]);
        if ($appIncludeCharge['isApproved']) {
          $token = array();
          // Get the default card.
          $card = json_decode($order->field_card_order->getValue()[0]['value']);
          $token['charge'] = $appIncludeCharge['chargeTokenId'];
          $token['card'] = $card->cardTokenId;
          $applyCharge['chargeTokenId'] = $token['charge'];
          // Apply charge
          $applyCharge['AppApplyCharge'] = $this->AppApplyCharge($token);
          return $applyCharge;
        }
        else {
          $message = "The user_id: " . $user->id() . " Error: The payment was not successfully: " . json_encode($appIncludeCharge);
          \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
        }
      }
      else {
        $message = "The user_id: " . $user->id() . " Error: We cannot login in the API: " . json_encode($appVerify);
        \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
      }
    }
    else {
      $message = "The user_id: " . $user->id() . " Error: The user cannot login in the API: " . json_encode($userVerify);
      \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
    }
    return t('Tuvimos problemas al realizar el pago, intente otra vez');
  }

  /**
   * Include charge to user.
   */
  public function appIncludeCharge($data) {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "applicationPassword" => (isset($config['app_pass']) ? $config['app_pass'] : NULL),
      "chargeDescription" => "Compra de productos de seguros",
      "userName" => $user->name,
      "transactionCurrency" => $data['currency_code'],
      "transactionAmount" => $data['number'],
    ];
    // Post method to add card.
    $response = \Drupal::httpClient()->post($api_base . 'AppIncludeCharge', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * Add user cards.
   *
   * @return:mixed|string
   */
  public function AppApplyCharge($token) {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "applicationPassword" => (isset($config['app_pass']) ? $config['app_pass'] : NULL),
      "userName" => $user->name,
      "chargeTokenId" => $token['charge'],
      "cardTokenId" => $token['card'],
    ];
    // Post method to add card.
    $response = \Drupal::httpClient()->post($api_base . 'AppApplyCharge', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  public function UserDeleteCard($card) {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : null),
      "applicationPassword" => (isset($config['app_pass']) ? $config['app_pass'] : null),
      "userName" => $user->name,
      "userPassword" => ucfirst($user->name . $user->id() . '*'),
      "cardTokenId" => $card,
    ];
    // Post method to add card.
    $response = \Drupal::httpClient()->post($api_base . 'UserDeleteCard', [
      'verify' => false,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, true);

    return $response;
  }

}
