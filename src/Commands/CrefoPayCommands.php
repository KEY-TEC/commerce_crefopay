<?php

namespace Drupal\commerce_crefopay\Commands;

use Drupal\commerce_crefopay\ImportManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Class CrefoPayCommands.
 *
 * CrefoPay helper commands.
 *
 * @package Drupal\sw_migrate\Commands
 */
class CrefoPayCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The import manager.
   *
   * @var \Drupal\commerce_crefopay\ImportManagerInterface
   */
  protected $importManager;

  /**
   * MigrateCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_crefopay\ImportManagerInterface $import_manager
   *   The import manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ImportManagerInterface $import_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->importManager = $import_manager;
  }

  /**
   * Import user with iban and bic into CrefoPay.
   *
   * @command crefopay:imp-bank
   * @validate-module-enabled commerce_crefopay
   * @aliases cp:ib
   */
  public function importBank($user_id, $order_id, $account_holder, $iban, $bic, $amount, $plan_reference) {
    $user = $this->entityTypeManager->getStorage('user')->load($user_id);
    $order = $this->entityTypeManager->getStorage('commerce_order')->load($order_id);
    $this->importManager->importSubscription($user, $order, $account_holder, $iban, $bic, $amount, $plan_reference);
  }

}
