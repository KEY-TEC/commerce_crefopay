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
use Upg\Library\Api\Reserve;
use Upg\Library\Api\Exception\ApiError;
use Upg\Library\Request\CreateTransaction as RequestCreateTransaction;
use Upg\Library\Request\GetTransactionPaymentInstruments as RequestGetTransactionPaymentInstruments;
use Upg\Library\Request\GetTransactionStatus as RequestGetTransactionStatus;
use Upg\Library\Request\Refund as RequestRefund;
use Upg\Library\Request\Reserve as RequestReserve;
use Upg\Library\Response\SuccessResponse;

interface TransactionClientInterface {

  public function reserveTransaction(Order $order, $payment_method, $payment_instrument_id);

  public function getTransactionPaymentInstruments(Order $order);

  public function refund(PaymentInterface $payment, Price $amount, $description, $capture_id);

  public function getTransactionStatus(Order $order);

  public function createTransaction(Order $order, User $user, AddressInterface $billing_address, $integration_type = "HostedPageBefore");

}
