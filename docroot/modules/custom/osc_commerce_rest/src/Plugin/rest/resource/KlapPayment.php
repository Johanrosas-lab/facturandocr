<?php

namespace Drupal\osc_commerce_rest\Plugin\rest\resource;

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
 *   id = "klap_payment",
 *   label = @Translation("Klap Payment Config"),
 *   uri_paths = {
 *     "canonical" = "/klap-payment"
 *   }
 * )
 */
class KlapPayment extends ResourceBase {

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
   * Constructs a new Beneficiary.
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
  public function get() {

    // You must implement the logic of your REST Resource here.
    // Use current user after it passes authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $user = \Drupal::currentUser();
    $data = array();
    $data['userName'] = $user->getUsername();
    $data['userPassword'] = ucfirst($user->getUsername() . $user->id() . '*');
    $data['url'] = \Drupal::service('klap_payment.user')->getApiModeUrl();
    $data['app'] = \Drupal::service('klap_payment.user')->getKlapPluginDefinition();
    $response = new ResourceResponse($data, 200);
    $disable_cache = new CacheableMetadata();
    $disable_cache->setCacheMaxAge(0);
    $response->addCacheableDependency($disable_cache);
    return $response;
  }

}
