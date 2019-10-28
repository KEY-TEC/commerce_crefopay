<?php

namespace Drupal\commerce_crefopay;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\user\Entity\User;

/**
 * ImportManager Interface.
 */
interface ImportManagerInterface {

  public function importSubscription(User $user, OrderInterface $order, $account_holder, $iban, $bic, $plan_reference);

}
