/**
 * Purchase-card quantity control (buttons or slider layout).
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  const HOLD_DELAY_MS = 350;
  const HOLD_INTERVAL_MS = 80;

  /**
   * @param {HTMLElement} root
   */
  function initQuantityControl(root) {
    const quantityInput = root.querySelector('[data-quantity-input]');
    if (!quantityInput) {
      return;
    }

    const quantityHidden = document.querySelector('[data-quantity-hidden]');
    const minusBtn = root.querySelector('[data-quantity-minus]');
    const plusBtn = root.querySelector('[data-quantity-plus]');
    const addBtns = root.querySelectorAll('[data-quantity-add]');
    const rangeInput = root.querySelector('[data-quantity-range]');
    const display = root.querySelector('[data-quantity-display]');

    const getMin = () => parseInt(quantityInput.min, 10) || 1;
    const getMax = () => parseInt(quantityInput.max, 10) || 999;

    /**
     * @param {number|string} newValue
     */
    function updateQuantity(newValue) {
      const min = getMin();
      const max = getMax();
      const value = Math.max(min, Math.min(max, parseInt(String(newValue), 10) || min));

      quantityInput.value = String(value);

      if (rangeInput) {
        rangeInput.value = String(value);
        rangeInput.setAttribute('aria-valuenow', String(value));
      }

      if (display) {
        display.textContent = String(value);
      }

      if (quantityHidden) {
        quantityHidden.value = String(value);
      }

      if (minusBtn) {
        minusBtn.disabled = value <= min;
      }
      if (plusBtn) {
        plusBtn.disabled = value >= max;
      }

      quantityInput.dispatchEvent(new Event('change', { bubbles: true }));

      document.dispatchEvent(
        new CustomEvent('nera:quantity:change', {
          detail: { quantity: value },
        })
      );
    }

    /**
     * @param {HTMLButtonElement} button
     * @param {number} delta -1 or +1
     */
    function bindHoldStep(button, delta) {
      let holdDelayId = null;
      let holdIntervalId = null;
      let skipNextClick = false;

      const stopHold = () => {
        clearTimeout(holdDelayId);
        clearInterval(holdIntervalId);
        holdDelayId = null;
        holdIntervalId = null;
        document.removeEventListener('pointerup', stopHold);
        document.removeEventListener('pointercancel', stopHold);
      };

      const step = () => {
        if (button.disabled) {
          stopHold();
          return false;
        }

        const before = parseInt(quantityInput.value, 10);
        updateQuantity(before + delta);
        const after = parseInt(quantityInput.value, 10);

        if (after === before || button.disabled) {
          stopHold();
          return false;
        }

        return true;
      };

      const startHold = () => {
        stopHold();
        step();
        holdDelayId = window.setTimeout(() => {
          holdIntervalId = window.setInterval(() => {
            step();
          }, HOLD_INTERVAL_MS);
        }, HOLD_DELAY_MS);
        document.addEventListener('pointerup', stopHold);
        document.addEventListener('pointercancel', stopHold);
      };

      button.addEventListener('pointerdown', e => {
        if (e.button !== 0 || button.disabled) {
          return;
        }

        skipNextClick = true;
        e.preventDefault();

        if (typeof button.setPointerCapture === 'function') {
          try {
            button.setPointerCapture(e.pointerId);
          } catch (_err) {
            /* ignore */
          }
        }

        startHold();
      });

      button.addEventListener('pointerup', stopHold);
      button.addEventListener('pointercancel', stopHold);

      button.addEventListener('click', () => {
        if (skipNextClick) {
          skipNextClick = false;
          return;
        }

        if (!button.disabled) {
          step();
        }
      });
    }

    if (minusBtn) {
      bindHoldStep(minusBtn, -1);
    }

    if (plusBtn) {
      bindHoldStep(plusBtn, 1);
    }

    addBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        const addAmount = parseInt(this.dataset.quantityAdd, 10);
        updateQuantity(parseInt(quantityInput.value, 10) + addAmount);
      });
    });

    if (rangeInput) {
      rangeInput.addEventListener('input', function () {
        updateQuantity(this.value);
      });
    }

    quantityInput.addEventListener('change', function () {
      updateQuantity(this.value);
    });

    quantityInput.addEventListener('input', function () {
      updateQuantity(this.value);
    });

    updateQuantity(quantityInput.value || getMin());
  }

  function initAllQuantityControls() {
    document.querySelectorAll('[data-quantity-control]').forEach(initQuantityControl);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllQuantityControls);
  } else {
    initAllQuantityControls();
  }

  window.neraInitQuantityControls = initAllQuantityControls;
})();
