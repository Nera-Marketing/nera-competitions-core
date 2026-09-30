/**
 * Prize Safety & Add-ons — admin behaviour.
 *
 * Two small jobs, each only when its markup is on the screen:
 *
 *  1. Prize edit page: the "Full bundle price" is locked (and emptied) until at least one
 *     add-on option is picked, and capped at the total of the options picked. The server
 *     checks it again on save (nera_prize_addons_acf_validate_bundle()).
 *  2. Add-ons Bundles settings: the message that refuses to delete a Catalog entry names
 *     each prize that uses it with the address of its edit page. ACF escapes the text of
 *     an error, so the addresses arrive as plain text and are turned into links here.
 *
 * Data comes from inc/prize-addons.php (neraPsaAdmin). Enqueued on product edit screens
 * and Theme Settings → WooCommerce.
 */
(function ($) {
  'use strict';

  if (typeof window.acf === 'undefined' || !$) {
    return;
  }

  var cfg = window.neraPsaAdmin || {};
  var prices = cfg.prices || {};
  var currency = cfg.currency || {};
  var i18n = cfg.i18n || {};

  /* ---- 1. Full bundle price ------------------------------------------------ */

  function money(amount) {
    return (currency.symbol || '') + Number(amount).toFixed(typeof currency.decimals === 'number' ? currency.decimals : 2);
  }

  function pickedTotal(ids) {
    return ids.reduce(function (sum, id) {
      return sum + (Number(prices[id]) || 0);
    }, 0);
  }

  function syncBundlePrice() {
    var $picker = $('[data-key="' + cfg.pickerKey + '"] select');
    var $field = $('[data-key="' + cfg.bundleKey + '"]');
    if (!$picker.length || !$field.length) {
      return;
    }

    var $input = $field.find('input[type="number"]').first();
    var ids = $picker.val() || [];
    var $hint = $field.find('.nera-psa-bundle-hint');
    if (!$hint.length) {
      $hint = $('<p class="description nera-psa-bundle-hint"></p>').appendTo($field.find('.acf-input').first());
    }

    if (!ids.length) {
      // Nothing to bundle: lock the field and drop any old price, so it cannot come back stale.
      $input.val('').prop('readonly', true).attr('aria-disabled', 'true').removeAttr('max');
      $field.addClass('nera-psa-locked');
      $hint.text(i18n.locked || '');
      return;
    }

    var total = pickedTotal(ids);
    $input.prop('readonly', false).removeAttr('aria-disabled').attr('max', total.toFixed(typeof currency.decimals === 'number' ? currency.decimals : 2));
    $field.removeClass('nera-psa-locked');
    $hint.text((i18n.max || '').replace('%s', money(total)));
  }

  $(document).on('change', '[data-key="' + cfg.pickerKey + '"] select', syncBundlePrice);
  acf.addAction('ready', syncBundlePrice);
  acf.addAction('append', syncBundlePrice);

  /* ---- 2. Links in the "cannot delete" message ------------------------------ */

  // "Title (https://…/post.php?post=1&action=edit)" becomes "Title (edit)" with a link.
  var ADDRESS = /\((https?:\/\/[^\s)]+)\)/g;

  function linkify($field) {
    $field.find('.acf-notice.-error p, .acf-error-message p').each(function () {
      var $p = $(this);
      var text = $p.text();
      if (!ADDRESS.test(text)) {
        ADDRESS.lastIndex = 0;
        return;
      }
      ADDRESS.lastIndex = 0;

      $p.empty().css('white-space', 'pre-line');
      var last = 0;
      var match;
      while ((match = ADDRESS.exec(text)) !== null) {
        $p.append(document.createTextNode(text.slice(last, match.index) + '('));
        $('<a></a>').attr({ href: match[1], target: '_blank', rel: 'noopener' }).text('edit').appendTo($p);
        $p.append(document.createTextNode(')'));
        last = match.index + match[0].length;
      }
      $p.append(document.createTextNode(text.slice(last)));
      ADDRESS.lastIndex = 0;
    });
  }

  acf.addAction('invalid_field', function (field) {
    if (field && field.$el) {
      linkify(field.$el);
    }
  });
})(window.jQuery);
