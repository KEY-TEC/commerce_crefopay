<?php

/**
 * @file
 * Contains \Drupal\commerce_crefopay\Form\CrefopayConfigForm.
 */

namespace Drupal\commerce_crefopay\Form;

use Drupal\Core\Form\ConfigFormBase;

use Drupal\Core\Form\FormStateInterface;

class CrefopayConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */

  public function getFormId() {
    return 'commerce_crefopay_config_form';
  }

  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('commerce_crefopay.settings');
    $form['baseUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base Url'),
      '#default_value' => $config->get('baseUrl'),
      '#required' => TRUE,
    ];
    $form['storeID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Store ID'),
      '#default_value' => $config->get('storeID'),
      '#required' => TRUE,
    ];
    $form['merchantID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant ID'),
      '#default_value' => $config->get('merchantID'),
      '#required' => TRUE,
    ];
    $form['merchantPassword'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant Password'),
      '#default_value' => $config->get('merchantPassword'),
      '#required' => TRUE,
    ];

    $form['shopPublicKey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public key'),
      '#default_value' => $config->get('shopPublicKey'),
      '#required' => TRUE,
    ];

    $order_types = \Drupal::entityTypeManager()->getStorage('commerce_order_type')->loadMultiple();
    $order_types_options = [];
    foreach ($order_types as $order_type) {
      $order_types_options[$order_type->id()] = $order_type->label();
    }

    $form['subscriptionOrderTypeId'] = [
      '#type' => 'select',
      '#title' => $this->t('Subscription Order Type'),
      '#default_value' => $config->get('subscriptionOrderTypeId'),
      '#required' => FALSE,
      '#options' => $order_types_options
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('commerce_crefopay.settings');
    $config->set('baseUrl', $form_state->getValue('baseUrl'));
    $config->set('storeID', $form_state->getValue('storeID'));
    $config->set('merchantID', $form_state->getValue('merchantID'));
    $config->set('shopPublicKey', $form_state->getValue('shopPublicKey'));
    $config->set('merchantPassword', $form_state->getValue('merchantPassword'));
    $config->set('subscriptionOrderTypeId', $form_state->getValue('subscriptionOrderTypeId'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */

  protected function getEditableConfigNames() {
    return [
      'commerce_crefopay.settings',
    ];
  }

}
