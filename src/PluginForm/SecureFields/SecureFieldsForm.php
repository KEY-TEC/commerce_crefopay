<?php

namespace Drupal\commerce_crefopay\PluginForm\SecureFields;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for secure fields integration.
 */
class SecureFieldsForm extends BasePaymentOffsiteForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway\BasePaymentGateway $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();

    $order = $payment->getOrder();
    $payment_data = $payment_gateway_plugin->handleTransaction($payment);

    $config_provider = $payment_gateway_plugin->getConfigProvider();

    $config_array = $config_provider->getConfigArray();
    $secure_fields_url = $config_provider->getSecureFieldsUrl($payment_gateway_plugin->getMode());

    $form['crefopay_payment'] = [
      '#theme' => 'crefopay_payment',
      '#allowed_methods' => $payment_data['allowedPaymentMethods'],
      '#allowed_intruments' => $payment_data['allowedPaymentInstruments'],
      '#additional_information' => $payment_data['additionalInformation'],
      '#order' => $order,
      '#attached' => [
        'library' => ['commerce_crefopay/crefopay'],
        'drupalSettings' => [
          'crefopay' => [
            'orderId' => $payment_gateway_plugin->getIdBuilder()->id($order),
            'placeholder' => [],
            'secureFieldsUrl' => $secure_fields_url,
            'shopPublicKey' => $config_array['shopPublicKey'],
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $form;
  }

}
