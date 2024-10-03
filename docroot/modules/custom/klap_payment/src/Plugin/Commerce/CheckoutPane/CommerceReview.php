<?php

namespace Drupal\klap_payment\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provide the formalization pane.
 *
 * @CommerceCheckoutPane(
 *   id = "review_product",
 *   label = @Translation("Custom review product"),
 *   default_step = "payment",
 * )
 */
class CommerceReview extends CheckoutPaneBase implements CheckoutPaneInterface {


  /**
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public function getEntityTypeManager(): \Drupal\Core\Entity\EntityTypeManagerInterface {
    return $this->entityTypeManager;
  }

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function setEntityTypeManager(\Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager): void {
    $this->entityTypeManager = $entityTypeManager;
  }
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
    $this->setEntityTypeManager($entity_type_manager);
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
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $payment = \Drupal::service('klap_payment.card')->applyPayment();
    if (gettype($payment) !== 'array' && gettype($payment) === 'object') {
        $form_state->setError($pane_form, $payment->__toString());      
    } else {
        if (isset($payment['AppApplyCharge']['isApproved']) && !$payment['AppApplyCharge']['isApproved']) {
          $form_state->setError($pane_form, $this->t('Hubo un problema con el pago, intente otra vez, o pruebe con otra tarjeta.'));      
        }
        if (isset($payment['AppApplyCharge']['isApproved']) && $payment['AppApplyCharge']['isApproved']) {
          // Save the payment data in the variable
          $payment_data = new \stdClass();
          $payment_data->chargeTokenId = $payment['chargeTokenId'];
          $payment_data->reason_text = $payment['AppApplyCharge']['reasonText'];
          $payment_data->system_trace = $payment['AppApplyCharge']['systemTrace'];
          $payment_data->retrieval_ref_no = $payment['AppApplyCharge']['retrievalRefNo'];
          $payment_data->card_masked = $payment['AppApplyCharge']['cardMasked'];
          // Load order.
          $order = \Drupal::routeMatch()->getParameter('commerce_order');
          // Set the data in format JSON in field_payment_data
          $order->set('field_payment_data', json_encode($payment_data));
          // Save the order
          $order->save();

          // Show a message in the log
          $user = \Drupal::currentUser();
          $message = "The user with id: " . $user->id() . " was pay successfully with the order " . $order->id();
          \Drupal::logger('klap_payment')->info("<pre>" . print_r($message, 1) ."</pre>");
        }
    }
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
   

  }
}
