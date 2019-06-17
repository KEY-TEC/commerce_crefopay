<?php

namespace Drupal\commerce_crefopay\Client;

use CommerceGuys\Addressing\Address;
use Drupal\address\AddressInterface;
use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\user\Entity\User;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Request\CreateTransaction as RequestCreateTransaction;
use Upg\Library\Response\SuccessResponse;

class TransactionClient {

  private $configProvider;

  private $personBuilder;

  private $addressBuilder;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(ConfigProviderInterface $config_provider, PersonBuilder $person_builder, AddressBuilder $address_builder, BasketBuilder $basket_builder, AmountBuilder $amount_builder) {
    $this->configProvider = $config_provider;
    $this->personBuilder = $person_builder;
    $this->addressBuilder = $address_builder;
    $this->basketBuilder = $basket_builder;
    $this->amountBuilder = $amount_builder;
  }

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
