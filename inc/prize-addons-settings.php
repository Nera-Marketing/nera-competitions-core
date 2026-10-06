<?php
/**
 * Prize Safety & Add-ons — site switch and Catalog.
 *
 * Theme Settings → WooCommerce → "Add-ons Bundles" holds the site-wide switch and the
 * Catalog: the one place Safety items and Add-on options are created and edited. A
 * prize only picks entries from it (see inc/acf/single-product/acf-prize-addons.php);
 * it cannot change them, and an entry a prize uses cannot be deleted
 * (docs/adr/0014).
 *
 * Loaded before the ACF field groups. It has no hooks into the storefront: the
 * functions that turn a prize's picks into what customers see are in
 * inc/helpers/prize-addons.php and read the Catalog through nera_prize_addons_catalog().
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

require_once get_template_directory() . '/inc/helpers/prize-safety-icons.php';

/** ACF option holding the site-wide switch (stored as options_nera_prize_addons_enabled). */
const NERA_PRIZE_ADDON_SITE_OPTION = 'nera_prize_addons_enabled';

/** ACF field key of the site-wide switch. */
const NERA_PRIZE_ADDON_ACF_SITE_SWITCH = 'field_nera_psag_enabled';

/** ACF option holding the site-wide Maximum term (stored as options_nera_prize_addons_max_term). */
const NERA_PRIZE_ADDON_SITE_MAX_TERM_OPTION = 'nera_prize_addons_max_term';

/** ACF field key of the Maximum term field. */
const NERA_PRIZE_ADDON_ACF_MAX_TERM = 'field_nera_psag_max_term';

/** Field key prefixes: the prize's box and the settings section (Catalog). */
const NERA_PRIZE_ADDON_KEY_PRODUCT = 'field_nera_psa_';
const NERA_PRIZE_ADDON_KEY_GLOBAL = 'field_nera_psag_';

/** Name prefix of the Catalog fields in the options table (kept from the "Global default" days). */
const NERA_PRIZE_ADDON_GLOBAL_PREFIX = 'psa_default_';

/** Catalog repeaters (field keys) and the hidden sub-field that holds each entry's stable ID. */
const NERA_PRIZE_ADDON_CATALOG_SAFETY = 'field_nera_psag_safety_items';
const NERA_PRIZE_ADDON_CATALOG_SAFETY_ID = 'field_nera_psag_safety_item_id';
const NERA_PRIZE_ADDON_CATALOG_ADDONS = 'field_nera_psag_addons_items';
const NERA_PRIZE_ADDON_CATALOG_ADDONS_ID = 'field_nera_psag_addon_option_id';

/** The prize's pickers (field keys) and the meta names they save to. */
const NERA_PRIZE_ADDON_PICK_SAFETY = 'field_nera_psa_safety_item_ids';
const NERA_PRIZE_ADDON_PICK_ADDONS = 'field_nera_psa_addon_option_ids';

/**
 * Whether the Add-ons Bundles switch is on. Off unless an admin turned it on.
 *
 * Read straight from the option so it works before ACF has initialised.
 *
 * @return bool
 */
function nera_prize_addons_site_enabled(): bool
{
  return '1' === (string) get_option('options_' . NERA_PRIZE_ADDON_SITE_OPTION, '0');
}

/**
 * Site-wide Maximum term (years) a customer may choose for an add-on option on
 * a prize that has Term choice switched on (docs/adr/0015).
 *
 * Read straight from the option, like the site switch, so it is available
 * wherever quote() runs. The field's own placeholder shows 10; empty or below
 * 1 reads as 10 here too, so an admin who never touches the field gets the
 * same number the settings screen already showed them.
 *
 * @return int
 */
function nera_prize_addons_max_term(): int
{
  $value = (int) get_option('options_' . NERA_PRIZE_ADDON_SITE_MAX_TERM_OPTION, '');
  return $value >= 1 ? $value : 10;
}

/**
 * Currency symbol for admin price fields, from WooCommerce.
 *
 * @return string Plain text, or '' when WooCommerce is not loaded.
 */
function nera_prize_addons_currency_symbol(): string
{
  return function_exists('get_woocommerce_currency_symbol')
    ? html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8')
    : '';
}

/* -------------------------------------------------------------------------
 * The Catalog
 * ---------------------------------------------------------------------- */

