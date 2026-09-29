/**
 * Spending limit in the Lucky Dip dialogs.
 *
 * Lottery for WooCommerce puts Lucky Dip tickets in the basket with its own handlers, so
 * the "you are over your limit, continue?" question cannot be asked inside the theme's
 * add-to-cart. Instead, the click on a button that adds tickets is held while the server
 * is asked (nera_add_to_cart_limit_preview) whether this add would take the basket over
 * the customer's limit. If so the Spending Limit plugin's dialog is shown; on "Continue
 * anyway" the same click is replayed and goes through, on Cancel nothing happens.
 *
 * With no answer (guest, no limit, plugin off, request failed) the click just goes on:
 * checkout still enforces the limit.
 */
(function ($) {
  'use strict';

  var ADDERS = '.lty-add-to-cart-lucky-dip-button, .lty-add-more-lucky-tip, .lty-regenerate-lucky-dip-add-to-cart-button';
  var cfg = window.neraLuckyDipLimit || {};
  var busy = false;

  function quantityFor(button) {
    var container = button.closest('.lty-lottery-ticket-lucky-dip-container') || document;
    var input = container.querySelector('.lty-lucky-dip-quantity') || document.querySelector('.lty-lucky-dip-quantity');
    return input && input.value ? input.value : '1';
  }

  function productId() {
    var el = document.querySelector('.lty-ticket-product-id');
    return el ? el.value : '';
  }

  function requestBody(button) {
    var params = new URLSearchParams();
    params.append('action', 'nera_add_to_cart_limit_preview');
    params.append('product_id', productId());
    params.append('quantity', quantityFor(button));

    var pid = productId();
    if (window.NeraLuckyDipAddons && document.querySelector('[data-nera-ld-addons][data-product-id="' + pid + '"]')) {
      params.append('nera_addons_submitted', '1');
      window.NeraLuckyDipAddons.selected(pid).forEach(function (id) {
        params.append('nera_addon_ids[]', id);
      });
    }
    return params;
  }

  function replay(button) {
    button.setAttribute('data-nera-limit-ok', '1');
    button.click();
  }

  document.addEventListener(
    'click',
    function (e) {
      var button = e.target.closest && e.target.closest(ADDERS);
      if (!button || !cfg.ajaxUrl) {
        return;
      }
      if (button.getAttribute('data-nera-limit-ok')) {
        button.removeAttribute('data-nera-limit-ok'); // the replayed click: let it through
        return;
      }
      if (button.disabled || button.getAttribute('aria-disabled') === 'true') {
        return;
      }

      // Hold the click (capture phase, ahead of Lottery for WooCommerce's handlers).
      e.preventDefault();
      e.stopImmediatePropagation();
      if (busy) {
        return;
      }
      busy = true;

      fetch(cfg.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: requestBody(button).toString(),
        credentials: 'same-origin',
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (res) {
          if (!res || !res.needs_confirmation) {
            return true;
          }
          var c = res.confirmation || {};
          return window.NeraSpendLimit && window.NeraSpendLimit.confirm
            ? window.NeraSpendLimit.confirm(c)
            : window.confirm(c.message || '');
        })
        .catch(function () {
          return true; // could not ask: do not block the customer, checkout still enforces it
        })
        .then(function (proceed) {
          busy = false;
          if (proceed) {
            replay(button);
          }
        });
    },
    true
  );
})(window.jQuery);
