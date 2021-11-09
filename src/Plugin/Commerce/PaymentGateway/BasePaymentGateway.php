<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_crefopay\Client\UserNotExistsException;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentStorageInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Upg\Library\Integration\Type;
use Upg\Library\User\Type as UserType;

/**
 * Class BasePaymentGateway.
 *
 * @package Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway
 */
abstract class BasePaymentGateway extends OffsitePaymentGatewayBase {

  /**
   * The transaction client.
   *
   * @var \Drupal\commerce_crefopay\Client\TransactionClientInterface
   */
  protected $transactionClient;

  /**
   * The subscription client.
   *
   * @var \Drupal\commerce_crefopay\Client\SubscriptionClientInterface
   */
  protected $subscriptionClient;

  /**
   * The IdBuilder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder
   */
  protected $idBuilder;

  /**
   * The config provider.
   *
   * @var \Drupal\commerce_crefopay\ConfigProviderInterface
   */
  protected $configProvider;

  /**
   * The logger.
   *
   * @var Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('datetime.time')
    );

    $instance->transactionClient = $container->get('commerce_crefopay.transaction_client');
    $instance->subscriptionClient = $container->get('commerce_crefopay.subscription_client');
    $instance->configProvider = $container->get('commerce_crefopay.config_provider');
    $instance->idBuilder = $container->get('commerce_crefopay.id_builder');
    $instance->logger = $container->get('logger.channel.commerce_payment');
    return $instance;
  }

  /**
   * Returns the config provider.
   *
   * @return \Drupal\commerce_crefopay\ConfigProviderInterface
   *   The config provider.
   */
  public function getConfigProvider() {
    return $this->configProvider;
  }

  /**
   * Returns the idBuilder.
   *
   * @return \Drupal\commerce_crefopay\Client\Builder\IdBuilder
   *   The idBuilder
   */
  public function getIdBuilder() {
    return $this->idBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
  }

  /**
   * Checks for a running transaction otherwise create a new one.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment entity.
   *
   * @return array
   *   The transaction status data.
   *   Keys:
   *    - allowedPaymentInstruments
   *    - additionalInformation
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\commerce_crefopay\Client\UserNotExistsException
   */
  public function handleTransaction(PaymentInterface $payment) {
    $order = $payment->getOrder();
    if ($order->getData('crefopay_transaction_started') != NULL &&
      is_array($order->getData('crefopay_transaction_data'))) {
      return $order->getData('crefopay_transaction_data');
    }
    else {
      $instruments = $this->createTransaction($payment);
      $data = [];
      $allowed_instruments = $instruments['allowedPaymentInstruments'];
      $data['allowedPaymentInstruments'] = [];
      /** @var \Upg\Library\Request\Objects\PaymentInstrument $allowed_intrument */
      foreach ($allowed_instruments as $allowed_instrument) {
        $data['allowedPaymentInstruments'][] = $allowed_instrument->toArray();
      }
      $data['allowedPaymentMethods'] = array_fill_keys($instruments['allowedPaymentMethods'], TRUE);
      $data['additionalInformation'] = $instruments['additionalInformation'];
      $order->setData('crefopay_transaction_data', $data);
      $order->setData('crefopay_transaction_started', TRUE);
      $order->setData('crefopay_language', \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId());
      $order->save();
      $this->createPayment($order);
      return $data;
    }
  }

  public function createPayment($order, $amount = 0, $remote_id = NULL, $state = 'new') {
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');

    if ($amount > 0) {
      // Amount is in cent: 6900.
      $amount = $amount/100;
    }
    $amount = new Price($amount, 'EUR');

    $payment = $payment_storage->create([
      'state' => $state,
      'amount' => $amount,
      'payment_gateway' => $this->parentEntity->id(),
      'order_id' => $order->id(),
      'remote_id' => $remote_id
    ]);
    $payment->save();
    return $payment;
  }

  /**
   * Update payment status.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   */
  public function updatePayment(PaymentInterface $payment, $amount = 0, $capture_id = NULL, $state = NULL) {
    $order = $payment->getOrder();

    $transaction_status = $this->transactionClient->getTransactionStatus($order);
    if (!$state) {
      $remote_state = $transaction_status['transactionStatus'];
      $state = $this->mapCrefopayStateToPayment($remote_state);
      $payment->setRemoteState($remote_state);
    }
    $payment->setState($state);

    $payment_method = $payment->getPaymentMethod();
    if ($payment_method == NULL) {
      $remote_payment_method = $transaction_status['additionalData']['paymentMethod'];
      $payment_method_type = $this->getMappedPaymentMethod($remote_payment_method);
      $payment_method_storage = $this->entityTypeManager->getStorage('commerce_payment_method');
      $payment_method = $payment_method_storage->create([
        'type' => $payment_method_type,
        'payment_gateway' => $this->parentEntity->id(),
        'remote_id' => $remote_payment_method,
        'uid' => $order->getCustomerId(),
      ]);
      $payment_method->save();
      $payment->payment_method->appendItem($payment_method);
    }

    if ($capture_id != NULL) {
      $payment->setRemoteId($capture_id);
    }

    if ($amount > 0) {
      // Amount is in cent: 6900.
      $amount = $amount/100;
    }
    $amount = new Price($amount, 'EUR');

    if ($amount->isPositive()) {
      // Update payment amount, if not zero.
      $payment->setAmount($amount);
    }

    $payment->save();
  }

