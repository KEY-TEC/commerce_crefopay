<?php

namespace Drupal\commerce_crefpay\Plugin\Commerce\EntityTrait;

use Drupal\entity\BundleFieldDefinition;
use Drupal\commerce\Plugin\Commerce\EntityTrait\EntityTraitBase;

/**
 * Provides the "order_item_subscription" trait.
 *
 * @CommerceEntityTrait(
 *   id = "order_item_crefopay_subscription",
 *   label = @Translation("Subscription"),
 *   entity_types = {"commerce_order_item"}
 * )
 */
class OrderItemSubscription extends EntityTraitBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['rental_quantity'] = BundleFieldDefinition::create('commerce_rental_quantity')
      ->setLabel(t('Rental Quantity'))
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
