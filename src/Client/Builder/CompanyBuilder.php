<?php

namespace Drupal\commerce_crefopay\Client\Builder;

use Upg\Library\Request\Objects\Company;

/**
 *
 */
class CompanyBuilder {

  /**
   * @param string $companyName
   * @return \Upg\Library\Request\Objects\Company
   */
  public function build(string $companyName): Company {
    $company = new Company();
    $company->setCompanyName($companyName);

    return $company;
  }

}
