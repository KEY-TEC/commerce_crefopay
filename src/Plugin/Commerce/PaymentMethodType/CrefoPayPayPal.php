<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentMethodType;

use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeBase;

/**
 * Provides the CrefoPay PayPal payment method type.
 *
 * @CommercePaymentMethodType(
 *   id = "crefopay_paypal",
 *   label = @Translation("CrefoPay PayPal"),
 *   create_label = @Translation("PayPal"),
 * )
 */
class CrefoPayPayPal extends PaymentMethodTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildLabel(PaymentMethodInterface $payment_method) {
    return $this->t('PayPal');
  }

}
