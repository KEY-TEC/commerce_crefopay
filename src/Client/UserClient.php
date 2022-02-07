<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\address\AddressInterface;
use Drupal\commerce_crefopay\Client\Builder\AddressBuilder;
use Drupal\commerce_crefopay\Client\Builder\PersonBuilder;
use Drupal\commerce_crefopay\Client\Builder\IdBuilder;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use CrefoPay\Library\Api\Exception\ApiError;
use CrefoPay\Library\Api\RegisterUserPaymentInstrument as ApiRegisterUserPaymentInstrument;
use CrefoPay\Library\Api\GetUserPaymentInstrument as ApiGetUserPaymentInstrument;
use CrefoPay\Library\Request\Objects\PaymentInstrument;
use CrefoPay\Library\Request\RegisterUser as RequestRegisterUser;
use CrefoPay\Library\Request\GetUserPaymentInstrument as RequestGetUserPaymentInstrument;
use CrefoPay\Library\Request\RegisterUserPaymentInstrument as RequestRegisterUserPaymentInstrument;
use CrefoPay\Library\Api\RegisterUser as ApiRegisterUser;
use CrefoPay\Library\Api\UpdateUser as ApiUpdateUser;
use CrefoPay\Library\Request\GetUser as RequestGetUser;
use CrefoPay\Library\Api\GetUser as ApiGetUser;
use CrefoPay\Library\Response\SuccessResponse;
use CrefoPay\Library\User\Type;

/**
 * User client implementation.
 */
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
  public function getUserPaymentInstrument(User $user) {
    $get_user_request = new RequestGetUserPaymentInstrument($this->configProvider->getConfig());
    $user_id = $this->idBuilder->id($user);
    $get_user_request->setUserID($user_id);
    $get_user_api = new ApiGetUserPaymentInstrument($this->configProvider->getConfig(), $get_user_request);
    try {
      $result = $get_user_api->sendRequest();
      if ($result instanceof SuccessResponse) {
        $instruments = $result->getData('paymentInstruments');
        return $instruments;
      }
    }
    catch (ApiError $api_error) {
      throw $api_error;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function registerOrUpdateUser(User $user, ProfileInterface $profile) {
    $crefo_existing_user = $this->getUser($user);
    $register_user_request = new RequestRegisterUser($this->configProvider->getConfig());
    $register_user_request->setUserID($this->idBuilder->id($user));
    $register_user_request->setUserType(Type::USER_TYPE_PRIVATE);
    $crefo_user = $this->personBuilder->build($user, $profile);
    $address = $profile->address[0];
    $crefo_billing_address = $this->addressBuilder->build($address);
    $register_user_request->setLocale($this->personBuilder->getLangcode($user));
    $register_user_request->setUserData($crefo_user);
    $register_user_request->setBillingAddress($crefo_billing_address);

    if ($crefo_existing_user != NULL) {
      $register_user_api = new ApiUpdateUser($this->configProvider->getConfig(), $register_user_request);
    }
    else {
      $register_user_api = new ApiRegisterUser($this->configProvider->getConfig(), $register_user_request);
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
  public function registerUserPaymentInstrument(User $user, PaymentInstrument $payment_instrument) {
    $register_user_payment_instrument_request = new RequestRegisterUserPaymentInstrument($this->configProvider->getConfig());
    $register_user_payment_instrument_request->setPaymentInstrument($payment_instrument);
    $user_id = $this->idBuilder->id($user);
    $register_user_payment_instrument_request->setUserID($user_id);
    $api_user_payment_instrument_request = new ApiRegisterUserPaymentInstrument($this->configProvider->getConfig(), $register_user_payment_instrument_request);

    try {
      $result = $api_user_payment_instrument_request->sendRequest();
      if ($result instanceof SuccessResponse) {
        $payment_instrument_id = $result->getData('paymentInstrumentID');
        return $payment_instrument_id;
      }
    }
    catch (ApiError $api_error) {
      throw $api_error;
    }
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
    }
    catch (ApiError $api_error) {
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
