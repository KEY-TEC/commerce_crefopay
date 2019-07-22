<?php

namespace Drupal\commerce_crefopay;

Interface ConfigProviderInterface {

  public function setMode($mode);
  public function getMode();
  public function getConfigArray();
  public function getConfig();
  public function getSubscriptionOrderTypeId();
  public function getSecureFieldsUrl();
  public function getApiUrl();
}