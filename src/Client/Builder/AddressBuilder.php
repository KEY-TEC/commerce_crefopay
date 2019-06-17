<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\address\AddressInterface;
use Upg\Library\Request\Objects\Address;

class AddressBuilder {

  /**
   * Build Crefopay address from Drupal address.
   *
   * @param Drupal\address\AddressInterface $address
   *   The Drupal address.
   *
   * @return Address
   *   The Crefopay address.
   */
  public function build(AddressInterface $address) {
    $crefo_pay_address = new Address();
    $crefo_pay_address->setStreet($address->getAddressLine1());
    $crefo_pay_address->setZip($address->getPostalCode());
    $crefo_pay_address->setCity($address->getLocality());
    $crefo_pay_address->setState($address->getAdministrativeArea());
    $crefo_pay_address->setCountry($address->getCountryCode());

    return $crefo_pay_address;
  }
}
