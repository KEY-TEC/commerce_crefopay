<?php

namespace Drupal\commerce_crefpay\Plugin\Commerce\EntityTrait;

use Drupal\entity\BundleFieldDefinition;
use Drupal\commerce\Plugin\Commerce\EntityTrait\EntityTraitBase;

/**
 * Provides the "order_item_subscription" trait.
 *
 * @CommerceEntityTrait(
 *   id = "order_crefopay_subscription",
 *   label = @Translation("Order type used for a crefopay subscription"),
 *   entity_types = {"commerce_order"}
 * )
 */
class OrderSubscription extends EntityTraitBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['crefopay_is_subscription'] = BundleFieldDefinition::create('boolean')
      ->setLabel(t('Subscription Order '))
      ->setDescription(t('Rental Quantity'))
      ->setCardinality(-1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'rental_quantity_default',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
