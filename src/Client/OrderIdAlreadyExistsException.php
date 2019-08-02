<?php

namespace Drupal\commerce_crefopay\Client;

/**
 * Throws when an order already exists in CrefoPay.
 */
class OrderIdAlreadyExistsException extends \Exception {

  /**
   * Constructor OrderIdAlreadyExistsException.
   *
   * @param string $order_id
   *   The existing order id.
   */
  public function __construct($order_id) {
    parent::__construct("Order id: $order_id already exists.", 1);
  }

}
