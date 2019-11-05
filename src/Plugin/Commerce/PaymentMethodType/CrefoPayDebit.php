<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentMethodType;

use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeBase;

/**
 * Provides the CrefoPay Debit payment method type.
 *
 * @CommercePaymentMethodType(
 *   id = "crefopay_debit",
 *   label = @Translation("CrefoPay Direct debit"),
 *   create_label = @Translation("Direct debit"),
 * )
 */
class CrefoPayDebit extends PaymentMethodTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildLabel(PaymentMethodInterface $payment_method) {
    return $this->t('Direct debit');
  }

}
