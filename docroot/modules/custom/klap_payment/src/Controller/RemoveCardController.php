<?php
namespace Drupal\klap_payment\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class RemoveCardController extends ControllerBase {

  /**
   * Remove Card.
   *
   * @return array
   *   A simple renderable array.
   */
  public function removeCard() {
    $cardContent = \Drupal::service('klap_payment.card')->getUserCard();
    $cardMasked = \Drupal::request()->request->get('cardMask');
    if ($cardContent['isAvailableCard']) {
      foreach ($cardContent['userCards'] as $key => $value) {
        if($value['cardMasked'] === $cardMasked) {
            \Drupal::service('klap_payment.card')->UserDeleteCard($value['cardTokenId']);
        }
      }
    }
    return [
        '#type' => 'markup',
        '#markup' => 'Ok'
      ];
  }

}
