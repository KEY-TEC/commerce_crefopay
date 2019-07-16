<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Upg\Library\Request\Objects\Amount;

class AmountBuilder {
  /**
   * @param OrderItemInterface $order_item
   * @return Amount
   */
  public function buildFromOrderItem(OrderItemInterface $order_item) {
    $amount = new Amount();
    $amount->setAmount(round($order_item->getTotalPrice()->getNumber() * 100));

    return $amount;
  }

  /**
   * @param OrderAdapterInterface $order
   *
   * @return Amount
   */
  public function buildFromOrder(OrderInterface $order) {
    $amount = new Amount();
    $amount->setAmount(round($order->getTotalPrice()->getNumber() * 100));
    return $amount;
  }

  /**
   * @param PaymentInterface $order
   *
   * @return Amount
   */
  public function buildFromPayment(PaymentInterface $payment) {
    $amount = new Amount();
    $amount->setAmount(round($payment->getAmount()->getNumber() * 100));
    return $amount;
  }
}
