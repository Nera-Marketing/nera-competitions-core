/**
 * Prize Add-ons — Alpine component for the purchase card.
 *
 * Drives Components/blocks/PrizeAddOns: ticking options, the collapse toggle
 * and the add-ons total. The total is for display only; the cart prices every
 * add-on line on the server (inc/prize-addons.php). The purchase card reads the
 * checked input[name="nera_addon_ids[]"] boxes when Enter Now is pressed.
 *
 * Loaded before Alpine core (see nera_enqueue_scripts in functions.php).
 */
(function () {
  'use strict';

  function formatMoney(amount, currency) {
    const n = Number(amount);
    const decimals = Number.isFinite(currency.decimals) ? currency.decimals : 2;
    const fixed = (Number.isFinite(n) ? n : 0).toFixed(decimals);
    const parts = fixed.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, currency.thousand || '');
    const number = decimals > 0 ? parts[0] + (currency.decimal || '.') + parts[1] : parts[0];
    const symbol = currency.symbol || '';

    switch (currency.position) {
      case 'left_space':
        return symbol + ' ' + number;
      case 'right':
        return number + symbol;
      case 'right_space':
        return number + ' ' + symbol;
      default:
        return symbol + number;
    }
  }

  function neraPrizeAddons(config) {
    const options = Array.isArray(config.options) ? config.options : [];
    const i18n = config.i18n || {};
    const currency = config.currency || {};

    return {
      open: true,
      selected: Array.isArray(config.selected) ? config.selected.slice() : [],
      i18n: i18n,

      toggle() {
        this.open = !this.open;
      },

      chosen() {
        return options.filter((option) => this.selected.includes(option.id));
      },

      subtotal() {
        return this.chosen().reduce((sum, option) => sum + Number(option.price || 0), 0);
      },

      allSelected() {
        return options.length > 0 && options.every((option) => this.selected.includes(option.id));
      },

      isFullBundle() {
        return config.bundlePrice !== null && config.bundlePrice !== undefined && this.allSelected();
      },

      total() {
        return this.isFullBundle() ? Number(config.bundlePrice) : this.subtotal();
      },

      toggleAll() {
        this.selected = this.allSelected() ? [] : options.map((option) => option.id);
      },

      format(amount) {
        return formatMoney(amount, currency);
      },

      summary() {
        if (!options.length) {
          return i18n.allPurchased || '';
        }
        const chosen = this.chosen();
        if (!chosen.length) {
          return String(i18n.summaryNone || '').replace('%d', options.length);
        }
        const count = String(i18n.summarySelected || '')
          .replace('%1$d', chosen.length)
          .replace('%2$d', options.length);
        return count + ' · ' + chosen.map((option) => option.title).join(', ');
      },
    };
  }

  function register() {
    window.Alpine.data('neraPrizeAddons', neraPrizeAddons);
  }

  if (window.Alpine) {
    register();
  } else {
    document.addEventListener('alpine:init', register);
  }

  window.NeraPrizeAddons = { formatMoney: formatMoney };
})();
