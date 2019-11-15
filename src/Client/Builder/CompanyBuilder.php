<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use Upg\Library\Request\Objects\Company;

/**
 *
 */
class CompanyBuilder {

  /**
   * @param string $companyName
   * @return \Upg\Library\Request\Objects\Company
   */
  public function build(User $user, ProfileInterface $profile): Company {
    $company = new Company();
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address */
    $address = $profile->address[0];
    $organization = $address->getOrganization();
    $company->setCompanyName($organization);
    $company->setEmail($user->getEmail());
    return $company;
  }

}
