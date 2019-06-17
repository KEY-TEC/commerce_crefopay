<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use CommerceGuys\Addressing\Address;
use Drupal\address\AddressInterface;
use Drupal\user\Entity\User;
use Upg\Library\Request\Objects\Person;

class PersonBuilder {

  /**
   * @param Address $address
   * @param         $email
   *
   * @return Person
   * @throws LocalizedException
   */
  public function build(User $user, AddressInterface $address) {

    $person = new Person();
    //$person->setSalutation($this->getSalutation($address->gen));
    $person->setName($address->getGivenName());
    $person->setSurname($address->getFamilyName());
    $person->setEmail($user->getEmail());

    return $person;
  }

}
