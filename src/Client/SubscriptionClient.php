<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\user\Entity\User;
use Upg\Library\Api\Exception\ApiError;
use Upg\Library\Request\GetSubscriptionPlans as RequestGetSubscriptionPlans;
use Upg\Library\Api\GetSubscriptionPlans as ApiGetSubscriptionPlans;
use Upg\Library\User\Type;
use Upg\Library\Request\CreateSubscription as RequestCreateSubscription;
use Upg\Library\Api\CreateSubscription as ApiCreateSubscription;

use Upg\Library\Request\UpdateSubscription as RequestUpdateSubscription;
use Upg\Library\Api\UpdateSubscription as ApiUpdateSubscription;

use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\AmountRange;
use Upg\Library\Response\SuccessResponse;

class SubscriptionClient extends AbstractClient implements SubscriptionClientInterface {

  /**
   * {@inheritdoc}
   */
  public function updateSubscription(Order $order, Price $amount, $action) {
    $subscription_create_request = new RequestUpdateSubscription($this->configProvider->getConfig());
    $subscription_create_request->setSubscriptionID($this->idBuilder->id($order));
    $subscription_create_request->setAction($action);
    $subscription_create_request->setRate($this->amountBuilder->buildFromPrice($amount)->getAmount());
    $subscriptions_create_api = new ApiUpdateSubscription($this->configProvider->getConfig(), $subscription_create_request);
    try {
      $result = $subscriptions_create_api->sendRequest();
      if ($result instanceof SuccessResponse) {
        return TRUE;
      }
    }
    catch (ApiError $ae) {
      $this->handleValidationExceptions($ae, $order->id());
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createSubscription(Order $order, User $user, AddressInterface $billing_address, $plan_reference) {
    $subscription_create_request = new RequestCreateSubscription($this->configProvider->getConfig());
    $subscription_create_request->setSubscriptionID($this->idBuilder->id($order));
    $subscription_create_request->setIntegrationType("HostedPageBefore");
    $subscription_create_request->setPlanReference($plan_reference);
    $subscription_create_request->setAmount($this->amountBuilder->buildFromOrder($order));
    $subscription_create_request->setUserData($this->personBuilder->build($user, $billing_address));
    $subscription_create_request->setUserID($this->idBuilder->id($user));
    $subscription_create_request->setBillingAddress($this->addressBuilder->build($billing_address));
    $subscription_create_request->setIntegrationType("HostedPageBefore");
    $subscription_create_request->setLocale($this->personBuilder->getLangcode($user));
    $subscription_create_request->setUserType(Type::USER_TYPE_PRIVATE);
    $this->basketBuilder->build($order, $subscription_create_request);
    $subscriptions_create_api = new ApiCreateSubscription($this->configProvider->getConfig(), $subscription_create_request);
    try {
      $result = $subscriptions_create_api->sendRequest();
      if ($result instanceof SuccessResponse) {
        return TRUE;
      }
    }
    catch (ApiError $ae) {
      $this->handleValidationExceptions($ae, $order->id());
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getSubscriptionPlans() {
    $subscriptions_request = new RequestGetSubscriptionPlans($this->configProvider->getConfig());
    $subscriptions_request->setAmount(new AmountRange(new Amount(0), new Amount(4000, 4000, 4000)));
    $subscriptions_api = new ApiGetSubscriptionPlans($this->configProvider->getConfig(), $subscriptions_request);
    $result = $subscriptions_api->sendRequest();
    if ($result instanceof SuccessResponse) {
      $plans = $result->getData('subscriptionPlans');
      return $plans;
    }
    return [];
  }

}
