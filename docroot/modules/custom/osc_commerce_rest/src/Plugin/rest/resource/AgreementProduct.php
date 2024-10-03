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
 *   id = "agreement_product",
 *   label = @Translation("Load agreement by product"),
 *   uri_paths = {
 *     "canonical" = "/agreement/{id}",
 *     "https://www.drupal.org/link-relations/create" = "/agreement/{id}"
 *   }
 * )
 */
class AgreementProduct extends ResourceBase {

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
    public function get($order_id) {
        $orders = \Drupal\commerce_order\Entity\Order::load($order_id);
        $agreement_order= array();
        foreach ($orders->order_items->getValue() as $key => $value) {
            $order_item = \Drupal\commerce_order\Entity\OrderItem::load($value['target_id']);
            $variation_id = $order_item->purchased_entity->getValue()[0]['target_id'];
            $order_item_id = $order_item->order_item_id->getValue()[0]['value'];
            $variation = \Drupal::entityManager()
                ->getStorage('commerce_product_variation')
                ->load($variation_id);
            $product_id = $variation->product_id->getValue()[0]['target_id'];
            $product = \Drupal::entityTypeManager()
                ->getStorage('commerce_product')
                ->load($product_id);
            $target_id = $product->field_agreement_product->getValue()[0]['target_id'];
            $product_file = \Drupal\file\Entity\File::load($target_id);
            $uri = $product_file->uri->value;
            $url = file_create_url($uri);
            $agreement_order[$order_item_id]['url'] = $url;
        }

        return new JsonResponse($agreement_order, 200);
    }
}
