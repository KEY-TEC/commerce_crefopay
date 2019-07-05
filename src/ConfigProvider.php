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

  public function getSubscriptionOrderTypeId() {
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return $config->get('subscriptionOrderTypeId');
  }

  public function getSecureFieldsUrl($mode) {
    if ($mode == 'test') {
      return "https://sandbox.crefopay.de/secureFields/";
    }
    elseif ($mode == 'live') {
      return "https://api.crefopay.de/secureFields/";
    }
  }


  public function getConfigArray() {
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return [
      'baseUrl' => $config->get('baseUrl'),
      'storeID' => $config->get('storeID'),
      'shopPublicKey' => $config->get('shopPublicKey'),
      'merchantID' => $config->get('merchantID'),
      'merchantPassword' => $config->get('merchantPassword'),
      'logEnabled' => FALSE
    ];
  }
}