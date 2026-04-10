/**
 * AlpineJS Toast Notification Store
 * Global store for managing toast notifications
 */
(function () {
  function initStores() {
    if (typeof Alpine === 'undefined') {
      console.error('Alpine.js not loaded - toast/postDialog stores cannot initialize');
      return;
    }

    Alpine.store('toast', {
      items: [],

      /**
       * Add a new toast notification
       * @param {string} message Message to display
       * @param {string} type Type of toast (success, error, warning, info)
       * @param {object|null} action Optional action button { label: 'Click me', callback: () => {} }
       * @returns {number} ID of the toast
       */
      add(message, type = 'info', action = null) {
        const id = Date.now() + Math.random();
        // Push initial state
        this.items.push({ id, message, type, action, isVisible: false });

        // Trigger enter animation after a small delay to ensure DOM update
        // We access the item via the array to ensure we are modifying the reactive proxy
        setTimeout(() => {
          const item = this.items.find(i => i.id === id);
          if (item) item.isVisible = true;
        }, 50);

        // Auto-remove after delay
        setTimeout(
          () => {
            this.remove(id);
          },
          type === 'error' ? 8000 : 5000
        );

        return id;
      },

      /**
       * Remove a toast by ID
       * @param {number} id Toast ID
       */
      remove(id) {
        const toast = this.items.find(item => item.id === id);
        if (!toast) return;

        // Trigger leave animation
        toast.isVisible = false;

        // Remove from DOM after animation finishes
        setTimeout(() => {
          this.items = this.items.filter(item => item.id !== id);
        }, 400); // Wait for transition duration
      },

      /**
       * Helper for success toasts
       */
      success(message, action = null) {
        return this.add(message, 'success', action);
      },

      /**
       * Helper for error toasts
       */
      error(message, action = null) {
        return this.add(message, 'error', action);
      },

      /**
       * Helper for warning toasts
       */
      warning(message, action = null) {
        return this.add(message, 'warning', action);
      },

      /**
       * Helper for info toasts
       */
      info(message, action = null) {
        return this.add(message, 'info', action);
      },
    });

    // Post Dialog Store
    Alpine.store('postDialog', {
      show: false,
      init() {},
    });
  }

  // Initialize when Alpine is ready
  // CRITICAL FIX: Attach alpine:init listener IMMEDIATELY, not after DOMContentLoaded
  // Alpine's init event can fire before DOMContentLoaded in some scenarios
  document.addEventListener('alpine:init', initStores);
})();
