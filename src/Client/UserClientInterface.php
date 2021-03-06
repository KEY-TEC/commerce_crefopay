<?php

namespace Drupal\commerce_crefopay\Client;

use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use Upg\Library\Request\Objects\PaymentInstrument;

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
   * @return \Upg\Library\Request\Objects\Person
   *   The Crefopay Person.
   *
   * @throws \Upg\Library\Api\Exception\ApiError
   * @throws \Upg\Library\Api\Exception\CurlError
   * @throws \Upg\Library\Api\Exception\InvalidHttpResponseCode
   * @throws \Upg\Library\Api\Exception\InvalidUrl
   * @throws \Upg\Library\Api\Exception\JsonDecode
   * @throws \Upg\Library\Api\Exception\MacValidation
   * @throws \Upg\Library\Api\Exception\RequestNotSet
   * @throws \Upg\Library\Api\Exception\Validation
   * @throws \Upg\Library\Mac\Exception\MacInvalid
   * @throws \Upg\Library\Serializer\Exception\VisitorCouldNotBeFound
   */
  public function getUser(User $user);

}
