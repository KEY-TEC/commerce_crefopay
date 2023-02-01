<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use CrefoPay\Library\Api\Exception\Validation;
use CrefoPay\Library\Risk\RiskClass;
use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\Core\Site\Settings;
use Drupal\user\Entity\User;
use CrefoPay\Library\Integration\Type;
use CrefoPay\Library\User\Type as UserType;

/**
 * Provides the Secure fields payment gateway for subscriptions.
 *
 * @CommercePaymentGateway(
 *   id = "crefopay_secure_fields_subscription",
 *   label = "CrefoPay SecureFields for subscriptions",
 *   display_label = "CrefoPay SecureFields for subscriptions",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_crefopay\PluginForm\SecureFields\SecureFieldsForm",
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
   * @return \CrefoPay\Library\Request\Objects\PaymentInstrument[]
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
    $trial_days = NULL;

    foreach ($items as $item) {
      $purchased_product = $item->getPurchasedEntity();

      if ($purchased_product->hasField('field_subscription_plan') &&
        $purchased_product->field_subscription_plan->entity != NULL) {
        /** @var \Drupal\sw_subscription\Entity\SubscriptionPlanInterface $subscription_plan */
        $subscription_plan = $purchased_product->field_subscription_plan->entity;
        $trial_days = $subscription_plan->getTrialDays();
        $plan_reference = $subscription_plan->crefopay_subscription_plan->value;
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
      $risk_class = RiskClass::RISK_CLASS_DEFAULT;
      $data = [
        'user_type' => $user_type,
        'risk_class' => $risk_class,
        'trial_days' => NULL
      ];
      $context = ['order' => $order];
      \Drupal::moduleHandler()
        ->alter('commerce_crefopay_transaction_data', $data, $context);
      $user_type = $data['user_type'];
      $risk_class = $data['risk_class'];

      if ($user_type == UserType::USER_TYPE_BUSINESS) {

        /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $billing_address */
        $billing_address = $billing_profile->address[0];
        if (empty($billing_address->getOrganization())) {
          throw new PaymentGatewayException($this->t('Company name is required.'));
        }
      }

      if (!empty($data['trial_days'])) {
        $trial_days = $data['trial_days'];
      }
      $instruments = $this->subscriptionClient->createSubscription($order, $user, $billing_profile, $plan_reference, $shipment_address, Type::INTEGRATION_TYPE_SECURE_FIELDS, $user_type, $trial_days, $risk_class);
    }
    catch (OrderIdAlreadyExistsException $oe) {
      // Throw new PaymentGatewayException('Order already exists.');
      // Transaction already started.
    }
    catch (Validation $validation_exception) {
      // Validation exception from api returned.
      $validations = [];
      foreach ($validation_exception->getValidationResults() as $validation_object) {
        foreach ($validation_object as $validation_messages) {
          foreach ($validation_messages as $validation_message) {
            // Make validation messages translatable.
            $validations[] = $this->t($validation_message)->render();
          }
        }
      }
      $this->logger->error($validation_exception->getMessage() . ': ' . implode(', ', $validations));
      throw new PaymentGatewayException($this->t($validation_exception->getMessage() . ': :validations', [':validations' => implode(', ', $validations)]));
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
