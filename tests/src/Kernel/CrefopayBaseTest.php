<?php


namespace Drupal\Tests\commerce_crefopay\Kernel;


use Drupal\commerce_crefopay\PaymentNotification;
use Drupal\commerce_crefopay_test\CrefopayTestConfigProvider;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\commerce_price\Price;
use Drupal\Tests\commerce_order\Kernel\OrderKernelTestBase;
use Drupal\Tests\ConfigTestTrait;


class CrefopayBaseTest extends OrderKernelTestBase {
  use ConfigTestTrait;

  protected $user;
  protected $order;

  /**
   * @var \Drupal\commerce_crefopay\PaymentNotificationManager
   */
  protected $paymentNotificationManager;

  public static $modules = [
    'commerce_payment',
    'commerce_order',
    'commerce_crefopay',
    'commerce_crefopay_test',
    'commerce_price',
    'commerce',
    'profile',
    'state_machine',
    'address'
  ];

  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('commerce_payment');
    $this->installConfig('commerce_payment');

    $this->container->set('commerce_crefopay.config_provider', new CrefopayTestConfigProvider($this->container->get('config.factory')));

    $gateway = PaymentGateway::create([
      'id' => 'crefopay',
      'label' => 'Crefopay',
      'plugin' => 'crefopay_secure_fields_subscription',
    ]);
    $gateway->save();

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);

    $order_item = OrderItem::create([
      'title' => 'Subscription',
      'type' => 'test',
      'quantity' => 1,
      'unit_price' => [
        'number' => '30.00',
        'currency_code' => 'USD',
      ],
    ]);
    $order_item->save();

    $order = Order::create([
      'type' => 'default',
      'uid' => $this->user->id(),
      'store_id' => $this->store->id(),
      'order_items' => [$order_item],
    ]);
    $order->set('payment_gateway', $gateway);
    $order->save();
    $this->order = $this->reloadEntity($order);
    $this->paymentNotificationManager = $this->container->get('commerce_crefopay.payment_notification_manager');

    $payment = Payment::create([
      'type' => 'payment_default',
      'payment_gateway' => 'crefopay',
      'order_id' => $this->order->id(),
      'amount' => new Price('30', 'EUR'),
      'state' => 'completed'
    ]);
    $payment->save();

  }

  public function testPaymentNotificationManager(){


    $notification = new PaymentNotification();
    $notification->setOrderId($this->order->id());
    $notification->setUserId($this->user->id());
    $notification->getOrderStatus(1);
    $notification->setCaptureId('123');


    $payments = \Drupal::entityTypeManager()->getStorage('commerce_payment')->loadMultipleByOrder($this->order);
    $this->assertCount(1, $payments);

    $this->paymentNotificationManager->handlePaymentNotification($notification);

  }

}
