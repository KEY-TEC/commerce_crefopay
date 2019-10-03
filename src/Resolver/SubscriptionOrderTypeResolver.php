<?php

namespace Drupal\commerce_crefopay\Resolver;

use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Returns the order type, based on order item type configuration.
 */
class SubscriptionOrderTypeResolver implements OrderTypeResolverInterface {

  /**
   * The order item type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $orderTypeStorage;

  /**
   * The config provider.
   *
   * @var Drupal\commerce_crefopay\ConfigProviderInterface
   */
  protected $configProvider;

  /**
   * Constructs a new SubscriptionOrderTypeResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigProviderInterface $config_provider) {
    $this->orderTypeStorage = $entity_type_manager->getStorage('commerce_order_type');
    $this->configProvider = $config_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(OrderItemInterface $order_item) {
    $purchased_product = $order_item->getPurchasedEntity();
    if ($purchased_product->hasField('field_subscription_plan' &&
      $purchased_product->field_subscription_plan->entity != NULL) ||
      $purchased_product->hasField('crefopay_subscription_plan') &&
      $purchased_product->crefopay_subscription_plan->value != NULL) {
      $order_type_id = $this->configProvider->getSubscriptionOrderTypeId();
      $order_type = $this->orderTypeStorage->load($order_type_id);
      if (empty($order_type) == FALSE) {
        return $order_type->id();
      }
    }
  }

}
