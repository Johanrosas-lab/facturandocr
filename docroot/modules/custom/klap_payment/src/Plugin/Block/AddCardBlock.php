<?php

namespace Drupal\klap_payment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'AddCardBlock' block.
 *
 * @Block(
 *   id = "klap_add_card_block",
 *   admin_label = @Translation("Add card block"),
 *   category = @Translation("Custom add card block")
 * )
 */
class AddCardBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */

  public function build() {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    /** @var \Drupal\profile\Entity\Profile $client_profile */
    $client_profile = \Drupal::entityTypeManager()->getStorage('profile')
      ->loadByUser($user, 'client');
    if ($client_profile) {
      $form = \Drupal::formBuilder()->getForm('Drupal\klap_payment\Form\AddCardForm');
      return $form;
    }
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
