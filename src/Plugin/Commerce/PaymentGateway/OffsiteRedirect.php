<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Off-site Redirect payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "crefopay_offsite_redirect",
 *   label = "Crefopay (Off-site redirect)",
 *   display_label = "Crefopay",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_crefopay\PluginForm\OffsiteRedirect\PaymentOffsiteForm",
 *   },
 *   requires_billing_information = FALSE,
 * )
 */
class OffsiteRedirect extends BasePaymentGateway {

}
