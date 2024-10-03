<?php

namespace Drupal\osc_commerce_rest\Plugin\rest\resource;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use modules\contrib\commerce\modules\product\commerce_product;

/**
 * Provides a Product Resource
 *
 * @RestResource(
 *   id = "api_products",
 *   label = @Translation("API - Products"),
 *   uri_paths = {
 *     "canonical" = "v1/products",
 *     "https://www.drupal.org/link-relations/create" = "v1/products"
 *	 }
 * )
 */

class Products extends ResourceBase
{
  /**
  * Responds to entity GET requests.
  * @return \Drupal\rest\ResourceResponse
  */
  public function get()
  {
      $storage = \Drupal::entityTypeManager()->getStorage('commerce_product');
      $result = $storage->loadByProperties(['type' => 'seguro']);
      $response = array();
      foreach ($result as $key => $entity)
      {
          $product = array(
            'product_id' => $entity->product_id->value,
            'product_title' => $entity->title->value,
            'field_branch' => $entity->field_branch->target_id,
            'field_category' => $entity->field_categoria->target_id,
            'field_modality_recruitment' => $entity->field_modality_recruitment->target_id,
            'field_participation_of_benefits' => $entity->field_participation_of_benefits->value,
            'field_provider_of_services_aux' => $entity->field_provider_of_services_aux->value,
            'field_renewable' => $entity->field_renewable->value,
            'field_service_cover' => $entity->field_service_cover->value,
            'field_solicitation_of_insurance' => $entity->field_solicitation_of_insurance->target_id,
            'field_validity' => $entity->field_validity->value,
            'field_type_contract' => $entity->field_type_contract->value,
            'field_additional_cover' => $entity->field_additional_cover->value,
            'field_insurance_carrier' => $this->getInsuraceCarrierInfo($entity->field_insurance_carrier->target_id),
            'field_type_contract' => $entity->field_type_contract->value,
            'field_order_fields' => json_decode($entity->field_order_fields->value,true),
            'variations' => $this->getVariationsInfo($entity),
            'field_register_date' => $entity->field_register_date->value,
            'field_premium_fractionation' => $entity->field_premium_fractionation->value,
            'field_fractionation_of_grace' => $entity->field_fractionation_of_grace->value
          );
          if(isset($entity->field_sugece_colons)) {
            $product['field_sugece_colons'] = $entity->field_sugece_colons->value;
            $product['field_sugece_dollars'] = 'N/A';
          }else{
            $product['field_sugece_colons'] = 'N/A';
            $product['field_sugece_dollars'] = $entity->field_sugece_dollars->value;
          }
          if(isset($entity->field_product_image->target_id)) {
              $id_image = $entity->field_product_image->target_id;
              $file = \Drupal\file\Entity\File::load($id_image);
              $url = file_create_url($file->getFileUri());
              $product['field_product_image'] = $url;
          }
          $response[] = $product;
      }
      return  new ResourceResponse($response, 200);
  }

  /**
   * Function get variation data from product.
   *
   * @param $product
   *
   * @return array
   */
  public function getVariationsInfo($product) {
    $variations = $product->getVariations();
    $variationsInfo = array();
    foreach ($variations as $key => $variation) {
        $attributes = $variation->attribute_plans->getValue();
        if($attributes){
            foreach ($attributes as $key => $attribute) {
                $attribute_value = \Drupal\commerce_product\Entity\ProductAttributeValue::load($attribute['target_id']);
                $attribute_name = $attribute_value->name->getValue();
                $variation_data[$attribute['target_id']] = array();
                $variation_data[$attribute['target_id']]['id'] = $attribute['target_id'];
                $variation_data[$attribute['target_id']]['plan'] = $attribute_name[0]['value'];
                $characteristics = $variation->field_characteristic->getValue();
                foreach ($characteristics as $key => $characteristic){
                  $paragraph = \Drupal::entityTypeManager()->getStorage('paragraph')->load($characteristic['target_id']);
                  $variation_data[$attribute['target_id']]['plans'][$key]['name'] = $paragraph->field_characteristic->value;
                  $taxonomy_price = $paragraph->field_variation_value->getValue();
                  $variation_data[$attribute['target_id']]['plans'][$key]['price'] = $taxonomy_price[0]['value'];
                }
                foreach($variation->price->getValue() as $key => $price){
                  $variation_data[$attribute['target_id']]['currency'] = $price['currency_code'];
                  $variation_data[$attribute['target_id']]['price'] = $price['number'];
                }
            }
        }
        $variationsInfo[] = array(
            'variation_id' => $variation->variation_id->value,
            'sku' => $variation->sku->value,
            'title' => $variation->title->value,
            'field_general_conditions' => $variation->field_general_conditions[0],
            'characteristics' => $variation_data
        );
    }
    return $variationsInfo;
  }

  public function getInsuraceCarrierInfo($id) {
    $node = \Drupal\node\Entity\Node::load($id);
    if(isset($node)){
      $insurance_carrier = array(
        'id'=>$node->nid->value,
        'title'=>$node->title->value
      );
      return $insurance_carrier;
    }
  }
}
