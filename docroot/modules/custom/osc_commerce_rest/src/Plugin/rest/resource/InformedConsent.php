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


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "informed_consent",
 *   label = @Translation("Informed Consent Custom"),
 *   uri_paths = {
 *     "canonical" = "/informed-consent",
 *      "https://www.drupal.org/link-relations/create" = "/informed-consent"
 *   }
 * )
 */
class InformedConsent extends ResourceBase {

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
   * Constructs a new Informed Consent.
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
    $query = \Drupal::entityQuery('flagging');
    $query->condition('flag_id', 'informed_consent_acceptance');
    $query->condition('uid', $user->id());
    $query->condition('entity_type', 'node');
    $ids = $query->execute();
    $result = array();
    if(empty($ids)){
      $result['status'] = FALSE;
      $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'informed_consent']);
      foreach ($entities as $key => $value) {
        $result['informed_consent_text'] = $value->body->getValue()[0]['value'];
        break;
      }
    }else{
      $result['status'] = TRUE;
    }

    \Drupal::logger('osc_commerce')->error("<pre>" . print_r('$variation', 1) ."</pre>");

   return new ResourceResponse($result, 200);
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
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post() {
    // You must implement the logic of your REST Resource here.
    // Use current user after it passes authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $flag_id = 'informed_consent_acceptance';
    $flag_service = \Drupal::service('flag');
    // Get the flag entity.
    $flag = $flag_service->getFlagById($flag_id);
    $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'informed_consent']);
    $result = array('status'=>FALSE);
    
    foreach ($entities as $key => $value) {
    // Flag an entity with a specific flag.
      $flag_service->flag($flag, $value);
      $result['status'] = TRUE; 
      break;
    }
    return new ModifiedResourceResponse($result, 200);
  }
}
