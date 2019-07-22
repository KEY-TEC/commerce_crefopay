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

interface UserClientInterface {

    public function registerOrUpdateUser(User $user, AddressInterface $billing_address);

  /**
   * Returns an Crefopay Person.
   *
   * Returns an Crefopay Person for the given User.
   * The Crefopay User has the same Id as the Drupal User.
   * If no Crefopay Person exists for the given Drupal User null will be returned.
   *
   * @param \Drupal\user\Entity\User $user
   *   The Drupal User
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
