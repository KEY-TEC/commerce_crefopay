<?php

namespace Drupal\commerce_crefopay;

/**
 * ConfigProvider Interface.
 */
interface ConfigProviderInterface {

  /**
   * Possible values are test or live.
   *
   * @param string $mode
   *   The mode.
   */
  public function setMode($mode);

  /**
   * Returns the mode.
   *
   * @return string
   *   The mode.
   */
  public function getMode();

  /**
   * Returns the config array.
   *
   * @param array $context
   *   Optional context array to allow config data alter.
   *
   * @return array
   *   The config array.
   *   Keys:
   *    - baseUrl
   *    - storeID
   *    - shopPublicKey
   *    - merchantID
   *    - merchantPassword
   *    - logEnabled.
   */
  public function getConfigArray(array $context = []);

  /**
   * Returns the CrefoPay library Config.
   *
   * @param array $context
   *   Optional context array to allow config data alter.
   *
   * @return \CrefoPay\Library\Config
   *   The Config.
   */
  public function getConfig(array $context = []);

  /**
   * Returns the default subscription order type id.
   *
   * @return string
   *   The order type id.
   */
  public function getSubscriptionOrderTypeId();

  /**
   * Returns the securefields url.
   *
   * @return string
   *   The secure fiels url.
   */
  public function getSecureFieldsUrl();

  /**
   * Returns the api url.
   *
   * @return string
   *   The api url.
   */
  public function getApiUrl();

}
