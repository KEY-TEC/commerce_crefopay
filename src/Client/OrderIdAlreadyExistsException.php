<?php

namespace Drupal\commerce_crefopay\Client;

class OrderIdAlreadyExistsException extends \Exception {

  public function __construct($order_id) {
    parent::__construct("Order id: $order_id already exists.", 1);
  }
}