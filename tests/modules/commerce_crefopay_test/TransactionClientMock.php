<?php


namespace Drupal\Tests\commerce_crefopay\modules\commerce_crefopay_test;


use Drupal\commerce_crefopay\Client\TransactionClient;
use Drupal\commerce_order\Entity\Order;

class TransactionClientMock extends TransactionClient {
  public function getTransactionStatus(Order $order) {

    $data = [
      'transactionStatus' => 1,
      'additionalData' => [
        'paymentMethod' => 'paypal'
      ]
    ];
    return $data;
  }
}
