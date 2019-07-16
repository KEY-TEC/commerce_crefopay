<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentInterface;
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
use Upg\Library\Request\Refund as RequestReserve;
use Upg\Library\Request\Reserve as RequestRefund;
use Upg\Library\Response\SuccessResponse;

class TransactionClient extends AbstractClient {



  public function reserveTransaction(Order $order, $payment_method, $payment_instrument_id) {
    $request = new RequestReserve($this->configProvider->getConfig());
    $request->setOrderID($this->idBuilder->id($order));
    $request->setPaymentMethod($payment_method);
    $request->setPaymentInstrumentID($payment_instrument_id);
    $reserve_transaction = new Reserve($this->configProvider->getConfig(), $request);
    $result = $reserve_transaction->sendRequest();
    if ($result instanceof SuccessResponse) {
      $all_data = $result->getAllData();
      return isset($all_data['redirectUrl']) ? $all_data['redirectUrl'] : NULL;
    }
  }

  public function createSecureFieldsTransaction(Order $order, User $user) {
    $request = new RequestCreateTransaction($this->configProvider->getConfig());
    $amount = $this->amountBuilder->buildFromOrder($order);
    $request->setUserID($this->idBuilder->id($user));
    $request->setOrderID($this->idBuilder->id($order));
    $request->setIntegrationType("SecureFields");
    $request->setAmount($amount);
    $request->setAutoCapture(TRUE);
    $request->setContext(RequestCreateTransaction::CONTEXT_ONLINE);
    $request->setUserType('PRIVATE');
    $this->basketBuilder->build($order, $request);

    $request->setLocale($this->personBuilder->getLangcode($user));
    $create_transaction = new CreateTransaction($this->configProvider->getConfig(), $request);
    try {
      $result = $create_transaction->sendRequest();
      if ($result instanceof SuccessResponse) {
        $all_data = $result->getAllData();
        return isset($all_data['redirectUrl']) ? $all_data['redirectUrl'] : NULL;
      }
    }
    catch (ApiError $api_error) {
      $this->handleValidationExceptions($api_error, $this->idBuilder->id($order));
    }
    return NULL;
  }

  public function getTransactionPaymentInstruments(Order $order) {
    $request = new RequestGetTransactionPaymentInstruments($this->configProvider->getConfig());
    $request->setOrderID($this->idBuilder->id($order));
    $get_transaction = new GetTransactionPaymentInstruments($this->configProvider->getConfig(), $request);
    try {
      $result = $get_transaction->sendRequest();
      if ($result instanceof SuccessResponse) {
        $all_data = $result->getAllData();
        return $all_data;
      }
    }
    catch (ApiError $api_error) {
      $this->handleValidationExceptions($api_error, $this->idBuilder->id($order));
    }
    return NULL;
  }

  public function refund(PaymentInterface $payment) {
    $order = $payment->getOrder();
    $request = new RequestRefund($this->configProvider->getConfig());
    $request->setOrderID($this->idBuilder->id($order));
    $request->setAmount($this->amountBuilder->buildFromPayment($payment));
    $refund_transaction = new Refund($this->configProvider->getConfig(), $request);
    try {
      $result = $refund_transaction->sendRequest();
      if ($result instanceof SuccessResponse) {
        $all_data = $result->getAllData();
        return $all_data;
      }
    }
    catch (ApiError $api_error) {
      $this->handleValidationExceptions($api_error, $this->idBuilder->id($order));
    }
    return NULL;
  }

  public function getTransactionStatus(Order $order) {
    $request = new RequestGetTransactionStatus($this->configProvider->getConfig());
    $request->setOrderID($this->idBuilder->id($order));
    $get_transaction = new GetTransactionStatus($this->configProvider->getConfig(), $request);
    try {
      $result = $get_transaction->sendRequest();
      if ($result instanceof SuccessResponse) {
        $all_data = $result->getAllData();
        return $all_data;
      }
    }
    catch (ApiError $api_error) {
      $this->handleValidationExceptions($api_error, $this->idBuilder->id($order));
    }
    return NULL;
  }

  public function createTransaction(Order $order, User $user, AddressInterface $billing_address, $integration_type = "HostedPageBefore") {
    $request = new RequestCreateTransaction($this->configProvider->getConfig());
    $amount = $this->amountBuilder->buildFromOrder($order);
    $request->setUserID($this->idBuilder->id($user));
    $request->setOrderID($this->idBuilder->id($order));
    $request->setIntegrationType($integration_type);
    $request->setAmount($amount);
    $request->setAutoCapture(TRUE);
    $request->setContext(RequestCreateTransaction::CONTEXT_ONLINE);
    $request->setUserType('PRIVATE');
    $crefo_billing_address = $this->addressBuilder->build($billing_address);
    $request->setBillingAddress($crefo_billing_address);
    $crefo_person = $this->personBuilder->build($user, $billing_address);
    $this->basketBuilder->build($order, $request);

    $request->setUserData($crefo_person);
    $request->setLocale($this->personBuilder->getLangcode($user));
    $create_transaction = new CreateTransaction($this->configProvider->getConfig(), $request);
    try {
      $result = $create_transaction->sendRequest();
      if ($result instanceof SuccessResponse) {
        $all_data = $result->getAllData();
        if ($integration_type == "HostedPageBefore") {
          return isset($all_data['redirectUrl']) ? $all_data['redirectUrl'] : NULL;
        }
        else {
          return $all_data;
        }
      }
    }
    catch (ApiError $api_error) {
      $this->handleValidationExceptions($api_error, $this->idBuilder->id($order));
    }
    return NULL;
  }

}
