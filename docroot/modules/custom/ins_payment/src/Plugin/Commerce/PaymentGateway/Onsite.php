<?php
namespace Drupal\ins_payment\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Exception\HardDeclineException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Onsite Checkout payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "ins_payment_onsite",
 *   label = @Translation("INS Payment"),
 *   display_label = @Translation("INS Payment"),
 *    forms = {
 *     "add-payment" = "Drupal\ins_payment\PluginForm\Onsite\PaymentMethodAddForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   modes = {
 *     "SANDBOX" = @Translation("SANDBOX"),
 *     "PRODUCTION" = @Translation("PRODUCTION"),
 *   },
 *   credit_card_types = {
 *     "mastercard", "visa", "amex",
 *   },
 * )
 */
class Onsite extends OnsitePaymentGatewayBase implements OnsiteInterface {
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PaymentTypeManager $payment_type_manager, PaymentMethodTypeManager $payment_method_type_manager, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'client_id' => '',
        'client_secret' => '',
        'format_xml' => '',
        'format_pem' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['api_base_test'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Base Path TEST'),
      '#description' => $this->t('The api base url from INS'),
      '#default_value' => $this->configuration['api_base_test'],
      '#size' => 60,
      '#required' => TRUE,
    ];
    $form['api_base_live'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Base Path LIVE'),
      '#description' => $this->t('The api base url from INS'),
      '#default_value' => $this->configuration['api_base_live'],
      '#size' => 60,
      '#required' => TRUE,
    ];
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('This information is getting from INS'),
      '#default_value' => $this->configuration['client_id'],
      '#size' => 40,
      '#required' => TRUE,
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('This information is getting from INS'),
      '#default_value' => $this->configuration['client_secret'],
      '#size' => 60,
      '#required' => TRUE,
    ];
    $form['format_xml'] = [
      '#type' => 'textarea',
      '#title' => $this->t('XML Base 64 Key'),
      '#description' => $this->t('Archivo dado por el INS'),
      '#default_value' => $this->configuration['format_xml'],
      '#required' => TRUE,
    ];
    $form['format_pem'] = [
      '#type' => 'textarea',
      '#title' => $this->t('PEM Format Key'),
      '#description' => $this->t('Archivo dado por el INS'),
      '#default_value' => $this->configuration['format_pem'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['client_id'] = $values['client_id'];
    $this->configuration['client_secret'] = $values['client_secret'];
    $this->configuration['format_xml'] = $values['format_xml'];
    $this->configuration['format_pem'] = $values['format_pem'];
    $this->configuration['api_base_test'] = $values['api_base_test'];
    $this->configuration['api_base_live'] = $values['api_base_live'];
  }

  /**
   * @inheritDoc
   */
  public function createPayment(PaymentInterface $payment, $capture = TRUE) {

    $this->assertPaymentState($payment, ['new']);
    $payment_method = $payment->getPaymentMethod();
    $this->assertPaymentMethod($payment_method);
    $billing_profile = $payment_method->getBillingProfile();
    // Validate correct billing information.
    if ($billing_profile) {
      /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $billing_address */
      $billing_address = $billing_profile->get('address')->first();
      if (empty($billing_address->getAddressLine1())) {
        throw new HardDeclineException('The payment was declined. You need to put the correct information');
      }
    }
    else {
      throw new HardDeclineException('The payment was declined. You need to put the correct information');
    }
    $message = $this->t('We encountered an error processing your payment method. Please verify your details and try again.');
    try {
      // Get ammount.
      $amount = number_format($payment->getAmount()->getNumber(),2);
      // Send data to api and get response id.
      $response = \Drupal::service('ins_payment.transaction_manager')->saleTransaction($payment, $payment_method);

      if ($response) {
        $payment->setRemoteId($response);
        $next_state = $capture ? 'completed' : 'authorization';
        $payment->setState($next_state);
        $tempstore = \Drupal::service('user.private_tempstore')->get('ins_payment');
        $tempstore->set('data', []);
        $payment->save();
      }

      else {
        $this->messenger()->addError($message);
        $order = \Drupal\commerce_order\Entity\Order::load($payment->getOrderId());
        $checkout_flow = $order->get('checkout_flow')->first()->get('entity')->getTarget()->getValue()->getPlugin();
        $step_id = $checkout_flow->getPane('payment_information')->getStepId();
        // Redirect to payment method form.
        $checkout_flow->redirectToStep($step_id);
      }
    }
    catch (DeclineException $e) {
      $this->messenger()->addError($message);
      $order = \Drupal\commerce_order\Entity\Order::load($payment->getOrderId());
      $checkout_flow = $order->get('checkout_flow')->first()->get('entity')->getTarget()->getValue()->getPlugin();
      $step_id = $checkout_flow->getPane('payment_information')->getStepId();
      $checkout_flow->redirectToStep($step_id);
    }
    catch (PaymentGatewayException $e) {
      $this->messenger()->addError($message);
      $order = \Drupal\commerce_order\Entity\Order::load($payment->getOrderId());
      $checkout_flow = $order->get('checkout_flow')->first()->get('entity')->getTarget()->getValue()->getPlugin();
      $step_id = $checkout_flow->getPane('payment_information')->getStepId();
      $checkout_flow->redirectToStep($step_id);
    }
  }

  /**
   * @inheritDoc
   */
  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    $required_keys = [
      // The expected keys are payment gateway specific and usually match
      // the PaymentMethodAddForm form elements. They are expected to be valid.
      'type', 'number', 'expiration',
    ];
    foreach ($required_keys as $required_key) {
      if (empty($payment_details[$required_key])) {
        throw new \InvalidArgumentException(sprintf('$payment_details must contain the %s key.', $required_key));
      }
    }

    // Check is billing information is valid.
    if ($billing_profile = $payment_method->getBillingProfile()) {
      /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $billing_address */
      $billing_address = $billing_profile->get('address')->first();
      if (empty($billing_address)) {
        throw new HardDeclineException('The payment method was declined. Please check your information');
      }
    }
    // Build card data.
    $cardData = [
      'number' => $payment_details['number'],
      'number_code' => $payment_details['security_code']
    ];

    $config = \Drupal::service('ins_payment.config_onsite')->getConfig();
    // Encrypt data.
    $data_encrypted = \Drupal::service('ins_payment.prepare_data')
      ->prepareEncryptedData($cardData, $config);
    // Save encrypted data in user private_tempstore
    $tempstore = \Drupal::service('user.private_tempstore')->get('ins_payment');
    $tempstore->set('data', $data_encrypted);

    $payment_method->card_type = $payment_details['type'];
    $payment_method->set('card_number', substr($payment_details['number'], -4));
    $payment_method->set('card_exp_month', $payment_details['expiration']['month']);
    $payment_method->set('card_exp_year', $payment_details['expiration']['year']);
    // Set payment with 60 seconds.
    $expires = $this->time->getRequestTime() + (3600 * 3) - 60;
    $payment_method->setExpiresTime($expires);

    $payment_method->save();


  }

  /**
   * @inheritDoc
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    // Delete the local entity.
    $payment_method->delete();

  }

  /**
   * @inheritDoc
   */
  public function capturePayment(PaymentInterface $payment, Price $amount = NULL) {
  }

  /**
   * @inheritDoc
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    $this->assertPaymentState($payment, ['completed', 'partially_refunded']);
    // If not specified, refund the entire amount.
    $amount = $amount ?: $payment->getAmount();

    $this->assertRefundAmount($payment, $amount);
    // Determine whether payment has been fully or partially refunded.
    $old_refunded_amount = $payment->getRefundedAmount();
    $new_refunded_amount = $old_refunded_amount->add($amount);
    if ($new_refunded_amount->lessThan($payment->getAmount())) {
      $payment->setState('partially_refunded');
    }
    else {
      $payment->setState('refunded');
    }

    $payment->setRefundedAmount($new_refunded_amount);
    $payment->save();
  }

  /**
   * @inheritDoc
   */
  public function voidPayment(PaymentInterface $payment) {
    $this->assertPaymentState($payment, ['authorization']);
    // Perform the void request here, throw an exception if it fails.
    $payment->setState('authorization_voided');
    $payment->save();
  }
}
