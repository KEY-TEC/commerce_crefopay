<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\AmountBuilder;
use Drupal\commerce_crefopay\Client\Builder\BasketBuilder;
use Drupal\commerce_crefopay\Client\Builder\CompanyBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\Client\Builder\IdBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use CrefoPay\Library\Api\Exception\ApiError;

/**
 * Abstract CrefoPay API client class.
 */
abstract class AbstractClient {

  /**
   * The config provider.
   *
   * @var \Drupal\commerce_crefopay\ConfigProviderInterface
   */
  protected $configProvider;

  /**
   * The id builder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder
   */
  protected $idBuilder;

  /**
   * The person builder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\PersonBuilder
   */
  protected $personBuilder;

  /**
   * The company builder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\CompanyBuilder
   */
  protected $companyBuilder;

  /**
   * The address builder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\AddressBuilder
   */
  protected $addressBuilder;

  /**
   * The basket builder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\BasketBuilder
   */
  protected $basketBuilder;

  /**
   * The amount builder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\AmountBuilder
   */
  protected $amountBuilder;

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AbstractClient constructor.
   */
  public function __construct(ConfigProviderInterface $config_provider, IdBuilder $id_builder, PersonBuilder $person_builder, CompanyBuilder $company_builder, AddressBuilder $address_builder, BasketBuilder $basket_builder, AmountBuilder $amount_builder, CacheBackendInterface $cache) {
    $this->configProvider = $config_provider;
    $this->idBuilder = $id_builder;
    $this->personBuilder = $person_builder;
    $this->companyBuilder = $company_builder;
    $this->addressBuilder = $address_builder;
    $this->basketBuilder = $basket_builder;
    $this->amountBuilder = $amount_builder;
    $this->cache = $cache;
  }

  /**
   * Handles Api exceptions and throws more specific Exceptions.
   */
  protected function handleValidationExceptions(ApiError $api_error, $order_id) {
    if (
      $api_error->getCode() === 2008 ||
      $api_error->getCode() === 2050

    ) {
      throw new OrderIdAlreadyExistsException($order_id);
    }
    else {
      throw $api_error;
    }
  }

}
