/**
 * Lottery alertable helpers for Nera theme.
 *
 * - Restyle ticket-selection alertable dialog (nera-lottery-alert).
 * - Route Lucky Dip AJAX errors to inline red text under Lucky Dip
 *   instead of opening the alertable modal.
 *
 * @package Nera_Competitions
 */
(function ($) {
  'use strict';

  var MODAL_CLASS = 'nera-lottery-alert';
  var OVERLAY_CLASS = 'nera-lottery-alert-overlay';
  var ERROR_SELECTOR = '.nera-lucky-dip-inline__error';
  var luckyDipPending = false;

  /**
   * Whether this alert is the ticket-selection validation message.
   *
   * @param {string} message
   * @return {boolean}
   */
  function isTicketSelectionAlert(message) {
    return (
      typeof lty_frontend_params !== 'undefined' &&
      typeof lty_frontend_params.ticket_selection_alert_message === 'string' &&
      message === lty_frontend_params.ticket_selection_alert_message
    );
  }

  /**
   * Show Lucky Dip error as inline text under the controls.
   *
   * @param {string} message
   */
  function showLuckyDipInlineError(message) {
    var $error = $(ERROR_SELECTOR).first();
    if (!$error.length) {
      return;
    }

    $error.text(message || '').prop('hidden', false);
  }

  /**
   * Clear Lucky Dip inline error.
   */
  function clearLuckyDipInlineError() {
    $(ERROR_SELECTOR).text('').prop('hidden', true);
  }

  /**
   * Wrap show/hide hooks to tag modal + overlay with Nera classes.
   *
   * @param {Object} options
   * @return {Object}
   */
  function withNeraClasses(options) {
    options = options || {};
    var baseShow = options.show || $.alertable.defaults.show;

    options.show = function () {
      $(this.modal).addClass(MODAL_CLASS);
      $(this.overlay).addClass(OVERLAY_CLASS);
      baseShow.call(this);
    };

    options.hide = function () {
      var $modal = $(this.modal);
      var $overlay = $(this.overlay);
      var pending = 2;

      function cleanup() {
        pending -= 1;
        if (pending === 0) {
          $modal.removeClass(MODAL_CLASS);
          $overlay.removeClass(OVERLAY_CLASS);
        }
      }

      $modal.fadeOut(100, cleanup);
      $overlay.fadeOut(100, cleanup);
    };

    return options;
  }

  /**
   * Patch $.alertable.alert and bind Lucky Dip error routing.
   */
  function init() {
    if (!$.alertable || typeof $.alertable.alert !== 'function') {
      return;
    }

    var originalAlert = $.alertable.alert;

    $.alertable.alert = function (message, options) {
      if (luckyDipPending) {
        luckyDipPending = false;
        showLuckyDipInlineError(message);
        return $.Deferred().resolve().promise();
      }

      if (isTicketSelectionAlert(message)) {
        return originalAlert.call($.alertable, message, withNeraClasses(options));
      }

      return originalAlert.call($.alertable, message, options);
    };

    // Flag before plugin click handlers (mousedown fires first).
    $(document).on(
      'mousedown',
      '.lty-add-to-cart-lucky-dip-button, .lty-lucky-dip-button',
      function () {
        luckyDipPending = true;
        clearLuckyDipInlineError();
      }
    );

    // Clear error when quantity changes.
    $(document).on('change input', '.nera-lucky-dip-inline .qty', function () {
      clearLuckyDipInlineError();
    });

    // Clear pending flag on success modal (no alertable error fired).
    if ($.modal && $.modal.OPEN) {
      $(document).on($.modal.OPEN, function (event, modal) {
        if (modal && modal.$elm && modal.$elm.hasClass('nera-lucky-dip-popup')) {
          luckyDipPending = false;
          clearLuckyDipInlineError();
        }
      });
    }
  }

  $(init);
})(jQuery);
