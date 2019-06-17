<?php


namespace Drupal\Tests\commerce_crefopay\Unit\Client;


use Drupal\Tests\UnitTestCase;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Config;
use Upg\Library\Request\Objects\Address;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Request\CreateTransaction as RequestCreateTransaction;
/**
 * Test CrefopayApiTest.
 *
 * @group commerce_crefopay
 */
class CrefopayApiTest extends UnitTestCase {

  /**
   * @var Config
   */
  private $config;

  public function setUp() {
    $config_data = [
      'merchantID' => '516',
      'merchantPassword' => 'YU60WTM6QBZ1CWZ0',
      'storeID' => 'KGTX4QORh2ONyok',
      'baseUrl' => 'https://sandbox.crefopay.de/2.0',
      'sendRequestsWithSalt' => TRUE,
      'logEnabled' => TRUE
    ];
    $this->config = new Config($config_data);
  }

  public function testCreatePayment(){
    $request = new RequestCreateTransaction($this->config);

    $amount = new Amount(5,1,20);

    $request->setUserID('1');
    $request->setOrderID('1');
    $request->setAmount($amount);
    $request->setContext(RequestCreateTransaction::CONTEXT_ONLINE);
    $request->setUserType('PRIVATE');
    $billing_address = new Address();
    $request->setBillingAddress($billing_address);
    $user = new Person();
    $user->setEmail('rafael.schally@key-tec.de');
    $user->setSurname('Schally');
    $user->setName('Rafael');
    $basketItem = new BasketItem();
    $basketItem->setBasketItemID('1');

    $basketItem->setBasketItemCount(1);
    $basketItem->setBasketItemAmount($amount);
    $basketItem->setBasketItemText('Test');
    $request->addBasketItem($basketItem);
    $request->setUserData($user);
    $request->setLocale('DE');
    $createTransaction = new CreateTransaction($this->config, $request);
    $response = $createTransaction->sendRequest();
  }

}