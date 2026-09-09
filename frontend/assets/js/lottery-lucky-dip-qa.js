/**
 * Block Lucky Dip add-to-cart until the skill question is answered.
 *
 * WHY THIS EXISTS
 * ---------------
 * Lottery for WooCommerce reads the chosen answer with one global selector,
 * `$('.lty-question-answer-id').val()`, and validates against
 * `.lty-lottery-question-answer-container[data-force="yes"]`. This theme
 * renders its question through the SkillQuestionAnswer component, so neither
 * existed: Lucky Dip posted an empty answer and the resulting order had its
 * tickets cancelled, with no warning shown because the missing container also
 * skipped the plugin's own check.
 *
 * The bridge input in purchase-card-body-inner.php restores the value. This
 * file restores the gate — deliberately in the theme rather than by setting
 * data-force="yes", because the plugin's version raises a $.alertable dialog
 * over the inline message in the popup's question column.
 *
 * WHY CAPTURE PHASE
 * -----------------
 * The plugin binds its handlers with jQuery delegation on `document`, which
 * runs during bubbling. A jQuery handler added later would run after the
 * plugin's and could not stop the request. A native capture-phase listener on
 * `document` runs before any of them, so stopping propagation there actually
 * prevents the add-to-cart.
 *
 * @package Nera_Competitions
 */
