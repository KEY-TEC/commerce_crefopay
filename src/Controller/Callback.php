<?php

namespace Drupal\commerce_crefopay\Controller;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Upg\Library\Api\Exception\ApiError;
use Upg\Library\Error\Codes;

/**
 *
 */
class Callback extends ControllerBase {

  /**
   *
   */
  public function success(Request $request) {
    $commerce_order = $this->getOrder($request);
    $options = [];

    if (!empty($commerce_order->getData('crefopay_language'))) {
      $lang_code = $commerce_order->getData('crefopay_language');
      $language = \Drupal::languageManager()->getLanguage($lang_code);
      if ($language != NULL) {
        $options['language'] = $language;
      }
    }
    if ($commerce_order != NULL) {
      \Drupal::logger('commerce_payment')
        ->notice("Success callback handler for order $commerce_order->id()");
    }
    else {
      \Drupal::logger('commerce_payment')
        ->error("Unable to load order");
    }


    return $this->redirect('commerce_payment.checkout.return', [
      'commerce_order' => $commerce_order->id(),
      'step' => 'payment',
    ], $options);
  }

  /**
   *
   */
  public function notification(Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded')) {
      $response['result'] = 'ok';
      $user_id = $request->request->get('userID');
      $capture_id = $request->request->get('captureID');
      $order_status = $request->request->get('orderStatus');
      $order_id = $request->request->get('orderID');
      $transaction_status = $request->request->get('transactionStatus');
      $subscription_id = $request->request->get('subscriptionID');

      $order_id = !empty($subscription_id) ? $subscription_id : $order_id;
      $id_service = \Drupal::service('commerce_crefopay.id_builder');
      $order_id = $id_service->realId($order_id);
      $commerce_order = Order::load($order_id);
      if ($commerce_order != NULL && !$commerce_order->get('payment_gateway')->isEmpty()) {
        \Drupal::logger('commerce_payment')
          ->debug("PN: Redirect to payment gateway for order $order_id");
        /** @var \Drupal\commerce_payment\Entity\PaymentMethod $payment_method */
        $payment_gateway = $commerce_order->get('payment_gateway')->entity;
        if ($payment_gateway != NULL) {
          return $this->redirect('commerce_payment.notify', [
            'commerce_payment_gateway' => $payment_gateway->id(),
          ],
            [
              'query' => [
                'userID' => $user_id,
                'orderID' => $order_id,
                'orderStatus' => $order_status,
                'transactionStatus' => $transaction_status,
                'subscriptionID' => $subscription_id,
                'captureID' => $capture_id,
              ],
            ]);
        }
      }
      else {
        \Drupal::logger('commerce_payment')
          ->critical("Unable to find payment gateway for $order_id | user id: $user_id | orderStatus $order_status");
      }
      return new JsonResponse($response);
    }
  }

  /**
   *
   */
  public function failure(Request $request) {
    $commerce_order = $this->getOrder($request);
    return $this->redirect('commerce_payment.checkout.return', [
      'commerce_order' => $commerce_order->id(),
      'step' => 'cancel',
    ]);
  }

  /**
   * Returns order for given request.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   The order.
   */
  private function getOrder(Request $request) {
    $id_service = \Drupal::service('commerce_crefopay.id_builder');
    $order_id = $id_service->realId($request->query->get('orderID'));
    if (empty($order_id)) {
      return NULL;
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
    try {
      $redirect_url = $transaction_client->reserveTransaction($commerce_order, $payment_method, $payment_instrument_id);
      if ($redirect_url != NULL) {
        $response = new TrustedRedirectResponse($redirect_url);
        $response->getCacheableMetadata()->setCacheMaxAge(0);
      }
      else {
        $response = $this->redirect('commerce_payment.checkout.return', [
          'commerce_order' => $commerce_order->id(),
          'step' => 'payment',
        ]);
      }
      return $response;
    } catch (ApiError $api_error) {
      if ($api_error->getCode() == Codes::ERROR_PAYMENT_DECLINED_FRAUD) {
        \Drupal::messenger()->addError('Dear Mr / Ms, 
Thank you for your interest in our products. 
In the course of the automatic solvency request over our credit provider (according to our AGB\'s), we unfortunately received a negative feedback.');
      }
      else {
        \Drupal::messenger()
          ->addError($this->t('Payment error: ' . $api_error->getMessage()));
      }
      $this->getLogger('commerce_payment')
        ->critical('Error in reserve Call: ' . $api_error->getMessage());
      return $this->redirect('commerce_payment.checkout.return', [
        'commerce_order' => $commerce_order->id(),
        'step' => 'cancel',
      ]);
    }


  }

}
