/**
 * Prize Add-ons in the Lucky Dip dialogs (template-parts/single-product/lucky-dip-addons.php).
 *
 * Plain JS on purpose: the Lucky Dip popups are injected by Lottery for WooCommerce after
 * Alpine has started, and Alpine does not pick up markup added later.
 *
 *  - One selection per prize, shared by every copy of the block on the page: the one in
 *    the Lucky Dip box, and the ones in the popups. A copy that appears later (a popup)
 *    starts from what was already chosen.
 *  - The total is for display only; the cart prices the add-on line on the server.
 *  - The chosen options ride along on the Lucky Dip requests that put tickets in the
 *    basket, so the add-on line is created or updated in the same step. The server side
 *    is nera_prize_addons_sync_from_lucky_dip() in inc/prize-addons.php.
 *  - In a popup that is shown after the tickets were added, a tick is saved to the basket
 *    at once (nera_prize_addons_save), asking about the spending limit first.
 */
(function ($) {
  'use strict';

  var BLOCK = '[data-nera-ld-addons]';
  var ADD_ACTIONS = /(?:^|&)action=lty_(?:process_lucky_dip|regenerate_lucky_dip_add_to_cart)(?:&|$)/;

  // Prize ID -> chosen option IDs. Absent until a block for that prize has been seen.
  var selection = {};

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

  function checkedIds(block) {
    return Array.prototype.map.call(block.querySelectorAll('.nera-ld-addons__check:checked'), function (i) {
      return i.value;
    });
  }

  function apply(block, ids) {
    Array.prototype.forEach.call(block.querySelectorAll('.nera-ld-addons__check'), function (input) {
      input.checked = ids.indexOf(input.value) !== -1;
    });
  }

  function render(block) {
    var cfg = config(block);
    var options = cfg.options || [];
    var ids = checkedIds(block);
    var subtotal = options.reduce(function (sum, o) {
      return ids.indexOf(o.id) !== -1 ? sum + Number(o.price || 0) : sum;
    }, 0);
    var all = options.length > 0 && ids.length === options.length;
    var full = cfg.bundlePrice !== null && cfg.bundlePrice !== undefined && all;
    var total = full ? Number(cfg.bundlePrice) : subtotal;

    var totalEl = block.querySelector('[data-nera-ld-total]');
    if (totalEl) {
      totalEl.textContent = '';
      if (full) {
        var strike = document.createElement('s');
        strike.textContent = formatMoney(subtotal, cfg.currency) + ' ';
        totalEl.appendChild(strike);
      }
      totalEl.appendChild(document.createTextNode(formatMoney(total, cfg.currency)));
    }

    var btn = block.querySelector('[data-nera-ld-select-all]');
    if (btn) {
      btn.classList.toggle('is-active', all);
      btn.setAttribute('aria-pressed', all ? 'true' : 'false');
    }

    // The line shown in place of the list while it is collapsed.
    var summary = block.querySelector('[data-nera-ld-summary]');
    if (summary) {
      var i18n = cfg.i18n || {};
      summary.textContent = ids.length
        ? String(i18n.summarySelected || '%1$d of %2$d selected').replace('%1$d', ids.length).replace('%2$d', options.length)
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

    if (selection[pid] === undefined) {
      selection[pid] = checkedIds(block); // first copy seen: what the basket already holds
    } else {
      apply(block, selection[pid]);
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

  function choose(productId, ids) {
    selection[productId] = ids;
    blocksFor(productId).forEach(function (b) {
      apply(b, ids);
      render(b);
    });
  }

  // The prize page's own Add-ons block (Components/blocks/PrizeAddOns, Alpine) keeps its own
  // `selected` list, read when Enter Now is pressed. Once the basket's add-on line has been
  // changed from a Lucky Dip dialog, bring that block in line so the two never disagree.
  function syncPrizePage(productId, ids) {
    var root = document.querySelector('[data-prize-addons="' + productId + '"]');
    if (!root || !window.Alpine || typeof window.Alpine.$data !== 'function') {
      return;
    }
    try {
      var data = window.Alpine.$data(root);
      if (data && Array.isArray(data.selected)) {
        data.selected = ids.slice();
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

  function withIds(params, ids) {
    ids.forEach(function (id) {
      params.append('nera_addon_ids[]', id);
    });
    return params;
  }

  // The popup after "add directly": the tickets are already in the basket, so a tick has no
  // add-to-cart request to ride on. Ask about the spending limit, then save it right away;
  // on Cancel or a failure the tick is put back.
  function persist(productId, ids, previous) {
    var busy = blocksFor(productId);
    busy.forEach(function (b) {
      b.classList.add('is-saving');
    });
    var revert = function () {
      choose(productId, previous);
    };

    var ask = new URLSearchParams({ action: 'nera_add_to_cart_limit_preview', product_id: productId, quantity: '0', nera_addons_submitted: '1' });
    post(withIds(ask, ids))
      .catch(function () {
        return null; // could not ask: carry on, checkout still enforces the limit
      })
      .then(function (res) {
        if (res && res.needs_confirmation && ids.length) {
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
        return post(withIds(new URLSearchParams({ action: 'nera_prize_addons_save', product_id: productId }), ids)).then(function (saved) {
          if (!saved || !saved.ok) {
            revert();
            toast('error', 'error');
            return;
          }
          syncPrizePage(productId, Array.isArray(saved.selected) ? saved.selected : ids);
          if (ids.length) {
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

  // A tick made by the customer (not a re-sync between copies).
  function userChoose(block, ids) {
    var pid = block.getAttribute('data-product-id');
    var previous = (selection[pid] || []).slice();
    choose(pid, ids);
    if (block.closest('[data-nera-lucky-dip-state="added"]')) {
      persist(pid, ids, previous);
    }
  }

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input || !input.classList || !input.classList.contains('nera-ld-addons__check')) {
      return;
    }
    var block = input.closest(BLOCK);
    userChoose(block, checkedIds(block));
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
    var ids = checkedIds(block);
    var all = options.length > 0 && ids.length === options.length;
    userChoose(
      block,
      all
        ? []
        : options.map(function (o) {
            return o.id;
          })
    );
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

  // Add the chosen options to the requests that put Lucky Dip tickets in the basket.
  if ($ && $.ajaxPrefilter) {
    $.ajaxPrefilter(function (options) {
      var data = options.data;
      if (typeof data !== 'string' || !ADD_ACTIONS.test(data)) {
        return;
      }
      var match = /(?:^|&)product_id=(\d+)/.exec(data);
      var pid = match ? match[1] : null;
      if (!pid || selection[pid] === undefined) {
        return; // no add-ons block for this prize: leave the request alone
      }
      options.data =
        data +
        '&nera_addons_submitted=1' +
        selection[pid]
          .map(function (id) {
            return '&nera_addon_ids%5B%5D=' + encodeURIComponent(id);
          })
          .join('');
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
      if (response && response.success && match && selection[match[1]] !== undefined) {
        syncPrizePage(match[1], selection[match[1]]);
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
    selected: function (productId) {
      return (selection[productId] || []).slice();
    },
  };
})(window.jQuery);
