<?php

namespace Drupal\klap_payment\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'default card' block.
 *
 * @Block(
 *   id = "klap_payment_default_card_block",
 *   admin_label = @Translation("Show default cards"),
 *   category = @Translation("Custom Default cards")
 * )
 */
class ShowDefaultCard extends BlockBase {
  /**
   * {@inheritdoc}
  */

  public function build() {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    /** @var \Drupal\profile\Entity\Profile $client_profile */
    $client_profile = \Drupal::entityTypeManager()->getStorage('profile')
      ->loadByUser($user, 'client');
    if ($client_profile) {
      $userVerify = \Drupal::service('klap_payment.user')->logOnUser();
      $result = array(
        '#markup' => $this->t('Llena los campos del perfil para ver esta opción.'),
      );
      if (isset($userVerify['isApproved']) && $userVerify['isApproved']) {
        $cardContent = \Drupal::service('klap_payment.card')->getDefaultUserCard();
        if ($cardContent['cardTokenId'] === "" && $cardContent['apiStatus'] === "No Default Card") {
          $message = "The user_id: " . $user->id() . " Error: The user don't have cards: " . json_encode($cardContent);
          \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
          $result = array(
            '#markup' => $this->t('No tienes tarjetas agregadas'),
          );
        }
        else {
          $result = array(
            '#type_card' => $cardContent['cardDescription'],
            '#mask_card' => $cardContent['maskedCard'],
            '#year_card' => $cardContent['expirationYear'],
            '#month_card' => $cardContent['expirationMonth'],
          );
        }
      }
      else {
        $message = "The user_id: " . $user->id() . " Error: The user cannot login in the API: " . json_encode($userVerify);
        \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
      }
      return $result;
    }
    else {
      $message = "The user_id: " . $user->id() . " Error: Don't have profile: " . json_encode($client_profile);
      \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
      return array(
        '#markup' => $this->t('Llena los campos del perfil para ver esta opción.'),
      );
    }
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
