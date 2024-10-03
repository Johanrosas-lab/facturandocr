<?php

namespace Drupal\osc_settings\Theme;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;

/**
 * Class Themer.
 */
class Themer implements ThemerInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;
  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Themer constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   */
    public function __construct(ConfigFactory $config_factory,
                                EntityTypeManagerInterface $entityTypeManager) {
      $this->config = $config_factory;
      $this->entityTypeManager = $entityTypeManager;
    }

  /**
   * Hook preprocess menu account.
   *
   * @param array $variables
   */
  function theme_preprocess_menu__account(array &$variables) {

    // Get menu items form menu account.
    if ($menu_items = (isset($variables['items']) ? $variables['items'] : NULL)) {
      if ($first_item = $menu_items[key($menu_items)]) {
        //  Get profile storage
        $profileStorage = $terms = \Drupal::service('entity_type.manager')
          ->getStorage("profile");

        // Get Client profile.
        if ($client_profile = $profileStorage->loadByUser($variables['user'], 'client')) {
          // Build the name
          $first_name = $client_profile->get('field_client_first_name')->value;
          $last_name = $client_profile->get('field_client_last_name')->value;
          if ($first_name && $last_name) {
            $first_item['title'] = $first_name . ' ' . $last_name;
          }
          else {
            $first_item['title'] = t('Mi cuenta');
          }
        }
        // Get service provider profile
        elseif ($service_provider = $client_profile = $profileStorage->loadByUser($variables['user'], 'service_provider')) {
          // Build the name
          $first_name = $service_provider->get('field_provider_first_name')->value;
          $last_name = $service_provider->get('field_provider_last_name')->value;
          if ($first_name && $last_name) {
            $first_item['title'] = $first_name . ' ' . $last_name;
          }
          else {
            $first_item['title'] = t('Mi cuenta');
          }
        }
        else {
          $first_item['title'] = t('Mi cuenta');
        }
        // Set new name to the link title
        $variables['items'][key($menu_items)] = $first_item;
        $variables['user_picture'] = $this->getUserPicture();

      }
    }
  }

  /**
   * Get and render user picture.
   *
   * @return array|null
   */
  function getUserPicture($id = NULL) {
    $image = NULL;

    if ($id) {
      // Use uid.
      $uid = $id;
    }
    else {
      // Getting the actual user uid.
      $uid = \Drupal::service('current_user')->id();
    }

    // Getting the actual user entity.
    /** @var \Drupal\user\Entity\User $user */
    $user = \Drupal::service('entity.manager')->getStorage('user')->load($uid);
    // Getting the user picture.
    if ($user) {
      if ($user->hasField('user_picture') && !$user->user_picture->isEmpty()) {
        $picture = $user->user_picture->entity->getFileUri();
      }
      else {
        // Get default user image.
        $default_image = $user->user_picture->getDataDefinition()
          ->getSetting('default_image');

        if ($default_image) {
          // Get file default image.
          $entityrepository = \Drupal::service('entity.repository');
          $defaultImageFile = $entityrepository->loadEntityByUuid('file', $default_image['uuid']);
          $picture = $defaultImageFile->getFileUri();
        }
      }

      if (!empty($picture)) {
        $image = [
          '#theme' => 'image',
          '#uri' => $picture,
          '#image_style_id' => 'thumbnail',
          '#attributes' => ['class' => ['img-circle']],
          '#alt' => '',
          '#title' => 'User',
          '#attached' => [],
        ];
      }
    }

    return $image;
  }

  /**
   * Implement preprocess praragraph().
   *
   * @param $variables
   */
  function theme_preprocess_paragraph__products_by_category_block(&$variables) {

    $paragraph = $variables['paragraph'];

    $args = array_map(function ($item) {
      return $item['target_id'];
    }, $paragraph->field_products_category_category->getValue());

    $variables['view_block'] = [
      '#type' => 'view',
      '#name' => 'products_by_category',
      '#display_id' => 'block_1',
      '#arguments' => [implode('+', $args)],
    ];
  }

  /**
   * Implements hook_preprocess_paragraph().
   *
   * @param $variables
   */
  function theme_preprocess_paragraph__download_app_cta(&$variables) {
    $paragraph = $variables['paragraph'];

    if (!$paragraph->field_download_link_type->isEmpty()) {
      $variables['type'] = $paragraph->field_download_link_type->value;
    }
    if (!$paragraph->field_download_link_link->isEmpty()) {
      $variables['link'] = $paragraph->field_download_link_link->uri;
    }
    if (!$paragraph->field_download_link_link->isEmpty()) {
      $variables['title'] = $paragraph->field_download_link_link->title;
    }
  }

  /**
   * Implements hook_preprocess_view().
   *
   * @param $variables
   */
  function theme_preprocess_views_view__products_by_category__block(&$variables) {
    // Get flag service.
    $flag = \Drupal::service('flag.link_builder');
    foreach ($variables['rows'] as &$row) {
      if (isset($row['items']) && count($row['items']) > 0) {
        foreach ($row['items'] as &$item) {
          if (isset($item['slide']['#item'])) {
            if (is_object($item['slide']['#item'])) {
              // Build flag link to each slider.
              $id = $item['slide']['#item']->getEntity()->id();
              $flag_link = $flag->build('commerce_product', $id, 'favorites');
              $variables['flag_link'] = $flag_link;
              $item['flag'] = $flag_link;
            }
            elseif (is_object($item['slide']['#build']['item'])) {
              $id = $item['slide']['#build']['item']->getEntity()->id();
              $flag_link = $flag->build('commerce_product', $id, 'favorites');
              $variables['flag_link'] = $flag_link;
              $item['flag'] = $flag_link;
            }
          }
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_theme().
   *
   * @param $variables
   */
  function theme_preprocess_views_view__suggestions__block(&$variables) {
    $flag = \Drupal::service('flag.link_builder');
    foreach ($variables['rows'] as &$row) {
      if (isset($row['items']) && count($row['items']) > 0) {
        foreach ($row['items'] as &$item) {
          if (isset($item['slide']['#item'])) {
            if (is_object($item['slide']['#item'])) {
              // Build flag link to each slider.
              $id = $item['slide']['#item']->getEntity()->id();
              $flag_link = $flag->build('commerce_product', $id, 'favorites');
              $variables['flag_link'] = $flag_link;
              $item['flag'] = $flag_link;
            }
            elseif (is_object($item['slide']['#build']['item'])) {
              $id = $item['slide']['#build']['item']->getEntity()->id();
              $flag_link = $flag->build('commerce_product', $id, 'favorites');
              $variables['flag_link'] = $flag_link;
              $item['flag'] = $flag_link;
            }
          }
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_theme().
   *
   * @param $variables
   */
  function theme_preprocess_commerce_product__seguro(&$variables) {

    $request = \Drupal::request();
    $back_link = $request->headers->get('referer');
    $variables['back_link'] = $back_link;

    $block = \Drupal\block\Entity\Block::load('shareeverywhereblock');
    $variables['share_everywhere'] = \Drupal::entityTypeManager()
      ->getViewBuilder('block')
      ->view($block);

    $block = \Drupal\block\Entity\Block::load('printfriendly');
    $variables['print_pdf'] = \Drupal::entityTypeManager()
      ->getViewBuilder('block')
      ->view($block);

    // $term = \Drupal::entityTypeManager()
    //   ->getStorage('taxonomy_term')
    //   ->loadByProperties(['name' => $variables['product']['field_categoria'][0]['#plain_text']]);
    // $vocabulary_id = $term;
    // foreach ($term as $key => $value) {
    //   $vocabulary_id = $key;
    // }

    // $variables['product_suggestion'] = [
    //   '#type' => 'view',
    //   '#name' => 'related_products',
    //   '#display_id' => 'block_1',
    //   '#arguments' => [$vocabulary_id],
    // ];

    // // Get cart message from config.
    // $config = \Drupal::config('osc_commerce_extra.cartmessages');
    // $variables['cart_message'] = $config->get('cart_add_message');
  }

  /**
   * Implements hook_preprocess_theme().
   *
   * @param $variables
   */
  function theme_preprocess_commerce_product_attribute_value__plans(&$variables) {
    $attribute = $variables['product_attribute_value_entity'];
    $variation_data = [];
    // Get current product by the url
    if ($product = \Drupal::routeMatch()->getParameter('commerce_product')) {
      if ($product->type->first()->getValue()['target_id'] == 'seguro') {
        // Get variations of the product
        if ($variations = $product->variations) {
          foreach ($variations as $variation) {
            // Get entity variation
            $variation_entity = $variation->entity;
            // Compare the current attribute with the product.
            if ($attribute->attribute_value_id->value == $variation_entity->attribute_plans->getValue()[0]['target_id']) {
              foreach ($variation_entity->field_characteristic as $paragraph) {
                // Get paragraph value from the variation.
                $paragraph_entity = $paragraph->entity;
                // Build data variation.
                $variation_data[] = [
                  'label' => $paragraph_entity->field_characteristic->value,
                  'value' => $paragraph_entity->field_variation_value->value,
                ];
              }
            }
          }
        }
      }
    }
    $variables['variations_data'] = $variation_data;
  }


  /**
   * Hook theme_preprocess_form();
   *
   * @param $variables
   */
  function theme_preprocess_add_to_cart_form(&$variables) {
    $variation_data = [];
    if ($product = \Drupal::routeMatch()->getParameter('commerce_product')) {
      if ($product->type->first()->getValue()['target_id'] == 'seguro') {
        // Get variations of the product
        if ($variation = $product->variations->first()) {
          // Get entity variation
          $variation_entity = $variation->entity;
          $title = $variation_entity->title->getValue();
          foreach ($variation_entity->field_characteristic as $paragraph) {
            // Get paragraph value from the variation.
            $paragraph_entity = $paragraph->entity;
            // Build data variation.
            $variation_data[] = [
              'label' => $paragraph_entity->field_characteristic->value,
              'value' => $paragraph_entity->field_variation_value->value,
              'title' => $title[0]['value'],
              'price' => $variation_entity->price->number,
            ];
          }
        }
      }
    }
    $variables['variations_data'] = $variation_data;
  }

  function theme_preprocess_user(&$variables) {

    $user = $variables['user'];
    /** @var \Drupal\profile\Entity\Profile $client_profile */
    $client_profile = $this->entityTypeManager->getStorage('profile')
      ->loadByUser($user, 'client');
    if ($client_profile) {
      $profile = new \stdClass();
      $profile->image = $this->getUserPicture($user->id());
      $profile->mail = $user->mail->value;
      $profile->name = $client_profile->field_client_first_name->value .
        ' ' . $client_profile->field_client_last_name->value;
      $profile->phone = $client_profile->field_client_phone_number->value;
      $profile->address_one = ((!$client_profile->field_client_address->isEmpty())
        ? $client_profile->field_client_address->view() : NULL);
      $profile->academic_level = (!$client_profile->field_client_academic_level->isEmpty()
        ? $client_profile->field_client_academic_level->view() : NULL);
      $profile->profession = (!$client_profile->field_client_profession->isEmpty()
        ? $client_profile->field_client_profession->view() : NULL);
      $profile->family_members_count = (!$client_profile->field_client_family_core_count->isEmpty()
        ? $client_profile->field_client_family_core_count->view() : NULL);
      $profile->professionals_count = (!$client_profile->field_client_count_professionals->isEmpty()
        ? $client_profile->field_client_count_professionals->view() : NULL);
      $profile->children_count = (!$client_profile->field_client_children_count->isEmpty()
        ? $client_profile->field_client_children_count->view() : NULL);
      $profile->travel_option = (!$client_profile->field_client_travel_option->isEmpty()
        ? $client_profile->field_client_travel_option->view() : NULL);
      $profile->health_care = (!$client_profile->field_client_health_type->isEmpty()
        ? $client_profile->field_client_health_type->view() : NULL);
      //social links
      $twitter_link = ($client_profile->field_twitter->getValue());
      $instagram_link = ($client_profile->field_instagram->getValue());
      $facebook_link = ($client_profile->field_facebook->getValue());
      $profile->user_facebook = $facebook_link[0]['platform_values']['facebook']['value'];
      $profile->user_twitter = $twitter_link[0]['platform_values']['twitter']['value'];
      $profile->user_instagram = $instagram_link[0]['platform_values']['instagram']['value'];

      $variables['profile'] = $profile;
      $variables['user_id'] = $user->id();


      // Generate user edit popup link.
      $options = [
        'title' => t('<i class="glyphicon glyphicon-pencil"></i>'),
        'url' => Url::fromRoute('profile.user_page.single',
          ['profile_type' => 'client', 'user'=> $user->id()],
          ['query' => array('destination' => '/user'), 'absolute' => TRUE,]),
      ];
      $variables['edit_link'] = $this->generatePopupLink($options);
      // Generate popup content link for address book.
      $options = [
        'title' => t('<i class="glyphicon glyphicon-pencil"></i>'),
        'url' => Url::fromRoute('profile.user_page.multiple',
          ['profile_type' => 'customer', 'user'=> $user->id()],
          ['query' => array('destination' => '/user'), 'absolute' => TRUE,]),
      ];
      $variables['edit_address'] = $this->generatePopupLink($options, FALSE);

    } else {
      // The user don't have a profile
      $profile = new \stdClass();
      $profile->image = $this->getUserPicture($user->id());
      $profile->mail = $user->mail->value;
      $variables['profile'] = $profile;
      $variables['user_id'] = $user->id();

      // Generate user edit popup link.
      $options = [
        'title' => t('<i class="glyphicon glyphicon-pencil"></i>'),
        'url' => Url::fromRoute('profile.user_page.single',
          ['profile_type' => 'client', 'user'=> $user->id()],
          ['query' => array('destination' => '/user'), 'absolute' => TRUE,]),
      ];
      $variables['edit_link'] = $this->generatePopupLink($options);
      // Generate popup content link for address book.
      $options = [
        'title' => t('<i class="glyphicon glyphicon-pencil"></i>'),
        'url' => Url::fromRoute('profile.user_page.multiple',
          ['profile_type' => 'customer', 'user'=> $user->id()],
          ['query' => array('destination' => '/user'), 'absolute' => TRUE,]),
      ];
      $variables['edit_address'] = $this->generatePopupLink($options, FALSE);

    }
    // Set default address from customer profiles by user.
    $customer_default = $this->getUserDefaultProfileFromMultiple('customer', $user);
    if (!empty($customer_default->address->address_line1)) {
      $variables['default_address'] = $customer_default->address->view();
    }
  }

  /**
   * Get default profile from multiple profiles by type.
   *
   * @param $profile_type
   * @param $user
   *
   * @return null
   */
   function getUserDefaultProfileFromMultiple($profile_type, $user) {
    $profiles = $this->entityTypeManager->getStorage('profile')
      ->loadMultipleByUser($user, $profile_type);
    if ($profiles) {
      foreach ($profiles as $profile) {
        if ($profile->isDefault()) {
          return $profile;
        }
      }
    }
    return NULL;
  }

  /**
   * Generate link popup of content.
   *
   * @param $options
   *
   * @return array
   */
  private function generatePopupLink($options, $popup = TRUE) {
     $link = [];
     if ($options) {
        $link = array(
          '#type' => 'link',
          '#title' => $options['title'],
          '#url' => $options['url'],
        );
        if ($popup) {
          $link['#attributes'] = [
            'class' => ['use-ajax popup-link'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 700,
            ]),
          ];
        }
     }
     return $link;
  }

}

