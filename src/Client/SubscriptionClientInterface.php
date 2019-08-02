<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\user\Entity\User;

/**
 * Subscription client interface.
 */
interface SubscriptionClientInterface {

  /**
   * Creates a CrefoPay subscriptions.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The order.
   * @param \Drupal\user\Entity\User $user
   *   The subscription user.
   * @param \Drupal\address\AddressInterface $billing_address
   *   The billing address.
   * @param $plan_reference
   *   The CrefoPay plan reference.
   *
   * @return array
   *   Payment instruments
   */
  public function createSubscription(Order $order, User $user, AddressInterface $billing_address, $plan_reference);

  /**
   * The updateSubscription call is used to update the charge
   * rate or cancel an active subscription.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The subscription to update.
   * @param \Drupal\commerce_price\Price $amount
   *   The new price.
   * @param string $action
   *   Possible Values:
   *    - CHANGE_RATE
   *    - CANCEL.
   */
  public function updateSubscription(Order $order, Price $amount, $action);

  /**
   * The getPlans call provides details of all
   * subscription plans created in the CrefoPay system.
   *
   * @return \Upg\Library\Request\Objects\SubscriptionPlan[]
   *   The plans.
   */
  public function getSubscriptionPlans();

}
