<?php

namespace Drupal\klap_payment\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;

/**
 * Provides the completion message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "completion_message_custom",
 *   label = @Translation("Completion message Custom"),
 *   default_step = "complete",
 * )
 */
class CompletionMessageCustom extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['#theme'] = 'commerce_checkout_completion_message_custom';
    $pane_form['#order_entity'] = $this->order;
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $pane_form['#user_email'] = $user->getEmail();
    return $pane_form;
  }

}
