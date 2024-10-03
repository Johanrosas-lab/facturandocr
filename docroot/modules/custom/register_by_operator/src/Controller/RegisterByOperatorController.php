<?php
/**
* @file
Contains \Drupal\register_by_operator\Controller\RegisterByOperatorController. **/
namespace Drupal\register_by_operator\Controller;
use Drupal\Core\Controller\ControllerBase;

class RegisterByOperatorController extends ControllerBase {
    public function register() {
	      $operator = \Drupal::request()->query->get('operator');
        $sponsor = \Drupal::request()->query->get('sponsor');
        if (isset($operator) && $operator) {
			      if (isset($sponsor) && $sponsor) {
	    	    }
		        $entity = \Drupal::entityManager()
              ->getStorage('user')
              ->create(array());
            $formObject = \Drupal::entityManager()
              ->getFormObject('user', 'register')
              ->setEntity($entity);
            $form = \Drupal::formBuilder()->getForm($formObject);
            $variables['register_form'] = $form;
            return $form;
		    } else {
	    	    $operator = "No existe operador";
	      }
        return ['#markup' => $operator];
    }
}
