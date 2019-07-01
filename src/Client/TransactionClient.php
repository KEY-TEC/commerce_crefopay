<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\user\Entity\User;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Api\Reserve;
use Upg\Library\Api\Exception\ApiError;
use Upg\Library\Request\CreateTransaction as RequestCreateTransaction;
use Upg\Library\Request\Reserve as RequestReserve;
use Upg\Library\Request\Capture as RequestCapture;
use Upg\Library\Response\SuccessResponse;

class TransactionClient extends AbstractClient {

  public function captureTransaction(Order $order, $payment_method, $payment_instrument_id) {
    $request = new RequestCapture($this->configProvider->getConfig());
    $request->setOrderID($this->uuidBuilder->id($order));
    $request->setPaymentMethod($payment_method);
    $request->setPaymentInstrumentID($payment_instrument_id);
    $reserve_transaction = new Reserve($this->configProvider->getConfig(), $request);
    $result = $reserve_transaction->sendRequest();
    if ($result instanceof SuccessResponse) {
      return $result->getData('redirectUrl');
    }
  }


  public function reserveTransaction(Order $order, $payment_method, $payment_instrument_id) {
    $request = new RequestReserve($this->configProvider->getConfig());
    $request->setOrderID($this->uuidBuilder->id($order));
    $request->setPaymentMethod($payment_method);
    $request->setPaymentInstrumentID($payment_instrument_id);
    $reserve_transaction = new Reserve($this->configProvider->getConfig(), $request);
    $result = $reserve_transaction->sendRequest();
    if ($result instanceof SuccessResponse) {
      return $result->getData('redirectUrl');
    }
  }

  public function createTransaction(Order $order, User $user, AddressInterface $billing_address) {
    $request = new RequestCreateTransaction($this->configProvider->getConfig());
    $amount = $this->amountBuilder->buildFromOrder($order);
    $request->setUserID($this->uuidBuilder->id($user));
    $request->setOrderID($this->uuidBuilder->id($order));
    $request->setIntegrationType("HostedPageBefore");
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
        return $result->getData('redirectUrl');
      }
    }
    catch (ApiError $api_error) {
      $this->handleValidationExceptions($api_error, $this->uuidBuilder->id($order));
    }
    return NULL;
  }

}
