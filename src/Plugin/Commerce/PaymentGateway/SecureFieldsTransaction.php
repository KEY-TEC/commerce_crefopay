<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;

/**
 * Provides the Secure fields payment gateway for payments.
 *
 * @CommercePaymentGateway(
 *   id = "crefopay_secure_fields",
 *   label = "CrefoPay SecureFields for transactions",
 *   display_label = "CrefoPay SecureFields for transaction",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_crefopay\PluginForm\SecureFields\SecureFieldsForm",
 *   },
 *   requires_billing_information = TRUE,
 * )
 */
class SecureFieldsTransaction extends BasePaymentGateway implements SupportsRefundsInterface {

}
