<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentMethodType;

use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeBase;

/**
 * Provides the CrefoPay Debit payment method type.
 *
 * @CommercePaymentMethodType(
 *   id = "crefopay_sofort",
 *   label = @Translation("CrefoPay Sofort Überweisung"),
 *   create_label = @Translation("Sofort Überweisung"),
 * )
 */
class CrefoPaySofort extends PaymentMethodTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildLabel(PaymentMethodInterface $payment_method) {
    return $this->t('Sofort Überweisung');
  }

}
