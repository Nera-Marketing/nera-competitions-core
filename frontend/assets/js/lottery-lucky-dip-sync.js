/**
 * Sync ticket grid after Lucky Dip adds tickets to cart.
 *
 * Plugin lucky_dip_action opens the success popup but does not reload the
 * ticket tab HTML, so in-cart tickets remain selectable until refreshed.
 *
 * @package Nera_Competitions
 */
(function ($) {
  'use strict';

  /**
   * Parse ticket numbers from the success popup chips.
   *
   * @param {jQuery} $modal
   * @return {string[]}
   */
  function getAddedTickets($modal) {
    var tickets = [];

    $modal.find('.nera-lucky-dip-popup__ticket-chip').each(function () {
      var value = $(this).text().trim();
      if (value) {
        tickets.push(value);
      }
    });

    return tickets;
  }

  /**
   * Remove newly carted tickets from the manual selection hidden field.
   *
   * @param {string[]} addedTickets
   */
  function pruneHiddenSelection(addedTickets) {
    if (!addedTickets.length) {
      return;
    }

    var $field = $('.nera-ticket-dialog .lty-lottery-ticket-numbers');
    if (!$field.length) {
      return;
    }

    var raw = $field.val();
    if (!raw) {
      return;
    }

    var selected = raw.split(',').map(function (ticket) {
      return ticket.trim();
    }).filter(Boolean);

    var addedSet = {};
    addedTickets.forEach(function (ticket) {
      addedSet[ticket] = true;
    });

    var pruned = selected.filter(function (ticket) {
      return !addedSet[ticket];
    });

    $field.val(pruned.join(','));
    $('.nera-ticket-dialog .lty-lottery-ticket-quantity').val(pruned.length);
  }

  /**
   * Update selection count badges in the ticket dialog trigger/header/footer.
   */
  function updateSelectionBadges() {
    var $field = $('.nera-ticket-dialog .lty-lottery-ticket-numbers');
    var raw = $field.length ? $field.val() : '';
    var count = 0;

    if (raw) {
      count = raw.split(',').map(function (ticket) {
        return ticket.trim();
      }).filter(Boolean).length;
    }

    document.querySelectorAll('[data-selected-ticket-count]').forEach(function (el) {
      el.textContent = count;
    });

    document.querySelectorAll('[data-count-badge]').forEach(function (el) {
      el.dataset.hasSelection = count > 0 ? 'true' : 'false';
    });
  }

  /**
   * Reload the active ticket tab via the plugin's existing AJAX handler.
   */
  function refreshActiveTicketTab() {
    var $activeTab = $('.nera-ticket-dialog .lty-lottery-ticket-tab.lty-active-tab');
    if ($activeTab.length) {
      $activeTab.trigger('click');
      return;
    }

    // Single-tab products: click the only tab if present.
    var $tab = $('.nera-ticket-dialog .lty-lottery-ticket-tab').first();
    if ($tab.length) {
      $tab.trigger('click');
    }
  }

  /**
   * Apply WooCommerce cart fragments to the DOM.
   *
   * @param {Object} fragments
   */
  function applyCartFragments(fragments) {
    if (!fragments) {
      return;
    }

    Object.keys(fragments).forEach(function (selector) {
      $(selector).replaceWith(fragments[selector]);
    });
  }

  /**
   * Refresh header cart badges (desktop + mobile) after Lucky Dip add-to-cart.
   */
  function refreshCartFragments() {
    var wcFragmentsUrl =
      typeof wc_cart_fragments_params !== 'undefined' &&
      wc_cart_fragments_params.wc_ajax_url
        ? wc_cart_fragments_params.wc_ajax_url
            .toString()
            .replace('%%endpoint%%', 'get_refreshed_fragments')
        : null;

    if (wcFragmentsUrl) {
      $.post(wcFragmentsUrl, function (data) {
        if (data && data.fragments) {
          applyCartFragments(data.fragments);
          $(document.body).trigger('wc_fragments_refreshed', [data.fragments, data.cart_hash]);
        }
      });
      return;
    }

    // Fallback when wc-cart-fragments params are unavailable.
    $(document.body).trigger('wc_fragment_refresh');
  }

  /**
   * Sync grid state after Lucky Dip success popup opens.
   *
   * @param {jQuery} $modal
   */
  function syncAfterLuckyDip($modal) {
    var addedTickets = getAddedTickets($modal);
    pruneHiddenSelection(addedTickets);
    updateSelectionBadges();
    refreshActiveTicketTab();
    refreshCartFragments();

    document.dispatchEvent(
      new CustomEvent('nera:cart:updated', {
        detail: { source: 'lucky-dip', tickets: addedTickets },
      })
    );
  }

  /**
   * Bind to jquery-modal open event for Nera Lucky Dip success popup.
   */
  function init() {
    if (!$.modal || !$.modal.OPEN) {
      return;
    }

    $(document).on($.modal.OPEN, function (event, modal) {
      if (!modal || !modal.$elm || !modal.$elm.hasClass('nera-lucky-dip-popup')) {
        return;
      }

      // Allow popup markup to finish rendering before reading chips.
      window.setTimeout(function () {
        syncAfterLuckyDip(modal.$elm);
      }, 0);
    });
  }

  $(init);
})(jQuery);
