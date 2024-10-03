<?php

namespace Drupal\osc_commerce_extra\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CartMessages.
 */
class CartMessages extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'osc_commerce_extra.cartmessages',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cart_messages';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('osc_commerce_extra.cartmessages');
    $form['cart_add_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Add a message when one product is added to the cart'),
      '#default_value' => $config->get('cart_add_message'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('osc_commerce_extra.cartmessages')
      ->set('cart_add_message', $form_state->getValue('cart_add_message'))
      ->save();
  }

}
