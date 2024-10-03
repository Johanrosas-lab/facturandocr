<?php

namespace Drupal\osc_commerce_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;


/**
 * Provides a Taxonomy Terms
 *
 * @RestResource(
 *   id = "api_taxonomies",
 *   label = @Translation("Api - Taxonomies"),
 *   uri_paths = {
 *     "canonical" = "/api/terms/{taxonomy}"
 *	 }
 * )
 */

class Taxonomies extends ResourceBase
{
  /**
  * Responds to entity GET requests.
  * @return \Drupal\rest\ResourceResponse
  */
  public function get($taxonomy)
  {
    switch ($taxonomy) {
      case 'branches':
        $taxonomy = 'ramo';
        break;
      case 'currencies':
        $taxonomy = 'moneda';
        break;
    }
    try {
      $terms = \Drupal::service('entity_type.manager')
      ->getStorage("taxonomy_term")
      ->loadTree($taxonomy, $parent = 0, $max_depth = NULL, $load_entities = TRUE);
      $branches = array();

      foreach ($terms as $term) {
       $branches[] = array(
        "id" => $term->tid->value,
        "name" => $term->name->value,
        "parents" => $term->parents[0],
        "image" => $term->field_image[0]?:""
       );
      }
      $response = $branches;
    } catch (Exception $e) {
      $response['error'] = t("Terms not found");
    }

    return  (new ResourceResponse($response));
  }
}
