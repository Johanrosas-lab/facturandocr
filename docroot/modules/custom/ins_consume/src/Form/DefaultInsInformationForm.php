<?php
/**
 * @file
 * Contains Drupal\ins_consume\Form\DefaultInsInformationForm.
 */
namespace Drupal\ins_consume\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class DefaultInsInformationForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ins_consume.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'default_information_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ins_consume.adminsettings');
    $form['default_company'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empresa'),
      '#default_value' => $config->get('default_company'),
      '#description' => $this->t("Dato proveido por el INS")
    ];
    $form['default_system'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sistema'),
      '#default_value' => $config->get('default_system'),
      '#description' => $this->t("Dato proveido por el INS")
    ];
    $form['default_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Direcciones'),
      '#default_value' => $config->get('default_address'),
      '#description' => $this->t("Dato proveido por el INS")
    ];
    $form['default_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL del servicio'),
      '#default_value' => $config->get('default_url'),
      '#description' => $this->t("Dato proveido por el INS")
    ];
    $form['default_url_consume'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL del servicio a consumir'),
      '#default_value' => $config->get('default_url_consume'),
      '#description' => $this->t("Dato proveido por el INS")
    ];
    $form['default_user_information'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Usuario'),
      '#default_value' => $config->get('default_user_information'),
      '#description' => $this->t("Identificador de usuario ante el sistema del INS")
    ];
    return parent::buildForm($form, $form_state);
  }
   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('ins_consume.adminsettings')
      ->set('default_company', $form_state->getValue('default_company'))
      ->set('default_url', $form_state->getValue('default_url'))
      ->set('default_system', $form_state->getValue('default_system'))
      ->set('default_address', $form_state->getValue('default_address'))
      ->set('default_user_information', $form_state->getValue('default_user_information'))
      ->set('default_url_consume', $form_state->getValue('default_url_consume'))
      ->save();
  }
}
