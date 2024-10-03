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
 *   id = "favorites",
 *   label = @Translation("Favorites"),
 *   uri_paths = {
 *     "canonical" = "/favorites/{id}"
 *   }
 * )
 */
class Favorites extends ResourceBase {

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

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $data = [];
    // Flag id.
    $flag_id = 'favorites';
    // Get the current account.
    $account = $this->currentUser->getAccount();
    // Build query for flagging entity
    $query = $this->entityQueryManager->get('flagging');
    $query->condition('flag_id', $flag_id);
    $query->condition('uid', $account->id());
    $query->condition('entity_type', 'commerce_product');
    // Execute query.
    if ($ids = $query->execute()) {
      /** @var \Drupal\flag\FlagService $flag */
      $flags = $this->entityTypeManager->getStorage('flagging')->loadMultiple($ids);
      /** @var \Drupal\flag\Entity\Flagging $flag */
      foreach ($flags as $flag) {
        // Get the flagged id.
        $flagged_entity_id = $flag->flagged_entity->first()->target_id;
        if ($flagged_entity_id) {
          // Get product.
          $product = $this->entityTypeManager->getStorage('commerce_product')
            ->load($flagged_entity_id);
          // Add the product to results.
          $data[] = $product;
        }
      }
    }

   return new ResourceResponse($data, 200);
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
  public function post($entity) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $flagged = ["Message" => "Error on product"];

    $flag_id = 'favorites';
    $flag_service = \Drupal::service('flag');
    // Get the flag entity.
    $flag = $flag_service->getFlagById($flag_id);
    if (isset($entity['product_id'])) {
      // Get product by id.
      $product = $this->entityTypeManager->getStorage('commerce_product')
        ->load($entity['product_id']);
      // Flag an entity with a specific flag.
      if ($product) {

        //Get all favorite products
        $list_favorites = $this->get_favorites();
        $product_id_favorite = array();
        foreach ($list_favorites as $key => $value) {
          $favorite = $value->product_id->getValue();
          $product_id_favorite[] = $favorite[0]['value'];
        }
        //Check if product is favorite or not
        if (in_array($entity['product_id'], $product_id_favorite )) {
          //Delete flag
          $flagged = $this->deleteFlag($entity);
        }else {
          //Create flag
          /** @var \Drupal\flag\Entity\Flag $flag_service */
          $flagged = $flag_service->flag($flag, $product);
        }


      }

    }

    return new ModifiedResourceResponse($flagged, 200);
  }

  /**
   * Responds to DELETE requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return array
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function deleteFlag($entity) {

    $flagged = ["Message" => "Error on product"];
    $flag_id = 'favorites';
    $flag_service = \Drupal::service('flag');
    // Get the flag entity.
    $flag = $flag_service->getFlagById($flag_id);
    if (!empty($entity['product_id'])) {
      // Get product by id.
      $product = $this->entityTypeManager->getStorage('commerce_product')
        ->load($entity['product_id']);
      if ($product) {
        // Get current user account.
        $account = $this->currentUser->getAccount();
        //  Unflag an entity with a specific flag.

        $flag_service->unflag($flag, $product, $account);
        $flagged = ['deleted' =>  1];
      }
    } else {
      $flagged = ["Message" => "No product as a parameter"];
    }

    return $flagged;
  }

  /**
   * Get all favorite item from user
   */
  public function get_favorites(){
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $data = [];
    // Flag id.
    $flag_id = 'favorites';
    // Get the current account.
    $account = $this->currentUser->getAccount();
    // Build query for flagging entity
    $query = $this->entityQueryManager->get('flagging');
    $query->condition('flag_id', $flag_id);
    $query->condition('uid', $account->id());
    $query->condition('entity_type', 'commerce_product');
    // Execute query.
    if ($ids = $query->execute()) {
      /** @var \Drupal\flag\FlagService $flag */
      $flags = $this->entityTypeManager->getStorage('flagging')->loadMultiple($ids);
      /** @var \Drupal\flag\Entity\Flagging $flag */
      foreach ($flags as $flag) {
        // Get the flagged id.
        $flagged_entity_id = $flag->flagged_entity->first()->target_id;
        if ($flagged_entity_id) {
          // Get product.
          $product = $this->entityTypeManager->getStorage('commerce_product')
            ->load($flagged_entity_id);
          // Add the product to results.
          $data[] = $product;
        }
      }
    }

   return $data;
  }
}
