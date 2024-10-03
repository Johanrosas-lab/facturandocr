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
use Symfony\Component\Translation\Exception\NotFoundResourceException;


/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "order_custom",
 *   label = @Translation("Order Custom"),
 *   uri_paths = {
 *     "canonical" = "/order_custom/{order_id}",
 *     "https://www.drupal.org/link-relations/create" = "/order_custom/{order_id}"
 *
 *   }
 * )
 */

class Order extends ResourceBase
{

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
    QueryFactory $entity_query
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityQueryManager = $entity_query;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
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
   * Responds to PATCH requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse|\Symfony\Component\Translation\Exception\NotFoundResourceException
   *   The HTTP response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function patch($order_id, $body) {
      $orders = \Drupal\commerce_order\Entity\Order::load($order_id);
      if (!is_null($orders)) {
        $orders->set('field_contract_data_form', json_encode($body['field_contract_data_form']));
        $orders->set('field_card_order', json_encode($body['field_card_order']));
        $orders->set('field_payment_data', json_encode($body['field_payment_data']));
        if (isset($body['field_beneficiaries_data']) && $body['field_beneficiaries_data']) {
          $orders->set('field_beneficiaries_data', json_encode($body['field_beneficiaries_data']));
        }
        $order_state = $orders->getState();
        $order_state_transitions = $order_state->getTransitions();
        if (isset($order_state_transitions['place'])) {
          // only place an order that's a draft, otherwise just move on with the rest of the transaction
          $order_state->applyTransition($order_state_transitions['place']);
          $orders->save();
        }

        //Get payment gateway
        $paymentGateway = $this->entityTypeManager->getStorage('commerce_payment_gateway')->load('klap');

        // Store payment in Drupal
        $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
        $payment = $payment_storage->create([
          'state' => 'completed',
          'amount' => $orders->getTotalPrice(),
          'order_id' => $orders->id(),
          'payment_gateway' => $paymentGateway,
          'remote_id' => $body['field_payment_data']['chargeTokenId'],
          'remote_state' => $body['field_payment_data']['retrieval_ref_no']
        ]);
        // Store and accept the payment.
        $payment->save();

        return new ModifiedResourceResponse($orders, 200);
      } else {
        return new NotFoundResourceException(t('La Orden no existe'));
      }

  }

}
