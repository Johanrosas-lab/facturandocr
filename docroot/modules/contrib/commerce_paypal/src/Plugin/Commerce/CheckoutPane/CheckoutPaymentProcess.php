<?php

namespace Drupal\commerce_paypal\Plugin\Commerce\CheckoutPane;

use Drupal\commerce\Response\NeedsRedirectException;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_paypal\Plugin\Commerce\PaymentGateway\CheckoutInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the PayPal Checkout payment process pane.
 *
 * This is required to capture/authorize payment when in the "shortcut" flow.
 *
 * @CommerceCheckoutPane(
 *   id = "paypal_checkout_payment_process",
 *   label = @Translation("PayPal Checkout payment process"),
 *   default_step = "payment"
 * )
 */
class CheckoutPaymentProcess extends CheckoutPaneBase {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new CheckoutPaymentProcess object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);

    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $checkout_flow,
      $container->get('entity_type.manager'),
      $container->get('logger.channel.commerce_paypal')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    if ($this->order->isPaid() || $this->order->getTotalPrice()->isZero()) {
      // No payment is needed if the order is free or has already been paid.
      return FALSE;
    }
    if ($this->checkoutFlow->getPluginId() !== 'paypal_checkout' ||
      empty($this->order->getData('commerce_paypal_checkout')) ||
      $this->order->get('payment_gateway')->isEmpty()) {
      return FALSE;
    }
    $checkout_data = $this->order->getData('commerce_paypal_checkout');
    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway */
    $payment_gateway = $this->order->payment_gateway->entity;
    return $checkout_data['flow'] == 'shortcut' && $payment_gateway->getPlugin() instanceof CheckoutInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway */
    $payment_gateway = $this->order->payment_gateway->entity;
    $payment_gateway_plugin = $payment_gateway->getPlugin();
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $payment_storage->create([
      'state' => 'new',
      'amount' => $this->order->getBalance(),
      'payment_gateway' => $payment_gateway->id(),
      'order_id' => $this->order->id(),
    ]);
    $next_step_id = $this->checkoutFlow->getNextStepId($this->getStepId());

    try {
      $payment_gateway_plugin->createPayment($payment);
      $this->checkoutFlow->redirectToStep($next_step_id);
    }
    catch (PaymentGatewayException $e) {
      $this->logger->error($e->getMessage());
      $message = $this->t('We encountered an unexpected error processing your payment. Please try again later.');
      $this->messenger()->addError($message);
      $this->redirectToCart();
    }
  }

  /**
   * Redirect to cart in case of a PaymentGatewayException exception.
   */
  protected function redirectToCart() {
    $this->order->get('checkout_flow')->setValue(NULL);
    $this->order->get('checkout_step')->setValue(NULL);
    $this->order->unlock();
    $this->order->save();
    throw new NeedsRedirectException(Url::fromRoute('commerce_cart.page')->toString());
  }

}
