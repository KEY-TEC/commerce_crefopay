<?php

namespace Drupal\commerce_crefopay\PluginForm\OffsiteRedirect;

use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
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
    /** @var \Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway\BasePaymentGateway $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();

    /** @var \Drupal\commerce_crefopay\Client\TransactionClientInterface $transaction_client */
    $redirect_url = $payment_gateway_plugin->handleTransaction($payment);
    $form['#redirect_url'] = $redirect_url;

    $data = [];
    $form = $this->buildRedirectForm($form, $form_state, $redirect_url, $data, 'GET');
    return $form;
  }

}
