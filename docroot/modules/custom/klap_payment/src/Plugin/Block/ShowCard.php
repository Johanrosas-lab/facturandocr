<?php

namespace Drupal\klap_payment\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'list_card' block.
 *
 * @Block(
 *   id = "klap_list_card_block",
 *   admin_label = @Translation("Show cards"),
 *   category = @Translation("Custom list cards")
 * )
 */
class ShowCard extends BlockBase {
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
        '#markup' => $this->t('Necesitas crear un perfil para ver esta opción.'),
      );
      if ($userVerify['isApproved']) {
        $cardContent = \Drupal::service('klap_payment.card')->getUserCard();
        if (!$cardContent['cardsCount']) {
          $message = "The user_id: " . $user->id() . " Error: The user don't have cards: " . json_encode($cardContent);
          \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
          $result = array(
            '#markup' => $this->t('No tienes tarjetas agregadas'),
          );
        }
        else {
          $result = \Drupal::formBuilder()->getForm('Drupal\klap_payment\Form\AddDefaultCardForm');
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
        '#markup' => "
                      <div class='no-info-msg'>
                        <p class='exclamation'>¡!</p>
                        <p class='message'>Llena los campos del perfil para activar tus medios de pago.</p>
                        <a href='#'>Llenar campos</a>
                      </div>",
      );
    }
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
