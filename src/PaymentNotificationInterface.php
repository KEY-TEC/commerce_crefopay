<?php


namespace Drupal\commerce_crefopay;


interface PaymentNotificationInterface {
  public function setUserId($user_id);
  public function getUserId();
  public function setCaptureId($capture_id);
  public function getCaptureId();
  public function setOrderStatus($order_status);
  public function getOrderStatus();
  public function setOrderId($order_id);
  public function getOrderId();
  public function setSubscriptionId($subscription_id);
  public function getSubscriptionId();
}
