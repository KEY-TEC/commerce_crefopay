/**
 * @file
 * Initialize secure fields.
 */

(function ($, window, Drupal) {

  'use strict';

  /**
   * Initialize crefopay secure fields.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior crefopay secure fields.
   */
  Drupal.behaviors.crefoSecureFieldsInit = {
    attach: function () {
      console.log('ATTA')
      var shopPublicKey = 'X';
      var secureFieldsClientInstance =
        new SecureFieldsClient(shopPublicKey,
          1,
          function () {
            console.log('paymentRegisteredCallback');
          }
          ,
          function () {
            console.log('initializationCompleteCallback');
          },
          []);
    }
  };

})(jQuery, window, Drupal);

// Declare $ global;
$ = jQuery;