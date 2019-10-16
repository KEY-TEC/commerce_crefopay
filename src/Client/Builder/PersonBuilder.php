<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\address\AddressInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use Upg\Library\Request\Objects\Person;

/**
 *
 */
class PersonBuilder {

  /**
   * @param \CommerceGuys\Addressing\Address $address
   * @param         $email
   *
   * @return \Upg\Library\Request\Objects\Person
   * @throws LocalizedException
   */
  public function build(User $user, ProfileInterface $profile) {

    $address = $profile->address[0];
    $person = new Person();

    $person->setName($address->getGivenName());
    $person->setSurname($address->getFamilyName());
    $person->setEmail($user->getEmail());
    if ($profile->hasField('field_birth') && !empty($profile->field_birth->value)) {
      $date = \DateTime::createFromFormat('Y-m-d', $profile->field_birth->value);
      $person->setDateOfBirth($date);
    }

    if ($profile->hasField('field_salutation') && !empty($profile->field_salutation->value)) {
      $salutation = $profile->field_salutation->value;
      if ($salutation == 'Herr') {
        $salutation = 'M';
      }
      else {
        $salutation = 'F';
      }
      $person->setSalutation($salutation);
    }

    return $person;
  }

  /**
   *
   */
  public function getLangcode(User $user) {
    $langcode = $user->getPreferredLangcode();
    if ($langcode === NULL) {
      return 'DE';
    }
    if (strpos('-', $langcode) !== 0) {
      $langcode_ary = explode('-', $langcode);
      $langcode = $langcode_ary[0];
    }
    return strtoupper($langcode);
  }

}
