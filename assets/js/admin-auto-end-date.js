/**
 * Product edit screen, Giveaway tab, while Auto end date is on:
 * picking or typing the Start Date fills the End Date with the same time of day,
 * `neraAutoEnd.days` calendar days later.
 *
 * An End Date the admin edits by hand is never overwritten (clearing it hands control back).
 * An End Date already on the page that is not "start + N days" counts as hand-set from the start,
 * so existing drops are not changed by opening them.
 *
 * Both fields are LFW datetime pickers with the value format "YYYY-MM-DD HH:mm" in site time,
 * so plain calendar arithmetic is enough here (LFW does the timezone conversion on save).
 * See inc/auto-end-date.php.
 */
(function ($) {
  'use strict';

  var cfg = window.neraAutoEnd || {};
  var days = parseInt(cfg.days, 10) || 14;

  function pad(n) {
    return (n < 10 ? '0' : '') + n;
  }

  /** "YYYY-MM-DD HH:mm" + days -> same format, or '' when the input cannot be read. */
  function addDays(value) {
    var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec($.trim(value || ''));
    if (!m) {
      return '';
    }
    // UTC fields only carry the wall-clock numbers; no timezone or clock-change maths is involved.
    var d = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3] + days, +m[4], +m[5]));
    return (
      d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()) +
      ' ' + pad(d.getUTCHours()) + ':' + pad(d.getUTCMinutes())
    );
  }

  $(function () {
    var $start = $('#_lty_start_date');
    var $end = $('#_lty_end_date');

    if (!$start.length || !$end.length) {
      return;
    }

    var $row = $end.closest('p.form-field');
    // Own paragraph under the End Date row so it never wraps beside the picker button.
    var $note = $('<p class="nera-auto-end-note"></p>').text(cfg.note || '').hide().insertAfter($row);
    var manual = false;

    // LFW posts the hidden field that follows the visible picker input, not the input itself;
    // the picker keeps both in step, so write both ("YYYY-MM-DD HH:mm", same as its altFormat).
    function setEnd(value) {
      $end.val(value);
      $end.nextAll('.lty_alter_datepicker_value').first().val(value);
    }

    function applies() {
      return 'lottery' === $('#product-type').val() &&
        '2' !== $('#lty_lottery_schedule_type').val() &&
        !$('input[name="lty_lottery_extend"]').length &&
        !$('input[name="lty_lottery_manual_relist"]').length;
    }

    function showNote(on) {
      $note.toggle(on && applies());
    }

    // An End Date that is already set and does not follow the rule belongs to the admin.
    var existing = $.trim($end.val());
    if (existing) {
      manual = existing !== addDays($start.val());
    }
    showNote(!manual);

    $start.on('change', function () {
      if (manual || !applies()) {
        return;
      }
      var end = addDays($start.val());
      if (end) {
        setEnd(end);
        showNote(true);
      }
    });

    $end.on('change', function () {
      if ('' === $.trim($end.val())) {
        manual = false;
        showNote(true);
        return;
      }
      // A change event we did not cause (we set the value without firing one) is a hand edit.
      manual = $.trim($end.val()) !== addDays($start.val());
      showNote(!manual);
    });

    $('#product-type, #lty_lottery_schedule_type').on('change', function () {
      showNote(!manual);
    });
  });
})(jQuery);
