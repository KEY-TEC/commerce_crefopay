<?php

namespace Drupal\commerce_crefopay\PluginForm\SecureFields;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;

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
    $instruments = $payment_gateway_plugin->createTransaction($payment);

    $config_provider = $payment_gateway_plugin->getConfigProvider();

    $config_array = $config_provider->getConfigArray();
    $secure_fields_url = $config_provider->getSecureFieldsUrl($payment_gateway_plugin->getMode());

    $allowed_intruments_twig = [];
    $allowed_intruments = $instruments['allowedPaymentInstruments'];

    /** @var \Upg\Library\Request\Objects\PaymentInstrument $allowed_intrument */
    foreach ($allowed_intruments as $allowed_intrument) {
      $allowed_intruments_twig[] = $allowed_intrument->toArray();
    }

    $form['crefopay_payment'] = [
      '#theme' => 'crefopay_payment',
      '#allowed_methods' => array_fill_keys($instruments['allowedPaymentMethods'], true),
      '#allowed_intruments' => $allowed_intruments_twig,
      '#additional_information' => $instruments['additionalInformation'],
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
    ];

    return $form;
  }

}
