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
 *   id = "formalization_product",
 *   label = @Translation("Formalization Product"),
 *   default_step = "order_information",
 * )
 */
class CommerceFormalization extends CheckoutPaneBase implements CheckoutPaneInterface {

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
    if (isset($this->order)) {
      // Order items: array.
      $order_items = $this->order->getItems();
      $order_id = $this->order->order_id->getValue()[0]['value'];
      $field_contract_data_form = [];
      if (isset($this->order->field_contract_data_form) && $this->order->field_contract_data_form) {
        $field_contract_data_form = json_decode($this->order->field_contract_data_form->value);
      }
      $pane_form['#prefix'] = "<h3 class='formalization-title'>Formalización de seguro</h3>";
      $pane_form['#prefix'] .= "<p class='formalization-warning'>Llenar el formulario y leer el contrato, son campos obligatorios para poder continuar con la compra.</p>";
      // Load item by item.
      foreach ($order_items as $key => $value) {
        $order_item_id = $value->order_item_id->getValue()[0]['value'];
        $variation_id = $value->purchased_entity->target_id;
        $pane_form[$key]['#prefix'] = "<div class='product-wrapper' id='product_form_" . $value->purchased_entity->target_id . "'>";
        $pane_form[$key]['#prefix'] .= "<div class='product-form-title'>" . $value->getTitle() . "</div>";
        $pane_form[$key]['open_modal'] = [
          '#type' => 'link',
          '#title' => $this->t('Llenar el formulario'),
          '#url' => Url::fromRoute('klap_payment.open_modal_form', ['order_item_id' => $order_item_id, 'order_id' => $order_id, 'variation_id' => $variation_id]),
          '#attributes' => [
            'class' => [
              'use-ajax',
              'button',
            ],
          ],
        ];

        if (isset($field_contract_data_form->$order_item_id)) {
          $pane_form[$key]['open_modal']['#suffix'] = "<i class='fas fa-times hidden'></i>";
          $pane_form[$key]['open_modal']['#suffix'] .= "<i class='fas fa-check '></i>";
        }
        else {
          $pane_form[$key]['open_modal']['#suffix'] = "<i class='fas fa-times'></i>";
          $pane_form[$key]['open_modal']['#suffix'] .= "<i class='fas fa-check hidden'></i>";
        }
        $variation = \Drupal::entityManager()
          ->getStorage('commerce_product_variation')
          ->load($variation_id);
        // Get product id.
        $product_id = $variation->product_id->target_id;
        // Get product.
        $product = \Drupal::entityTypeManager()
          ->getStorage('commerce_product')
          ->load($product_id);
        $product_file = \Drupal\file\Entity\File::load($product->field_general_conditions->getValue()[0]['target_id']);
        $uri = $product_file->uri->value;
        $file_url = file_create_url($uri);
        $pane_form[$key]['open_modal']['#suffix'] .= "<div class='field-contract'><a href='" . $file_url . "' target='_blank'>Leer el contrato</a></div>";
        $pane_form[$key]['accept'] = array(
          '#title' => '',
          '#type' => 'checkboxes',
          '#description' => '',
          '#options' => ['He leído y acepto el contrato del seguro'],
          '#required' => true,
        );
        $pane_form[$key]['#suffix'] = "</div>";
      }
    }
    // Attach the library for pop-up dialogs/modals.
    $pane_form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order_items = $this->order->getItems();
    $order_id = $this->order->order_id->getValue()[0]['value'];
    $field_contract_data_form = [];
    if (isset($this->order->field_contract_data_form) && $this->order->field_contract_data_form) {
      $field_contract_data_form = json_decode($this->order->field_contract_data_form->value);
    }
    foreach ($order_items as $key => $value) {
      $order_item_id = $value->order_item_id->value;
      if (!isset($field_contract_data_form->$order_item_id)) {
        $form_state->setError($pane_form, $this->t('Debes llenar todos los formularios.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
  }
}
