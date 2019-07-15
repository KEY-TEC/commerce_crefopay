<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the Secure fields payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "crefopay_secure_fields",
 *   label = "Crefopay (Secure fields)",
 *   display_label = "Crefopay (Secure fields)",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_crefopay\PluginForm\SecureFields\SecureFieldsForm",
 *   },
 *   requires_billing_information = TRUE,
 * )
 */
class SecureFields extends BasePaymentGateway {

}
