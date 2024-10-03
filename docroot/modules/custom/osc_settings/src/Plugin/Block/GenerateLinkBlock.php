<?php


  namespace Drupal\osc_settings\Plugin\Block;
  use Drupal\Core\Block\BlockBase;

  /**
   * Generate access link to sponsor/operator
   *
   * @Block(
   *   id = "generate_link_osc",
   *   admin_label = @Translation("Generar link de acceso"),
   *   category = @Translation("Generar link de acceso"),
   * )
   */
  class GenerateLinkBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */

    public function build() {
      $user = \Drupal::currentUser();
      $roles = $user->getRoles();
      if (in_array('op', $roles) || in_array('sponsor', $roles)) {
        $uid = $user->id();
        $host = \Drupal::request()->getSchemeAndHttpHost();
        $register = $host . '/cliente/registro?digital_agent=' . $uid;
        $login = $host . '/cliente/login?digital_agent=' . $uid;
      }
      return [
        '#theme' => 'osc_settings_links_block',
        '#register' => $register,
        '#login' => $login
      ];
    }

    public function getCacheMaxAge() {
      return 0;
    }
  }
