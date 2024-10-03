<?php
namespace Drupal\klap_payment\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class AddCardController extends ControllerBase
{

  /**
   * Add Card.
   *
   * @return array
   *   A simple renderable array.
   */
  public function AddCard()
  {
    $cardContent = \Drupal::service('klap_payment.card')->getUserCard();
    $cardMasked = \Drupal::request()->request->get('cardMask');
    if ($cardContent['isAvailableCard']) {
      foreach ($cardContent['userCards'] as $key => $value) {
        if ($value['cardMasked'] === $cardMasked) {
          $cardFavorite = \Drupal::service('klap_payment.card')->addFavoriteCard($value['cardTokenId']);
          break;
        }
      }
    }
    return [
      '#type' => 'markup',
      '#markup' => 'Ok'
    ];
  }

}
