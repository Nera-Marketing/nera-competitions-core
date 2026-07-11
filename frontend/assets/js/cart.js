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
      // Custom HTML confirm dialog (native confirm() is unreliable in mobile WebViews).
      // Falls back to native confirm only if Alpine is unavailable.
      const ask = window.Alpine
        ? Alpine.store('dialog').confirm({
            title: 'Remove item?',
            message: 'Are you sure you want to remove this item from your cart?',
            confirmText: 'Remove',
            cancelText: 'Cancel',
            variant: 'danger',
          })
        : Promise.resolve(window.confirm('Are you sure you want to remove this item?'));

      ask.then(confirmed => {
        if (!confirmed) return;

        // Standard removal URL usually available in render
        // But since we want AJAX, we can use the wc_cart_remove_item endpoint or ?remove_item=key query
        // Safest: Use the remove link href if available, prevent default, and fetch it.
        // We didn't render standard remove link, so let's construct it or use form.

        const url = `?remove_item=${key}&_wpnonce=${document.querySelector('#woocommerce-cart-nonce').value}`;

        const itemRow = document.getElementById(`cart-item-${key}`);
        if (itemRow) {
          itemRow.style.transform = 'translateX(100px)';
          itemRow.style.opacity = '0';
        }

        fetch(url)
          .then(response => response.text())
          .then(html => {
            // Parse and replace like update
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newForm = doc.querySelector('.woocommerce-cart-form');
            const emptyCart = doc.querySelector('.nera-cart-empty-state'); // if cart became empty

            const container = document.querySelector('.woocommerce-cart-form').parentNode;

            if (emptyCart) {
              // Cart is now empty
              document.querySelector('.woocommerce-cart-form').remove();
              // Inject message
              // Best to reload or just put the HTML
              location.reload();
              return;
            }

            if (newForm) {
              document.querySelector('.woocommerce-cart-form').innerHTML = newForm.innerHTML;
            }

            jQuery(document.body).trigger('wc_fragment_refresh');
            if (window.Alpine) Alpine.store('toast').info('Item removed');
          })
          .catch(err => location.reload()); // Fallback
      });
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
