<?php

namespace Drupal\commerce_crefopay;

use Drupal\Core\Config\ConfigFactoryInterface;
use CrefoPay\Library\Config;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * ConfigProvider default implementation.
 */
class ConfigProvider implements ConfigProviderInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current mode being used, either live or test.
   *
   * @var string
   */
  protected $mode;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ConfigProvider constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function setMode($mode) {
    $this->mode = $mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getMode() {
    if ($this->mode != NULL) {
      return $this->mode;
    }
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return $config->get('mode');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(array $context = []) {
    return new Config($this->getConfigArray($context));
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscriptionOrderTypeId() {
    $config = $this->configFactory->get('commerce_crefopay.settings');
    return $config->get('subscriptionOrderTypeId');
  }

  /**
   * {@inheritdoc}
   */
  public function getSecureFieldsUrl() {
    $mode = $this->getMode();
    if ($mode == 'test') {
      return "https://sandbox.crefopay.de/secureFields/";
    }
    elseif ($mode == 'live') {
      return "https://api.crefopay.de/secureFields/";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getApiUrl() {
    $mode = $this->getMode();
    if ($mode == 'test') {
      return "https://sandbox.crefopay.de/2.0";
    }
    elseif ($mode == 'live') {
      return "https://api.crefopay.de/2.0";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigArray(array $context = []) {
    $config = $this->configFactory->get('commerce_crefopay.settings');

    $config_ary = [
      'baseUrl' => $this->getApiUrl(),
      'storeID' => $config->get('storeID'),
      'shopPublicKey' => $config->get('shopPublicKey'),
      'merchantID' => $config->get('merchantID'),
      'merchantPassword' => $config->get('merchantPassword'),
      'logEnabled' => FALSE,
    ];

    if (!empty($context)) {
      // Allow config alter when context is provided.
      $this->moduleHandler->alter('crefopay_config', $config_ary, $context);
    }

    return $config_ary;
  }

}
