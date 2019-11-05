<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentMethodType;

use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeBase;

/**
 * Provides the CrefoPay Unknown payment method type.
 *
 * @CommercePaymentMethodType(
 *   id = "crefopay_unknown",
 *   label = @Translation("CrefoPay Unknown"),
 *   create_label = @Translation("Unknown"),
 * )
 */
class CrefoPayUnknown extends PaymentMethodTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildLabel(PaymentMethodInterface $payment_method) {
    return $this->t('Unknown');
  }

}
