<?php

namespace Drupal\commerce_crefopay;

use Drupal\commerce_crefopay\Client\SubscriptionClientInterface;
use Drupal\commerce_crefopay\Client\UserClientInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\user\Entity\User;

/**
 * ImportManager default implementation.
 */
class ImportManager implements ImportManagerInterface {


  private $subscriptionClient;

  private $userClient;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(UserClientInterface $user_client, SubscriptionClientInterface $subscription_client) {
    $this->userClient = $user_client;
    $this->subscriptionClient = $subscription_client;
  }


  public function importSubscription(User $user, OrderInterface $order, $iban, $bic, $amount) {
    $address = $order->getBillingProfile()->get('address')[0];
    $this->userClient->registerOrUpdateUser($user, $address);


  }
}
