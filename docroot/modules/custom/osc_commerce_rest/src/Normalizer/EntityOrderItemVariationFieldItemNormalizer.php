<?php

namespace Drupal\osc_commerce_rest\Normalizer;

use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\Product;
use Drupal\serialization\Normalizer\ComplexDataNormalizer;

class EntityOrderItemVariationFieldItemNormalizer extends ComplexDataNormalizer {

  protected $supportedInterfaceOrClass = OrderItem::class;

  /**
   * @param \Drupal\commerce_order\Entity\OrderItem $object
   * @param null $format
   * @param array $context
   *
   * @return array|bool|float|int|string
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $values = parent::normalize($object, $format, $context);
    //Expand purchased entity
    $values['purchased_entity'] = $object->getPurchasedEntity() ?? [];

    return $values;
  }
}
