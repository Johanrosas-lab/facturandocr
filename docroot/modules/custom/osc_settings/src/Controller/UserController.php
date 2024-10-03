<?php
/**
* @file */
namespace Drupal\osc_settings\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\core\modules\user\src\Form;
use Drupal\user\ProfileForm;

 /**
  * Controlador para devolver el contenido de las páginas definidas
  */
  class UserController extends ControllerBase {
    public function addProvider() {
      $user = \Drupal::currentUser();
      $profile = $this->entityTypeManager()->getStorage('profile')->create([
        'uid' => $user->id(),
        'type' => 'provider',
      ]);
      $form =  \Drupal::service('entity.form_builder')->getForm($profile, 'add', ['uid' => $user->id(), 'created' => REQUEST_TIME]);
      return [
        '#type' => 'markup',
        '#markup' =>\Drupal::service('renderer')->render($form)
      ];
    }
  }
