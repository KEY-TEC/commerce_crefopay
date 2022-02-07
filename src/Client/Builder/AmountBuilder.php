<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_price\Price;
use CrefoPay\Library\Request\Objects\Amount;

/**
 *
 */
class AmountBuilder {

  /**
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   * @return \CrefoPay\Library\Request\Objects\Amount
   */
  public function buildFromOrderItem(OrderItemInterface $order_item) {
    $amount = new Amount();
    $amount->setAmount(round($order_item->getTotalPrice()->getNumber() * 100));

    return $amount;
  }

  /**
   * @param OrderAdapterInterface $order
   *
   * @return \CrefoPay\Library\Request\Objects\Amount
   */
  public function buildFromOrder(OrderInterface $order) {
    $amount = new Amount();
    $amount->setAmount(round($order->getTotalPrice()->getNumber() * 100));
    return $amount;
  }

  /**
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $order
   *
   * @return \CrefoPay\Library\Request\Objects\Amount
   */
  public function buildFromPayment(PaymentInterface $payment) {
    $amount = new Amount();
    $amount->setAmount(round($payment->getAmount()->getNumber() * 100));
    return $amount;
  }

  /**
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $order
   *
   * @return \CrefoPay\Library\Request\Objects\Amount
   */
  public function buildFromPrice(Price $price) {
    $amount = new Amount();
    $amount->setAmount(round($price->getNumber() * 100));
    return $amount;
  }

}
