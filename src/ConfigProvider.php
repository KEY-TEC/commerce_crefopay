<?php

namespace Drupal\commerce_crefopay;

use Drupal\Core\Config\ConfigFactoryInterface;
use Upg\Library\Config;

Class ConfigProvider implements ConfigProviderInterface {

  private $configFactory;

  /**
   * CrefopayConfigProvider constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  public function getConfig() {
    return new Config($this->getConfigArray());
  }

  public function getConfigArray() {
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return [
      'baseUrl' => $config->get('baseUrl'),
      'storeID' => $config->get('storeID'),
      'merchantID' => $config->get('merchantID'),
      'merchantPassword' => $config->get('merchantPassword'),
      'logEnabled' => FALSE
    ];
  }
}