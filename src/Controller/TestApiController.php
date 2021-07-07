<?php


namespace Drupal\commerce_crefopay\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;

class TestApiController {
  public function getTransactionStatus(){
    return new JsonResponse(['mac' => '9a1dc136061156d626bfdd15a2419396a5cfb7b2']);
  }
}
