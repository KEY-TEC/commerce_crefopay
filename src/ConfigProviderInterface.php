<?php

namespace Drupal\commerce_crefopay;

Interface ConfigProviderInterface {
  public function getConfigArray();
  public function getConfig();
}