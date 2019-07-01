<?php

namespace Drupal\commerce_crefopay\Controller\Callback;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Notification extends ControllerBase {

  public function execute(Request $request) {
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/x-www-form-urlencoded' ) ) {
      $response['result'] = 'ok';
      $user_id = $request->request->get('userID');
      $order_status = $request->request->get('orderStatus');
      $transaction_status = $request->request->get('transactionStatus');
      $subscription_id = $request->request->get('subscriptionID');

      \Drupal::logger('commerce_crefopay_notification')->notice("Notification for user: $user_id: Order: $order_status; Transaction: $transaction_status; Subscription: $subscription_id");
      return new JsonResponse( $response );
    }
  }
}