<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\user\Entity\User;
use Upg\Library\Request\GetSubscriptionPlans as RequestGetSubscriptionPlans;
use Upg\Library\Api\GetSubscriptionPlans as ApiGetSubscriptionPlans;
use Upg\Library\User\Type;
use Upg\Library\Request\CreateSubscription as RequestCreateSubscription;
use Upg\Library\Api\CreateSubscription as ApiCreateSubscription;

use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\AmountRange;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Response\SuccessResponse;

class SubscriptionClient {

  private $configProvider;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(ConfigProviderInterface $config_provider) {
    $this->configProvider = $config_provider;
  }
  public function createSubscription(User $user, $plan_reference) {
    $subscription_create_request = new RequestCreateSubscription($this->configProvider->getConfig());
    $subscription_create_request->setSubscriptionID('start XX');
    $subscription_create_request->setPlanReference($plan_reference);
    $subscription_create_request->setAmount(new Amount(1));
    $basketItem = new BasketItem();
    $basketItem->setBasketItemID('1');
    $basketItem->setBasketItemCount(1);
    $basketItem->setBasketItemAmount(new Amount(1));
    $basketItem->setBasketItemText('Test');
    $subscription_create_request->setUserID($user->id());
    $subscription_create_request->setLocale('DE');
    $subscription_create_request->setUserType(Type::USER_TYPE_PRIVATE);
    $subscription_create_request->addBasketItem($basketItem);
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
