/**
 * Ticket bundles as threshold tiers — front-end half.
 *
 * The server reprices the cart item (inc/lty-bundle-tier-pricing.php). This keeps the
 * product page honest about it, so the unit price and running total shown before adding
 * to the cart are the ones that will actually be charged.
 *
 * It also repairs a stale selection: Lottery for WooCommerce binds its own
 * quantity-change handling to `.lty-participate-now .qty` and writes prices into
 * `.lty-lottery-price`, neither of which this theme's QuantitySelector renders. So the
 * plugin never learns the quantity changed, and a chosen bundle stays highlighted while
 * no longer applying. Rather than duplicate that logic, we call the plugin's own
 * maybe_select_predefined_button() with the real quantity.
 */
(function () {
  'use strict';

  var cfg = window.neraBundleTiers;

  if (!cfg || !cfg.tiers || !cfg.tiers.length) {
    return;
  }

  /* ------------------------------------------------------------------
     Pricing — mirrors nera_lty_bundle_tier_price() / the PHP clamp.
     ------------------------------------------------------------------ */

  function tierRate(qty) {
    for (var i = 0; i < cfg.tiers.length; i++) {
      if (qty >= cfg.tiers[i].qty) {
        /* Never above the plain unit price — same guard as the server. */
        return Math.min(cfg.tiers[i].per, cfg.basePrice);
      }
    }

    return cfg.basePrice;
  }

  function money(amount) {
    var c = cfg.currency || {};
    var decimals = typeof c.decimals === 'number' ? c.decimals : 2;
    var parts = Math.abs(amount).toFixed(decimals).split('.');

    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, c.thousandSep || ',');

    var number = parts.join(c.decimalSep || '.');
    var format = c.format || '%1$s%2$s';

    return format.replace('%1$s', c.symbol || '').replace('%2$s', number);
  }

  /* ------------------------------------------------------------------
     DOM.
     ------------------------------------------------------------------ */

  function quantityInput() {
    return document.querySelector('[data-quantity-input]');
  }

  function currentQuantity() {
    var input = quantityInput();
    var qty = input ? parseInt(input.value, 10) : NaN;

    return isNaN(qty) || qty < 1 ? 1 : qty;
  }

  function render(qty) {
    var block = document.querySelector('[data-ticket-price]');
    if (!block) {
      return;
    }

    var rate = tierRate(qty);

    var unit = block.querySelector('[data-ticket-price-unit]');
    if (unit) {
      unit.innerHTML = money(rate);
    }

    /* The total row ships hidden and unused; reveal it once there is a quantity
       worth totalling, so a single ticket does not gain a redundant "Total: £0.39". */
    var row = block.querySelector('[data-ticket-price-total-row]');
    var total = block.querySelector('[data-ticket-price-total]');

    if (row && total) {
      if (qty > 1) {
        total.textContent = money(rate * qty);
        row.removeAttribute('hidden');
      } else {
        row.setAttribute('hidden', 'hidden');
      }
    }
  }

  /**
   * Hand the quantity to the plugin so it can select the matching bundle, or clear a
   * selection that no longer matches. Guarded: if the plugin's frontend.js is absent or
   * renamed, the price display above still works.
   */
  function syncPluginSelection(qty) {
    try {
      if (
        window.LTY_Frontend &&
        'function' === typeof window.LTY_Frontend.maybe_select_predefined_button
      ) {
        window.LTY_Frontend.maybe_select_predefined_button(qty, cfg.productId);
      }
    } catch (e) {
      /* A selection-highlight failure must never take the price display with it. */
    }
  }

  var lastQuantity = null;

  function update() {
    var qty = currentQuantity();

    if (qty === lastQuantity) {
      return;
    }

    lastQuantity = qty;
    render(qty);
    syncPluginSelection(qty);
  }

  /* ------------------------------------------------------------------
     Triggers. The quantity is moved by typing, by the +/- and +N buttons, by the
     range slider, and by clicking a bundle — all of which end up writing to
     [data-quantity-input], so watching it covers every route.
     ------------------------------------------------------------------ */

  function bind() {
    var input = quantityInput();
    if (!input) {
      return;
    }

    ['input', 'change'].forEach(function (evt) {
      input.addEventListener(evt, update);
    });

    /* Programmatic writes (bundle click, +N buttons) may set .value without
       dispatching, so also watch the attribute and poll on interaction. */
    if (window.MutationObserver) {
      new MutationObserver(update).observe(input, { attributes: true, attributeFilter: ['value'] });
    }

    document.addEventListener('click', function (e) {
      if (
        e.target &&
        e.target.closest &&
        e.target.closest('[data-quantity-control], .nera-predefined-buttons-wrapper')
      ) {
        /* Let the other handler write the value first. */
        window.setTimeout(update, 0);
      }
    });

    render(currentQuantity());
    lastQuantity = currentQuantity();
  }

  if ('loading' === document.readyState) {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();
