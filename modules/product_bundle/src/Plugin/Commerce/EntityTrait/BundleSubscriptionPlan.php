<?php

namespace Drupal\commerce_crefopay_product_bundle\Plugin\Commerce\EntityTrait;


use Drupal\commerce_crefopay\Plugin\Commerce\EntityTrait\PurchasableEntitySubscriptionPlan;

/**
 * Provides the "purchasable_bundle_entity_subscription_plan" trait.
 *
 * @CommerceEntityTrait(
 *   id = "purchasable_bundle_entity_subscription_plan",
 *   label = @Translation("Bundle Subscription Plan"),
 *   entity_types = {"commerce_product_bundle"}
 * )
 */
class BundleSubscriptionPlan extends PurchasableEntitySubscriptionPlan {

}
