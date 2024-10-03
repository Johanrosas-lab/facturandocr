<?php
namespace Drupal\osc_settings\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\user\Entity\User;
/**
* Implements the Simple form controller.
*
* @see \Drupal\Core\Form\FormBase
*/
class EditProfilePicture extends FormBase {
  public function buildForm(array $form, FormStateInterface $form_state) {
    //Loads current user
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    //Saves user inside form_state
    $form_state->set('user', $user);
    //Gets user profile picture
    //The profile is new
    if ($user->user_picture->isEmpty()) {
      $picture = '/sites/oscseguros.dev.dd/files/default_images/user_1.JPG';
    }
    else {
      $picture = $user->get('user_picture')->entity->url();
    }
    //Shows current profile picture
    $form['image'] = array(
      '#markup' => "<img src='$picture' alt=''>",
    );
    $form['profile_image'] = [
      '#type' => 'managed_file',
      '#title' => t('Profile Picture'),
      '#upload_validators' => array(
          'file_validate_extensions' => array('gif png jpg jpeg'),
          'file_validate_size' => array(25600000),
      ),
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://pictures/' . date('Y-m'),
      '#required' => FALSE,

   ];
   $form['actions'] = [
       '#type' => 'actions',
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => t('Eliminar foto'),
      '#submit' => array('::deleteProfilePicture'),
      '#attributes' => array('class' => array('btn-delete-picture')),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Guardar'),
      '#attributes' => array('class' => array('btn-save-picture')),
   ];
     return $form;
  }
  public function getFormId() {
     return 'osc_settings_edit_profile_picture';
  }
  public function deleteProfilePicture(array &$form, FormStateInterface $form_state) {
    $field = \Drupal\field\Entity\FieldConfig::loadByName('user', 'user', 'user_picture');
    $default_image = $field->getSetting('default_image');
    $file = \Drupal::entityManager()->loadEntityByUuid('file', $default_image['uuid']);
    $picture=file_create_url($file->getFileUri());
    $user = $form_state->get('user');
      //Saves image on user profile
    $user->set('user_picture',
              ['target_id' => $file->id(),
              'alt' => $this->t('Member photo')]);
    $user->save();
  }
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if(!$form_state->getSubmitHandlers()){
        if(!$form_state->getValue('profile_image')){
          $form_state->setErrorByName('profile_image', $this->t("Please select a picture"));
        }
    }
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //Gets image
    $image = $form_state->getValue('profile_image');
    if (!empty($image)) {
      //Uploads image
      $file = File::load($image[0]);
      /** @var \Drupal\user\Entity\User $user */
      $user = $form_state->get('user');
      //Saves image on user profile
      $user->set('user_picture', [
      'target_id' => $file->id(),
      'alt' => $this->t('Member photo'),
       ]);
      $user->save();
    }
  }
}
