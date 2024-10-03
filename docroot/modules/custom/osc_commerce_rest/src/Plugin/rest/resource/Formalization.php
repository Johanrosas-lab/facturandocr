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
 *   id = "formalization_product",
 *   label = @Translation("Formalization Product"),
 *   uri_paths = {
 *     "canonical" = "/formalization/{order_id}",
 *     "https://www.drupal.org/link-relations/create" = "/formalization/{order_id}"
 *   }
 * )
 */

class Formalization extends ResourceBase {

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
    $formalization_data = array();
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
    // $formalization_data[$product_id] = $product->field_order_fields->getValue()[0]['value'];
      $form_fields_array = json_decode($product->field_order_fields->getValue()[0]['value']);
      foreach ($form_fields_array as $fields_group) {
        $formalization_data[$order_item_id][] = $this->createField($fields_group->fields);
      }
    }

    return new JsonResponse($formalization_data, 200);
  }

  /**
   * Create the basic configuration field.
   *
   * This method is used to retrieve the basic configuration fields.
   *
   * @param array $field_array
   *   Data field.
   *
   * @return array
   *   list fields.
   */

  function createField(array $field_array) {
    $field = [];
    foreach ($field_array as $value) {
      if ($value->type === 'string') {
        $field[$value->machine_name] = [
          '#type' => 'textfield',
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => true,
        ];
      }
      elseif ($value->type === 'list_string' || $value->type === 'boolean') {
        $options = [];
        if ($value->settings) {
          $settings = $value->settings->allowed_values;
          foreach ($settings as $opt) {
            $options[$opt->key] = $opt->value;
          }
        }
        else {
          $options = [
            1 => 'Si',
            0 => 'No',
          ];
        }
        $field[$value->machine_name] = [
          '#type' => 'radios',
          '#title' => $value->label,
          '#options' => $options,
          '#default_value' => '',
          '#required' => true,
        ];
      }
      elseif ($value->type === 'decimal' || $value->type === 'integer') {
        $field[$value->machine_name] = [
          '#type' => 'number',
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => true,
        ];
      }
      else {
        $field[$value->machine_name] = [
          '#type' => $value->type,
          '#title' => $value->label,
          '#default_value' => '',
          '#required' => true,
        ];
      }
    }
    $this->aasort($field, "#title");
    return $field;

  }

  /**
   * Sort array by ASC
   */
  function aasort(&$array, $key) {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach ($array as $ii => $va) {
      $sorter[$ii] = $va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
      $ret[$ii] = $array[$ii];
    }

    $array = $ret;
    // Remove letter alphabetic.
    foreach ($array as $key => $value) {
      $title = explode(". ", $value['#title']);
      $array[$key]['#title'] = $title[1];
    }
  }

  public function patch($order_id, $body) {
    $order = \Drupal::entityManager()->getStorage('commerce_order')->load($order_id);
    $order->set('field_contract_data_form', $body);
    $order->save();
    return new ResourceResponse('OK', 200);
  }

}
