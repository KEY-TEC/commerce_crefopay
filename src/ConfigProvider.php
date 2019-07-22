<?php

namespace Drupal\commerce_crefopay;

use Drupal\Core\Config\ConfigFactoryInterface;
use Upg\Library\Config;

Class ConfigProvider implements ConfigProviderInterface {

  private $configFactory;

  private $mode;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  public function setMode($mode) {
    $this->mode = $mode;
  }

  public function getMode() {
    if ($this->mode != NULL) {
      return $this->mode;
    }
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return $config->get('mode');
  }

  public function getConfig() {
    return new Config($this->getConfigArray());
  }

  public function getSubscriptionOrderTypeId() {
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return $config->get('subscriptionOrderTypeId');
  }

  public function getSecureFieldsUrl() {
    $mode = $this->getMode();
    if ($mode == 'test') {
      return "https://sandbox.crefopay.de/secureFields/";
    }
    elseif ($mode == 'live') {
      return "https://api.crefopay.de/secureFields/";
    }
  }

  public function getApiUrl() {
    $mode = $this->getMode();
    if ($mode == 'test') {
      return "https://sandbox.crefopay.de/2.0";
    }
    elseif ($mode == 'live') {
      return "https://api.crefopay.de/2.0";
    }
  }

  public function getConfigArray() {
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return [
      'baseUrl' => $this->getApiUrl(),
      'storeID' => $config->get('storeID'),
      'shopPublicKey' => $config->get('shopPublicKey'),
      'merchantID' => $config->get('merchantID'),
      'merchantPassword' => $config->get('merchantPassword'),
      'logEnabled' => FALSE,
    ];
  }
}