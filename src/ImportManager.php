<?php

namespace Drupal\commerce_crefopay;

use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_crefopay\Client\SubscriptionClientInterface;
use Drupal\commerce_crefopay\Client\TransactionClient;
use Drupal\commerce_crefopay\Client\UserClientInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\user\Entity\User;
use CrefoPay\Library\Request\Objects\PaymentInstrument;
use CrefoPay\Library\Integration\Type;
use CrefoPay\Library\Risk\RiskClass;
use CrefoPay\Library\User\Type as UserType;


/**
 * ImportManager default implementation.
 */
class ImportManager implements ImportManagerInterface {


  private $subscriptionClient;

  private $transactionClient;

  private $userClient;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(UserClientInterface $user_client, SubscriptionClientInterface $subscription_client, TransactionClient $transaction_client) {
    $this->userClient = $user_client;
    $this->subscriptionClient = $subscription_client;
    $this->transactionClient = $transaction_client;
  }

  public function importSubscription(User $user, OrderInterface $order, $account_holder, $iban, $bic, $plan_reference, $trial_days) {
    $profile = $order->getBillingProfile();
    $this->userClient->registerOrUpdateUser($user, $profile);

    $existing_payment_instruments = $this->userClient->getUserPaymentInstrument($user);

    $found_existing_instrument = FALSE;
    /** @var \CrefoPay\Library\Request\Objects\PaymentInstrument $existing_payment_instrument */
    foreach ($existing_payment_instruments as $payment_instrument) {
      if (strtolower($payment_instrument->getBic()) == strtolower($bic)) {
        $found_existing_instrument = TRUE;
        $payment_instrument_id = $payment_instrument->getPaymentInstrumentID();
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
    try {
      $this->subscriptionClient->createSubscription($order, $user, $profile, $plan_reference, NULL, Type::INTEGRATION_TYPE_API ,UserType::USER_TYPE_PRIVATE, $trial_days, RiskClass::RISK_CLASS_TRUSTED);
    }
    catch (OrderIdAlreadyExistsException $already_exists_exception) {
      // DO NOTHING. Transaction already started.
    }

    $this->transactionClient->reserveTransaction($order, "DD", $payment_instrument_id);
  }
}
