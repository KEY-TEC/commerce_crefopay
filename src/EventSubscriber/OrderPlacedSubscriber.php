<?php

namespace Drupal\commerce_crefopay\EventSubscriber;

use Drupal\commerce_crefopay\Plugin\Commerce\PaymentGateway\BasePaymentGateway;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderPlacedSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\commerce_payment\PaymentStorageInterface
   */
  private $paymentStorage;


  public static function getSubscribedEvents() {
    return [
      'commerce_order.place.post_transition' => ['onPlace', 0],
    ];
  }

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->paymentStorage = $entity_type_manager->getStorage('commerce_payment');
  }

  public function onPlace(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    $payments = $this->paymentStorage->loadMultipleByOrder($order);
    foreach ($payments as $payment) {
      $gateway = $payment->getPaymentGateway();
      $gateway_plugin = $gateway->getPlugin();
      if ($gateway_plugin instanceof BasePaymentGateway) {
        $gateway_plugin->capture($payment);
      }
    }
  }

}
