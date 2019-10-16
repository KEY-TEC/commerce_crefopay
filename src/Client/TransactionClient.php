<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\ProfileInterface;
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

/**
 * Transaction client implementation.
 */
class TransactionClient extends AbstractClient implements TransactionClientInterface {

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function refund(PaymentInterface $payment, Price $amount, $description, $capture_id) {
    $order = $payment->getOrder();
    $request = new RequestRefund($this->configProvider->getConfig());
    $request->setOrderID($this->idBuilder->id($order));
    $request->setRefundDescription($description);
    $request->setCaptureID($capture_id);
    $request->setAmount($this->amountBuilder->buildFromPrice($amount));
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

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function createTransaction(Order $order, User $user, ProfileInterface $billing_profile, $integration_type = "HostedPageBefore", ProfileInterface $shipping_profile = NULL) {
    $request = new RequestCreateTransaction($this->configProvider->getConfig());
    $amount = $this->amountBuilder->buildFromOrder($order);
    $request->setUserID($this->idBuilder->id($user));
    $request->setOrderID($this->idBuilder->id($order));
    $request->setIntegrationType($integration_type);
    $request->setAmount($amount);
    $request->setAutoCapture(TRUE);
    $request->setContext(RequestCreateTransaction::CONTEXT_ONLINE);
    $request->setUserType('PRIVATE');
    $billing_address = $billing_profile->address[0];
    $crefo_billing_address = $this->addressBuilder->build($billing_address);
    $request->setBillingAddress($crefo_billing_address);
    $crefo_person = $this->personBuilder->build($user, $billing_profile);
    $this->basketBuilder->build($order, $request);

    $request->setUserData($crefo_person);
    $request->setLocale($this->personBuilder->getLangcode($user));
    $shipping_address = $shipping_profile->address[0];
    if ($shipping_address != NULL) {
      $crefo_shipping_address = $this->addressBuilder->build($shipping_address);
      $request->setShippingAddress($crefo_shipping_address);
    }

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
