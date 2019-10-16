<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\user\Entity\User;

/**
 * Provides the Secure fields payment gateway for subscriptions.
 *
 * @CommercePaymentGateway(
 *   id = "crefopay_secure_fields_subscription",
 *   label = "CrefoPay SecureFields for subscriptions",
 *   display_label = "CrefoPay SecureFields for subscriptions",
 *   forms = {
 *     "offsite-payment" =
 *   "Drupal\commerce_crefopay\PluginForm\SecureFields\SecureFieldsForm",
 *   },
 *   requires_billing_information = TRUE,
 * )
 */
class SecureFieldsSubscription extends BasePaymentGateway {

  /**
   * Calls CrefoPay create subscription.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The current payment.
   *
   * @return \Upg\Library\Request\Objects\PaymentInstrument[]
   *   Payment instruments.
   */
  protected function createTransaction(PaymentInterface $payment) {
    $order = $payment->getOrder();
    $billing_profile = $order->getBillingProfile();

    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address_item */
    $user = User::load($order->getCustomerId());
    $shipment_address = NULL;
    $instruments = NULL;
    $items = $order->getItems();
    $plan_reference = NULL;
    foreach ($items as $item) {
      $purchased_product = $item->getPurchasedEntity();

      if ($purchased_product->hasField('field_subscription_plan') &&
        $purchased_product->field_subscription_plan->entity != NULL) {
        $plan_reference = $purchased_product->field_subscription_plan->entity->crefopay_subscription_plan->value;
        break;
      }
      else if ($purchased_product->hasField('crefopay_subscription_plan') &&
        $purchased_product->crefopay_subscription_plan->value != NULL) {
        $plan_reference = $purchased_product->crefopay_subscription_plan->value;
        break;
      }
    }
    if ($plan_reference == NULL) {
      throw new PaymentGatewayException('Unknown subscription plan. Please check product configuration.');
    }
    try {
      $shipment_address = $this->getShipmentProfile($order);
      $instruments = $this->subscriptionClient->createSubscription($order, $user, $billing_profile, $plan_reference, $shipment_address);
    }
    catch (OrderIdAlreadyExistsException $oe) {
      // Throw new PaymentGatewayException('Order already exists.');
      // Transaction already started.
    }
    catch (\Throwable $exception) {
      $this->logger->error($exception->getMessage());
      throw new PaymentGatewayException($this->t('We encountered an unexpected error processing your payment method. Please try again later.'));
    }
    if ($instruments == NULL) {
      try {
        $instruments = $this->transactionClient->getTransactionPaymentInstruments($order);
      }
      catch (\Throwable $exception) {
        $this->logger->error($exception->getMessage());
        throw new PaymentGatewayException($this->t('We encountered an unexpected error processing your payment method. Please try again later.'));
      }

    }
    return $instruments;
  }

}
