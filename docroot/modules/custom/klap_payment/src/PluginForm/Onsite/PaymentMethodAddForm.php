<?php

namespace Drupal\klap_payment\PluginForm\Onsite;

use Drupal\commerce_payment\PluginForm\PaymentMethodAddForm as BasePaymentMethodAddForm;
use Drupal\Core\Form\FormStateInterface;

class PaymentMethodAddForm extends BasePaymentMethodAddForm {

  /**
   * {@inheritdoc}
   */
  protected function buildCreditCardForm(array $element, FormStateInterface $form_state) {
    $element = parent::buildCreditCardForm($element, $form_state);
    // Just test
//    $api_mode = \Drupal::service('klap_payment.user')
//      ->userDelete();
//    kint($api_mode);
    // Default to a known valid test credit card number.
    $element['number']['#default_value'] = '4111111111111111';
    return $element;
  }

}
