<?php

namespace Drupal\commerce_crefopay\Controller\Callback;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Confirm extends ControllerBase {

  public function execute(Request $request) {

    /** @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder $id_service */
    $id_service = \Drupal::service('commerce_crefopay.id_builder');
    $order_id = $id_service->realId($request->query->get('orderID'));
    $payment_method = $request->get('paymentMethod');
    $payment_instrument_id = $request->get('paymentInstrumentID');
    $commerce_order = Order::load($order_id);

    /** @var \Drupal\commerce_crefopay\Client\TransactionClient $transaction_client */
    $transaction_client = \Drupal::service('commerce_crefopay.transaction_client');
    $redirect_url = $transaction_client->reserveTransaction($commerce_order, $payment_method, $payment_instrument_id);
    if ($redirect_url != NULL) {
      return new TrustedRedirectResponse($redirect_url);
    }

    return $this->redirect('commerce_payment.checkout.return', ['commerce_order' => $order_id, 'step' => 'payment' ]);
  }
}