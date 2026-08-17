/**
 * Fix Lottery LFW date/datetime pickers on product edit.
 * Appends the jQuery UI picker to body and repositions under the input
 * so it is not offset/clipped inside WooCommerce Product data.
 */
(function ($) {
  'use strict';

  var Z = 100000;
  var SELECTOR = '.lty_datetimepicker, .lty_datepicker';

  function placePicker(input, inst) {
    if (!inst || !inst.dpDiv || !input) {
      return;
    }
    var $input = $(input);
    var $dp = inst.dpDiv;
    var offset = $input.offset();
    if (!offset) {
      return;
    }

    $dp.appendTo('body');
    $dp.css({
      position: 'absolute',
      top: offset.top + $input.outerHeight(),
      left: offset.left,
      zIndex: Z,
    });

    // If the panel would run off the bottom of the viewport, open above the field.
    var winTop = $(window).scrollTop();
    var winH = $(window).height();
    var dpH = $dp.outerHeight() || 0;
    if (offset.top + $input.outerHeight() + dpH > winTop + winH && offset.top - dpH > winTop) {
      $dp.css('top', offset.top - dpH);
    }
  }

  function bindPicker($el) {
    if (!$el.length || !$el.hasClass('hasDatepicker')) {
      return;
    }

    $el.datepicker('option', 'beforeShow', function (input, inst) {
      setTimeout(function () {
        placePicker(input, inst);
      }, 0);
      return {};
    });

    $el.datepicker('option', 'onChangeMonthYear', function () {
      var input = this;
      var inst = $.datepicker._getInst(input);
      setTimeout(function () {
        placePicker(input, inst);
      }, 0);
    });
  }

  function fixAll() {
    $(SELECTOR).each(function () {
      bindPicker($(this));
    });
  }

  $(function () {
    // LTY enhanced.js inits pickers on ready; run after it.
    setTimeout(fixAll, 0);
    setTimeout(fixAll, 300);

    $(document).on('focus click', SELECTOR, function () {
      bindPicker($(this));
    });

    // Calendar icon button (showOn: "button") sits next to the input.
    $(document).on('click', '.ui-datepicker-trigger', function () {
      var $input = $(this).prevAll(SELECTOR).first();
      if (!$input.length) {
        $input = $(this).siblings(SELECTOR).first();
      }
      if ($input.length) {
        bindPicker($input);
      }
    });
  });
})(jQuery);
