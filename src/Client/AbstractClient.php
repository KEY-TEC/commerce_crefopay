<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;

abstract class AbstractClient {


  /**
   * @var \Drupal\commerce_crefopay\ConfigProviderInterface
   */
  protected $configProvider;

  /**
   * @var \Drupal\commerce_crefopay\Client\Builder\PersonBuilder
   */
  protected $personBuilder;

  /**
   * @var \Drupal\commerce_crefopay\Client\Builder\AddressBuilder
   */
  protected $addressBuilder;

  /**
   * @var \Drupal\commerce_crefopay\Client\Builder\BasketBuilder
   */
  protected $basketBuilder;

  /**
   * @var \Drupal\commerce_crefopay\Client\Builder\AmountBuilder
   */
  protected $amountBuilder;

  /**
   * AbstractClient constructor.
   */
  public function __construct(ConfigProviderInterface $config_provider, PersonBuilder $person_builder, AddressBuilder $address_builder, BasketBuilder $basket_builder, AmountBuilder $amount_builder) {
    $this->configProvider = $config_provider;
    $this->personBuilder = $person_builder;
    $this->addressBuilder = $address_builder;
    $this->basketBuilder = $basket_builder;
    $this->amountBuilder = $amount_builder;
  }

}
