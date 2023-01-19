<?php

namespace Drupal\commerce_crefopay\Client;

use CrefoPay\Library\Api\Capture as CaptureApi;
use CrefoPay\Library\Request\Capture;
use CrefoPay\Library\Request\Objects\Amount;
use CrefoPay\Library\Risk\RiskClass;
use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use CrefoPay\Library\Api\CreateTransaction;
use CrefoPay\Library\Api\GetTransactionPaymentInstruments;
use CrefoPay\Library\Api\GetTransactionStatus;
use CrefoPay\Library\Api\Refund;
use CrefoPay\Library\Api\Reserve;
use CrefoPay\Library\Api\Exception\ApiError;
use CrefoPay\Library\Request\CreateTransaction as RequestCreateTransaction;
use CrefoPay\Library\Request\GetTransactionPaymentInstruments as RequestGetTransactionPaymentInstruments;
use CrefoPay\Library\Request\GetTransactionStatus as RequestGetTransactionStatus;
use CrefoPay\Library\Request\MacCalculator;
use CrefoPay\Library\Request\Refund as RequestRefund;
use CrefoPay\Library\Request\Reserve as RequestReserve;
use CrefoPay\Library\Response\SuccessResponse;
use CrefoPay\Library\User\Type as UserType;

/**
 * Transaction client implementation.
 */
class TransactionClient extends AbstractClient implements TransactionClientInterface {

  /**
   * {@inheritdoc}
   */
  public function reserveTransaction(Order $order, $payment_method, $payment_instrument_id) {
    $config = $this->configProvider->getConfig(['order' => $order]);
    $request = new RequestReserve($config);
    $request->setOrderID($this->idBuilder->id($order));
    $request->setPaymentMethod($payment_method);
    $request->setPaymentInstrumentID($payment_instrument_id);
    $reserve_transaction = new Reserve($config, $request);
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
    $config = $this->configProvider->getConfig(['order' => $order]);
    $request = new RequestGetTransactionPaymentInstruments($config);
    $request->setOrderID($this->idBuilder->id($order));
    $get_transaction = new GetTransactionPaymentInstruments($config, $request);
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

  public function capture(PaymentInterface $payment){
    $order = $payment->getOrder();
    $transaction_status = $this->getTransactionStatus($order);
    $capture_request = new Capture($this->configProvider->getConfig());
    $capture_request->setCaptureID($this->idBuilder->id($order).'xxx');
    $capture_request->setOrderID($this->idBuilder->id($order));
    $capture_request->setAmount(new Amount(100));
    $capture = new CaptureApi($this->configProvider->getConfig(), $capture_request);
    try {
      $result = $capture->sendRequest();
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
    $config = $this->configProvider->getConfig(['order' => $order]);
    $request = new RequestRefund($config);
    $request->setOrderID($this->idBuilder->id($order));
    $request->setRefundDescription($description);
    $request->setCaptureID($capture_id);
    $request->setAmount($this->amountBuilder->buildFromPrice($amount));
    $refund_transaction = new Refund($config, $request);
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
    $config = $this->configProvider->getConfig(['order' => $order]);
    $request = new RequestGetTransactionStatus($config);
    $request->setOrderID($this->idBuilder->id($order));
    $get_transaction = new GetTransactionStatus($config, $request);
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

  public function validateMac(OrderInterface $order) {
    $this->getTransactionStatus($order);
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction(Order $order, User $user, ProfileInterface $billing_profile, $integration_type = "HostedPageBefore", ProfileInterface $shipping_profile = NULL, $user_type = UserType::USER_TYPE_PRIVATE, $risk_class = RiskClass::RISK_CLASS_DEFAULT) {
    $config = $this->configProvider->getConfig(['order' => $order]);
    $request = new RequestCreateTransaction($config);
    $amount = $this->amountBuilder->buildFromOrder($order);

    $request->setOrderID($this->idBuilder->id($order));
    $request->setIntegrationType($integration_type);
    $request->setAmount($amount);
    $request->setAutoCapture(TRUE);
    $request->setContext(RequestCreateTransaction::CONTEXT_ONLINE);
    $request->setUserType($user_type);
    $request->setUserRiskClass($risk_class);

    $user_id = $this->idBuilder->id($user);
    if ($user_type == UserType::USER_TYPE_BUSINESS) {
      $company = $this->companyBuilder->build($user, $billing_profile);
      $request->setCompanyData($company);
      $user_id = 'B' . $user_id;
    }
    $request->setUserID($user_id);

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

    $create_transaction = new CreateTransaction($config, $request);
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
