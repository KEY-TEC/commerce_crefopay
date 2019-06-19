<?php
namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_braintree\ErrorHelper;
use Drupal\commerce_payment\CreditCard;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Exception\HardDeclineException;
use Drupal\commerce_payment\Exception\InvalidRequestException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
* Provides the HostedFields payment gateway.
*
* Not supported right now!
*
* @CommercePaymentGateway(
*   id = "commerce_crefopay_hostedfields",
*   label = "Crefopay (Hosted Fields) - Not supported right now",
*   display_label = "Crefopay",
*   forms = {
*     "add-payment-method" = "Drupal\commerce_crefopay\PluginForm\HostedFields\PaymentMethodAddForm",
*   },
*   js_library = "commerce_crefopay/crefopay",
*   payment_method_types = {"credit_card", "paypal"},
*   credit_card_types = {
*     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa",
*   },
* )
*/
class HostedFields extends OnsitePaymentGatewayBase {

  public function createPayment(PaymentInterface $payment, $capture = TRUE) {
    // @TODO: Implemnet createPayment.

  }

  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    // TODO: Implement createPaymentMethod() method.
  }

  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    // TODO: Implement deletePaymentMethod() method.
  }
}