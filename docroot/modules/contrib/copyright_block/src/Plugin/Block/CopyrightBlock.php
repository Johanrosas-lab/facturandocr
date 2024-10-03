<?php

namespace Drupal\copyright_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Adds a copyright block.
 *
 * @Block(
 *   id = "copyright_block",
 *   admin_label = @Translation("Copyright block"),
 * )
 */
class CopyrightBlock extends BlockBase implements BlockPluginInterface {

  public function settings() {
    return $this->settings;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['start_year'] = [
      '#title' => $this->t('Start year'),
      '#type' => 'number',
      '#min' => '1900',
      '#max' => date('Y'),
      '#required' => TRUE,
      '#default_value' => $config['start_year'],
    ];

    $form['seperator'] = [
      '#title' => $this->t('Seperator'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $config['seperator'],
    ];

    $form['text'] = [
      '#title' => $this->t('Copyright statement text'),
      '#type' => 'text_format',
      '#required' => TRUE,
      '#default_value' => $config['text']['value'],
      '#format' => $config['text']['format'],
    ];

    $form['token_tree'] = [
      '#theme' => 'token_tree_link',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('start_year', $form_state->getValue('start_year'));
    $this->setConfigurationValue('seperator', $form_state->getValue('seperator'));
    $this->setConfigurationValue('text', $form_state->getValue('text'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $token = \Drupal::token();

    // Token data.
    $text = $token->replace($config['text']['value'], [], compact('config'));

    return [
      '#markup' => $text,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('copyright_block.settings');

    return [
      'start_year' => date('Y'),
      'seperator' => $default_config->get('seperator'),
      'text' => [
        'value' => $default_config->get('text.value'),
        'format' => $default_config->get('text.format'),
      ],
    ];
  }
}
