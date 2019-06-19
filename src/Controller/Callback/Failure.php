<?php

namespace Drupal\commerce_crefopay\Controller\Callback;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class Failure extends ControllerBase {

  public function execute(Request $request) {
    $order_id = $request->query->get('orderID');
    $commerce_order = Order::load($order_id);
    /** @var \Drupal\commerce_checkout\Entity\CheckoutFlowInterface $checkout_flow */
    $checkout_flow = $commerce_order->checkout_flow->entity;
    $checkout_flow_plugin = $checkout_flow->getPlugin();
    $checkout_flow_plugin->redirectToStep('cancel');
  }
}