<?php

namespace Drupal\klap_payment\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

/**
 * Provide the formalization pane.
 *
 * @CommerceCheckoutPane(
 *   id = "payment_method",
 *   label = @Translation("Payment Method"),
 *   default_step = "review",
 * )
 */
class CommercePaymentMethod extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cardContent = \Drupal::service('klap_payment.card')->getUserCard();
    $options = [];
    $default = '';
    foreach ($cardContent['userCards'] as $key => $value) {
      $options[$value['cardMasked']] = "<p class='klap_payment-type klap_payment-type-" . $value['cardDescription'] . "'>" . "</p>";
      $options[$value['cardMasked']] .= "<p class='klap_payment-number'>" . $value['cardMasked'] . "</p>";
      $options[$value['cardMasked']] .= "<p class='klap_payment-month'> <span>Fecha de expiracion: </span>" . $value['expirationMonth'] . "</p>";
      $options[$value['cardMasked']] .= "<p class='klap_payment-date'> / " . $value['expirationYear'] . "</p>";
      $options[$value['cardMasked']] .= "<span class='klap_payment-checked'></span>";
      if ($value['isDefault']) {
        $default = $value['cardMasked'];
      }
    }
    $pane_form['userCards'] = [
      '#type' => 'fieldset',
      '#title' => t('Seleccionar tarjetas registradas:'),
    ];
    $pane_form['userCards']['options'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $default,
    ];
    // $form['#attached']['library'][] = "klap_payment/klap_payment.commands";
    $pane_form['#attached']['library'][] = "klap_payment/klap_payment.card";
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cardContent = \Drupal::service('klap_payment.card')->getUserCard();
    $userCard = $form_state->getValue('payment_method');
    $order = $this->order;
    foreach ($cardContent['userCards'] as $key => $value) {
      if ($userCard['userCards']['options'] == $value['cardMasked']) {

        $card_json = json_encode($value);
        if (isset($order->field_card_order) && $order->field_card_order) {
          $order->set('field_card_order', '');
        }
        $order->set('field_card_order', $card_json);
        $order->save();
        break;
      }
    }
  }

}
