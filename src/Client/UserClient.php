<?php

namespace Drupal\commerce_crefopay\Client;

use CommerceGuys\Addressing\Address;
use Drupal\address\AddressInterface;
use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\Client\Builder\IdBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\user\Entity\User;
use Upg\Library\Api\Exception\ApiError;
use Upg\Library\Request\RegisterUser as RequestRegisterUser;
use Upg\Library\Api\RegisterUser as ApiRegisterUser;
use Upg\Library\Api\UpdateUser as ApiUpdateUser;
use Upg\Library\Request\GetUser as RequestGetUser;
use Upg\Library\Api\GetUser as ApiGetUser;
use Upg\Library\Response\SuccessResponse;
use Upg\Library\User\Type;

class UserClient implements UserClientInterface {

  private $configProvider;

  private $personBuilder;

  private $addressBuilder;

  private $idBuilder;

  /**
   * UserClient constructor.
   */
  public function __construct(ConfigProviderInterface $config_provider, IdBuilder $uuid_builder, PersonBuilder $person_builder, AddressBuilder $address_builder) {
    $this->configProvider = $config_provider;
    $this->personBuilder = $person_builder;
    $this->addressBuilder = $address_builder;
    $this->idBuilder = $uuid_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function registerOrUpdateUser(User $user, AddressInterface $billing_address, $mode) {
    $crefo_existing_user = $this->getUser($user);
    $register_user_request = new RequestRegisterUser($this->configProvider->getConfig($mode));
    $register_user_request->setUserID($this->idBuilder->id($user));
    $register_user_request->setUserType(Type::USER_TYPE_PRIVATE);

    $crefo_user = $this->personBuilder->build($user, $billing_address);
    $crefo_billing_address = $this->addressBuilder->build($billing_address);
    $register_user_request->setLocale($this->personBuilder->getLangcode($user));
    $register_user_request->setUserData($crefo_user);
    $register_user_request->setBillingAddress($crefo_billing_address);

    if ($crefo_existing_user != NULL) {
      $register_user_api = new ApiUpdateUser($this->configProvider->getConfig($mode), $register_user_request);
    }
    else {
      $register_user_api = new ApiRegisterUser($this->configProvider->getConfig($mode), $register_user_request);
    }

    $result = $register_user_api->sendRequest();
    if ($result instanceof SuccessResponse) {
      return $crefo_user;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUser(User $user) {
    $user_get_request = new RequestGetUser($this->configProvider->getConfig());
    $user_get_request->setUserID($this->idBuilder->id($user));
    $user_get_api = new ApiGetUser($this->configProvider->getConfig(), $user_get_request);
    try {
      $result = $user_get_api->sendRequest();
      if ($result instanceof SuccessResponse) {
        $user = $result->getData('userData');
        return $user;
      }
    } catch (ApiError $api_error) {
      // Return for "User already exists Exception" (2015).
      if ($api_error->getCode() === 2015) {
        return NULL;
      }
      else {
        throw $api_error;
      }
    }
    return NULL;
  }

}