/**
 * The Catalog: every Safety item or Add-on option an admin has defined.
 *
 * Keyed by the entry's stable ID, in the order set in the settings. Entries without
 * an ID or title (and add-on options without a price above 0) are left out.
 *
 * @param string $kind  'safety' or 'addons'.
 * @param bool   $flush Forget what was read earlier in this request (after a save).
 * @return array<string,array<string,mixed>>
 */
function nera_prize_addons_catalog(string $kind, bool $flush = false): array
{
  static $cache = [];
  if ($flush) {
    $cache = [];
  }
  if (isset($cache[$kind])) {
    return $cache[$kind];
  }
  if (!function_exists('get_field')) {
    return [];
  }

  $is_safety = 'safety' === $kind;
  $rows = (array) get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . ($is_safety ? 'safety_items' : 'addons_items'), 'option');

  $entries = [];
  foreach ($rows as $row) {
    if (!is_array($row)) {
      continue;
    }
    $id = sanitize_key((string) ($row[$is_safety ? 'item_id' : 'option_id'] ?? ''));
    $title = trim((string) ($row['title'] ?? ''));
    if ('' === $id || '' === $title || isset($entries[$id])) {
      continue;
    }

    if ($is_safety) {
      $icon = function_exists('nera_prize_safety_resolve_icon')
        ? nera_prize_safety_resolve_icon((string) ($row['icon'] ?? ''), (string) ($row['icon_custom'] ?? ''))
        : preg_replace('/[^a-z0-9_]/', '', strtolower(trim((string) ($row['icon'] ?? ''))));
      $entries[$id] = [
        'id' => $id,
        'icon' => $icon ?: 'health_and_safety',
        'title' => $title,
        'description' => trim((string) ($row['description'] ?? '')),
      ];
      continue;
    }

    $price = round((float) ($row['price'] ?? 0), 2);
    if ($price <= 0) {
      continue;
    }
    $entries[$id] = [
      'id' => $id,
      'title' => $title,
      'description' => trim((string) ($row['description'] ?? '')),
      'price' => $price,
    ];
  }

  return $cache[$kind] = $entries;
}

/**
 * The Catalog as picker choices for a prize: ID => label.
 *
 * @param string $kind 'safety' or 'addons'.
 * @return array<string,string>
 */
function nera_prize_addons_catalog_choices(string $kind): array
{
  $choices = [];
  foreach (nera_prize_addons_catalog($kind) as $id => $entry) {
    $choices[$id] = 'addons' === $kind
      ? sprintf(
        '%s — %s',
        $entry['title'],
        function_exists('nera_prize_addons_money_text')
          ? nera_prize_addons_money_text($entry['price'])
          : number_format($entry['price'], 2)
      )
      : $entry['title'];
  }

  return $choices;
}

/**
 * Give a prize's picker its choices, and say where to add entries when there are none.
 *
 * @param array|false $field ACF field about to load.
 * @param string      $kind  'safety' or 'addons'.
 * @return array|false
 */
function nera_prize_addons_load_picker($field, string $kind)
{
  if (!is_array($field)) {
    return $field;
  }

  $field['choices'] = nera_prize_addons_catalog_choices($kind);
  if (empty($field['choices'])) {
    $field['instructions'] = sprintf(
      /* translators: %s: link to the settings page */
      __('The catalog is empty. Add entries in %s first.', 'nera-competitions'),
      '<a href="' . esc_url(admin_url('admin.php?page=acf-options-woocommerce')) . '">' . esc_html__('Theme Settings → WooCommerce → Add-ons Bundles', 'nera-competitions') . '</a>'
    );
  }

  return $field;
}
add_filter('acf/load_field/key=' . NERA_PRIZE_ADDON_PICK_SAFETY, static fn($field) => nera_prize_addons_load_picker($field, 'safety'));
add_filter('acf/load_field/key=' . NERA_PRIZE_ADDON_PICK_ADDONS, static fn($field) => nera_prize_addons_load_picker($field, 'addons'));

/* -------------------------------------------------------------------------
 * Settings section: Theme Settings → WooCommerce → Add-ons Bundles
 * ---------------------------------------------------------------------- */

/**
 * The "Add-ons Bundles" accordion for Theme Settings → WooCommerce.
 *
 * Sits above "Spin To Win": the site switch, then (only while it is on) the default
 * texts and the Catalog of Safety items and of Add-on options.
 *
 * @return array[] ACF fields, accordion start to accordion end.
 */
