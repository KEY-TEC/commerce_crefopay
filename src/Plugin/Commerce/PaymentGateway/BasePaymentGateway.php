<?php

namespace Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_crefopay\Client\Builder\IdBuilder;
use Drupal\commerce_crefopay\Client\TransactionClient;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BasePaymentGateway extends OffsitePaymentGatewayBase implements SupportsRefundsInterface {

  /**
   * The transaction client.
   *
   * @var \Drupal\commerce_crefopay\Client\TransactionClient
   */
  protected $transactionClient;

  /**
   * The IdBuilder.
   *
   * @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder
   */
  protected $idBuilder;

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
   * @param \Drupal\commerce_crefopay\Client\TransactionClient $transaction_client
   *   The transaction client.
   * @param \Drupal\commerce_crefopay\Client\Builder\IdBuilder $id_builder
   *   The id builder.
   * @param Psr\Log\LoggerInterface
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PaymentTypeManager $payment_type_manager, PaymentMethodTypeManager $payment_method_type_manager, TimeInterface $time, TransactionClient $transaction_client, IdBuilder $id_builder, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager, $time);
    $this->transactionClient = $transaction_client;
    $this->idBuilder = $id_builder;
    $this->logger = $logger;
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
      $container->get('commerce_crefopay.transaction_client'),
      $container->get('commerce_crefopay.id_builder'),
      $container->get('logger.channel.commerce_payment')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function onNotify(Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded')) {
      $response['result'] = 'ok';
      $user_id = $request->request->get('userID');
      $order_status = $request->request->get('orderStatus');
      $transaction_status = $request->request->get('transactionStatus');
      $subscription_id = $request->request->get('subscriptionID');

      $this->logger->notice("Notification for user: $user_id: Order: $order_status; Transaction: $transaction_status; Subscription: $subscription_id");
      return new JsonResponse($response);

    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {

    $transaction_status = $this->transactionClient->getTransactionStatus($order);

    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $remote_state = $transaction_status['transactionStatus'];
    switch ($remote_state) {
      case "Done":
        {
          $state = "completed";
          break;
        }
      case "New":
        {
          $state = "new";
          break;
        }
      case "AcknowledgePending":
      case "FraudPending":
      case "MerchantPending":
      case "CIAPending":
        {
          $state = "authorization";
          break;
        }

      case "Cancelled":
      case "FraudCancelled":
        {
          $state = "refunded";
          break;
        }
      case "Expired":
        {
          $state = 'authorization_expired';
          break;
        }
      default:
        {
          $state = "new";
          break;
        }

    }
    $payment = $payment_storage->create([
      'state' => $state,
      'amount' => $order->getBalance(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'remote_id' => $transaction_status['additionalData']['paymentReference'],
      'remote_state' => $remote_state,
    ]);
    $payment->save();
  }

  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {

  }
}
