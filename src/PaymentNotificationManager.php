<?php


namespace Drupal\commerce_crefopay;


use Drupal\commerce_order\Entity\Order;

class PaymentNotificationManager {

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
      /** @var \Drupal\commerce_payment\Entity\PaymentGateway $payment_gateway */
      $payment_gateway = $commerce_order->get('payment_gateway')->entity;

      /** @var \Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway\BasePaymentGateway $plugin */
      $plugin = $payment_gateway->getPlugin();
      $remote_id = $notification->getCaptureId();
      if (empty($remote_id)) {
        $remote_id = $notification->getOrderId();
      }

      if (!empty($remote_id)) {
        $payment = $plugin->getPaymentByOrder($commerce_order, $remote_id);
      }

      if (!empty($payment)) {
        $plugin->updatePayment($payment, $remote_id, $plugin->mapCrefopayStateToPayment($status));
      }
      else {
        $plugin->validateMac($commerce_order);
        $payment = $plugin->createPayment($commerce_order, $remote_id, $plugin->mapCrefopayStateToPayment($status));
        // Trigger update hooks.
        $payment->save();
      }
    }
    else {
      \Drupal::logger('commerce_payment')
        ->critical("Unable to find payment gateway for order $order_id | user id: {$notification->getUserId()} | orderStatus {$notification->getStatus()}");
    }
  }

}
