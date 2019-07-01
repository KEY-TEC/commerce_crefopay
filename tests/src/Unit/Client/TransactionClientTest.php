<?php


namespace Drupal\Tests\commerce_crefopay\Unit\Client;


use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\Client\TransactionClient;
use Drupal\commerce_crefopay\Client\UserClient;
use Drupal\commerce_crefopay_test\CrefopayTestConfigProvider;
use Drupal\Tests\UnitTestCase;

/**
 * Test CrefopayApiTest.
 *
 * @group commerce_crefopay
 */
class TransactionClientTest extends AbstractClientTest {

  /**
   * @var TransactionClient
   */
  private $transactionClient;

  public function setUp() {
    parent::setUp();
    $this->transactionClient = new TransactionClient(new CrefopayTestConfigProvider(), new PersonBuilder(), new AddressBuilder(), new BasketBuilder(), new AmountBuilder());
  }
  public function testCreateTransaction(){
    $result = $this->transactionClient->createTransaction($this->orderMock, $this->userMock, $this->billingAddressMock);
    $this->assertTrue($result);

  }

}
