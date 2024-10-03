<?php

namespace Drupal\osc_commerce_rest\Normalizer;

use Drupal\commerce_product\Entity\Product;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\serialization\Normalizer\ComplexDataNormalizer;

class EntityProductVariationFieldItemNormalizer extends ComplexDataNormalizer {

  protected $supportedInterfaceOrClass = Product::class;

  /**
   * @param Product $object
   * @param null $format
   * @param array $context
   *
   * @return array|bool|float|int|string
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $values = parent::normalize($object, $format, $context);

    $product_variations = $object->getVariations();
    // Delete variations array key, as we're about to replace it
    unset($values['variations']);
    $values['field_order_fields'] = json_decode($values['field_order_fields'][0]['value']);
    foreach ($product_variations as $variation) {
      // Set variations with all of their fields
      $values['variations'][] = $variation->toArray();
    }
    // Transcode product characteristics
    $_characteristic = [];
    if (isset($values['variations'])) {
      foreach ($values['variations'] as $index => $single_variation) {
        foreach ($single_variation['field_characteristic'] as  $cindex => $characteristic) {
          try {
            $paragraph = end(\Drupal::entityTypeManager()
              ->getStorage('paragraph')
              ->loadByProperties(['id' => $characteristic['target_id']]));
            $_characteristic['field_characteristic'] = $paragraph->field_characteristic->value;
            $_characteristic['field_variation_value'] = $paragraph->field_variation_value->value;
            // Set new characteristics
            $values['variations'][$index]['field_characteristic'][$cindex] = $_characteristic;
          } catch (InvalidPluginDefinitionException $e) {
          } catch (PluginNotFoundException $e) {
          }
        }
      }
    }

    return $values;
  }
}
