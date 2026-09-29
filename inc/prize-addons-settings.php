<?php
/**
 * Prize Safety & Add-ons — site switch and Global defaults.
 *
 * Theme Settings → WooCommerce → "Add-ons Bundles" holds the site-wide switch
 * and a Global default for Safety and Add-ons. A prize's own fields are filled
 * from the Global default once, when the admin opens a prize whose list is still
 * empty; after Update the prize keeps its own copy (docs/adr/0013).
 *
 * Loaded before the ACF field groups, so it defines the field builder both the
 * product box and the settings section use.
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

/** Field key prefixes: the product box and the Global default section. */
const NERA_PRIZE_ADDON_KEY_PRODUCT = 'field_nera_psa_';
const NERA_PRIZE_ADDON_KEY_GLOBAL = 'field_nera_psag_';

/** Name prefix of the Global default fields in the options table. */
const NERA_PRIZE_ADDON_GLOBAL_PREFIX = 'psa_default_';

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

/**
 * Safety and Add-ons fields, shared by the product box and the Global default.
 *
 * The product scope keeps the keys and names it always had, so saved data is
 * unaffected. The Show switches and tabs are not part of this: they belong to
 * the product box only.
 *
 * @param string $prefix       Field key prefix (NERA_PRIZE_ADDON_KEY_*).
 * @param string $name_prefix  Field name prefix ('' for a product).
 * @param array  $cond_safety  ACF conditional logic for the Safety fields.
 * @param array  $cond_addons  ACF conditional logic for the Add-ons fields.
 * @return array{safety:array,addons:array}
 */
