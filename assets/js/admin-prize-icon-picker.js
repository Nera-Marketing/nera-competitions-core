/**
 * Prize Safety — icon preview in the admin icon dropdown.
 *
 * The Safety "Icon" field is an ACF select (select2 UI) whose values are
 * Material Symbols names. This shows each icon next to its label, in the list
 * and in the chosen value, so admins pick by sight instead of by name.
 * Enqueued on Theme Settings → WooCommerce (and product edit screens) by inc/prize-addons.php.
 */
(function ($) {
  'use strict';

  if (typeof window.acf === 'undefined' || !$) {
    return;
  }

  // The Icon field of the Safety catalog (Theme Settings > WooCommerce > Add-ons Bundles).
  const FIELD_KEY = /^field_nera_psag_safety_item_icon$/;
  const CUSTOM = '__custom';

  function render(state) {
    if (!state || !state.id || state.id === CUSTOM || state.children) {
      return state ? state.text : '';
    }
    const $choice = $('<span class="nera-icon-choice"></span>');
    $('<span class="material-symbols-outlined nera-icon-choice__glyph" aria-hidden="true"></span>')
      .text(state.id)
      .appendTo($choice);
    $('<span class="nera-icon-choice__label"></span>').text(state.text).appendTo($choice);
    return $choice;
  }

  function withIconTemplates(args, $select) {
    // Identify the field from the DOM: the legacy acf.add_filter API passes a
    // jQuery element where acf.addFilter passes a field model.
    if ($select && FIELD_KEY.test($select.closest('.acf-field').attr('data-key') || '')) {
      args.templateResult = render;
      args.templateSelection = render;
      args.dropdownCssClass = ((args.dropdownCssClass || '') + ' nera-icon-choice-dropdown').trim();
    }
    return args;
  }

  if (typeof window.acf.addFilter === 'function') {
    window.acf.addFilter('select2_args', withIconTemplates);
  } else {
    window.acf.add_filter('select2_args', withIconTemplates);
  }

  window.NeraPrizeIconPicker = { render: render };
})(window.jQuery);
