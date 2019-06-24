<?php

namespace Drupal\commerce_crefopay\Controller\Callback;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

class Success extends ControllerBase {

  public function execute(Request $request) {
    $order_id = $request->query->get('orderID');
    return $this->redirect('commerce_payment.checkout.return', ['commerce_order' => $order_id, 'step' => 'payment' ]);
  }
}