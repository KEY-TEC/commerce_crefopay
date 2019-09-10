<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_price\Price;
use Drupal\user\Entity\User;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Api\GetTransactionPaymentInstruments;
use Upg\Library\Api\GetTransactionStatus;
use Upg\Library\Api\Refund;

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
  public function createTransaction(Order $order, User $user, AddressInterface $billing_address, $integration_type = "HostedPageBefore", AddressInterface $shipping_address = NULL);

}
