<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Upg\Library\Request\Objects\Amount;

class AmountBuilder {
  /**
   * @param Quote $quote
   * @return Amount
   */
  public function buildFromOrderItem(OrderItemInterface $order_item) {
    $amount = new Amount();
    $amount->setAmount(ceil($order_item->getTotalPrice()->getNumber()));

    return $amount;
  }

  /**
   * @param OrderAdapterInterface $order
   *
   * @return Amount
   */
  public function buildFromOrder(OrderInterface $order) {
    $amount = new Amount();
    $amount->setAmount(ceil($order->getTotalPrice()->getNumber()));
    return $amount;
  }
}
