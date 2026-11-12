/**
 * Product edit screen, Giveaway tab, while the percentage cap is on:
 *  - moves the percentage field directly under "Maximum Tickets per User";
 *  - locks that fixed field (read-only, so it still posts and its saved number
 *    survives) and drops its "required" error so an empty value cannot block saving.
 *
 * See inc/percentage-entry-cap.php.
 */
(function ($) {
  'use strict';

  $(function () {
    var $fixed = $('#_lty_user_maximum_tickets');
    var $fixedRow = $fixed.closest('.form-field');
    var $pctRow = $('.nera-pct-cap-field');

    if (!$fixed.length || !$pctRow.length) {
      return;
    }

    $fixedRow.after($pctRow);

    $fixed
      .prop('readonly', true)
      .addClass('nera-pct-cap-locked')
      .removeAttr('data-error')
      .removeData('error');

    var note = (window.neraPctCap && window.neraPctCap.note) || '';
    if (note) {
      $('<span class="description nera-pct-cap-note"></span>').text(note).appendTo($fixedRow);
    }
  });
})(jQuery);
