<?php


namespace Drupal\commerce_crefopay;


use Drupal\commerce_order\Entity\Order;

class PaymentNotificationManager {
  public function handlePaymentNotification(PaymentNotificationInterface $notification){
    $subscription_id = $notification->getSubscriptionId();
    $order_id = !empty($subscription_id) ? $subscription_id : $notification->getOrderId();
    $id_service = \Drupal::service('commerce_crefopay.id_builder');
    $order_id = $id_service->realId($order_id);
    $commerce_order = Order::load($order_id);
    if ($commerce_order != NULL && !$commerce_order->get('payment_gateway')->isEmpty()) {
      $commerce_order = Order::load($order_id);
      /** @var \Drupal\commerce_payment\Entity\PaymentGateway $payment_gateway */
      $payment_gateway = $commerce_order->get('payment_gateway')->entity;

      $plugin = $payment_gateway->getPlugin();
      $payment = $plugin->getPaymentByOrder($commerce_order, $notification->getCaptureId());
      if ($payment != NULL) {
        $plugin->updatePayment($payment, $notification->getCaptureId());
      }
    }
    else {
      \Drupal::logger('commerce_payment')
        ->critical("Unable to find payment gateway for $order_id | user id: {$notification->getUserId()} | orderStatus {$notification->getOrderStatus()}");
    }
  }
}
