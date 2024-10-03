<?php
/**
* @file
Contains \Drupal\register_by_operator\Controller\LoginByOperatorController. **/
namespace Drupal\register_by_operator\Controller;
use Drupal\Core\Controller\ControllerBase;
use \Drupal\user\Entity\User;


class LoginByOperatorController extends ControllerBase {
  public function login() {
    $digital_agent = \Drupal::request()->query->get('digital_agent');
    $error = FALSE;
    if ($digital_agent) {
      if (!\Drupal::currentUser()->id()) {
        $user = User::load($digital_agent);
        $roles = $user->getRoles();
        $service = \Drupal::service('osc_settings.sponsor_operator_relation');
        if (in_array('op', $roles)) {
          $user_name = $service->getOperatorName($digital_agent);
        } elseif (in_array('sponsor', $roles)) {
          $user_name = $service->getSponsorName($digital_agent);
        } else {
          $error = $this->t("The digital agent is not valid.");
        }
      } else {
        $error = $this->t("Only for user anonymous");
      }
    } else {
      $error = $this->t("The id's operator is required.");
    }
    if (!$error) {
      $form = \Drupal::formBuilder()->getForm("Drupal\user\Form\UserLoginForm");
      $render = \Drupal::service('renderer');
      $login_form = $render->renderPlain($form);
    }

    return [
      '#theme' => 'osc_consumer_login',
      '#error' => $error,
      '#login_form' => isset($login_form) ? $login_form : NULL,
      '#user_name' => isset($user_name) ? $user_name : NULL
    ];
  }
}
