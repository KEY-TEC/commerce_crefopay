<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\user\Entity\User;
use Upg\Library\Api\Exception\ApiError;
use Upg\Library\Api\Exception\Validation;
use Upg\Library\Request\GetSubscriptionPlans as RequestGetSubscriptionPlans;
use Upg\Library\Api\GetSubscriptionPlans as ApiGetSubscriptionPlans;
use Upg\Library\User\Type;
use Upg\Library\Request\CreateSubscription as RequestCreateSubscription;
use Upg\Library\Api\CreateSubscription as ApiCreateSubscription;

use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\AmountRange;
use Upg\Library\Response\SuccessResponse;

interface SubscriptionClientInterface {

  /**
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
   * @return bool
   *   True if successful
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
   *    - CANCEL
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
