/**
 * Prize Add-ons in the Lucky Dip dialogs (template-parts/single-product/lucky-dip-addons.php).
 *
 * Plain JS on purpose: the Lucky Dip popups are injected by Lottery for WooCommerce after
 * Alpine has started, and Alpine does not pick up markup added later.
 *
 *  - One selection per prize, shared by every copy of the block on the page: the one in
 *    the Lucky Dip box, and the ones in the popups. A copy that appears later (a popup)
 *    starts from what was already chosen. The selection is an id => years map
 *    (docs/adr/0015) — a missing id means 1 year, same convention as the server.
 *  - The total is for display only; the cart prices the add-on line on the server, which
 *    re-clamps whatever Term this posts (off prize switch => 1, else 1..max_term).
 *  - The chosen options and years ride along on the Lucky Dip requests that put tickets in
 *    the basket, so the add-on line is created or updated in the same step. The server side
 *    is nera_prize_addons_sync_from_lucky_dip() in inc/prize-addons.php.
 *  - In a popup that is shown after the tickets were added, a tick (or a Years edit) is
 *    saved to the basket at once (nera_prize_addons_save), asking about the spending limit
 *    first.
 */
(function ($) {
  'use strict';

  var BLOCK = '[data-nera-ld-addons]';
  var ADD_ACTIONS = /(?:^|&)action=lty_(?:process_lucky_dip|regenerate_lucky_dip_add_to_cart)(?:&|$)/;

  // Prize ID -> { option_id: years }. Absent until a block for that prize has been seen.
  var selectionState = {};

  function config(block) {
    try {
      return JSON.parse(block.getAttribute('data-config') || '{}');
    } catch (e) {
      return {};
    }
  }

  function formatMoney(amount, currency) {
    currency = currency || {};
    var decimals = isFinite(currency.decimals) ? currency.decimals : 2;
    var fixed = (isFinite(Number(amount)) ? Number(amount) : 0).toFixed(decimals);
    var parts = fixed.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, currency.thousand || '');
    var number = decimals > 0 ? parts[0] + (currency.decimal || '.') + parts[1] : parts[0];
    var symbol = currency.symbol || '';
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

  function blocksFor(productId) {
    return Array.prototype.filter.call(document.querySelectorAll(BLOCK), function (b) {
      return b.getAttribute('data-product-id') === String(productId);
    });
  }

  function findOption(cfg, id) {
    return (cfg.options || []).filter(function (o) {
      return o.id === id;
    })[0];
  }

  // Clamped years for PRICING math only — never to rewrite what was typed.
  // Term choice off ⇒ always 1 year, same as the server's own clamp
  // (nera_prize_addons_quote(), docs/adr/0015). The input itself (via
  // readSelection()/apply() below) always keeps the raw typed value, even
  // out of range: the input's own min/max make the browser mark it
  // :invalid and report a native validation message instead of the value
  // being silently corrected away.
  function clampForPricing(cfg, years) {
    if (!cfg.termsEnabled) {
      return 1;
    }
    var n = parseInt(years, 10);
    if (!isFinite(n) || n < 1) {
      return 1;
    }
    var max = isFinite(cfg.maxTerm) && cfg.maxTerm >= 1 ? cfg.maxTerm : 10;
    return Math.min(n, max);
  }

  // Reads the block's own checkboxes + their paired Years inputs into one
  // { option_id: years } map — the single source of truth for "what is
  // ticked right now" (apply() below writes the same shape back to the DOM).
  function readSelection(block) {
    var sel = {};
    Array.prototype.forEach.call(block.querySelectorAll('.nera-ld-addons__check:checked'), function (input) {
      var id = input.value;
      var yearsInput = block.querySelector('[data-nera-ld-years="' + id + '"]');
      var years = yearsInput ? parseInt(yearsInput.value, 10) : 1;
      sel[id] = isFinite(years) && years >= 1 ? years : 1;
    });
    return sel;
  }

  function apply(block, selection) {
    Array.prototype.forEach.call(block.querySelectorAll('.nera-ld-addons__check'), function (input) {
      var isSelected = Object.prototype.hasOwnProperty.call(selection, input.value);
      input.checked = isSelected;
      var yearsInput = block.querySelector('[data-nera-ld-years="' + input.value + '"]');
      if (yearsInput) {
        yearsInput.disabled = !isSelected;
        if (isSelected) {
          yearsInput.value = selection[input.value];
        }
      }
    });
  }

  // Mirrors nera_prize_addons_quote()'s formula (display only; the server is
  // always authoritative — docs/adr/0015): complete sets price at the bundle
  // rate, years beyond the smallest Term price at each option's own rate.
  function computeTotals(cfg, selection) {
    var options = cfg.options || [];
    var ids = Object.keys(selection);
    var all = options.length > 0 && ids.length === options.length;
    var hasBundle = cfg.bundlePrice !== null && cfg.bundlePrice !== undefined;
    var full = hasBundle && all;

    var subtotal = options.reduce(function (sum, o) {
      return Object.prototype.hasOwnProperty.call(selection, o.id)
        ? sum + Number(o.price || 0) * clampForPricing(cfg, selection[o.id])
        : sum;
    }, 0);

    var bundleSets = 0;
    var extraYearsTotal = 0;
    var total = subtotal;
    if (full) {
      bundleSets = Math.min.apply(
        null,
        ids.map(function (id) {
          return clampForPricing(cfg, selection[id]);
        })
      );
      var extraCharge = 0;
      ids.forEach(function (id) {
        var option = findOption(cfg, id);
        var price = option ? Number(option.price || 0) : 0;
        var extraY = Math.max(0, clampForPricing(cfg, selection[id]) - bundleSets);
        extraYearsTotal += extraY;
        extraCharge += price * extraY;
      });
      total = bundleSets * Number(cfg.bundlePrice) + extraCharge;
    }

    return { subtotal: subtotal, total: total, full: full, bundleSets: bundleSets, extraYearsTotal: extraYearsTotal };
  }

  function render(block) {
    var cfg = config(block);
    var options = cfg.options || [];
    var sel = readSelection(block);
    var totals = computeTotals(cfg, sel);

    var totalEl = block.querySelector('[data-nera-ld-total]');
    if (totalEl) {
      totalEl.textContent = '';
      if (totals.full) {
        var strike = document.createElement('s');
        strike.textContent = formatMoney(totals.subtotal, cfg.currency) + ' ';
        totalEl.appendChild(strike);
      }
      totalEl.appendChild(document.createTextNode(formatMoney(totals.total, cfg.currency)));
    }

    // Per-option amount: always its own price x years (1 when unticked) —
    // never the bundle-adjusted "only the years beyond the set" figure, which
    // reads as a confusing £0.00 next to a ticked, paid-for option whose
    // single year the bundle happened to cover. The bundle discount still
    // shows, just only in the aggregate total above (docs/adr/0015). The
    // static "(price / year)" beside it (template-parts/single-product/
    // lucky-dip-addons.php) never changes with the Term, so only this total
    // needs updating here.
    Array.prototype.forEach.call(block.querySelectorAll('[data-nera-ld-amount]'), function (el) {
      var id = el.getAttribute('data-nera-ld-amount');
      var option = findOption(cfg, id);
      var price = option ? Number(option.price || 0) : 0;
      var isSelected = Object.prototype.hasOwnProperty.call(sel, id);
      var amount = price * (isSelected ? clampForPricing(cfg, sel[id]) : 1);
      el.textContent = formatMoney(amount, cfg.currency);
    });

    var btn = block.querySelector('[data-nera-ld-select-all]');
    if (btn) {
      var all = options.length > 0 && Object.keys(sel).length === options.length;
      btn.classList.toggle('is-active', all);
      btn.setAttribute('aria-pressed', all ? 'true' : 'false');
    }

    // The line shown in place of the list while it is collapsed.
    var summary = block.querySelector('[data-nera-ld-summary]');
    if (summary) {
      var i18n = cfg.i18n || {};
      var count = Object.keys(sel).length;
      summary.textContent = count
        ? String(i18n.summarySelected || '%1$d of %2$d selected').replace('%1$d', count).replace('%2$d', options.length)
        : String(i18n.summaryNone || 'None selected · %d extras available').replace('%d', options.length);
    }
  }

  function setCollapsed(block, collapsed) {
    block.classList.toggle('is-collapsed', collapsed);
    var toggle = block.querySelector('[data-nera-ld-toggle]');
    if (toggle) {
      toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      var label = toggle.getAttribute(collapsed ? 'data-label-show' : 'data-label-hide');
      if (label) {
        toggle.setAttribute('aria-label', label);
      }
    }
    var body = block.querySelector('[data-nera-ld-body]');
    if (body) {
      body.hidden = collapsed;
    }
    var summary = block.querySelector('[data-nera-ld-summary]');
    if (summary) {
      summary.hidden = !collapsed;
    }
  }

  function init(block) {
    if (block.getAttribute('data-nera-ld-ready')) {
      return;
    }
    block.setAttribute('data-nera-ld-ready', '1');
    var pid = block.getAttribute('data-product-id');

    if (selectionState[pid] === undefined) {
      selectionState[pid] = readSelection(block); // first copy seen: what the basket already holds
    } else {
      apply(block, selectionState[pid]);
    }
    render(block);
  }

  function initAll(root) {
    if (root.matches && root.matches(BLOCK)) {
      init(root);
    }
    if (root.querySelectorAll) {
      Array.prototype.forEach.call(root.querySelectorAll(BLOCK), init);
    }
  }

  function choose(productId, selection) {
    selectionState[productId] = selection;
    blocksFor(productId).forEach(function (b) {
      apply(b, selection);
      render(b);
    });
  }

  // The prize page's own Add-ons block (Components/blocks/PrizeAddOns, Alpine) keeps its own
  // `selected` id => years map, read when Enter Now is pressed. Once the basket's add-on line
  // has been changed from a Lucky Dip dialog, bring that block in line so the two never disagree.
  function syncPrizePage(productId, selection) {
    var root = document.querySelector('[data-prize-addons="' + productId + '"]');
    if (!root || !window.Alpine || typeof window.Alpine.$data !== 'function') {
      return;
    }
    try {
      var data = window.Alpine.$data(root);
      if (data && data.selected && typeof data.selected === 'object') {
        data.selected = Object.assign({}, selection);
      }
    } catch (e) {
      // The block is only a convenience mirror: never let it break the dialog.
    }
  }

  // The theme's toast (Alpine store), with the wording supplied by the server so it is translatable.
  function toast(type, key) {
    var messages = (window.neraLuckyDipAddons || {}).i18n || {};
    var message = messages[key];
    var store = window.Alpine && window.Alpine.store ? window.Alpine.store('toast') : null;
    if (message && store && typeof store[type] === 'function') {
      store[type](message);
    }
  }

  function post(params) {
    return fetch((window.neraLuckyDipLimit || {}).ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: params.toString(),
      credentials: 'same-origin',
    }).then(function (r) {
      return r.json();
    });
  }

  function withSelection(params, selection) {
    Object.keys(selection).forEach(function (id) {
      params.append('nera_addon_ids[]', id);
      params.append('nera_addon_years[' + id + ']', selection[id]);
    });
    return params;
  }

  // The popup after "add directly": the tickets are already in the basket, so a tick (or a
  // Years edit) has no add-to-cart request to ride on. Ask about the spending limit, then
  // save it right away; on Cancel or a failure the previous selection is put back.
  function persist(productId, selection, previous) {
    var busy = blocksFor(productId);
    busy.forEach(function (b) {
      b.classList.add('is-saving');
    });
    var revert = function () {
      choose(productId, previous);
    };

    var ask = new URLSearchParams({ action: 'nera_add_to_cart_limit_preview', product_id: productId, quantity: '0', nera_addons_submitted: '1' });
    post(withSelection(ask, selection))
      .catch(function () {
        return null; // could not ask: carry on, checkout still enforces the limit
      })
      .then(function (res) {
        if (res && res.needs_confirmation && Object.keys(selection).length) {
          var c = res.confirmation || {};
          return window.NeraSpendLimit && window.NeraSpendLimit.confirm ? window.NeraSpendLimit.confirm(c) : window.confirm(c.message || '');
        }
        return true;
      })
      .then(function (proceed) {
        if (!proceed) {
          revert();
          return null;
        }
        return post(withSelection(new URLSearchParams({ action: 'nera_prize_addons_save', product_id: productId }), selection)).then(function (saved) {
          if (!saved || !saved.ok) {
            revert();
            toast('error', 'error');
            return;
          }
          var savedIds = Array.isArray(saved.selected) ? saved.selected : [];
          var savedYears = saved.years && typeof saved.years === 'object' ? saved.years : {};
          var confirmed = {};
          savedIds.forEach(function (id) {
            confirmed[id] = savedYears[id] || 1;
          });
          syncPrizePage(productId, confirmed);
          if (savedIds.length) {
            // Something was added or changed: the "added to basket" chime goes with a toast.
            toast('success', 'saved');
            document.dispatchEvent(new CustomEvent('nera:cart:updated', { detail: { productId: productId } }));
          } else {
            // Everything was taken out: say so, but not with the "added" chime.
            toast('info', 'removed');
          }
          if (window.jQuery) {
            window.jQuery(document.body).trigger('wc_fragment_refresh');
          }
        });
      })
      .catch(function () {
        revert();
        toast('error', 'error');
      })
      .then(function () {
        busy.forEach(function (b) {
          b.classList.remove('is-saving');
        });
      });
  }

  // A tick or a Years edit made by the customer (not a re-sync between copies).
  function userChoose(block, selection) {
    var pid = block.getAttribute('data-product-id');
    var previous = Object.assign({}, selectionState[pid] || {});
    choose(pid, selection);
    if (block.closest('[data-nera-lucky-dip-state="added"]')) {
      persist(pid, selection, previous);
    }
  }

  // Years input: re-render on every keystroke (visual only, no server round
  // trip), persist once the field is committed (change — blur/enter/stepper).
  document.addEventListener('input', function (e) {
    var input = e.target;
    if (input && input.hasAttribute && input.hasAttribute('data-nera-ld-years')) {
      if (input.reportValidity) {
        input.reportValidity(); // native out-of-range message; :invalid styling is pure CSS
      }
      var block = input.closest(BLOCK);
      if (block) {
        render(block);
      }
    }
  });

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input) {
      return;
    }
    if (input.classList && input.classList.contains('nera-ld-addons__check')) {
      var block = input.closest(BLOCK);
      userChoose(block, readSelection(block));
      return;
    }
    if (input.hasAttribute && input.hasAttribute('data-nera-ld-years')) {
      var yearsBlock = input.closest(BLOCK);
      userChoose(yearsBlock, readSelection(yearsBlock));
    }
  });

  document.addEventListener('click', function (e) {
    var toggle = e.target.closest && e.target.closest('[data-nera-ld-toggle]');
    if (toggle) {
      e.preventDefault();
      var owner = toggle.closest(BLOCK);
      setCollapsed(owner, !owner.classList.contains('is-collapsed'));
      return;
    }

    var btn = e.target.closest && e.target.closest('[data-nera-ld-select-all]');
    if (!btn) {
      return;
    }
    e.preventDefault();
    var block = btn.closest(BLOCK);
    var cfg = config(block);
    var options = cfg.options || [];
    var sel = readSelection(block);
    var all = options.length > 0 && Object.keys(sel).length === options.length;
    var next = {};
    if (!all) {
      options.forEach(function (o) {
        next[o.id] = sel[o.id] || 1;
      });
    }
    userChoose(block, next);
  });

  // Copies that arrive later (the Lucky Dip popups) join in.
  new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      Array.prototype.forEach.call(m.addedNodes, function (n) {
        if (n.nodeType === 1) {
          initAll(n);
        }
      });
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  // Add the chosen options and years to the requests that put Lucky Dip tickets in the basket.
  if ($ && $.ajaxPrefilter) {
    $.ajaxPrefilter(function (options) {
      var data = options.data;
      if (typeof data !== 'string' || !ADD_ACTIONS.test(data)) {
        return;
      }
      var match = /(?:^|&)product_id=(\d+)/.exec(data);
      var pid = match ? match[1] : null;
      if (!pid || selectionState[pid] === undefined) {
        return; // no add-ons block for this prize: leave the request alone
      }
      var extra = '&nera_addons_submitted=1';
      Object.keys(selectionState[pid]).forEach(function (id) {
        extra += '&nera_addon_ids%5B%5D=' + encodeURIComponent(id);
        extra += '&nera_addon_years%5B' + encodeURIComponent(id) + '%5D=' + encodeURIComponent(selectionState[pid][id]);
      });
      options.data = data + extra;
    });
  }

  // A Lucky Dip add that carried add-ons went through: the basket's add-on line now matches
  // what was sent, so the prize page's block follows.
  if ($) {
    $(document).ajaxSuccess(function (event, xhr, settings) {
      var data = settings && settings.data;
      if (typeof data !== 'string' || !ADD_ACTIONS.test(data) || data.indexOf('nera_addons_submitted=1') === -1) {
        return;
      }
      var response = xhr.responseJSON;
      if (!response) {
        try {
          response = JSON.parse(xhr.responseText);
        } catch (e) {
          return;
        }
      }
      var match = /(?:^|&)product_id=(\d+)/.exec(data);
      if (response && response.success && match && selectionState[match[1]] !== undefined) {
        syncPrizePage(match[1], selectionState[match[1]]);
      }
    });
  }

  function ready() {
    initAll(document);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }

  window.NeraLuckyDipAddons = {
    // id => years (docs/adr/0015). lucky-dip-limit.js reads this to build
    // its own spending-limit preview request.
    selected: function (productId) {
      return Object.assign({}, selectionState[productId] || {});
    },
  };
})(window.jQuery);
