<?php


namespace Drupal\commerce_crefopay;


use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Extension\ModuleHandlerInterface;

class PaymentNotificationManager {

  /**
   * The module handler
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * PaymentNotificationManager constructor.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  public function handlePaymentNotification(PaymentNotificationInterface $notification) {
    $subscription_id = $notification->getSubscriptionId();
    $order_id = !empty($subscription_id) ? $subscription_id : $notification->getOrderId();
    $id_service = \Drupal::service('commerce_crefopay.id_builder');
    $order_id = $id_service->realId($order_id);
    $commerce_order = Order::load($order_id);
    $status = $notification->getStatus();

    if (empty($status)) {
      $status = $notification->getTransactionStatus();
    }

    if ($commerce_order != NULL && !$commerce_order->get('payment_gateway')
        ->isEmpty()) {
      $commerce_order = Order::load($order_id);
      /** @var \Drupal\commerce_payment\Entity\PaymentGateway $payment_gateway */
      $payment_gateway = $commerce_order->get('payment_gateway')->entity;

      /** @var \Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway\BasePaymentGateway $plugin */
      $plugin = $payment_gateway->getPlugin();
      $capture_id = $notification->getCaptureId();

      if (!empty($capture_id)) {
        $payment = $plugin->getPaymentByOrder($commerce_order, $notification->getCaptureId());
      }

      if (!empty($payment)) {
        $plugin->updatePayment($payment, $notification->getCaptureId());
      }
      else {
        $plugin->validateMac($commerce_order);
        $remote_id = $capture_id;
        if(empty($id)){
          $remote_id = $notification->getSubscriptionId();
        }
        $payment = $plugin->createPayment($commerce_order, $remote_id, $plugin->mapCrefopayStateToPayment($status));
        // Envoke alter hook to allow other module operations on the payment.
        $this->moduleHandler->alter('payment_created', $payment, $notification);
      }
    }
    else {
      \Drupal::logger('commerce_payment')
        ->critical("Unable to find payment gateway for $order_id | user id: {$notification->getUserId()} | orderStatus {$notification->getStatus()}");
    }
  }

}