  /**
   * Returns the shipment address if one exists.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The order.
   *
   * @return \Drupal\address\Element\Address
   *  The shipment address.
   */
  protected function getShipmentProfile(Order $order) {
    $shipment_address = NULL;
    if ($order->hasField('shipments')) {
      $shipments = $order->shipments;
      if (isset($shipments[0])) {
        /** @var \Drupal\commerce_shipping\Entity\Shipment $shipment */
        $shipment = $shipments[0]->entity;
        if ($shipment != NULL) {
          $shipment_profile = $shipment->getShippingProfile();
          return $shipment_profile;
        }
      }
    }
    return $shipment_address;
  }

  /**
   * Calls a CrefoPay "create transaction".
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   *
   * @return \Upg\Library\Request\Objects\PaymentInstrument[]
   *   Payment instruments.
   */
  protected function createTransaction(PaymentInterface $payment) {
    $order = $payment->getOrder();
    $billing_profile = $order->getBillingProfile();
    /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address_item */

    $user = User::load($order->getCustomerId());
    if ($user == NULL && $user->id() == 0) {
      throw new UserNotExistsException($order->getCustomerId());
    }

    $instrument_profile = $this->getShipmentProfile($order);
    /** @var \Drupal\commerce_crefopay\Client\TransactionClient $transaction_client */
    try {
      $user_type = UserType::USER_TYPE_PRIVATE;
      $data = [
        'user_type' => $user_type,
      ];
      $context = ['order' => $order];
      \Drupal::moduleHandler()
        ->alter('commerce_crefopay_transaction_data', $data, $context);
      $user_type = $data['user_type'];
      $instruments = $this->transactionClient->createTransaction($order, $user, $billing_profile, Type::INTEGRATION_TYPE_SECURE_FIELDS, $instrument_profile, $user_type);
    } catch (OrderIdAlreadyExistsException $oe) {
      // Throw new PaymentGatewayException('Order already exists.');
      // Transaction already started.
      $this->logger->error($oe->getMessage());
    } catch (\Throwable $exception) {
      $this->logger->error($exception->getMessage());
      throw new PaymentGatewayException($this->t('We encountered an unexpected error processing your payment method. Please try again later.'));
    }

    if ($instruments == NULL) {
      try {
        $instruments = $this->transactionClient->getTransactionPaymentInstruments($order);
      } catch (\Throwable $exception) {
        $this->logger->error($exception->getMessage());
        throw new PaymentGatewayException($this->t('We encountered an unexpected error processing your payment method. Please try again later.'));
      }

    }
    return $instruments;
  }

  public function getPaymentByOrder(OrderInterface $order, $remote_id = NULL) {
    /** @var PaymentStorageInterface $payment_storage */
    $payment_storage = \Drupal::entityTypeManager()
      ->getStorage('commerce_payment');

    if ($remote_id != NULL) {
      $payments = $payment_storage->loadByProperties([
        'remote_id' => $remote_id,
        'order_id' => $order->id(),
      ]);
      $payment = reset($payments);
      return $payment ?: NULL;
    }

    $payments = $payment_storage->loadMultipleByOrder($order);
    foreach ($payments as $payment) {
      // Take the first payment found.
      return $payment;
    }

    $order_id = $order->id();
    \Drupal::logger('commerce_payment')
      ->critical("PN: No payment found for Order: $order_id ");
    return NULL;
  }

  /**
   * Returns the mapped payment method.
   */
  private function getMappedPaymentMethod($remote_id) {
    switch ($remote_id) {
      case "CC":
      case "CC3D":
        $type = "crefopay_credit_card";
        break;
      case "PAYPAL":
        $type = "crefopay_paypal";
        break;
      case "SU":
        $type = "crefopay_sofort";
        break;
      case "DD":
        $type = "crefopay_debit";
        break;
      default:
        $type = "crefopay_unknown";
        break;
    }
    return $type;
  }

  public function mapCrefopayStateToPayment($crefopay_state){
    switch ($crefopay_state) {
      case "DONE":
        $state = "completed";
        break;

      case "NEW":
        $state = "new";
        break;

      case "ACKNOWLEDGEPENDING":
      case "FRAUDPENDING":
      case "MERCHANTPENDING":
      case "CIAPENDING":
      case "INPROGRESS":
        $state = "authorization";
        break;

      case "CANCELLED":
      case "FRAUDCANCELLED":
        $state = "refunded";
        break;

      case "EXPIRED":
        $state = 'authorization_expired';
        break;

      default:
        $state = "new";
        break;
    }
    return $state;
  }
  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    $payment = $this->getPaymentByOrder($order);
    if ($payment != NULL) {
      $this->updatePayment($payment);
      if ($order->payment_method->entity == NULL && $payment->getPaymentMethod() != NULL) {
        $order->payment_method->appendItem($payment->getPaymentMethod());
        $order->save();
      }
    }
  }

  public function validateMac($order){
  }

  /**
   * Refund payment.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   * @param \Drupal\commerce_price\Price|null $amount
   *   The amount.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    $this->transactionClient->refund($payment, $amount, "Refund", $payment->getOrderId());

    $this->updatePayment($payment);
    $payment->save();
  }

}
