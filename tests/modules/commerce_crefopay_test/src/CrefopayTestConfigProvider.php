<?php

namespace Drupal\commerce_crefopay_test;

use Drupal\commerce_crefopay\ConfigProviderInterface;
use Upg\Library\Config;

Class CrefopayTestConfigProvider implements ConfigProviderInterface {

  public function getConfig() {
    return new Config($this->getConfigArray());
  }

  public function getConfigArray() {
    return [
      'merchantID' => '516',
      'merchantPassword' => 'YU60WTM6QBZ1CWZ0',
      'storeID' => 'KGTX4QORh2ONyok',
      'baseUrl' => 'https://sandbox.crefopay.de/2.0',
      'sendRequestsWithSalt' => TRUE,
      'logEnabled' => TRUE
    ];
  }
}