function nera_prize_addons_settings_fields(): array
{
  $on = [[['field' => NERA_PRIZE_ADDON_ACF_SITE_SWITCH, 'operator' => '==', 'value' => '1']]];
  $currency = nera_prize_addons_currency_symbol();
  $prefix = NERA_PRIZE_ADDON_KEY_GLOBAL;
  $names = NERA_PRIZE_ADDON_GLOBAL_PREFIX;

  return [
    [
      'key' => 'field_nera_psag_accordion',
      'label' => 'Add-ons Bundles',
      'name' => '',
      'type' => 'accordion',
      'placement' => 'top',
      'open' => 0,
      'multi_expand' => 0,
      'endpoint' => 0,
    ],
    [
      'key' => NERA_PRIZE_ADDON_ACF_SITE_SWITCH,
      'label' => 'Enable Add-ons Bundles',
      'name' => NERA_PRIZE_ADDON_SITE_OPTION,
      'type' => 'true_false',
      'instructions' => 'Turns the free Safety list and paid Add-ons on prize pages on or off for the whole site. While off, customers see and can buy nothing from it and add-on lines are removed from baskets; saved prize settings and placed orders are kept.',
      'default_value' => 0,
      'ui' => 1,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
      'wrapper' => ['width' => '', 'class' => 'nera-acf-field--toggle', 'id' => ''],
    ],
    [
      'key' => NERA_PRIZE_ADDON_ACF_MAX_TERM,
      'label' => 'Maximum term (years)',
      'name' => NERA_PRIZE_ADDON_SITE_MAX_TERM_OPTION,
      'type' => 'number',
      'instructions' => 'The longest Term, in years, a customer may choose for an add-on option on a prize that has Year choice switched on. Leave empty for 10.',
      'min' => 1,
      'step' => 1,
      'placeholder' => 10,
      'conditional_logic' => $on,
      'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
    ],

    // ---- Safety ---------------------------------------------------------
    [
      'key' => 'field_nera_psag_safety_heading',
      'label' => 'Safety catalog',
      'name' => '',
      'type' => 'message',
      'message' => 'Every Safety item is defined here. A prize picks from this list on its edit page and cannot change an item. Editing an item changes it on every prize that uses it, and an item a prize uses cannot be deleted.',
      'new_lines' => 'wpautop',
      'esc_html' => 0,
      'conditional_logic' => $on,
    ],
    [
      'key' => $prefix . 'safety_title',
      'label' => 'Default title',
      'name' => $names . 'safety_title',
      'type' => 'text',
      'placeholder' => 'Included free with this prize',
      'instructions' => 'Shown on a prize that has no title of its own. Leave empty to use "Included free with this prize".',
      'conditional_logic' => $on,
    ],
    [
      'key' => $prefix . 'safety_description',
      'label' => 'Default description',
      'name' => $names . 'safety_description',
      'type' => 'textarea',
      'rows' => 2,
      'new_lines' => '',
      'placeholder' => 'e.g. Every prize includes the basic safety equipment to keep you safe on the water.',
      'conditional_logic' => $on,
    ],
    [
      'key' => NERA_PRIZE_ADDON_CATALOG_SAFETY,
      'label' => 'Items',
      'name' => $names . 'safety_items',
      'type' => 'repeater',
      'layout' => 'block',
      'button_label' => 'Add item',
      'min' => 0,
      'conditional_logic' => $on,
      'sub_fields' => [
        [
          // Stable ID so a prize keeps pointing at the same item however it is renamed or reordered.
          // Generated on save, hidden in admin.
          'key' => NERA_PRIZE_ADDON_CATALOG_SAFETY_ID,
          'label' => 'ID',
          'name' => 'item_id',
          'type' => 'text',
          'readonly' => 1,
        ],
        [
          // Searchable dropdown with an icon preview (assets/js/admin-prize-icon-picker.js).
          'key' => $prefix . 'safety_item_icon',
          'label' => 'Icon',
          'name' => 'icon',
          'type' => 'select',
          'choices' => function_exists('nera_prize_safety_icon_acf_choices') ? nera_prize_safety_icon_acf_choices() : [],
          'default_value' => 'health_and_safety',
          'ui' => 1,
          'ajax' => 0,
          'allow_null' => 0,
          'return_format' => 'value',
          'wrapper' => ['width' => '30'],
        ],
        [
          'key' => $prefix . 'safety_item_title',
          'label' => 'Title',
          'name' => 'title',
          'type' => 'text',
          'required' => 1,
          'placeholder' => 'e.g. Life jackets',
          'wrapper' => ['width' => '30'],
        ],
        [
          'key' => $prefix . 'safety_item_description',
          'label' => 'Description',
          'name' => 'description',
          'type' => 'text',
          'placeholder' => 'e.g. Auto-inflate',
          'wrapper' => ['width' => '40'],
        ],
        [
          'key' => $prefix . 'safety_item_icon_custom',
          'label' => 'Icon name',
          'name' => 'icon_custom',
          'type' => 'text',
          'instructions' => 'Any icon name from fonts.google.com/icons, e.g. "kayaking".',
          'placeholder' => 'e.g. kayaking',
          'wrapper' => ['width' => '30'],
          'conditional_logic' => [[['field' => $prefix . 'safety_item_icon', 'operator' => '==', 'value' => '__custom']]],
        ],
      ],
    ],

    // ---- Add-ons --------------------------------------------------------
    [
      'key' => 'field_nera_psag_addons_heading',
      'label' => 'Add-ons catalog',
      'name' => '',
      'type' => 'message',
      'message' => 'Every Add-on option is defined here, with its price. A prize picks from this list on its edit page and cannot change an option. Editing an option (its price included) changes it on every prize that uses it, and an option a prize uses cannot be deleted. Only the Full bundle price is set on the prize itself.',
      'new_lines' => 'wpautop',
      'esc_html' => 0,
      'conditional_logic' => $on,
    ],
    [
      'key' => $prefix . 'addons_title',
      'label' => 'Default title',
      'name' => $names . 'addons_title',
      'type' => 'text',
      'placeholder' => 'e.g. The extras that matter',
      'instructions' => 'Shown on a prize that has no title of its own.',
      'conditional_logic' => $on,
    ],
    [
      'key' => $prefix . 'addons_description',
      'label' => 'Default description',
      'name' => $names . 'addons_description',
      'type' => 'textarea',
      'rows' => 2,
      'new_lines' => '',
      'placeholder' => "e.g. Cover your first year's running costs.",
      'conditional_logic' => $on,
    ],
    [
      'key' => NERA_PRIZE_ADDON_CATALOG_ADDONS,
      'label' => 'Options',
      'name' => $names . 'addons_items',
      'type' => 'repeater',
      'instructions' => 'Each option has a fixed price above 0 (free items belong in Safety).',
      'layout' => 'block',
      'button_label' => 'Add option',
      'min' => 0,
      'conditional_logic' => $on,
      'sub_fields' => [
        [
          // Stable ID so orders already placed, and the "Purchased" lock, keep matching however the
          // option is renamed or reordered. Generated on save, hidden in admin.
          'key' => NERA_PRIZE_ADDON_CATALOG_ADDONS_ID,
          'label' => 'ID',
          'name' => 'option_id',
          'type' => 'text',
          'readonly' => 1,
        ],
        [
          'key' => $prefix . 'addon_title',
          'label' => 'Option',
          'name' => 'title',
          'type' => 'text',
          'required' => 1,
          'placeholder' => 'e.g. First year storage',
          'wrapper' => ['width' => '70'],
        ],
        [
          'key' => $prefix . 'addon_price',
          'label' => 'Price per year',
          'name' => 'price',
          'type' => 'number',
          'instructions' => 'What one year of this option costs. On a prize with Year choice on, the customer pays this price times the number of years they choose.',
          'required' => 1,
          'min' => 0.01,
          'step' => 0.01,
          'prepend' => $currency,
          'wrapper' => ['width' => '30', 'class' => 'nera-psa-money'],
        ],
        [
          'key' => $prefix . 'addon_description',
          'label' => 'Description',
          'name' => 'description',
          'type' => 'textarea',
          'rows' => 2,
          'new_lines' => '',
          'placeholder' => 'e.g. Storage and launch fees for the first season',
        ],
      ],
    ],
    [
      'key' => 'field_nera_psag_accordion_end',
      'label' => '',
      'name' => '',
      'type' => 'accordion',
      'endpoint' => 1,
    ],
  ];
}
