<?php

namespace Drupal\commerce_crefopay;

use Drupal\commerce_crefopay\Client\SubscriptionClientInterface;
use Drupal\commerce_crefopay\Client\UserClientInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\user\Entity\User;
use Upg\Library\Request\Objects\PaymentInstrument;
use Upg\Library\Integration\Type;


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


  public function importSubscription(User $user, OrderInterface $order, $account_holder, $iban, $bic, $amount, $plan_reference) {
    $profile = $order->getBillingProfile();
    $this->userClient->registerOrUpdateUser($user, $profile);

    $existing_payment_instruments = $this->userClient->getUserPaymentInstrument($user);

    $found_existing_instrument = FALSE;
    /** @var \Upg\Library\Request\Objects\PaymentInstrument $existing_payment_instrument */
    foreach ($existing_payment_instruments as $payment_instrument) {
      if ($existing_payment_instruments->getUnmaskedIban() == $iban) {
        $found_existing_instrument = TRUE;
        break;
      }
    }
    if ($found_existing_instrument == FALSE) {
      $instrument = new PaymentInstrument();
      $instrument->setBic($bic);
      $instrument->setIban($iban);
      $instrument->setAccountHolder($account_holder);
      $instrument->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_BANK);
      $payment_instrument_id = $this->userClient->registerUserPaymentInstrument($user, $instrument);
    }


    $profile = $order->getBillingProfile();
    $this->subscriptionClient->createSubscription($order, $user, $profile, $plan_reference, NULL, Type::INTEGRATION_TYPE_API);

  }
}
