/**
 * Nera Cart Logic
 * Handles interactive cart updates via AJAX
 */
(function () {
  'use strict';

  const NeraCart = {
    updateTimer: null,

    // Manual update logic relies on form submission
    // updateQuantity removed to support manual "Update Cart" button workflow

    /**
     * Remove item from cart
     * @param {string} key
     */
    removeItem: function (key) {
      if (!confirm('Are you sure you want to remove this item?')) return;

      NeraCart.request(`?remove_item=${key}`, `cart-item-${key}`, 'Item removed');
    },

    /**
     * Remove one option from a prize's add-on line (the × on an option).
     * Removing the last option removes the line. Handled by
     * nera_prize_addons_handle_remove_option() in inc/prize-addons.php.
     * @param {string} key      Add-on cart line key.
     * @param {string} optionId Add-on option ID.
     */
    removeAddonOption: function (key, optionId) {
      const query = `?nera_addon_line=${encodeURIComponent(key)}&nera_remove_addon=${encodeURIComponent(optionId)}`;
      NeraCart.request(query, `cart-item-${key}`, 'Add-on removed', false);
    },

    /**
     * Fetch a cart-changing URL and swap the refreshed cart form in.
     * @param {string}  query      Query string (the nonce is appended).
     * @param {string}  rowId      Row to fade while waiting.
     * @param {string}  message    Toast shown when done.
     * @param {boolean} slideAway  Slide the whole row out (true) or just fade it.
     */
    request: function (query, rowId, message, slideAway = true) {
      const nonce = document.querySelector('#woocommerce-cart-nonce').value;
      const url = `${query}&_wpnonce=${nonce}`;

      const itemRow = document.getElementById(rowId);
      if (itemRow) {
        if (slideAway) {
          itemRow.style.transform = 'translateX(100px)';
        }
        itemRow.style.opacity = slideAway ? '0' : '0.5';
      }

      fetch(url)
        .then(response => response.text())
        .then(html => {
          // Parse and replace like update
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newForm = doc.querySelector('.woocommerce-cart-form');
          const emptyCart = doc.querySelector('.nera-cart-empty-state'); // if cart became empty

          if (emptyCart) {
            // Cart is now empty: reload to show the empty state
            location.reload();
            return;
          }

          if (newForm) {
            document.querySelector('.woocommerce-cart-form').innerHTML = newForm.innerHTML;
          }

          jQuery(document.body).trigger('wc_fragment_refresh');
          if (window.Alpine) Alpine.store('toast').info(message);
        })
        .catch(err => location.reload()); // Fallback
    },
  };

  // Expose
  window.NeraCart = NeraCart;

  // Fix for WooCommerce disabled button: ensure Update Cart button is always enabled
  document.addEventListener('DOMContentLoaded', () => {
    const updateBtn = document.querySelector('button[name="update_cart"]');
    if (updateBtn) {
      // Remove disabled attribute
      updateBtn.removeAttribute('disabled');

      // Monitor and prevent WooCommerce from re-disabling it
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
            if (updateBtn.hasAttribute('disabled')) {
              updateBtn.removeAttribute('disabled');
            }
          }
        });
      });

      observer.observe(updateBtn, { attributes: true });
    }
  });
})();
