/**
 * Basket Hold countdown — cart & checkout (ADR 0009).
 *
 * Paints remaining time on [data-nera-basket-hold] nodes. When a hold expires:
 * sound + toast → AJAX remove that cart line only → WC notice survives reload → sound again via sessionStorage.
 */
(function () {
  'use strict';

  var cfg = window.neraBasketHold;
  if (!cfg || !cfg.holds) {
    return;
  }

  var skew = (cfg.serverNow || Math.floor(Date.now() / 1000)) - Math.floor(Date.now() / 1000);
  var removing = {};

  function now() {
    return Math.floor(Date.now() / 1000) + skew;
  }

  function formatRemaining(seconds) {
    if (seconds < 0) {
      seconds = 0;
    }
    var m = Math.floor(seconds / 60);
    var s = seconds % 60;
    return m + ':' + (s < 10 ? '0' : '') + s;
  }

  function toast(message, type) {
    type = type || 'warning';
    if (window.Alpine && Alpine.store && Alpine.store('toast')) {
      Alpine.store('toast')[type](message);
      return;
    }
    if (window.console) {
      console.log('[Basket Hold]', message);
    }
  }

  function sprintfName(template, name) {
    return (template || '%s').replace('%s', name || 'Item');
  }

  function notifyExpired(name) {
    try {
      sessionStorage.setItem('nera_basket_hold_sound', '1');
    } catch (e) {
      // ignore
    }
    document.dispatchEvent(new CustomEvent('nera:basket-hold:expired'));
    toast(sprintfName(cfg.i18n.expiredToast, name), 'warning');
  }

  function expireHold(key, name) {
    if (removing[key]) {
      return;
    }
    removing[key] = true;

    notifyExpired(name);

    var body = new FormData();
    body.append('action', 'nera_basket_hold_expire');
    body.append('nonce', cfg.nonce);
    body.append('cart_item_key', key);

    fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (json) {
        // Surviving toast after reload comes from wc_add_notice in the AJAX handler.
        toast(sprintfName(cfg.i18n.removedToast, name || (json.data && json.data.name)), 'error');

        var nodes = document.querySelectorAll(
          '[data-nera-basket-hold][data-cart-item-key="' + key + '"]'
        );
        nodes.forEach(function (node) {
          var row =
            document.getElementById('cart-item-' + key) ||
            node.closest('[data-cart-item-key]') ||
            node.parentElement;
          if (row) {
            row.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(12px)';
          }
        });

        // Give the toast + sound a moment to register before reload.
        setTimeout(function () {
          if (document.body.classList.contains('woocommerce-checkout')) {
            window.location.reload();
            return;
          }
          if (json.data && json.data.cart_empty) {
            window.location.reload();
            return;
          }
          if (window.jQuery) {
            jQuery(document.body).trigger('wc_update_cart');
            jQuery(document.body).trigger('wc_fragment_refresh');
          }
          window.location.reload();
        }, 1200);
      })
      .catch(function () {
        window.location.reload();
      });
  }

  function tick() {
    var nodes = document.querySelectorAll('[data-nera-basket-hold]');
    nodes.forEach(function (node) {
      var key = node.getAttribute('data-cart-item-key');
      var expires = parseInt(node.getAttribute('data-expires-at'), 10) || 0;
      if (!key || !expires) {
        return;
      }

      var remaining = expires - now();
      var timeEl = node.querySelector('.nera-basket-hold__time');
      if (timeEl) {
        timeEl.textContent = formatRemaining(remaining);
      }

      if (remaining <= 0) {
        var meta = cfg.holds[key] || {};
        expireHold(key, meta.name || '');
      }
    });
  }

  function init() {
    tick();
    window.setInterval(tick, 1000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
