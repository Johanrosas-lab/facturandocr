<?php
namespace Drupal\klap_payment\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class PaymentController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function listCard() {
    $element = array();
    return $element;
  }

}
