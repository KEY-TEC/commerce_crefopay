<?php

namespace Drupal\commerce_crefopay\Client\Builder;

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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return string
   */
  public function id(EntityInterface $entity) {
    return $this->prefix . $entity->id();
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return string
   */
  public function realId($id) {
    return str_replace($this->prefix, "", $id);
  }

}
