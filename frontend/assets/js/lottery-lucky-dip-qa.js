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

  var TRIGGER_SELECTOR = '.lty-regenerate-lucky-dip-add-to-cart-button, .lty-add-to-cart-lucky-dip-button';

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
   * Reveal the inline error inside the popup's question column.
   *
   * @param {HTMLElement} popup
   */
  function showError(popup) {
    var error = popup.querySelector('[data-nera-qa-error]');
    if (!error) {
      return;
    }

    error.hidden = false;

    if (typeof error.scrollIntoView === 'function') {
      error.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
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

    var error = column.querySelector('[data-nera-qa-error]');
    if (error) {
      error.hidden = true;
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

      if (hasAnswer()) {
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
})();
