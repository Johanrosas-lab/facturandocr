<?php

namespace Drupal\osc_commerce_extra\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * @CommerceCheckoutFlow(
 *  id = "osc_commerce_extra_checkout_flow",
 *  label = @Translation("OSC Extra Checkoutflow."),
 * )
 */
class OscCommerceExtraCheckoutFlow extends CheckoutFlowWithPanesBase {


  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    return [
        'formalizacion' => [
          'label' => $this->t('Formalización'),
          'previous_label' => $this->t('Regresar al carrito'),
          'has_order_summary' => FALSE,
        ],
        'resumen' => [
          'label' => $this->t('Resumen'),
          'previous_label' => $this->t('Volver al resumen de compra'),
          'has_order_summary' => TRUE,
        ],
        'pago' => [
          'label' => $this->t('Pago'),
          'previous_label' => $this->t('Volver a la formalización de seguro'),
          'has_order_summary' => FALSE,
        ],
        'revision' => [
          'label' => $this->t('Revisión'),
          'previous_label' => $this->t('Volver a la forma de pago'),
          'has_order_summary' => FALSE,
        ]
      ] + parent::getSteps();
  }
}
