<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use Upg\Library\User\Type as UserType;

/**
 * Subscription client interface.
 */
interface SubscriptionClientInterface {

  /**
   * Reset subscription cache.
   */
  public function resetCache();

  /**
   * Creates a CrefoPay subscriptions.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The order.
   * @param \Drupal\user\Entity\User $user
   *   The subscription user.
   * @param \Drupal\profile\Entity\ProfileInterface $billing_profile
   *   The billing profile.
   * @param $plan_reference
   *   The CrefoPay plan reference.
   * @param \Drupal\profile\Entity\ProfileInterface $shipping_profile
   *   The shipping profile.
   *
   * @return array
   *   Payment instruments
   */
  public function createSubscription(Order $order, User $user, ProfileInterface $billing_profile, $plan_reference, ProfileInterface $shipping_profile = NULL, $integration_type = NULL, $user_type = UserType::USER_TYPE_PRIVATE);

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
