<?php

namespace Drupal\commerce_crefopay\PluginForm\OffsiteRedirect;

use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class PaymentOffsiteForm extends BasePaymentOffsiteForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
    $order = $payment->getOrder();
    $billing_profile = $order->getBillingProfile();
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address_item */
    $address = $billing_profile->address[0];

    $user = User::load(\Drupal::currentUser()->id());
    // Simulate an API call failing and throwing an exception, for test purposes.
    // See PaymentCheckoutTest::testFailedCheckoutWithOffsiteRedirectGet().
    if ($order->getBillingProfile()->get('address')->family_name == 'FAIL') {
      throw new PaymentGatewayException('Could not get the redirect URL.');
    }
    /** @var \Drupal\commerce_crefopay\ConfigProviderInterface $config_provider */
    $config_provider = \Drupal::service('commerce_crefopay.config_provider');
    $subscription_order_type_id = $config_provider->getSubscriptionOrderTypeId();

    if ($subscription_order_type_id === $order->bundle()) {
      $items = $order->getItems();

      $plan_reference = NULL;
      foreach ($items as $item) {
        $purchased_product = $item->getPurchasedEntity();
        if ($purchased_product->hasField('crefopay_subscription_plan') &&
          $purchased_product->crefopay_subscription_plan->value != NULL) {
        }
        $plan_reference = $purchased_product->crefopay_subscription_plan->value;
        break;
      }
      if ($plan_reference == NULL) {
        throw new \Exception('Unknown subscription plan. Please check product configuration.');
      }
      /** @var \Drupal\commerce_crefopay\Client\SubscriptionClient $subscription_client */
      $subscription_client = \Drupal::service('commerce_crefopay.subscription_client');
      $redirect_url = $subscription_client->createSubscription($order, $user, $address);
    }
    else {
      /** @var \Drupal\commerce_crefopay\Client\TransactionClient $transaction_client */
      $transaction_client = \Drupal::service('commerce_crefopay.transaction_client');
      $redirect_url = $transaction_client->createTransaction($order, $user, $address);
    }

    $data = [
    ];
    $form = $this->buildRedirectForm($form, $form_state, $redirect_url, $data, 'GET');
    return $form;
  }

}
