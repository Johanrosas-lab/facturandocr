<?php

namespace Drupal\ins_payment\Services;

class ConfigOnsite {

  /**
   * Get commerce ins_payment_onsite config.
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getConfig() {
    // Get plugin type commerce payment gateway.
    $gateway = \Drupal::entityTypeManager()
      ->getStorage('commerce_payment_gateway')->loadByProperties([
        'plugin' => 'ins_payment_onsite',
      ]);
    $plugin = reset($gateway);
    // Get plugin.
    $plugin = $plugin->getPlugin();
    if ($config = $plugin->getConfiguration()) {
      return $config;
    }
    \Drupal::logger('INS_PAYMENT')
      ->error('<pre>' . print_r('No found any payment related ins_payment installed', 1) . '</pre>');
    return FALSE;
  }
}
