<?php

namespace Drupal\commerce_crefopay\Client;

/**
 * Thrown when a user not found.
 */
class UserNotExistsException extends \Exception {

  /**
   * Constructor UserNotExistsException.
   *
   * @param string $user_id
   *   The user id.
   */
  public function __construct($user_id) {
    parent::__construct("User id: $user_id not exists.", 1);
  }

}
