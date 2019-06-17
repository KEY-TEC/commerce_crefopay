<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Magento\Quote\Model\Quote\Item;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Amount;

class BasketItemBuilder
{
    /**
     * @param Item $item
     * @return BasketItem
     */
    public function build(OrderItemInterface $item): BasketItem
    {
        $basketItem = new BasketItem();
        $basketItemAmount = new Amount();
        $basketItemAmount->setAmount(ceil($item->getTotalPrice() * 100));

        $basketItem->setBasketItemText($item->getTitle());
        $basketItem->setBasketItemCount($item->getQuantity());
        $basketItem->setBasketItemAmount($basketItemAmount);

        return $basketItem;
    }
}
