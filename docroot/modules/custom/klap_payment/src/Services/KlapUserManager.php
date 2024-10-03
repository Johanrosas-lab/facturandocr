<?php

namespace Drupal\klap_payment\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Class KlapManager.
 */
class KlapUserManager implements KlapUserManagerInterface {

  /**
   * Users Data.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Information about the Configuration API.
   *
   * @var ConfigFactoryInterface
   */
  private $mainConfig;

  /**
   * Provides an interface for entity type managers.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * KlapUserManager constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The account viewing the form.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $currentUser,
                              ConfigFactoryInterface $config,
                              EntityTypeManagerInterface $entityTypeManager) {

    $this->mainConfig = $config->get('klap.main_settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * Function get Api Mode url.
   *
   * @param \Drupal\Core\Plugin\PluginBase $payment
   *    Base class for plugins supporting metadata inspection and translation.
   *
   * @return:array \Drupal\Core\Config\ImmutableConfig|mixed|null
   */
  public function getApiModeUrl(PluginBase $payment = NULL) {
    $api_url = NULL;
    if ($payment) {
      $config = $payment->getConfiguration();
    }
    else {
      $config = $this->getKlapPluginDefinition();
    }
		// Get api url form configuration file.
    if ($config) {
      if ('test' == $config['mode']) {
        $api_url = $config['app_test_url'];
      }
      else {
        $api_url = $config['app_live_url'];
      }
    }
    return $api_url;
  }

  /**
   * Get Plugin definition klap_onsite.
   *
   * @return:boolean
   */
  public function getKlapPluginDefinition() {
    // Get plugin type commerce payment gateway.
    $gateway = \Drupal::entityTypeManager()
      ->getStorage('commerce_payment_gateway')->loadByProperties([
        'plugin' => 'klap_onsite',
      ]);
    $plugin = reset($gateway);
    // Get plugin.
    $plugin = $plugin->getPlugin();
    if ($config = $plugin->getConfiguration()) {
      return $config;
    }
    return FALSE;
  }

  /**
   * Function create new user into the klap platform.
   *
   * @return:null
   */
  public function createUser($profile_object) {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build all params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $user->getUsername(),
      "userFirstName" => $profile_object->field_client_first_name,
      "userLastName" => $profile_object->field_client_last_name,
      "userPassword" => ucfirst($user->getUsername() . $user->id() . '*'),
      "userEmail" => $user->getEmail(),
      "userCallerId" => $profile_object->field_client_phone_number,
    ];
    // Post method for creating users.
    $response = \Drupal::httpClient()->post($api_base . 'createUser', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Get response decoded.
    $response = json_decode($response, TRUE);
    return $response;
  }

  /**
   * Log on user on platform.
   *
   * @return:mixed|string
   */
  public function logOnUser() {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $user->getUsername(),
      "userPassword" => ucfirst($user->getUsername() . $user->id() . '*'),
    ];
    // Post method to log users.
    $response = \Drupal::httpClient()->post($api_base . 'logOnUser', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * Delete user form the platform.
   *
   * @return:mixed|string
   */
  public function userDelete($userDelete) {
    // Get current user.
    // $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "userName" => $userDelete->name,
      "userPassword" => ucfirst($userDelete->name . $userDelete->id . '*'),
      "userEmail" => $userDelete->email,
    ];

    // Post method to log users.
    $response = \Drupal::httpClient()->post($api_base . 'UserDeleteProfile', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

  /**
   * Log on app.
   *
   * @return:mixed|string
   */
  public function logOnApp() {
    // Get current user.
    $user = $this->currentUser->getAccount();
    // Get api url base.
    $api_base = $this->getApiModeUrl();
    // Get config from plugin klap_onsite.
    $config = $this->getKlapPluginDefinition();
    // Build Params.
    $params = [
      "applicationName" => (isset($config['app_name']) ? $config['app_name'] : NULL),
      "applicationPassword" => (isset($config['app_pass']) ? $config['app_pass'] : NULL),
    ];
    // Post method to log users.
    $response = \Drupal::httpClient()->post($api_base . 'LogOnApp', [
      'verify' => FALSE,
      'body' => json_encode($params),
      'headers' => [
        'Content-type' => 'application/json',
      ],
    ])->getBody()->getContents();
    // Decode response.
    $response = json_decode($response, TRUE);

    return $response;
  }

}
