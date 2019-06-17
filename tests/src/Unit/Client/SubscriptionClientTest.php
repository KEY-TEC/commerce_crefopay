<?php


namespace Drupal\Tests\commerce_crefopay\Unit\Client;


use Drupal\commerce_crefopay\Client\SubscriptionClient;
use Drupal\commerce_crefopay_test\CrefopayTestConfigProvider;
use Drupal\Tests\commerce_crefopay\Unit\AbstractClientTest;
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
    $this->subscriptionClient = new SubscriptionClient(new CrefopayTestConfigProvider());
  }

  public function testCreateSubscription(){
    $plans = $this->subscriptionClient->createSubscription($this->userMock, "start");
    $this->assertNotNull($plans);
  }

  public function testGetSubscriptionPlans(){
    $plans = $this->subscriptionClient->getSubscriptionPlans();
    $this->assertNotNull($plans);
  }

}
