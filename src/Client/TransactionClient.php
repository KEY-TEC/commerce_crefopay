<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\user\Entity\User;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Request\CreateTransaction as RequestCreateTransaction;
use Upg\Library\Response\SuccessResponse;

class TransactionClient extends AbstractClient {

  public function createTransaction(Order $order, User $user, AddressInterface $billing_address) {
    $request = new RequestCreateTransaction($this->configProvider->getConfig());

    $amount = $this->amountBuilder->buildFromOrder($order);

    $request->setUserID($user->id());
    $request->setOrderID($order->id());
    $request->setIntegrationType("HostedPageBefore");
    $request->setAmount($amount);
    $request->setContext(RequestCreateTransaction::CONTEXT_ONLINE);
    $request->setUserType('PRIVATE');
    $crefo_billing_address = $this->addressBuilder->build($billing_address);
    $request->setBillingAddress($crefo_billing_address);
    $crefo_person = $this->personBuilder->build($user, $billing_address);
    $this->basketBuilder->build($order, $request);

    $request->setUserData($crefo_person);
    $request->setLocale('DE');
    $create_transaction = new CreateTransaction($this->configProvider->getConfig(), $request);
    $response = $create_transaction->sendRequest();
    if ($response instanceof SuccessResponse) {
      return $response->getData('redirectUrl');
    }
    return NULL;
  }

}
