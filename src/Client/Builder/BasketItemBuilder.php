<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Amount;

/**
 *
 */
class BasketItemBuilder {

  /**
   * @param \Magento\Quote\Model\Quote\Item $item
   * @return \Upg\Library\Request\Objects\BasketItem
   */
  public function build(OrderItemInterface $item): BasketItem {
    $basketItem = new BasketItem();
    $basketItemAmount = new Amount();
    $basketItemAmount->setAmount(ceil($item->getTotalPrice() * 100));
    $basketItem->setBasketItemID($item->uuid());
    $basketItem->setBasketItemText($item->getTitle());
    $basketItem->setBasketItemCount($item->getQuantity());
    $basketItem->setBasketItemAmount($basketItemAmount);

    return $basketItem;
  }

}
