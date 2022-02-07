<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use CrefoPay\Library\Request\Objects\PaymentInstrument;

/**
 * Defines the interface for user related API calls.
 */
interface UserClientInterface {

  /**
   * Register or update an CrefoPay User.
   *
   * @return string
   *   The payment instrument id.
   */
  public function registerUserPaymentInstrument(User $user, PaymentInstrument $payment_instrument);

  /**
   * Returns registred payment instruments.
   *
   * @return PaymentInstrument[]
   *   The registred payment instruments.
   */
  public function getUserPaymentInstrument(User $user);

  /**
   * Register or update an CrefoPay User.
   */
  public function registerOrUpdateUser(User $user, ProfileInterface $profile);

  /**
   * Returns an Crefopay Person.
   *
   * Returns an Crefopay Person for the given User.
   * The Crefopay User has the same Id as the Drupal User.
   * If no Crefopay Person exists for the given Drupal User null will be returned.
   *
   * @param \Drupal\user\Entity\User $user
   *   The Drupal User.
   *
   * @return \CrefoPay\Library\Request\Objects\Person
   *   The Crefopay Person.
   *
   * @throws \CrefoPay\Library\Api\Exception\ApiError
   * @throws \CrefoPay\Library\Api\Exception\CurlError
   * @throws \CrefoPay\Library\Api\Exception\InvalidHttpResponseCode
   * @throws \CrefoPay\Library\Api\Exception\InvalidUrl
   * @throws \CrefoPay\Library\Api\Exception\JsonDecode
   * @throws \CrefoPay\Library\Api\Exception\MacValidation
   * @throws \CrefoPay\Library\Api\Exception\RequestNotSet
   * @throws \CrefoPay\Library\Api\Exception\Validation
   * @throws \CrefoPay\Library\Mac\Exception\MacInvalid
   * @throws \CrefoPay\Library\Serializer\Exception\VisitorCouldNotBeFound
   */
  public function getUser(User $user);

}
