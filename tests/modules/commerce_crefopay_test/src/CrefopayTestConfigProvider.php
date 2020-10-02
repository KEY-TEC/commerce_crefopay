<?php

namespace Drupal\commerce_crefopay_test;

use Drupal\commerce_crefopay\ConfigProvider;

Class CrefopayTestConfigProvider extends ConfigProvider {

  public function getMode() {
    return 'test';
  }

  public function getSubscriptionOrderTypeId() {
    return 'subscription';
  }

  public function getConfigArray() {
    return [
      'baseUrl' => 'http://commerce_crefopay.docksal/',
      'merchantID' => '516',
      'merchantPassword' => 'YU60WTM6QBZ1CWZ0',
      'storeID' => 'KGTX4QORh2ONyok',
      'sendRequestsWithSalt' => TRUE,
      'logEnabled' => FALSE
    ];
  }
}
