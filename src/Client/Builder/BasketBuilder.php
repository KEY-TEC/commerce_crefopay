<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Upg\Library\Request\AbstractRequest;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Amount;

class BasketBuilder {

  /**
   * @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder
   */
  private $idBuilder;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(IdBuilder $uuid_builder) {
    $this->idBuilder = $uuid_builder;
  }

  /**
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   * @param CreateTransactionRequest $createTransactionRequest
   *
   * @return void
   */
  public function build(OrderInterface $order, AbstractRequest $createTransactionRequest) {
    /** @var $quoteItem QuoteItem */
    foreach ($order->getItems() as $order_item) {
      $basket_item = $this->buildItem($order_item);
      $createTransactionRequest->addBasketItem($basket_item);
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
    $basket_item_amount->setAmount(round($order_item->getTotalPrice()->getNumber() * 100));
    $basket_item->setBasketItemID($order_item->id());
    $basket_item->setBasketItemCount($order_item->getQuantity());
    $basket_item->setBasketItemText($order_item->getTitle());
    $basket_item->setBasketItemCount(ceil($order_item->getQuantity()));
    $basket_item->setBasketItemAmount($basket_item_amount);
    return $basket_item;
  }
}
