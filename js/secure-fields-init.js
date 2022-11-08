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
        /**
         * Translated error messages.
         */
        var errorMessages = {
          "The field paymentInstrument.validity has an invalid format.": Drupal.t("The field Validity has an invalid format."),
          "The field paymentInstrument.number is invalid.": Drupal.t("The field Number is invalid."),
          "The field paymentInstrument.cvv is not a valid CVV.": Drupal.t("The field CVV is not a valid CVV."),
          "The field paymentInstrument.cvv is invalid.": Drupal.t("The field CVV is invalid."),
          "The field paymentInstrument.accountHolder is invalid.": Drupal.t("The field Accountholder is invalid."),
          "The field paymentInstrument.validity is invalid.": Drupal.t("The field CVV is invalid."),
          "The field paymentInstrument.bic is invalid.": Drupal.t("The field BIC is invalid."),
          "The field paymentInstrument.iban is invalid.": Drupal.t("The field IBAN is invalid."),
          "The field paymentInstrument.bic is not a valid BIC.": Drupal.t("The field BIC is invalid."),
          "The field paymentInstrument.iban is not a valid IBAN.": Drupal.t("The field IBAN is invalid."),
          "The field paymentInstrument.bankAccountHolder is invalid.": Drupal.t("The field Account holder is invalid."),
          "The credit card is expired.": Drupal.t("The credit card is expired."),
          "Invalid card number.": Drupal.t("Invalid card number."),
          "The field paymentMethod is missing.": Drupal.t("The field paymentMethod is missing."),
          "Payment error: The payment has been rejected. Please use another payment method.": Drupal.t("Payment error: The payment has been rejected. Please use another payment method.")
        };
        // Deselect selected bank account.
        $("input[name='paymentMethod'], input[data-crefopay='paymentInstrument.bankAccountHolder'], input[data-crefopay='paymentInstrument.iban'], input[data-crefopay='paymentInstrument.bic']")
          .focus(function () {
            $('input[data-crefopay="paymentInstrument.id"]').prop('checked', false);
          });

        // Empty bank account fields when selecting stored bank account.
        $("input[data-crefopay='paymentInstrument.id']").click(function () {
          $("input[data-crefopay='paymentInstrument.bankAccountHolder'], input[data-crefopay='paymentInstrument.iban'], input[data-crefopay='paymentInstrument.bic']").val("");
        });

        // Deselect existing credit cards
        $("input[id='new-cc']")
          .focus(function () {
            $('input[data-crefopay-type="existing-cc"]').prop('checked', false);
          });
        // Deselect "new credit card"
        $('input[data-crefopay-type="existing-cc"]')
          .focus(function () {
            $('input[id="new-cc"]').prop('checked', false);
          });
        var container = $(this);
        var shopPublicKey = drupalSettings.crefopay.shopPublicKey;
        var configuration = {
            url: drupalSettings.crefopay.secureFieldsUrl,
            placeholders: {}
          }
        ;
        var secureFieldsClientInstance =
          new SecureFieldsClient(shopPublicKey,
            drupalSettings.crefopay.orderId,
            function (response) {
              if (response.resultCode === 0) {
                // Successful registration, continue to next page using
                // JavaScript
                var prefix = drupalSettings.path.pathPrefix != '' ? '/' + drupalSettings.path.pathPrefix : '/';
                var url = prefix + 'crefopay/confirm?orderID=' + response.orderNo + '&paymentMethod=' + response.paymentMethod;
                if (response.paymentInstrumentId != null) {
                  url += '&paymentInstrumentID=' + response.paymentInstrumentId;
                }
                location.href = url;
                return false;
              }
              else {
                var errorContainer = $('.crefopay-form__error');
                errorContainer.addClass('crefopay-form__error--show');
                errorContainer.empty();
                for (var i in response.errorDetails) {
                  var translatedMessage = response.errorDetails[i].description;
                  if (errorMessages[translatedMessage] != null) {
                    translatedMessage = errorMessages[translatedMessage];
                  }
                  errorContainer.append('<div class="crefopay-form__error-item">' + translatedMessage + '</div>');
                }
                $('html, body').animate({
                  scrollTop: 0
                }, 1000);
              }
            }
            ,
            function (result) {
              //setTimeout(function(){
              // secureFieldsClientInstance.registerPayment(); }, 3000);

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
