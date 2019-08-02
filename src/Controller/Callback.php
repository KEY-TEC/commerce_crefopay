<?php

namespace Drupal\commerce_crefopay\Controller;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class Callback extends ControllerBase {

  /**
   *
   */
  public function success(Request $request) {
    $commerce_order = $this->getOrder($request);
    return $this->redirect('commerce_payment.checkout.return', ['commerce_order' => $commerce_order->id(), 'step' => 'payment']);
  }

  /**
   *
   */
  public function notification(Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded')) {
      $response['result'] = 'ok';
      $user_id = $request->request->get('userID');
      $order_status = $request->request->get('orderStatus');
      $transaction_status = $request->request->get('transactionStatus');
      $subscription_id = $request->request->get('subscriptionID');

      \Drupal::logger('commerce_crefopay_notification')->notice("Notification for user: $user_id: Order: $order_status; Transaction: $transaction_status; Subscription: $subscription_id");
      return new JsonResponse($response);
    }
  }

  /**
   *
   */
  public function failure(Request $request) {
    $commerce_order = $this->getOrder($request);
    return $this->redirect('commerce_payment.checkout.return', ['commerce_order' => $commerce_order->id(), 'step' => 'cancel']);
  }

  /**
   *
   */
  private function getOrder(Request $request) {
    $id_service = \Drupal::service('commerce_crefopay.id_builder');
    $order_id = $id_service->realId($request->query->get('orderID'));
    if (empty($order_id)) {
      return;
    }
    $commerce_order = Order::load($order_id);
    return $commerce_order;
  }

  /**
   *
   */
  public function confirm(Request $request) {

    /** @var \Drupal\commerce_crefopay\Client\Builder\IdBuilder $id_service */

    $payment_method = $request->get('paymentMethod');
    $payment_instrument_id = $request->get('paymentInstrumentID');
    $commerce_order = $this->getOrder($request);

    /** @var \Drupal\commerce_crefopay\Client\TransactionClient $transaction_client */
    $transaction_client = \Drupal::service('commerce_crefopay.transaction_client');
    $redirect_url = $transaction_client->reserveTransaction($commerce_order, $payment_method, $payment_instrument_id);
    if ($redirect_url != NULL) {
      return new TrustedRedirectResponse($redirect_url);
    }

    return $this->redirect('commerce_payment.checkout.return', ['commerce_order' => $commerce_order->id(), 'step' => 'payment']);
  }

}
