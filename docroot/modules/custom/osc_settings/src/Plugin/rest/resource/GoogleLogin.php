<?php

namespace Drupal\osc_settings\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
//use Drupal\social_auth\SocialAuthUserManager;
//use Drupal\social_auth_google\GoogleAuthManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "google_login",
 *   label = @Translation("Google login"),
 *   uri_paths = {
 *     "canonical" = "/google/login",
 *     "https://www.drupal.org/link-relations/create" = "/google/login"
 *   }
 * )
 */
class GoogleLogin extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
//   * The Google authentication manager.
//   *
//   * @var \Drupal\social_auth_google\GoogleAuthManager
//   */
//  protected $googleManager;
//
//  /**
//   * The user manager.
//   *
//   * @var \Drupal\social_auth\SocialAuthUserManager
//   */
//  protected $userManager;
//  /**
//   * The session manager.
//   *
//   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
//   */
//  protected $session;

  /**
   * Constructs a new GoogleLogin object.
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
//    GoogleAuthManager $google_manager,
//    SessionInterface $session) {
//    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
//    $this->userManager = $user_manager;
//    $this->googleManager = $google_manager;
//    $this->currentUser = $current_user;
//    $this->session = $session;
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
//    return new static(
//      $configuration,
//      $plugin_id,
//      $plugin_definition,
//      $container->getParameter('serializer.formats'),
//      $container->get('logger.factory')->get('osc_settings'),
//      $container->get('current_user'),
//      $container->get('social_auth.user_manager'),
//      $container->get('social_auth_google.manager'),
//      $container->get('session')
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

//    // You must to implement the logic of your REST Resource here.
//    // Use current user after pass authentication to validate access.
//    if (!$this->currentUser->hasPermission('access content')) {
//      throw new AccessDeniedHttpException();
//    }
//
//    $email = $entity['email'];
//    $name = $entity['name'];
//    $id = $entity['id'];
//    $picture_url = $entity['picture'];
//    $session = ['Message' => 'Error on login'];
//
//    $this->session->set('social_auth_google_access_token', $entity['token']);
//
//    $this->userManager->authenticateUser($email, $name, $id, $picture_url);
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
//
//    return new ModifiedResourceResponse($session, 200);
  }

}
