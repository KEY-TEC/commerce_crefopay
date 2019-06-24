<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\user\Entity\User;
use Upg\Library\Request\GetSubscriptionPlans as RequestGetSubscriptionPlans;
use Upg\Library\Api\GetSubscriptionPlans as ApiGetSubscriptionPlans;
use Upg\Library\User\Type;
use Upg\Library\Request\CreateSubscription as RequestCreateSubscription;
use Upg\Library\Api\CreateSubscription as ApiCreateSubscription;

use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\AmountRange;
use Upg\Library\Response\SuccessResponse;

class SubscriptionClient extends AbstractClient {

  public function createSubscription(Order $order, User $user, AddressInterface $billing_address, $plan_reference) {
    $subscription_create_request = new RequestCreateSubscription($this->configProvider->getConfig());
    $subscription_create_request->setSubscriptionID($order->id());
    $subscription_create_request->setIntegrationType("HostedPageBefore");
    $subscription_create_request->setPlanReference($plan_reference);
    $subscription_create_request->setAmount($this->amountBuilder->buildFromOrder($order));
    $subscription_create_request->setUserData($this->personBuilder->build($user));
    $subscription_create_request->setUserID($user->id());
    $subscription_create_request->setBillingAddress($this->addressBuilder->build($billing_address));
    $subscription_create_request->setIntegrationType("HostedPageBefore");
    $subscription_create_request->setLocale('DE');
    $subscription_create_request->setUserType(Type::USER_TYPE_PRIVATE);
    $basket_item = $this->basketBuilder->build($order);
    $subscription_create_request->addBasketItem($basket_item);
    $subscriptions_create_api = new ApiCreateSubscription($this->configProvider->getConfig(), $subscription_create_request);
    $result = $subscriptions_create_api->sendRequest();
    if ($result instanceof SuccessResponse) {
      return TRUE;
    }
  }

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
