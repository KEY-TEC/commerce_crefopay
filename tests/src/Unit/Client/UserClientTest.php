<?php


namespace Drupal\Tests\commerce_crefopay\Unit\Client;


use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\Client\UserClient;
use Drupal\commerce_crefopay_test\CrefopayTestConfigProvider;

/**
 * Test CrefopayApiTest.
 *
 * @group commerce_crefopay
 */
class UserClientTest extends AbstractClientTest {

  /**
   * @var UserManager
   */
  private $userClient;

  public function setUp() {
    parent::setUp();
    $this->userClient = new UserClient(new CrefopayTestConfigProvider(), new PersonBuilder(), new AddressBuilder());
  }

  public function testGetUser(){
    $user = $this->userClient->getUser($this->userMock);
    $this->assertNull($user);
  }

  public function testRegisterOrUpdateUser(){
    $user = $this->userClient->registerOrUpdateUser($this->userMock, $this->billingAddressMock);
    $this->assertNotNull($user);
  }

}
