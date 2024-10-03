<?php

namespace Drupal\osc_settings\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Drupal\social_auth\SocialAuthUserManager;
//use Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "facebook_login",
 *   label = @Translation("Facebook login"),
 *   uri_paths = {
 *     "canonical" = "/facebook/login",
 *     "https://www.drupal.org/link-relations/create" = "/facebook/login"
 *   }
 * )
 */
class FacebookLogin extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  /**
   * The user manager.
   *
   * @var \Drupal\social_auth\SocialAuthUserManager
   */
  private $userManager;
  /**
   * The Facebook Persistent Data Handler.
   *
   * @var \Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler
   */
//  private $persistentDataHandler;

  /**
   * Constructs a new FacebookLogin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
//  public function __construct(
//    array $configuration,
//    $plugin_id,
//    $plugin_definition,
//    array $serializer_formats,
//    LoggerInterface $logger,
//    AccountProxyInterface $current_user,
//    SocialAuthUserManager $user_manager,
//    FacebookAuthPersistentDataHandler $persistent_data_handler) {
//    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
//
//    $this->currentUser = $current_user;
//    $this->userManager = $user_manager;
//    $this->persistentDataHandler = $persistent_data_handler;
//    // Sets the plugin id.
//    $this->userManager->setPluginId('social_auth_facebook');
//
//    // Sets the session keys to nullify if user could not logged in.
//    $this->userManager->setSessionKeysToNullify([
//      $this->persistentDataHandler->getSessionPrefix() . 'access_token',
//    ]);
//
//  }

  /**
   * {@inheritdoc}
   */
//  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
//    return new static(
//      $configuration,
//      $plugin_id,
//      $plugin_definition,
//      $container->getParameter('serializer.formats'),
//      $container->get('logger.factory')->get('osc_settings'),
//      $container->get('current_user'),
//      $container->get('social_auth.user_manager'),
//      $container->get('social_auth_facebook.persistent_data_handler')
//    );
//  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($entity) {

//    $email = $entity['email'];
//    $name = $entity['name'];
//    $id = $entity['id'];
//    $picture_url = $entity['picture'];
//    $session = ['Message' => 'Error on login'];
//    // Saves access token to session so that event subscribers can call FB API.
//    $this->persistentDataHandler->set('access_token', $entity['token']);
//
//    $user_login = $this->userManager->authenticateUser($email, $name, $id, $picture_url);
//
//    /** @var \Drupal\user\Entity\User $user */
//    $user = $this->currentUser->getAccount();
//
//    if ($user) {
//      $user_session = \Drupal::request()->getSession();
//
//      if ($user_session) {
//        $session = [
//          "token_type" => "Bearer",
//          "access_token" => $entity['token'],
//          "mail" => $email,
//          "username" => $user->getUsername(),
//          "user_id" => $user->id()
//        ];
//      }
//    }
//
//    return new ModifiedResourceResponse($session, 200);
  }

}
