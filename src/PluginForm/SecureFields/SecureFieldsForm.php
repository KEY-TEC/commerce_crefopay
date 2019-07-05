<?php

namespace Drupal\commerce_crefopay\PluginForm\SecureFields;

use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

class SecureFieldsForm extends BasePaymentOffsiteForm {

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
    /** @var \Drupal\commerce_crefopay\ConfigProviderInterface $config_provider */
    $config_provider = \Drupal::service('commerce_crefopay.config_provider');
    $subscription_order_type_id = $config_provider->getSubscriptionOrderTypeId();
    $transaction_client = \Drupal::service('commerce_crefopay.transaction_client');

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
        throw new PaymentGatewayException('Unknown subscription plan. Please check product configuration.');
      }
      /** @var \Drupal\commerce_crefopay\Client\SubscriptionClient $subscription_client */
      $subscription_client = \Drupal::service('commerce_crefopay.subscription_client');
      try {
        $subscription_client->createSubscription($order, $user, $address, $plan_reference);
      }
      catch (OrderIdAlreadyExistsException $oe) {
        //throw new PaymentGatewayException('Order already exists.');
        // Transaction already started.
      }
      catch (\Throwable $exception) {
        throw new PaymentGatewayException('Unexcpected error.');
      }
    }
    else {
      /** @var \Drupal\commerce_crefopay\Client\TransactionClient $transaction_client */
      try {
        $transaction_client->createTransaction($order, $user, $address, "SecureFields");
        /** @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder $id_builder */
      }
      catch (OrderIdAlreadyExistsException $oe) {
        //throw new PaymentGatewayException('Order already exists.');
        // Transaction already started.
      }
      catch (\Throwable $exception) {
        throw new PaymentGatewayException('Unexcpected error.');
      }

    }

    $instruments = $transaction_client->getTransactionPaymentInstruments($order);
    $id_builder = \Drupal::service('commerce_crefopay.id_builder');
    /** @var \Drupal\commerce_crefopay\ConfigProvider $config_provider */
    $config_provider = \Drupal::service('commerce_crefopay.config_provider');
    $config_array = $config_provider->getConfigArray();
    $secure_fields_url = $config_provider->getSecureFieldsUrl('test');

    $form['crefopay_payment'] = [
      '#theme' => 'crefopay_payment',
      '#allowed_methods' => array_fill_keys($instruments['allowedPaymentMethods'], true),
      '#additional_information' => $instruments['additionalInformation'],
      '#attached' => [
        'library' => ['commerce_crefopay/crefopay'],
        'drupalSettings' => [
        'crefopay' => [
          'orderId' => $id_builder->id($order),
          'placeholder' => [],
          'secureFieldsUrl' => $secure_fields_url,
          'shopPublicKey' => $config_array['shopPublicKey']
          ]
        ]
      ],
    ];

    return $form;
  }

}
