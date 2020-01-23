<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\Core\Site\Settings;
use Drupal\user\Entity\User;
use Upg\Library\Integration\Type;
use Upg\Library\User\Type as UserType;

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
    // This is for debug purpose. For development it should be possible to use a static
    // plan reference.
    $debug_plan_reference = Settings::get('crefopay_id_debug_plan_reference');
    if (empty($debug_plan_reference) == FALSE) {
      $plan_reference = $debug_plan_reference;
    }

    if ($plan_reference == NULL) {
      throw new PaymentGatewayException('Unknown subscription plan. Please check product configuration.');
    }
    try {
      $shipment_address = $this->getShipmentProfile($order);
      $user_type = UserType::USER_TYPE_PRIVATE;
      $data = [
        'user_type' => $user_type,
        'trial_days' => NULL
      ];
      $context = ['order' => $order];
      \Drupal::moduleHandler()
        ->alter('commerce_crefopay_transaction_data', $data, $context);
      $user_type = $data['user_type'];
      if ($user_type == UserType::USER_TYPE_BUSINESS) {
        /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $billing_address */
        $billing_address = $billing_profile->address[0];
        if (empty($billing_address->getOrganization())) {
          throw new PaymentGatewayException($this->t('Company name is required.'));
        }
      }

      $trial_days = NULL;
      if (!empty($data['trial_days'])) {
        $trial_days = $data['trial_days'];
      }

      $instruments = $this->subscriptionClient->createSubscription($order, $user, $billing_profile, $plan_reference, $shipment_address, Type::INTEGRATION_TYPE_SECURE_FIELDS, $user_type, $trial_days);
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
