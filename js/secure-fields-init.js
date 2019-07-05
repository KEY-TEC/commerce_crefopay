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
      $('.crefopay-form').once('crefopay-form').each(function () {
        var container = $(this);
        var shopPublicKey = drupalSettings.crefopay.shopPublicKey;
        var configuration = {
            url: drupalSettings.crefopay.secureFieldsUrl,
            placeholders: {}
          }
        ;
        console.log(shopPublicKey);
        var secureFieldsClientInstance =
          new SecureFieldsClient(shopPublicKey,
            drupalSettings.crefopay.orderId,
            function (response) {
              console.log(response);
              if (response.resultCode === 0) {
                // Successful registration, continue to next page using JavaScript
                var prefix = drupalSettings.path.pathPrefix != '' ? '/' + drupalSettings.path.pathPrefix : '';
                var url = prefix + '/crefopay/confirm?orderID=' + response.orderNo + '&paymentMethod=' + response.paymentMethod;
                if (response.paymentInstrumentId != null) {
                  url += '&paymentInstrumentID=' + response.paymentInstrumentId;
                }
                location.href = url;
                return false;
              } else {
                var errorContainer = $('.crefopay-form__error');
                errorContainer.addClass('crefopay-form__error--show');
                errorContainer.empty();
                for (var i in response.errorDetails) {
                  errorContainer.append('<div class="crefopay-form__error-item">' + Drupal.t(response.errorDetails[i].description) + '</div>');
                }
                $('html, body').animate({
                  scrollTop: 0
                }, 1000);
              }
            }
            ,
            function (result) {
              console.log('initializationCompleteCallback');
              console.log(result);

              //setTimeout(function(){ secureFieldsClientInstance.registerPayment(); }, 3000);

            },
            configuration);

        $('input[name=paymentMethod]', container).click(function (event) {
          var tab = $(this).attr('value');
          container.attr('data-crefopay-active-tab', tab);
          $('*[data-crefopay-tab]').removeClass('crefopay-form__tab--active');
          $('*[data-crefopay-tab=' + tab + ']').addClass('crefopay-form__tab--active');

        });
        $('.crefo-send', container).once('crefopay-send').each(function () {
          $(this).click(function () {
            $('.crefopay-form__error').removeClass('crefopay-form__error--show');
            secureFieldsClientInstance.registerPayment();
          })
        });
      });

    }
  };

})(jQuery, window, Drupal);

// Declare $ global;
$ = jQuery;