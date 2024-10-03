<?php

namespace Drupal\osc_settings\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "entity_legal",
 *   label = @Translation("Entity legal"),
 *   uri_paths = {
 *     "canonical" = "/legacy/document/{id}",
 *     "https://www.drupal.org/link-relations/create" = "/legacy/document"
 *   }
 * )
 */
class EntityLegal extends ResourceBase {

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
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a new EntityLegal object.
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
    Request $currentRequest) {
    parent::__construct($configuration, $plugin_id, $plugin_definition,
      $serializer_formats, $logger, $entityTypeManager
    );
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentRequest = $currentRequest;
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
      $container->get('logger.factory')->get('osc_settings'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest()
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

    $published_version = ['Message' => 'Error, Not legal document found'];
    $check = $this->currentRequest->query->get('check');

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    // Get entity legal document by id.
    $document = $this->entityTypeManager->getStorage('entity_legal_document')
      ->load($entity);
    if ($document) {
      $published_version = $document->getPublishedVersion();
    }

    return new ResourceResponse($published_version, 200);
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
    // Get entity legal document by id.

    if (isset($entity['document_id'])) {
      $document = $this->entityTypeManager
        ->getStorage('entity_legal_document')->load($entity['document_id']);

      if ($document) {
        // Get last published document.
        $published_version = $document->getPublishedVersion();
        // Compare users id.
        if ($entity['user_id'] == $this->currentUser->id()) {
          // Store the accept of the user.
          $this->entityTypeManager
            ->getStorage('entity_legal_document_acceptance')
            ->create([
              'document_version_name' => $published_version->id(),
            ])
            ->save();
          $published_version = $published_version->id();
        }
        else {
          $published_version = ['Message' => t("The user_id is not the same in the current session")];
        }
      } else {
        $published_version = ['Message' => t("The legal document_id is not the correct")];
      }
    }
    else {
      $published_version = ['Message' => t("The legal document_id must be included")];
    }

    return new ModifiedResourceResponse($published_version, 200);
  }

}
