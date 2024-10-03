<?php

namespace Drupal\commerce_paypal\PluginForm\Checkout;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\commerce_paypal\SmartPaymentButtonsBuilderInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Off-site form for PayPal Checkout.
 *
 * This is provided as a fallback when no "review" step is present in Checkout.
 */
class PaymentOffsiteForm extends BasePaymentOffsiteForm implements ContainerInjectionInterface {

  /**
   * The Smart payment buttons builder.
   *
   * @var \Drupal\commerce_paypal\SmartPaymentButtonsBuilderInterface
   */
  protected $builder;

  /**
   * Constructs a new PaymentOffsiteForm object.
   *
   * @param \Drupal\commerce_paypal\SmartPaymentButtonsBuilderInterface $builder
   *   The Smart payment buttons builder.
   */
  public function __construct(SmartPaymentButtonsBuilderInterface $builder) {
    $this->builder = $builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_paypal.smart_payment_buttons_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    if ($payment_gateway_plugin->getPaymentSolution() == 'smart_payment_buttons') {
      $form['paypal_smart_payment_buttons'] = $this->builder->build($payment->getOrder(), $payment->getPaymentGateway(), TRUE);
      return $form;
    }
    else {
      $order = $payment->getOrder();
      // This is normally done automatically for onsite payment gateway, but
      // since our gateway is offsite, we have to manually do it.
      $payment_gateway_plugin->createPayment($payment);
      /** @var \Drupal\commerce_checkout\Entity\CheckoutFlowInterface $checkout_flow */
      $checkout_flow = $order->get('checkout_flow')->entity;
      $checkout_flow_plugin = $checkout_flow->getPlugin();
      $redirect_step_id = $checkout_flow_plugin->getNextStepId('payment');
      $checkout_flow_plugin->redirectToStep($redirect_step_id);
    }
  }

}
