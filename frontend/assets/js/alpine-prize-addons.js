/**
 * Prize Add-ons — Alpine component for the purchase card.
 *
 * Drives Components/blocks/PrizeAddOns: ticking options, the per-option Term
 * (years), the collapse toggle, and the add-ons total. Everything here is
 * display only — the cart prices every add-on line on the server
 * (inc/prize-addons.php, docs/adr/0015) and re-clamps whatever this posts.
 * The purchase card reads the checked input[name="nera_addon_ids[]"] boxes
 * and their paired input[name="nera_addon_years[<id>]"] when Enter Now is
 * pressed.
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
    const termsEnabled = !!config.termsEnabled;
    const maxTerm = Number.isFinite(config.maxTerm) && config.maxTerm >= 1 ? config.maxTerm : 10;

    // Term choice off ⇒ every option is 1 year, same as the server
    // (nera_prize_addons_quote()'s own clamp) — so a prize without the
    // switch behaves exactly as it did before terms existed.
    function clampYears(value) {
      if (!termsEnabled) {
        return 1;
      }
      const n = Math.trunc(Number(value));
      if (!Number.isFinite(n) || n < 1) {
        return 1;
      }
      return Math.min(n, maxTerm);
    }

    function findOption(id) {
      return options.find((option) => option.id === id);
    }

    // id => years. A plain reactive object (Alpine tracks property add/
    // remove/assign the same way Vue 3 does), not a Map — the server-rendered
    // config.selected is already this exact shape (docs/adr/0015).
    const initialSelected = {};
    if (config.selected && typeof config.selected === 'object') {
      Object.keys(config.selected).forEach((id) => {
        initialSelected[id] = clampYears(config.selected[id]);
      });
    }

    return {
      open: true,
      selected: initialSelected,
      i18n: i18n,

      toggle() {
        this.open = !this.open;
      },

      isChecked(id) {
        // `in`, not hasOwnProperty.call() — Alpine's reactivity (like Vue 3's)
        // tracks the `in` operator's `has` trap but not a plain
        // hasOwnProperty call, so the latter silently never re-evaluates
        // :disabled/:checked when a single option is ticked (only a whole
        // re-assignment of `selected`, e.g. toggleAll(), happened to work).
        return id in this.selected;
      },

      toggleOption(id) {
        if (this.isChecked(id)) {
          delete this.selected[id];
        } else {
          this.selected[id] = 1;
        }
      },

      years(id) {
        return this.selected[id] || 1;
      },

      setYears(id, value) {
        if (!this.isChecked(id)) {
          return;
        }
        this.selected[id] = clampYears(value);
      },

      chosen() {
        return options.filter((option) => this.isChecked(option.id));
      },

      allSelected() {
        return options.length > 0 && options.every((option) => this.isChecked(option.id));
      },

      isFullBundle() {
        return config.bundlePrice !== null && config.bundlePrice !== undefined && this.allSelected();
      },

      // Mirrors nera_prize_addons_quote()'s formula exactly (display only;
      // the server is always authoritative — docs/adr/0015): the number of
      // complete bundle sets is the smallest Term among the chosen options.
      bundleSets() {
        if (!this.isFullBundle()) {
          return 0;
        }
        return Math.min.apply(null, this.chosen().map((option) => this.years(option.id)));
      },

      extraYears(id) {
        if (!this.isFullBundle()) {
          return 0;
        }
        return Math.max(0, this.years(id) - this.bundleSets());
      },

      // The amount shown beside one option: always its own price × years
      // (ticked or not — years(id) already reads an unticked option as 1).
      // Never the bundle-adjusted "only the years beyond the set" figure:
      // that read as a confusing £0.00 next to a ticked, paid-for option
      // whose single year the bundle happened to cover. The bundle discount
      // still shows, just only in the aggregate total() below — the same way
      // the existing "Select all for £X (save £Y)" banner already explains a
      // bundle with no extra years, matching the server's own
      // nera_prize_addons_quote() (docs/adr/0015).
      optionAmount(id) {
        const option = findOption(id);
        const price = option ? Number(option.price || 0) : 0;
        return price * this.years(id);
      },

      // "£48.00 - £24.00 / year" — the option's money column when Term choice
      // is on (docs/adr/0015): what it currently costs, then its per-year rate.
      priceLine(id) {
        const option = findOption(id);
        const price = option ? Number(option.price || 0) : 0;
        return this.format(this.optionAmount(id)) + ' - ' + this.format(price) + ' ' + (i18n.perYear || '');
      },

      subtotal() {
        return this.chosen().reduce((sum, option) => sum + Number(option.price || 0) * this.years(option.id), 0);
      },

      total() {
        if (!this.isFullBundle()) {
          return this.subtotal();
        }
        const extra = this.chosen().reduce(
          (sum, option) => sum + Number(option.price || 0) * this.extraYears(option.id),
          0
        );
        return this.bundleSets() * Number(config.bundlePrice) + extra;
      },

      toggleAll() {
        if (this.allSelected()) {
          this.selected = {};
          return;
        }
        const next = {};
        options.forEach((option) => {
          next[option.id] = this.isChecked(option.id) ? this.years(option.id) : 1;
        });
        this.selected = next;
      },

      format(amount) {
        return formatMoney(amount, currency);
      },

      // "N bundle set(s) + M extra year(s)" — only once the bundle applies
      // and at least one option's Term runs past the shared set count.
      extraYearsLine() {
        if (!this.isFullBundle()) {
          return '';
        }
        const sets = this.bundleSets();
        const extraTotal = this.chosen().reduce((sum, option) => sum + this.extraYears(option.id), 0);
        if (extraTotal <= 0) {
          return '';
        }
        return String(i18n.bundleSets || '').replace('%1$d', sets).replace('%2$d', extraTotal);
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
