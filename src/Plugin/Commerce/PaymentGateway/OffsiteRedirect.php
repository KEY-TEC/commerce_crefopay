<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;

/**
 * Provides the Off-site Redirect payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "crefopay_offsite_redirect",
 *   label = "CrefoPay HostedPage (Offsite redirect)",
 *   display_label = "CrefoPay HostedPage (Offsite redirect)",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_crefopay\PluginForm\OffsiteRedirect\PaymentOffsiteForm",
 *   },
 *   requires_billing_information = TRUE,
 * )
 */
class OffsiteRedirect extends BasePaymentGateway implements SupportsRefundsInterface {

}
