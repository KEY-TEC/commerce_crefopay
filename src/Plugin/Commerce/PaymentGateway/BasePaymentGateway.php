<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_crefopay\Client\Builder\IdBuilder;
use Drupal\commerce_crefopay\Client\OrderIdAlreadyExistsException;
use Drupal\commerce_crefopay\Client\SubscriptionClientInterface;
use Drupal\commerce_crefopay\Client\TransactionClientInterface;
use Drupal\commerce_crefopay\Client\UserNotExistsException;
use Drupal\commerce_crefopay\ConfigProviderInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentStorageInterface;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
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
   * Constructs a new PaymentGatewayBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_payment\PaymentTypeManager $payment_type_manager
   *   The payment type manager.
   * @param \Drupal\commerce_payment\PaymentMethodTypeManager $payment_method_type_manager
   *   The payment method type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\commerce_crefopay\ConfigProviderInterface $config_provider
   *   The config provider.
   * @param \Drupal\commerce_crefopay\Client\TransactionClientInterface $transaction_client
   *   The transaction client.
   * @param \Drupal\commerce_crefopay\Client\SubscriptionClientInterface $subscription_client
   *   The subscription client.
   * @param \Drupal\commerce_crefopay\Client\Builder\IdBuilder $id_builder
   *   The id builder.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PaymentTypeManager $payment_type_manager, PaymentMethodTypeManager $payment_method_type_manager, TimeInterface $time, ConfigProviderInterface $config_provider, TransactionClientInterface $transaction_client, SubscriptionClientInterface $subscription_client, IdBuilder $id_builder, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time);
    $this->transactionClient = $transaction_client;
    $this->subscriptionClient = $subscription_client;
    $this->configProvider = $config_provider;
    $this->idBuilder = $id_builder;
    $this->logger = $logger;
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
      $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
      $payment = $payment_storage->create([
        'state' => 'new',
        'amount' => $order->getBalance(),
        'payment_gateway' => $this->entityId,
        'order_id' => $order->id(),
      ]);
      $payment->save();
      return $data;
    }
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
      $user_type = Type::USER_TYPE_PRIVATE;
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('datetime.time'),
      $container->get('commerce_crefopay.config_provider'),
      $container->get('commerce_crefopay.transaction_client'),
      $container->get('commerce_crefopay.subscription_client'),
      $container->get('commerce_crefopay.id_builder'),
      $container->get('logger.channel.commerce_payment')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
  }

  public function getPaymentByOrder(OrderInterface $order, $capture_id = NULL) {
    $payment = NULL;
    /** @var PaymentStorageInterface $payment_storage */
    $payment_storage = \Drupal::entityTypeManager()
      ->getStorage('commerce_payment');
    if ($capture_id != NULL) {
      $payment = $payment_storage->loadByRemoteId($capture_id);
    }
    if ($payment == NULL) {
      $payments = $payment_storage->loadMultipleByOrder($order);
      /** @var PaymentInterface $payment */
      foreach ($payments as $item) {
        $payment = $item;
        break;
      }
    }
    if ($payment == NULL) {
      $order_id = $order->id();
      \Drupal::logger('commerce_payment')
        ->critical("PN: No payment found for Order: $order_id ");
      return NULL;
    }
    return $payment;
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

  /**
   * Update payment status.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   */
  public function updatePayment(PaymentInterface $payment, $capture_id = NULL) {
    $order = $payment->getOrder();
    $transaction_status = $this->transactionClient->getTransactionStatus($order);
    $remote_state = $transaction_status['transactionStatus'];
    $remote_payment_method = $transaction_status['additionalData']['paymentMethod'];
    $payment_method = $payment->getPaymentMethod();
    if ($payment_method == NULL) {
      $payment_method_type = $this->getMappedPaymentMethod($remote_payment_method);
      $payment_method_storage = $this->entityTypeManager->getStorage('commerce_payment_method');
      $payment_method = $payment_method_storage->create([
        'type' => $payment_method_type,
        'payment_gateway' => $this->entityId,
        'remote_id' => $remote_payment_method,
        'uid' => $order->getCustomerId(),
      ]);
      $payment_method->save();
    }

    switch ($remote_state) {
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
    if ($capture_id != NULL) {
      $payment->setRemoteId($capture_id);
    }
    if ($payment->getPaymentMethod() == NULL && $payment_method != NULL) {
      $payment->payment_method->appendItem($payment_method);
    }
    $payment->setRemoteState($remote_state);
    $payment->setState($state);
    $payment->save();

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