(function () {
  'use strict';

  // Every button that can put Lucky Dip tickets in the cart. Add More was
  // missing here, so it was the one route that reached the plugin ungated.
  var TRIGGER_SELECTOR =
    '.lty-regenerate-lucky-dip-add-to-cart-button, .lty-add-to-cart-lucky-dip-button, .lty-add-more-lucky-tip';

  /**
   * The one canonical answer holder. See purchase-card-body-inner.php.
   *
   * @return {HTMLInputElement|null}
   */
  function bridge() {
    return document.querySelector('[data-nera-qa-bridge]');
  }

  /**
   * Whether an answer has been chosen anywhere — the popup writes to the same
   * input the product page uses, so one lookup covers both.
   *
   * @return {boolean}
   */
  function hasAnswer() {
    var el = bridge();

    return !!(el && String(el.value).trim() !== '');
  }

  /**
   * Mark the question as unanswered so the options themselves turn red.
   *
   * Replaces a sentence of validation copy under the list. The warning above
   * the button already says an answer is required, so repeating it after the
   * click only added a third block of text to read — colouring the thing that
   * needs attention points straight at it instead.
   *
   * @param {HTMLElement} popup
   */
  function showError(popup) {
    var column = popup.querySelector('[data-nera-qa-column]');
    if (!column) {
      return;
    }

    column.setAttribute('data-nera-qa-invalid', 'yes');

    if (typeof column.scrollIntoView === 'function') {
      column.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
  }

  /**
   * Record the chosen answer and reflect it everywhere it is read.
   *
   * The popup is rendered without Alpine (see SkillQuestionAnswer's
   * `interactive` flag), so selection state is ours to manage: the clicked
   * option is flagged for CSS, the bridge input is updated because that is
   * what LFW posts, and the window event lets the product page underneath
   * move its own radio so the customer is not asked the question twice.
   *
   * @param {HTMLElement} option
   */
  function selectOption(option) {
    var column = option.closest('[data-nera-qa-column]');
    if (!column) {
      return;
    }

    // Once the answer is in the cart it cannot be changed from here, so the
    // column is frozen and shows what was committed.
    if ('yes' === column.getAttribute('data-nera-qa-frozen')) {
      return;
    }

    var answerId = option.getAttribute('data-nera-qa-option') || '';

    column.querySelectorAll('[data-nera-qa-option]').forEach(function (el) {
      el.setAttribute('data-nera-qa-selected', el === option ? 'yes' : 'no');
    });

    var input = option.querySelector('input[type="radio"]');
    if (input) {
      input.checked = true;
    }

    document.querySelectorAll('[data-nera-qa-bridge]').forEach(function (el) {
      el.value = answerId;
    });

    column.removeAttribute('data-nera-qa-invalid');

    // A new choice makes any previous verdict on the old one stale.
    var message = column.querySelector('[data-nera-qa-message]');
    if (message) {
      message.textContent = '';
      message.hidden = true;
    }

    window.dispatchEvent(
      new CustomEvent('nera-qa-answer-selected', { detail: { answerId: answerId } })
    );
  }

  document.addEventListener('click', function (event) {
    var target = event.target;
    if (!target || typeof target.closest !== 'function') {
      return;
    }

    var option = target.closest('[data-nera-qa-option]');
    if (option) {
      selectOption(option);
    }
  });

  document.addEventListener(
    'click',
    function (event) {
      var target = event.target;
      if (!target || typeof target.closest !== 'function') {
        return;
      }

      var trigger = target.closest(TRIGGER_SELECTOR);
      if (!trigger) {
        return;
      }

      // Only guard popups that actually asked a question. A product without a
      // skill question keeps its original behaviour untouched.
      var popup = trigger.closest('[data-nera-qa-popup]');
      if (!popup) {
        return;
      }

      /*
       * Re-read the answer from the popup and write it to the bridge before the
       * plugin reads it.
       *
       * The bridge is written when an option is clicked, but it lives on the
       * product page while the option lives in a popup, and several things can
       * repaint the page in between — cart-fragment refreshes replace that part
       * of the DOM after every add, and Alpine re-initialises whatever replaces
       * it from server-rendered state. Any of those puts the previously
       * committed answer back. That is how a wrong answer chosen after a
       * successful add still reached the cart as the earlier correct one, and
       * why it passed server validation.
       *
       * Taking the value from what the customer can actually see, at the moment
       * they act on it, removes the whole class of staleness rather than one
       * path through it.
       */
      var selected = popup.querySelector('[data-nera-qa-option][data-nera-qa-selected="yes"]');
      var answerId = selected ? selected.getAttribute('data-nera-qa-option') : '';

      if (answerId) {
        document.querySelectorAll('[data-nera-qa-bridge]').forEach(function (el) {
          el.value = answerId;
        });

        return;
      }

      // The popup asks a question and nothing is chosen in it.
      if (hasAnswer() && !popup.querySelector('[data-nera-qa-option]')) {
        return;
      }

      event.preventDefault();
      event.stopImmediatePropagation();
      showError(popup);
    },
    true
  );

  // Note: the add-to-cart sound is NOT fired from here. lottery-lucky-dip-sync.js
  // already emits `nera:cart:updated` when a success popup opens, which
  // cart-sound.js listens for. Emitting it here as well made it play twice.

  /**
   * Show the same corner toast a normal add-to-cart shows.
   *
   * Lucky Dip adds through the plugin's own AJAX, which never reached the
   * theme's Alpine toast store — so the sound played but nothing was said.
   * Rather than hook the request, this listens to the event
   * lottery-lucky-dip-sync.js already fires once a success popup opens, which
   * is the one moment we know the cart actually changed.
   *
   * Filtered on `source === 'lucky-dip'`: the product page's own submitForm
   * fires the same event and raises its own toast, so an unfiltered listener
   * would double up on every ordinary add-to-cart.
   */
  document.addEventListener('nera:cart:updated', function (event) {
    var detail = event.detail || {};
    if ('lucky-dip' !== detail.source) {
      return;
    }

    if (!window.Alpine || !window.Alpine.store || !window.Alpine.store('toast')) {
      return;
    }

    var tickets = Array.isArray(detail.tickets) ? detail.tickets : [];
    var count = tickets.length;
    var message = count
      ? count + (1 === count ? ' ticket added to cart' : ' tickets added to cart')
      : 'Tickets added to cart';

    window.Alpine.store('toast').success(message, {
      label: 'View Cart',
      callback: function () {
        var link = document.querySelector('.lty-view-cart');
        window.location.href = link ? link.getAttribute('href') : '/cart/';
      },
    });
  });
})();
