<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\EntityTrait;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\entity\BundleFieldDefinition;
use Drupal\commerce\Plugin\Commerce\EntityTrait\EntityTraitBase;

/**
 * Provides the "purchasable_entity_subscription_plan" trait.
 *
 * @CommerceEntityTrait(
 *   id = "purchasable_entity_subscription_plan",
 *   label = @Translation("Subscription Plan"),
 *   entity_types = {"commerce_product_variation"}
 * )
 */
class PurchasableEntitySubscriptionPlan extends EntityTraitBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['crefopay_subscription_plan'] = BundleFieldDefinition::create('list_string')
      ->setLabel(t('Subscription Plan'))
      ->setDescription(t('Select an Subscription plan'))
      ->setCardinality(1)
      ->setSetting('allowed_values_function', 'commerce_crefopay_subscripton_plans_options')
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
