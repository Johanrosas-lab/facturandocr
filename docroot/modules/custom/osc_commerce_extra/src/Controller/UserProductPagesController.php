<?php

namespace Drupal\osc_commerce_extra\Controller;

/**
 * @file
 * Contains \Drupal\osc_commerce_extra\Controller\UserProductPagesController.
 */

use Drupal\Core\Controller\ControllerBase;

/**
 * Controlador para devolver el contenido de las páginas definidas.
 */
class UserProductPagesController extends ControllerBase {

  /**
   * LoadVariation function.
   *
   * @param[$variation_id]
   *   Get the variation by.
   */
  public function loadVariation($variation_id) {
    $variation = \Drupal::entityManager()
      ->getStorage('commerce_product_variation')
      ->load($variation_id);
    $product_id = $variation->product_id->getValue()[0]['target_id'];
    $product = \Drupal::entityTypeManager()
      ->getStorage('commerce_product')
      ->load($product_id);
    if (isset($product->field_insurance_restrictions)) {
      $build['#field_insurance_restrictions'] = $product->field_insurance_restrictions->getValue()[0]['value'];
    }
    if (isset($product->field_general_conditions)) {
      $product_file = \Drupal\file\Entity\File::load($product->field_general_conditions->getValue()[0]['target_id']);
      $uri = $product_file->uri->value;
      $file_url = file_create_url($uri);
      $build['#field_general_conditions'] = $file_url;
    }
    if (isset($product->field_product_image)) {
      $product_file = \Drupal\file\Entity\File::load($product->field_product_image->getValue()[0]['target_id']);
      $uri = $product_file->uri->value;
      $file_url = file_create_url($uri);
      $build['#field_product_image'] = $file_url;
    }

    $build['#title'] = "Detalles del seguro";
    $build['#title_product'] = $variation->title->getValue()[0]['value'];
    $build['#price'] = $variation->price->getValue()[0];
    $build['#theme'] = 'osc_commerce_extra_user_variation';
    return $build;

  }

}
