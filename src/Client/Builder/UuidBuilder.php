<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\Core\Entity\EntityInterface;

class UuidBuilder {

  private $prefix = "TEST-4-";
  /**
   * @param EntityInterface $entity
   *
   * @return String
   */
  public function id(EntityInterface $entity) {
    return $this->prefix . $entity->id();
  }

  /**
   * @param EntityInterface $entity
   *
   * @return String
   */
  public function realId($id) {
    return str_replace($this->prefix,"", $id);
  }
}
