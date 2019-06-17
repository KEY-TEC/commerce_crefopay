<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\CreateTransaction as CreateTransactionRequest;

class BasketBuilder {

  /**
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   * @param CreateTransactionRequest $createTransactionRequest
   *
   * @return void
   */
  public function build(OrderInterface $order, CreateTransactionRequest $createTransactionRequest) {
    /** @var $quoteItem QuoteItem */
    foreach ($order->getItems() as $order_item) {
      if ((bool) intval($order_item->getTotalPrice()->getNumber())) {
        $basket_item = $this->buildItem($order_item);
        $createTransactionRequest->addBasketItem($basket_item);
      }
    }
  }

  /**
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *
   * @return BasketItem
   */
  private function buildItem(OrderItemInterface $order_item) {
    $basket_item = new BasketItem();
    $basket_item_amount = new Amount();
    $basket_item_amount->setAmount(ceil($order_item->getTotalPrice()->getNumber()));

    $basket_item->setBasketItemText($order_item->getTitle());
    $basket_item->setBasketItemCount(ceil($order_item->getQuantity()));
    $basket_item->setBasketItemAmount($basket_item_amount);

    return $basket_item;
  }
}
