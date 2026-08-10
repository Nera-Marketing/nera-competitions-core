/**
 * Wallet Partial Payment — checkout controls for a buyer-chosen Wallet Contribution.
 *
 * See CONTEXT.md and docs/adr/0004 + 0005. Drives the "Part wallet, part card" option in
 * template-parts/checkout/wallet-partial-payment.php.
 *
 * Deliberate shape of this file:
 *
 * 1. EVERY listener is delegated on document.body — the option lives inside the payment
 *    fragment WooCommerce may replace on shipping/coupon updates.
 *
 * 2. Switching payment method uses radio.click(), NOT `.checked = true` — WooCommerce binds
 *    panel open/close on click (checkout.js).
 *
 * 3. Edge reroutes (amount 0 → Card, amount = Basket Total → Full Wallet Payment) run only on
 *    commit (change / focusout / Enter), never per keystroke. No AJAX, no update_checkout
 *    (ADR 0005). Mid-range amounts update the on-page split summary only; the contribution is
 *    committed on Place order (ADR 0004).
 */
(function () {
  'use strict';

  var ROOT = '[data-nera-wallet-partial]';
  var RADIO = '[data-nera-wallet-radio]';
  var AMOUNT = '[data-nera-wallet-amount]';
  var ERROR = '[data-nera-wallet-error]';
  var FLASH = '[data-nera-wallet-flash]';
  var SUMMARY = '[data-nera-wallet-summary]';
  var FLAG = '[data-nera-wallet-partial-flag]';

  var flashMessage = '';

  function container() {
    return document.querySelector(ROOT);
  }

  function isSplitSelected(box) {
    var radio = box.querySelector(RADIO);

    return !!radio && radio.checked;
  }

  function amountOf(box) {
    var input = box.querySelector(AMOUNT);
    if (!input) {
      return 0;
    }

    var value = parseFloat(input.value);

    return isNaN(value) || value < 0 ? 0 : value;
  }

  function money(box, value) {
    var symbol = box.getAttribute('data-currency') || '';
    var formatted = (Math.round(value * 100) / 100).toFixed(2);

    return symbol ? symbol + formatted : formatted;
  }

  function showError(box, message) {
    var node = box.querySelector(ERROR);
    if (!node) {
      return;
    }

    node.textContent = message || '';
    node.hidden = !message;
  }

  function paintFlash(box) {
    var node = box.querySelector(FLASH);
    if (!node) {
      return;
    }

    node.textContent = flashMessage;
    node.hidden = !flashMessage;
  }

  function setFlash(box, message) {
    flashMessage = message || '';
    paintFlash(box);
  }

  function setPartialFlag(box, on) {
    var flag = box.querySelector(FLAG);
    if (flag) {
      flag.value = on ? '1' : '0';
    }
  }

  function paintSummary(box) {
    var node = box.querySelector(SUMMARY);
    if (!node) {
      return;
    }

    if (!isSplitSelected(box)) {
      node.innerHTML = '';
      return;
    }

    var amount = amountOf(box);
    var basket = parseFloat(box.getAttribute('data-basket')) || 0;
    var max = parseFloat(box.getAttribute('data-max')) || 0;

    if (amount > max) {
      amount = max;
    }

    var remainder = Math.max(0, basket - amount);
    var walletLabel = money(box, amount);
    var cardLabel = money(box, remainder);

    node.innerHTML =
      '<strong>' +
      walletLabel +
      '</strong> from your wallet, <strong>' +
      cardLabel +
      '</strong> charged to your card.';
  }

  /**
   * Move the buyer to another payment method via WooCommerce's click handler.
   */
  function switchTo(box, gatewayAttr, message) {
    var id = 'payment_method_' + (box.getAttribute(gatewayAttr) || '');
    var radio = document.getElementById(id);

    if (!radio) {
      showError(
        box,
        'Please choose a different amount, or pick another payment method above.'
      );

      return false;
    }

    showError(box, '');
    setFlash(box, message);
    setPartialFlag(box, false);
    radio.click();

    return true;
  }

  /**
   * Edge amounts only: 0 → Card, full Basket Total → Full Wallet Payment. Mid-range: paint.
   *
   * @param {Element} box
   * @param {boolean} allowSwitch
   */
  function commitAmount(box, allowSwitch) {
    if (!isSplitSelected(box)) {
      setPartialFlag(box, false);
      paintSummary(box);

      return;
    }

    setPartialFlag(box, true);

    var max = parseFloat(box.getAttribute('data-max')) || 0;
    var basket = parseFloat(box.getAttribute('data-basket')) || 0;
    var amount = amountOf(box);
    var input = box.querySelector(AMOUNT);

    if (amount > max) {
      amount = max;
      if (input) {
        input.value = amount.toFixed(2);
      }
    }

    if (allowSwitch) {
      if (basket > 0 && amount >= basket) {
        if (
          switchTo(
            box,
            'data-wallet-gateway',
            'That covers the whole order — switched you to Wallet payment.'
          )
        ) {
          return;
        }
      }

      if (amount <= 0) {
        if (
          switchTo(
            box,
            'data-card-gateway',
            'No wallet credit applied — switched you to card payment.'
          )
        ) {
          return;
        }
      }
    }

    if (input && amount > 0 && amount.toFixed(2) !== input.value) {
      input.value = amount.toFixed(2);
    }

    showError(box, '');
    paintSummary(box);
  }

  /* ---- Events ---------------------------------------------------------------------- */

  document.body.addEventListener(
    'change',
    function (event) {
      var target = event.target;
      if (!target || 'payment_method' !== target.name) {
        return;
      }

      var box = container();
      if (!box) {
        return;
      }

      if (target.hasAttribute && target.hasAttribute('data-nera-wallet-radio')) {
        setFlash(box, '');
        setPartialFlag(box, true);
        /* Selecting Partial Payment with an illegal amount already in the field. */
        commitAmount(box, true);

        return;
      }

      setPartialFlag(box, false);
      showError(box, '');
      paintSummary(box);
    },
    false
  );

  document.body.addEventListener(
    'input',
    function (event) {
      if (!event.target.closest || !event.target.closest(AMOUNT)) {
        return;
      }

      var box = container();
      if (box && isSplitSelected(box)) {
        paintSummary(box);
      }
    },
    false
  );

  function onAmountCommit(event) {
    if (!event.target.closest || !event.target.closest(AMOUNT)) {
      return;
    }

    var box = container();
    if (box) {
      commitAmount(box, isSplitSelected(box));
    }
  }

  document.body.addEventListener('change', onAmountCommit, false);
  document.body.addEventListener('focusout', onAmountCommit, false);

  document.body.addEventListener(
    'keydown',
    function (event) {
      if ('Enter' !== event.key) {
        return;
      }
      if (!event.target.closest || !event.target.closest(AMOUNT)) {
        return;
      }

      event.preventDefault();

      var box = container();
      if (box) {
        commitAmount(box, isSplitSelected(box));
      }
    },
    false
  );

  /**
   * Click "of £X" (or any [data-nera-wallet-fill]) to push that amount into the field
   * and focus it so the buyer can edit immediately. Does not edge-switch on click —
   * blur/Enter still commits per ADR 0005.
   */
  document.body.addEventListener(
    'click',
    function (event) {
      var fillBtn =
        event.target && event.target.closest
          ? event.target.closest('[data-nera-wallet-fill]')
          : null;
      if (!fillBtn) {
        return;
      }

      var box = fillBtn.closest(ROOT) || container();
      if (!box) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      var radio = box.querySelector(RADIO);
      if (radio && !radio.checked) {
        radio.click();
      }

      var fill = parseFloat(fillBtn.getAttribute('data-nera-wallet-fill'));
      var max = parseFloat(box.getAttribute('data-max')) || 0;
      if (isNaN(fill) || fill < 0) {
        fill = max;
      }
      if (fill > max) {
        fill = max;
      }

      var input = box.querySelector(AMOUNT);
      if (input) {
        input.value = fill.toFixed(2);
      }

      setFlash(box, '');
      setPartialFlag(box, true);
      showError(box, '');
      paintSummary(box);

      // radio.click() steals focus — re-focus the amount field after the panel opens.
      window.setTimeout(function () {
        var field = box.querySelector(AMOUNT);
        if (!field) {
          return;
        }
        field.focus();
        if (typeof field.select === 'function') {
          field.select();
        }
      }, 50);
    },
    false
  );

  /* Fragment re-render can wipe the flash note and flag — restore what we can. */
  if (window.jQuery) {
    window.jQuery(document.body).on('updated_checkout', function () {
      var box = container();
      if (!box) {
        return;
      }

      paintFlash(box);
      setPartialFlag(box, isSplitSelected(box));
      paintSummary(box);
    });
  }
})();