function nera_prize_addons_acf_fields(string $prefix, string $name_prefix, array $cond_safety, array $cond_addons): array
{
  $currency = nera_prize_addons_currency_symbol();
  $money = ['width' => '30', 'class' => 'nera-psa-money'];

  $safety = [
    [
      'key' => $prefix . 'safety_title',
      'label' => 'Title',
      'name' => $name_prefix . 'safety_title',
      'type' => 'text',
      'placeholder' => 'Included free with this prize',
      'instructions' => 'Leave empty to use "Included free with this prize".',
      'conditional_logic' => $cond_safety,
    ],
    [
      'key' => $prefix . 'safety_description',
      'label' => 'Description',
      'name' => $name_prefix . 'safety_description',
      'type' => 'textarea',
      'rows' => 2,
      'new_lines' => '',
      'placeholder' => 'e.g. Every prize includes the basic safety equipment to keep you safe on the water.',
      'conditional_logic' => $cond_safety,
    ],
    [
      'key' => $prefix . 'safety_items',
      'label' => 'Items',
      'name' => $name_prefix . 'safety_items',
      'type' => 'repeater',
      'layout' => 'block',
      'button_label' => 'Add item',
      'min' => 0,
      'conditional_logic' => $cond_safety,
      'sub_fields' => [
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
  ];

  $addons = [
    [
      'key' => $prefix . 'addons_title',
      'label' => 'Title',
      'name' => $name_prefix . 'addons_title',
      'type' => 'text',
      'placeholder' => 'e.g. The extras that matter',
      'conditional_logic' => $cond_addons,
    ],
    [
      'key' => $prefix . 'addons_description',
      'label' => 'Description',
      'name' => $name_prefix . 'addons_description',
      'type' => 'textarea',
      'rows' => 2,
      'new_lines' => '',
      'placeholder' => "e.g. Cover your first year's running costs.",
      'conditional_logic' => $cond_addons,
    ],
    [
      'key' => $prefix . 'addons_items',
      'label' => 'Options',
      'name' => $name_prefix . 'addons_items',
      'type' => 'repeater',
      'instructions' => 'Each option has a fixed price above 0 (free items belong in Safety). Price changes apply to new orders only.',
      'layout' => 'block',
      'button_label' => 'Add option',
      'min' => 0,
      'conditional_logic' => $cond_addons,
      'sub_fields' => [
        [
          // Stable ID so reordering or renaming options never breaks older
          // orders or the "Purchased" lock. Generated on save, hidden in admin.
          'key' => $prefix . 'addon_option_id',
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
          'label' => 'Price',
          'name' => 'price',
          'type' => 'number',
          'required' => 1,
          'min' => 0.01,
          'step' => 0.01,
          'prepend' => $currency,
          'wrapper' => $money,
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
      'key' => $prefix . 'addons_bundle_price',
      'label' => 'Full bundle price',
      'name' => $name_prefix . 'addons_bundle_price',
      'type' => 'number',
      'instructions' => 'Optional. Charged instead of the options total when a customer selects every option in one purchase. Must be lower than the options total. Leave empty for no bundle discount.',
      'min' => 0.01,
      'step' => 0.01,
      'prepend' => $currency,
      'wrapper' => $money,
      'conditional_logic' => $cond_addons,
    ],
  ];

  return ['safety' => $safety, 'addons' => $addons];
}

/**
 * The "Add-ons Bundles" accordion for Theme Settings → WooCommerce.
 *
 * Sits above "Spin To Win": the site switch, then (only while it is on) the
 * Global default for Safety and for Add-ons.
 *
 * @return array[] ACF fields, accordion start to accordion end.
 */
function nera_prize_addons_settings_fields(): array
{
  $on = [[['field' => NERA_PRIZE_ADDON_ACF_SITE_SWITCH, 'operator' => '==', 'value' => '1']]];
  $fields = nera_prize_addons_acf_fields(NERA_PRIZE_ADDON_KEY_GLOBAL, NERA_PRIZE_ADDON_GLOBAL_PREFIX, $on, $on);

  return array_merge(
    [
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
        'key' => 'field_nera_psag_safety_heading',
        'label' => 'Safety — Global default',
        'name' => '',
        'type' => 'message',
        'message' => 'Copied into a prize the first time its Safety list is set up. Editing it later does not change prizes that already saved their own list.',
        'new_lines' => 'wpautop',
        'esc_html' => 0,
        'conditional_logic' => $on,
      ],
    ],
    $fields['safety'],
    [
      [
        'key' => 'field_nera_psag_addons_heading',
        'label' => 'Add-ons — Global default',
        'name' => '',
        'type' => 'message',
        'message' => 'Copied into a prize the first time its Add-ons are set up. Editing it later does not change prizes that already saved their own options.',
        'new_lines' => 'wpautop',
        'esc_html' => 0,
        'conditional_logic' => $on,
      ],
    ],
    $fields['addons'],
    [
      [
        'key' => 'field_nera_psag_accordion_end',
        'label' => '',
        'name' => '',
        'type' => 'accordion',
        'endpoint' => 1,
      ],
    ]
  );
}

/* -------------------------------------------------------------------------
 * Fill a prize from the Global default (admin form only)
 * ---------------------------------------------------------------------- */

/**
 * Global default rows re-keyed for the product box's repeater.
 *
 * Option IDs are left empty so every prize gets fresh ones when it is saved.
 *
 * @param string $section 'safety' or 'addons'.
 * @return array<int,array<string,mixed>>
 */
function nera_prize_addons_global_rows(string $section): array
{
  if (!function_exists('get_field')) {
    return [];
  }

  $p = NERA_PRIZE_ADDON_KEY_PRODUCT;
  $rows = [];
  if ('safety' === $section) {
    foreach ((array) get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . 'safety_items', 'option') as $row) {
      if ('' === trim((string) ($row['title'] ?? ''))) {
        continue;
      }
      $rows[] = [
        $p . 'safety_item_icon' => (string) ($row['icon'] ?? ''),
        $p . 'safety_item_title' => (string) $row['title'],
        $p . 'safety_item_description' => (string) ($row['description'] ?? ''),
        $p . 'safety_item_icon_custom' => (string) ($row['icon_custom'] ?? ''),
      ];
    }
  } else {
    foreach ((array) get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . 'addons_items', 'option') as $row) {
      if ('' === trim((string) ($row['title'] ?? ''))) {
        continue;
      }
      $rows[] = [
        $p . 'addon_option_id' => '',
        $p . 'addon_title' => (string) $row['title'],
        $p . 'addon_price' => $row['price'] ?? '',
        $p . 'addon_description' => (string) ($row['description'] ?? ''),
      ];
    }
  }

  return $rows;
}

/**
 * Whether the prize being edited should be filled from the Global default.
 *
 * True on a product edit screen, with the switch on, when that prize has saved
 * no rows yet for the section. Nothing is stored until the admin clicks Update.
 *
 * @param string $section 'safety' or 'addons'.
 * @return bool
 */
function nera_prize_addons_should_prefill(string $section): bool
{
  if (!is_admin() || !nera_prize_addons_site_enabled() || !function_exists('acf_get_form_data')) {
    return false;
  }

  $post_id = acf_get_form_data('post_id');
  if (!is_numeric($post_id) || 'product' !== get_post_type((int) $post_id)) {
    return false;
  }

  return (int) get_post_meta((int) $post_id, $section . '_items', true) < 1;
}

/**
 * Fill one product field from its Global default counterpart.
 *
 * @param array|false $field   ACF field about to render.
 * @param string      $section 'safety' or 'addons'.
 * @param string      $name    Product field name (e.g. 'safety_title').
 * @return array|false
 */
function nera_prize_addons_prefill_field($field, string $section, string $name)
{
  if (!is_array($field) || !nera_prize_addons_should_prefill($section)) {
    return $field;
  }
  if (!empty($field['value']) || !function_exists('get_field')) {
    return $field;
  }

  $value = get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . $name, 'option');
  if (null !== $value && false !== $value && '' !== $value) {
    $field['value'] = $value;
  }

  return $field;
}

/**
 * Fill an empty product repeater with the Global default rows.
 *
 * @param array|false $field   ACF repeater about to render.
 * @param string      $section 'safety' or 'addons'.
 * @return array|false
 */
function nera_prize_addons_prefill_rows($field, string $section)
{
  if (!is_array($field) || !nera_prize_addons_should_prefill($section) || !empty($field['value'])) {
    return $field;
  }

  $rows = nera_prize_addons_global_rows($section);
  if (!empty($rows)) {
    $field['value'] = $rows;
  }

  return $field;
}

foreach (
  [
    'safety' => ['safety_title', 'safety_description'],
    'addons' => ['addons_title', 'addons_description', 'addons_bundle_price'],
  ] as $nera_psa_section => $nera_psa_names
) {
  foreach ($nera_psa_names as $nera_psa_name) {
    add_filter(
      'acf/prepare_field/key=' . NERA_PRIZE_ADDON_KEY_PRODUCT . $nera_psa_name,
      static function ($field) use ($nera_psa_section, $nera_psa_name) {
        return nera_prize_addons_prefill_field($field, $nera_psa_section, $nera_psa_name);
      }
    );
  }
  add_filter(
    'acf/prepare_field/key=' . NERA_PRIZE_ADDON_KEY_PRODUCT . $nera_psa_section . '_items',
    static function ($field) use ($nera_psa_section) {
      return nera_prize_addons_prefill_rows($field, $nera_psa_section);
    }
  );
}
unset($nera_psa_section, $nera_psa_names, $nera_psa_name);
