<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use CrefoPay\Library\User\Type as UserType;

/**
 * Defines the interface for transaction related API calls.
 */
interface TransactionClientInterface {

  /**
   * Reserves an transaction.
   *
   * @see https://docs.crefopay.de/api/#reserve
   */
  public function reserveTransaction(Order $order, $payment_method, $payment_instrument_id);

  /**
   * This call gets a refreshed list of payment instruments.
   *
   * @see https://docs.crefopay.de/api/#gettransactionpaymentinstruments
   *
   * @return array
   *   The payment instructions.
   */
  public function getTransactionPaymentInstruments(Order $order);

  /**
   * The refund call allows the merchant to return money to the use.
   */
  public function refund(PaymentInterface $payment, Price $amount, $description, $capture_id);

  /**
   * Returns the transaction status.
   *
   * @see https://docs.crefopay.de/api/#gettransactionstatus
   */
  public function getTransactionStatus(Order $order);

  /**
   * Create a transaction.
   *
   * @see https://docs.crefopay.de/api/#createtransaction
   */
  public function createTransaction(Order $order, User $user, ProfileInterface $billing_profile, $integration_type = "HostedPageBefore", ProfileInterface $shipping_profile = NULL, $user_type = UserType::USER_TYPE_PRIVATE);

  public function validateMac(OrderInterface $order);

}
