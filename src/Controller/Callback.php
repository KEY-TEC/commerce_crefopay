<?php

namespace Drupal\commerce_crefopay\Controller;

use Drupal\commerce_crefopay\Exception\NotificationHandleException;
use Drupal\commerce_crefopay\PaymentNotification;
use Drupal\commerce_crefopay\PaymentNotificationManager;
use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use CrefoPay\Library\Api\Exception\ApiError;
use CrefoPay\Library\Error\Codes;

/**
 *
 */
class Callback extends ControllerBase {

  public static function create(ContainerInterface $container) {
    return new static($container->get('commerce_crefopay.payment_notification_manager'));
  }

  public function __construct(PaymentNotificationManager $paymentNotificationManager){
    $this->paymentNotificationManager = $paymentNotificationManager;
  }

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
      $order_id = $commerce_order->id();
      \Drupal::logger('commerce_payment')
        ->notice("Success callback handler for order $order_id.");
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
    $response['result'] = 'ok';
    $params = $request->request->all();
    // PM: temporarily removed debug log.
    //$this->getLogger('commerce_payment')->info('Incoming notification: ' . json_encode($params));

    if (0 === strpos($request->headers->get('Content-Type'), 'application/x-www-form-urlencoded')) {
      $notification = new PaymentNotification();
      $notification->setUserId($request->request->get('userID'));
      $notification->setCaptureId($request->request->get('captureID'));
      $notification->setStatus($request->request->get('orderStatus'));
      $notification->setOrderId($request->request->get('orderID'));
      $notification->setSubscriptionId($request->request->get('subscriptionId'));
      $notification->setTransactionStatus($request->request->get('transactionStatus'));
      $notification->setAmount($request->request->get('amount'));

      try {
        $this->paymentNotificationManager->handlePaymentNotification($notification);
        return new JsonResponse($response);
      }
      catch (\Exception $e) {
        $message = 'Payment notification handle exception: ' . $e->getMessage() . ' PARAMS: ' . json_encode($params);
        $this->getLogger('commerce_payment')->critical($message);
        throw new NotificationHandleException($message, 500);
      }
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
        \Drupal::messenger()->addError($this->t('Dear Mr / Ms,
Thank you for your interest in our products.
In the course of the automatic solvency request over our credit provider (according to our AGB\'s), we unfortunately received a negative feedback.'));
        $this->getLogger('commerce_payment')
          ->critical('Error in reserve Call: ' . $api_error->getMessage());
      }
      else {
        \Drupal::messenger()
          ->addError($this->t('Payment error: ' . $api_error->getMessage()));
      }
      return $this->redirect('commerce_payment.checkout.return', [
        'commerce_order' => $commerce_order->id(),
        'step' => 'cancel',
      ]);
    }
  }

}
