<?php

namespace Drupal\commerce_crefopay\Client;

class UserNotExistsException extends \Exception {

  public function __construct($user_id) {
    parent::__construct("User id: $user_id not exists.", 1);
  }
}