<?php

namespace Drupal\klap_payment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class AddDefaultCardForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'klap_payment_add_default_card_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cardContent = \Drupal::service('klap_payment.card')->getUserCard();
    $options = array();
    $default = '';
    foreach ($cardContent['userCards'] as $key => $value) {
      $options[$value['cardMasked']] = "<p class='klap_payment-type klap_payment-type-" . $value['cardDescription'] .  "'>" .  "</p>";
      $options[$value['cardMasked']] .= "<p class='klap_payment-number'>" . $value['cardMasked'] . "</p>";
      $options[$value['cardMasked']] .= "<p class='klap_payment-month'> <span>Fecha de expiracion: </span>" . $value['expirationMonth'] . "</p>";
      $options[$value['cardMasked']] .= "<p class='klap_payment-date'> / " . $value['expirationYear'] . "</p>";
      $options[$value['cardMasked']] .= "<span class='klap_payment-checked'></span>";
      // $options[$value['cardMasked']] .= "<span class='klap_payment-remove' data-toggle='tooltip' data-placement='top' title='Eliminar tarjeta'><i class='fas fa-trash-alt'></i></span>";
      if ($value['isDefault']) {
        $default = $value['cardMasked'];
      }
    }
    $form['userCards'] = array(
      '#type' => 'fieldset',
      '#title' => t('Seleccionar tarjetas registradas:'),
    );
    $form['userCards']['options'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $default,
    ];
    // $form['#attached']['library'][] = "klap_payment/klap_payment.commands";
    $form['#attached']['library'][] = "klap_payment/klap_payment.card";
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
