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
* @CommercePaymentGateway(
*   id = "commerce_crefopay_hostedfields",
*   label = "Crefopay (Hosted Fields)",
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
    $this->assertPaymentState($payment, ['new']);
    $payment_method = $payment->getPaymentMethod();
    $this->assertPaymentMethod($payment_method);
    $amount = $payment->getAmount();
    $currency_code = $payment->getAmount()->getCurrencyCode();


    $transaction_data = [
      'channel' => 'CommerceGuys_BT_Vzero',
      'merchantAccountId' => $this->configuration['merchant_account_id'][$currency_code],
      // orderId must be unique.
      'orderId' => $payment->getOrderId() . '-' . $this->time->getCurrentTime(),
      'amount' => $amount->getNumber(),
      'options' => [
        'submitForSettlement' => $capture,
      ],
    ];
    if ($payment_method->isReusable()) {
      $transaction_data['paymentMethodToken'] = $payment_method->getRemoteId();
    }
    else {
      $transaction_data['paymentMethodNonce'] = $payment_method->getRemoteId();
    }

    try {
      //$result = $this->api->transaction()->sale($transaction_data);
      //ErrorHelper::handleErrors($result);
    }
    catch (\Braintree\Exception $e) {
      ErrorHelper::handleException($e);
    }

    $next_state = $capture ? 'completed' : 'authorization';
    $payment->setState($next_state);
    $payment->setRemoteId($result->transaction->id);
    // @todo Find out how long an authorization is valid, set its expiration.
    $payment->save();

  }

  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    // TODO: Implement createPaymentMethod() method.
  }

  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    // TODO: Implement deletePaymentMethod() method.
  }
}