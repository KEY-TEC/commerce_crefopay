<?php


namespace Drupal\Tests\commerce_crefopay\Unit\Client;


use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\Client\SubscriptionClient;
use Drupal\commerce_crefopay_test\CrefopayTestConfigProvider;

/**
 * Test CrefopayApiTest.
 *
 * @group commerce_crefopay
 */
class SubscriptionClientTest extends AbstractClientTest {

  /**
   * @var SubscriptionClient
   */
  private $subscriptionClient;

  public function setUp() {
    parent::setUp();
    $this->subscriptionClient = new SubscriptionClient(new CrefopayTestConfigProvider(), new PersonBuilder(), new AddressBuilder(), new BasketBuilder(), new AmountBuilder());
  }

  public function testCreateSubscription(){
    $plans = $this->subscriptionClient->createSubscription($this->orderMock, $this->userMock, $this->billingAddressMock, "start");
    $this->assertNotNull($plans);
  }

  public function testGetSubscriptionPlans(){
    $plans = $this->subscriptionClient->getSubscriptionPlans();
    $this->assertNotNull($plans);
  }

}
