<?php

namespace Drupal\ins_consume\EventSubscriber;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Cart Event Subscriber.
 */
class CartEventSubscriber implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger, CartManagerInterface $cart_manager) {
    $this->messenger = $messenger;
    $this->cartManager = $cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ORDER_ITEM_REMOVE => [['removeVariation', 100]]
    ];
  }

  /**
   * Remove a related product automatically
   *
   * @param \Drupal\commerce_cart\Event\CartOrderItemRemoveEvent $event
   *   The cart remove item event.
   *
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function removeVariation(CartOrderItemRemoveEvent $event) {
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation */
    $product_variation = $event->getOrderItem();
    $product_variation->delete(); 
  }

}