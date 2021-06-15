<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Site\Settings;

/**
 *
 */
class IdBuilder {

  private $prefix = "";

  /**
   * ConfigProvider constructor.
   */
  public function __construct() {
    $this->prefix = Settings::get('crefopay_id_prefix');
  }

  /**
   * Generates Crefopay ID based on an static prefix and
   * an prefix filed inside the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return string
   *   The order.
   */
  public function id(EntityInterface $entity) {
    $prefix = $this->prefix;
    if ($entity->hasField('field_prefix') && !empty($entity->field_prefix->value)) {
      $prefix .= $entity->field_prefix->value;
    }

    $id = $entity->id();

    if ($entity instanceof OrderInterface) {
      $id = $entity->getOrderNumber();
    }

    return $prefix . $id;
  }

  /**
   * Returns the drupal entity id based on
   *
   * @param string $id
   *
   * @return string
   *   The entity id.
   */
  public function realId($id) {
    $id = str_replace($this->prefix, "", $id);
    $id = str_replace('VERL2', "", $id);
    $id = str_replace('VERL', "", $id);

    $parts = explode('-', $id);
    $id = end($parts);

    return $id;
  }

}
