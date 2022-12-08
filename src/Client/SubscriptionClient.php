<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_price\Price;
use Drupal\Core\Cache\Cache;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use CrefoPay\Library\Api\Exception\ApiError;
use CrefoPay\Library\Request\GetSubscriptionPlans as RequestGetSubscriptionPlans;
use CrefoPay\Library\Api\GetSubscriptionPlans as ApiGetSubscriptionPlans;
use CrefoPay\Library\Risk\RiskClass;
use CrefoPay\Library\User\Type as UserType;
use CrefoPay\Library\Integration\Type;
use CrefoPay\Library\Request\CreateSubscription as RequestCreateSubscription;
use CrefoPay\Library\Api\CreateSubscription as ApiCreateSubscription;

use CrefoPay\Library\Request\UpdateSubscription as RequestUpdateSubscription;
use CrefoPay\Library\Api\UpdateSubscription as ApiUpdateSubscription;

use CrefoPay\Library\Request\Objects\Amount;
use CrefoPay\Library\Request\Objects\AmountRange;
use CrefoPay\Library\Response\SuccessResponse;



/**
 * Subscription client implementation.
 */
class SubscriptionClient extends AbstractClient implements SubscriptionClientInterface {

  /**
   * The cached plans.
   *
   * @var array
   */
  private $plans = NULL;

  public function resetCache() {
    $this->cache->delete('crefopay_plans');
  }


  /**
   * {@inheritdoc}
   */
  public function updateSubscription(Order $order, Price $amount, $action) {
    $config = $this->configProvider->getConfig();
    $context = ['order' => $order];
    $this->moduleHandler->alter('crefopay_config', $config, $context);
    $subscription_create_request = new RequestUpdateSubscription($config);
    $subscription_create_request->setSubscriptionID($this->idBuilder->id($order));
    $subscription_create_request->setAction($action);
    $subscription_create_request->setRate($this->amountBuilder->buildFromPrice($amount)->getAmount());
    $subscriptions_create_api = new ApiUpdateSubscription($config, $subscription_create_request);
    try {
      $result = $subscriptions_create_api->sendRequest();
      if ($result instanceof SuccessResponse) {
        return $result->getAllData();
      }
    }
    catch (ApiError $ae) {
      $this->handleValidationExceptions($ae, $order->id());
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function createSubscription(Order $order, User $user, ProfileInterface $billing_profile, $plan_reference, ProfileInterface $shipping_profile = NULL, $integration_type = Type::INTEGRATION_TYPE_SECURE_FIELDS, $user_type = UserType::USER_TYPE_PRIVATE, int $trial_days = NULL, $risk_class = RiskClass::RISK_CLASS_DEFAULT) {
    $config = $this->configProvider->getConfig();
    $context = ['order' => $order];
    $this->moduleHandler->alter('crefopay_config', $config, $context);
    $subscription_create_request = new RequestCreateSubscription($config);
    $subscription_create_request->setSubscriptionID($this->idBuilder->id($order));
    $subscription_create_request->setIntegrationType($integration_type);
    $subscription_create_request->setPlanReference($plan_reference);
    $subscription_create_request->setAmount($this->amountBuilder->buildFromOrder($order));

    $user_id = $this->idBuilder->id($user);
    if ($user_type == UserType::USER_TYPE_BUSINESS) {
      $user_id = 'B' . $user_id;
      $company = $this->companyBuilder->build($user, $billing_profile);
      $subscription_create_request->setCompanyData($company);
    }
    $subscription_create_request->setUserID($user_id);
    $subscription_create_request->setUserType($user_type);
    $user_data = $this->personBuilder->build($user, $billing_profile);
    $subscription_create_request->setUserData($user_data);
    $subscription_create_request->setUserRiskClass($risk_class);
    if ($trial_days !== NULL) {
      $subscription_create_request->setTrialPeriod($trial_days);
    }

    $billing_address = $billing_profile->address[0];
    $subscription_create_request->setBillingAddress($this->addressBuilder->build($billing_address));
    if ($shipping_profile != NULL) {
      $shipping_address = $shipping_profile->address[0];
      $subscription_create_request->setShippingAddress($this->addressBuilder->build($shipping_address));
    }
    $subscription_create_request->setLocale($this->personBuilder->getLangcode($user));
    $this->basketBuilder->build($order, $subscription_create_request);
    $subscriptions_create_api = new ApiCreateSubscription($config, $subscription_create_request);
    try {
      $result = $subscriptions_create_api->sendRequest();
      if ($result instanceof SuccessResponse) {
        return $result->getAllData();
      }
    }
    catch (ApiError $ae) {
      $this->handleValidationExceptions($ae, $order->id());
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getSubscriptionPlans($page = 1, $recursive = FALSE) {
    if (!$recursive) {
      if ($this->plans !== NULL) {
        return $this->plans;
      }
      $cache = $this->cache;
      $this->plans = $cache->get('crefopay_plans');
      if ($this->plans !== FALSE) {
        return $this->plans->data;
      }
      $this->plans = [];
    }

    // Send request.
    $subscriptions_request = new RequestGetSubscriptionPlans($this->configProvider->getConfig());
    $subscriptions_request->setAmount(new AmountRange(new Amount(0), new Amount(4000, 4000, 4000)));
    $subscriptions_request->setPageNumber($page);
    $subscriptions_api = new ApiGetSubscriptionPlans($this->configProvider->getConfig(), $subscriptions_request);
    $result = $subscriptions_api->sendRequest();
    if ($result instanceof SuccessResponse) {
      $plans = $result->getData('subscriptionPlans');
      foreach ($plans as $plan) {
        $this->plans[$plan->getPlanReference()] = $plan->getName();
      }

      $page_size = $result->getData('pageSize');
      $total = $result->getData('totalEntries');
      if ($page * $page_size < $total) {
        $this->getSubscriptionPlans(++$page, TRUE);
      }

      if (!$recursive) {
        $cache->set('crefopay_plans', $this->plans, Cache::PERMANENT, ['crefopay_plans_list']);
      }
      return $this->plans;
    }

    return [];
  }

}
