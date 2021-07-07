<?php


namespace Drupal\commerce_crefopay;


class PaymentNotification implements PaymentNotificationInterface {

  private $userId;
  private $captureId;
  private $orderStatus;
  private $orderId;
  private $subscriptionId;
  private $transactionStatus;

  public function __construct($userId = NULL, $captureId = NULL, $orderStatus = NULL, $orderId = NULL, $subscriptionId = NULL, $transaction_status = NULL){
    $this->userId = $userId;
    $this->captureId = $captureId;
    $this->orderStatus = $orderStatus;
    $this->orderId = $orderId;
    $this->subscriptionId = $subscriptionId;
    $this->transactionStatus = $transaction_status;
  }

  /**
   * @return mixed
   */
  public function getUserId() {
    return $this->userId;
  }

  /**
   * @param mixed $userId
   */
  public function setUserId($userId): void {
    $this->userId = $userId;
  }

  /**
   * @return mixed
   */
  public function getCaptureId() {
    return $this->captureId;
  }

  /**
   * @param mixed $captureId
   */
  public function setCaptureId($captureId): void {
    $this->captureId = $captureId;
  }

  /**
   * @return mixed
   */
  public function getStatus() {
    return $this->orderStatus;
  }

  /**
   * @param mixed $orderStatus
   */
  public function setStatus($orderStatus): void {
    $this->orderStatus = $orderStatus;
  }

  /**
   * @return mixed
   */
  public function getOrderId() {
    return $this->orderId;
  }

  /**
   * @param mixed $orderId
   */
  public function setOrderId($orderId): void {
    $this->orderId = $orderId;
  }

  /**
   * @return mixed
   */
  public function getSubscriptionId() {
    return $this->subscriptionId;
  }

  /**
   * @param mixed $subscriptionId
   */
  public function setSubscriptionId($subscriptionId): void {
    $this->subscriptionId = $subscriptionId;
  }

  public function getTransactionStatus() {
    return $this->transactionStatus;
  }

  public function setTransactionStatus($transaction_status) {
    $this->transactionStatus = $transaction_status;
  }

}
