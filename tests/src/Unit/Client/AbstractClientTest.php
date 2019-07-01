<?php


namespace Drupal\Tests\commerce_crefopay\Unit\Client;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\Tests\UnitTestCase;

/**
 * Test CrefopayApiTest.
 *
 * @group commerce_crefopay
 */
abstract class AbstractClientTest extends UnitTestCase {

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $userMock;

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $billingAddressMock;

  /**
   * @var Drupal\commerce_order\Entity\Order
   */
  protected $orderMock;

  public function setUp() {
    $uuid = time();
    $this->userMock = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()
      ->getMock();
    $this->userMock->expects($this->any())
      ->method('id')
      ->will($this->returnValue(12));
    $this->userMock->expects($this->any())
      ->method('uuid')
      ->will($this->returnValue("USER-" . $uuid));

    $this->userMock->expects($this->any())
      ->method('getEmail')
      ->will($this->returnValue("christian.wiedemann@key-tec.de"));

    /** @var \Drupal\user\Entity\User $user */
    $this->billingAddressMock = $this->getMockBuilder('Drupal\address\Plugin\Field\FieldType\AddressItem')
      ->disableOriginalConstructor()
      ->getMock();
    $this->billingAddressMock->expects($this->any())
      ->method('getGivenName')
      ->will($this->returnValue("Christian"));
    $this->billingAddressMock->expects($this->any())
      ->method('getFamilyName')
      ->will($this->returnValue("Wiedemann"));

    $this->billingAddressMock->expects($this->any())
      ->method('getAddressLine1')
      ->will($this->returnValue("Blutenburgstr. 68"));
    $this->billingAddressMock->expects($this->any())
      ->method('getLocality')
      ->will($this->returnValue("Munich"));
    $this->billingAddressMock->expects($this->any())
      ->method('getAdministrativeArea')
      ->will($this->returnValue("Bavaria"));
    $this->billingAddressMock->expects($this->any())
      ->method('getCountryCode')
      ->will($this->returnValue("DE"));
    $this->billingAddressMock->expects($this->any())
      ->method('getPostalCode')
      ->will($this->returnValue("80336"));


    $order_item_mock = $this->getMockBuilder('Drupal\commerce_order\Entity\OrderItem')
      ->disableOriginalConstructor()
      ->getMock();
    $order_item_mock->expects($this->any())
      ->method('uuid')
      ->will($this->returnValue("OITEM-" . $uuid));

    $order_item_mock->expects($this->any())
      ->method('getTotalPrice')
      ->will($this->returnValue(new Price(100, "USD")));
    $order_item_mock->expects($this->any())
      ->method('getTitle')
      ->will($this->returnValue("Item title"));
    $order_item_mock->expects($this->any())
      ->method('getQuantity')
      ->will($this->returnValue(1));

    $this->orderMock = $this->getMockBuilder('Drupal\commerce_order\Entity\Order')
      ->disableOriginalConstructor()
      ->getMock();
    $this->orderMock->expects($this->any())
      ->method('id')
      ->will($this->returnValue(17));
    $this->orderMock->expects($this->any())
      ->method('uuid')
      ->will($this->returnValue("ORD-" . $uuid));

    $this->orderMock->expects($this->any())
      ->method('getItems')
      ->will($this->returnValue(
        [$order_item_mock]
      ));
    $this->orderMock->expects($this->any())
      ->method('getTotalPrice')
      ->will($this->returnValue(new Price(100, "USD")));

  }

}
