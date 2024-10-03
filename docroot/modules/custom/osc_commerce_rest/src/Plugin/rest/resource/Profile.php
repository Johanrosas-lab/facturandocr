<?php
namespace Drupal\osc_commerce_rest\Plugin\rest\resource;

// use Drupal\Core\Config\ConfigFactory;
// use Drupal\Core\Entity\EntityTypeManagerInterface;
// use Drupal\rest\Plugin\ResourceBase;
// use Drupal\rest\ResourceResponse;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Cache\CacheableMetadata;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "profiles",
 *   label = @Translation("Profile Custom test"),
 *   uri_paths = {
 *     "canonical" = "/profile",
 *     "https://www.drupal.org/link-relations/create" = "/profile"
 *
 *   }
 * )
 */

class Profile extends ResourceBase {

   /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity query manager injected into the service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  private $entityQueryManager;
  /**
   * Constructs a new Favorites object.
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
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entityTypeManager,
    QueryFactory $entity_query) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityQueryManager = $entity_query;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('osc_commerce_rest'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('entity.query')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($entity) {
    //Load user
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

    //Load profile data.
    /** @var \Drupal\profile\Entity\Profile $client_profile */
    $client_profile = $this->entityTypeManager->getStorage('profile')
      ->loadByUser($user, 'client');

    // Remove cache.
    $response = new ResourceResponse($client_profile, 200);
    $disable_cache = new CacheableMetadata();
    $disable_cache->setCacheMaxAge(0);
    $response->addCacheableDependency($disable_cache);
    
    return $response;
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function post($entity) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }


    $user_id = $this->currentUser->id();
    $client_profile = array(
      'type' => 'client',
      'uid' => $user_id,
      'status' => TRUE,
      'field_client_id' => $entity['field_client_id'],
      'field_client_first_name' => $entity['field_client_first_name'],
      'field_client_last_name' => $entity['field_client_last_name'],
      'field_client_birthday' => $entity['field_client_birthday'],
      'field_client_phone_number' => $entity['field_client_phone_number'],
      'field_client_address' => $entity['field_client_address']
    );
    $profile_data = \Drupal::entityManager()->getStorage('profile')->create($client_profile);
    $profile_data->save();
    return new ModifiedResourceResponse($profile_data, 201);
  }

  /**
   * Responds to PATCH requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function patch($entity) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $user = \Drupal::currentUser();
    $profile = \Drupal::entityTypeManager()->getStorage('profile')->loadByUser($user, 'client');
    if ($profile) {
      foreach ($entity as $key => $value) {
        if (gettype($value) === 'array' && isset($value[0])) {
          $profile->set($key, $value[0]);
        }
        else {
          $profile->set($key, $value);
        }
      }
    }
    $profile->save();
    return new ModifiedResourceResponse($profile, 200);
  }

}
