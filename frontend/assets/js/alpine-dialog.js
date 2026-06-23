/**
 * AlpineJS Dialog Store
 *
 * Promise-based replacement for native window.confirm() / window.alert().
 * Native browser dialogs are unreliable inside mobile WebViews (Android WebView
 * silently drops them unless the host app wires WebChromeClient.onJsAlert/onJsConfirm),
 * so all confirmation/alert UX is rendered as DOM instead.
 *
 * Markup lives in footer.php (single shared block driven by this store).
 *
 * Usage:
 *   const ok = await Alpine.store('dialog').confirm({ title, message, confirmText, cancelText, variant });
 *   await Alpine.store('dialog').alert({ title, message, confirmText });
 *
 * `message` is rendered with x-html — callers must pass already-safe strings.
 */
(function () {
  function initStore() {
    if (typeof Alpine === 'undefined') {
      console.error('Alpine.js not loaded - dialog store cannot initialize');
      return;
    }

    Alpine.store('dialog', {
      open: false,
      mode: 'confirm', // 'confirm' | 'alert'
      title: '',
      message: '',
      confirmText: 'Confirm',
      cancelText: 'Cancel',
      variant: 'primary', // 'primary' | 'danger' -> confirm button color
      _resolve: null,

      /**
       * Show a confirm dialog (Cancel + Confirm buttons).
       * @returns {Promise<boolean>} resolves true on confirm, false on cancel/dismiss
       */
      confirm(opts = {}) {
        return this._show({
          mode: 'confirm',
          confirmText: 'Yes',
          cancelText: 'Cancel',
          ...opts,
        });
      },

      /**
       * Show an alert dialog (single OK button).
       * @returns {Promise<boolean>} resolves true when dismissed
       */
      alert(opts = {}) {
        return this._show({
          mode: 'alert',
          confirmText: 'OK',
          ...opts,
        });
      },

      _show(opts) {
        // Reset to defaults first so leftover state from a previous call never leaks
        Object.assign(
          this,
          { title: '', message: '', confirmText: 'Confirm', cancelText: 'Cancel', variant: 'primary' },
          opts,
        );
        this.open = true;
        document.body.style.overflow = 'hidden';
        return new Promise(resolve => {
          this._resolve = resolve;
        });
      },

      accept() {
        this._settle(true);
      },

      cancel() {
        // alert() callers simply ignore the boolean
        this._settle(false);
      },

      _settle(result) {
        if (!this.open) return;
        this.open = false;
        document.body.style.overflow = '';
        const resolve = this._resolve;
        this._resolve = null;
        if (resolve) resolve(result);
      },
    });
  }

  // Attach immediately (Alpine's init event can fire before DOMContentLoaded)
  document.addEventListener('alpine:init', initStore);
})();
