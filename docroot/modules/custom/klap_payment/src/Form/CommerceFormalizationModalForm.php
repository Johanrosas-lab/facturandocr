<?php

namespace Drupal\klap_payment\Form;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\klap_payment\Ajax\ReadMessageCommand;

/**
 * CommerceFormalizationModalForm class.
 */
class CommerceFormalizationModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_formalization_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $variation_id = \Drupal::routeMatch()->getParameter('variation_id');
    $order_id = \Drupal::routeMatch()->getParameter('order_id');
    $form['#prefix'] = '<div id="modal_example_form">';
    $form['#prefix'] .= "<div id='modal_example_form_" . $variation_id . "'>";
    $form['#suffix'] = '</div></div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];
    if (isset($variation_id) && $variation_id) {
      $variation = ProductVariation::load($variation_id);
      // Get product id.
      /**
       * @var ProductVariation $product_id
       */
      $product_id = $variation->product_id->target_id;
      // Get product.
      /**
       * @var Product $product
       */
      $product = Product::load($product_id);
      $form_fields_string = $product->field_order_fields->value;
      $form_fields_array = json_decode($form_fields_string);
      foreach ($form_fields_array as $fields_group) {
        $form[$product_id][] = $this->createField($fields_group->fields);
      }
      $form_state->set('product_id', $product_id);
      $form_state->set('variation_id', $variation_id);
      $form_state->set('order_id', $order_id);
    }

    // A required checkbox field.
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Aceptar y Guardar'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'disabled'
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = "klap_payment/klap_payment.commands";
    $form['#attached']['library'][] = "klap_payment/klap_payment.modal";

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    // If there are any form errors, re-display the form.
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    else {
      $product_id = $form_state->getStorage()['product_id'];
      $variation_id = $form_state->getStorage()['variation_id'];
      $order_id = $form_state->getStorage()['order_id'];
      $this->saveProduct($order_id, $form_state->getValues());
      $response->addCommand(new ReadMessageCommand($variation_id));
      $response->addCommand(new CloseModalDialogCommand());

    }
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.commerce_formalization_modal_form'];
  }

  /**
   * Create the basic configuration field.
   *
   * This method is used to retrieve the basic configuration fields.
   *
   * @param array $field_array
   *   Data field.
   *
   * @return array
   *   list fields.
   */
  public function createField(array $field_array) {
    $field = [];
    foreach ($field_array as $value) {
      if ($value->type === 'string') {
        $field[$value->machine_name] = [
          '#type' => 'textfield',
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => TRUE,
        ];
      }
      elseif ($value->type === 'list_string' || $value->type === 'boolean') {
        $options = [];
        if ($value->settings) {
          $settings = $value->settings->allowed_values;


          foreach ($settings as $opt) {
            $options[$opt->key] = $opt->value;
          }
        }
        else {
          $options = [
            1 => 'Si',
            0 => 'No',
          ];
        }
        $array = $options;
        reset($array);
        $field[$value->machine_name] = [
          '#type' => 'radios',
          '#title' => $value->label,
          '#options' => $options,
          '#default_value' => $value->type === 'list_string' ? key($options) : '',
          '#required' => TRUE,
        ];
      }
      elseif ($value->type === 'decimal' || $value->type === 'integer') {
        $field[$value->machine_name] = [
          '#type' => 'number',
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => TRUE,
        ];
      }
      elseif ($value->type === 'datetime') {
        $field[$value->machine_name] = [
          '#type' => 'date',
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => true,
        ];
      }
      else {
        $field[$value->machine_name] = [
          '#type' => $value->type,
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => TRUE,
        ];
      }
    }
    $this->aasort($field, "#title");
    return $field;

  }

  /**
   * Sort array by ASC
   */
  public function aasort (&$array, $key) {
      $sorter = array();
      $ret = array();
      reset($array);
      foreach ($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
      }
      asort($sorter);
      foreach ($sorter as $ii => $va) {
        $ret[$ii] = $array[$ii];
      }

      $array = $ret;
      // Remove letter alphabetic.
      foreach($array as $key => $value) {
        $title = explode(". ", $value['#title']);
        $array[$key]['#title'] = $title[1];
      }
  }

  /**
   * Complete the form and set the data in the order.
   */
  public function saveProduct($order_id, $product) {
    $fields = new \stdClass();
    foreach ($product as $key => $value) {
      if (strpos($key, 'field_') !== FALSE) {
        $fields->$key = $value;
      }
    }
    $order_item_id = \Drupal::routeMatch()->getParameter('order_item_id');
    $order = \Drupal::entityManager()->getStorage('commerce_order')->load($order_id);
    // Check if exist data.
    if (isset($order->field_contract_data_form)) {
      $field_contract = $order->field_contract_data_form->getValue()[0];
      $field_contract = json_decode($field_contract['value']);
    }
    else {
      $field_contract = new \stdClass();
    }
    $field_contract->{$order_item_id} = $fields;
    $field_data = json_encode($field_contract);
    $order->set('field_contract_data_form', $field_data);
    $order->save();
  }

}
