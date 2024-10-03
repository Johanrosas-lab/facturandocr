<?php

namespace Drupal\klap_payment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

// use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class AddCardForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'klap_payment_add_card_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    /** @var \Drupal\profile\Entity\Profile $client_profile */
    $client_profile = \Drupal::entityTypeManager()->getStorage('profile')
      ->loadByUser($user, 'client');
    if ($client_profile) {
      $form['owner_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Nombre del titular'),
        '#required' => TRUE,
        '#size' => 40,
        '#maxlength' => 255,
        '#attributes' => ['placeholder' => ['Como aparece en la tarjeta']],
      ];
      $form['number_card'] = [
        '#type' => 'number',
        '#title' => $this->t('Número de la tarjeta'),
        '#required' => TRUE,
        '#size' => 40,
        '#maxlength' => 255,
        '#attributes' => ['placeholder' => ['Sin espacion ni guiones']],
      ];
      $form['year_expired'] = [
        '#type' => 'number',
        '#required' => TRUE,
        '#size' => 40,
        '#attributes' => ['placeholder' => ['Año']],
      ];
      $form['month_expired'] = [
        '#type' => 'number',
        '#required' => TRUE,
        '#size' => 40,
        '#attributes' => ['placeholder' => ['Mes']],
      ];
      $form['security_code'] = [
        '#type' => 'number',
        '#title' => $this->t('Código de seguridad (cvv)'),
        '#required' => TRUE,
        '#size' => 40,
        '#attributes' => ['placeholder' => ['3 digitos']],
      ];
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Registrar tarjeta'),
        '#button_type' => 'primary',
      );
      return $form;
    }
    else {
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('Debes de llenar todos los campos del perfil para pode acceder a este sistema'), $messenger::TYPE_WARNING);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!$this->validatecard($form_state->getValue('number_card'))) {
      $form_state->setErrorByName('Número de tarjeta invalida', $this->t('La tarjeta que ingreso es invalida.'));
    }
    if (strlen($form_state->getValue('year_expired')) !== 4) {
      $form_state->setErrorByName('Año', $this->t('El año que ingreso es invalido.'));
    }
    if (strlen($form_state->getValue('month_expired')) !== 2) {
      $form_state->setErrorByName('Mes', $this->t('El mes que ingreso es invalido.'));
    }
    if (strlen($form_state->getValue('security_code')) !== 3) {
      $form_state->setErrorByName('Código de seguridad', $this->t('El número de tarjeta de seguridad es invalido.'));
    }
    $userVerify = \Drupal::service('klap_payment.user')->logOnUser();
    if (!$userVerify['isApproved']) {
      $form_state->setErrorByName('Error', $this->t('Tenemos un error en el sistema, vuelva a intentarlo por favor.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $dataCard = array(
      'cardDescription' => $this->validatecard($form_state->getValue('number_card')),
      'primaryAccountNumber' => $form_state->getValue('number_card'),
      'expirationMonth' => $form_state->getValue('month_expired'),
      'expirationYear' => $form_state->getValue('year_expired'),
      'verificationValue' => $form_state->getValue('security_code'),
    );
    $result = \Drupal::service('klap_payment.card')->addUserCard($dataCard);
    if ($result['apiStatus'] === "Successful") {
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('La tarjeta ha sido agregada con exito!'));     
    }
    else {
      if ($result['apiStatus'] === "Already Exist") {
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('No se pudo agregar la tarjeta con exito, la tarjeta ya existe.'), $messenger::TYPE_ERROR); 
      }
      else {
        $message = "The user: " . \Drupal::currentUser()->id() . " was not able to create cards:" . json_encode($result);
        \Drupal::logger('klap_payment')->error("<pre>" . print_r($message, 1) . "</pre>");
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('No se pudo agregar la tarjeta con exito'), $messenger::TYPE_ERROR); 
      }
    }
  }

  /**
   * That validates a credit card.
   */
  public function validatecard($number) {
    $type = '';

    $cardtype = array(
      "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
      "mastercard" => "/^5[1-5][0-9]{14}$/",
      "amex"       => "/^3[47][0-9]{13}$/",
      "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
    );

    if (preg_match($cardtype['visa'], $number)) {
      $type = "visa";
      return 'visa';
    }
    elseif (preg_match($cardtype['mastercard'], $number)) {
      $type = "mastercard";
      return 'mastercard';
    }
    elseif (preg_match($cardtype['amex'], $number)) {
      $type = "amex";
      return 'amex';
    }
    elseif (preg_match($cardtype['discover'], $number)) {
      $type = "discover";
      return 'discover';
    }
    else {
      return FALSE;
    }
  }

}
