<?php
/**
 * Advanced Custom Fields - Prize Safety & Add-ons
 *
 * Per-giveaway Safety list (free, display only) and paid Add-ons (fixed price
 * per option, optional Full bundle price). Hooks that normalise option IDs and
 * validate the bundle price live in inc/prize-addons.php.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_local_field_group')) {
  $nera_psa_currency = function_exists('get_woocommerce_currency_symbol')
    ? html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8')
    : '£';

  acf_add_local_field_group([
    'key' => 'group_nera_prize_safety_addons',
    'title' => 'Prize Safety & Add-ons',
    'fields' => [
      // ========================================
      // Tab: Safety
      // ========================================
      [
        'key' => 'field_nera_psa_tab_safety',
        'label' => 'Safety',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_nera_psa_safety_enabled',
        'label' => 'Show Safety',
        'name' => 'safety_enabled',
        'type' => 'true_false',
        'instructions' => 'Show the free safety equipment the winner of this prize receives. Display only: nothing is added to the cart.',
        'default_value' => 0,
        'ui' => 1,
      ],
      [
        'key' => 'field_nera_psa_safety_title',
        'label' => 'Title',
        'name' => 'safety_title',
        'type' => 'text',
        'placeholder' => 'Included free with this prize',
        'instructions' => 'Leave empty to use "Included free with this prize".',
        'conditional_logic' => [[['field' => 'field_nera_psa_safety_enabled', 'operator' => '==', 'value' => '1']]],
      ],
      [
        'key' => 'field_nera_psa_safety_description',
        'label' => 'Description',
        'name' => 'safety_description',
        'type' => 'textarea',
        'rows' => 2,
        'new_lines' => '',
        'placeholder' => 'e.g. Every prize includes the basic safety equipment to keep you safe on the water.',
        'conditional_logic' => [[['field' => 'field_nera_psa_safety_enabled', 'operator' => '==', 'value' => '1']]],
      ],
      [
        'key' => 'field_nera_psa_safety_items',
        'label' => 'Items',
        'name' => 'safety_items',
        'type' => 'repeater',
        'layout' => 'block',
        'button_label' => 'Add item',
        'min' => 0,
        'conditional_logic' => [[['field' => 'field_nera_psa_safety_enabled', 'operator' => '==', 'value' => '1']]],
        'sub_fields' => [
          [
            // Searchable dropdown with an icon preview (assets/js/admin-prize-icon-picker.js).
            'key' => 'field_nera_psa_safety_item_icon',
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
            'key' => 'field_nera_psa_safety_item_title',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
            'required' => 1,
            'placeholder' => 'e.g. Life jackets',
            'wrapper' => ['width' => '30'],
          ],
          [
            'key' => 'field_nera_psa_safety_item_description',
            'label' => 'Description',
            'name' => 'description',
            'type' => 'text',
            'placeholder' => 'e.g. Auto-inflate',
            'wrapper' => ['width' => '40'],
          ],
          [
            'key' => 'field_nera_psa_safety_item_icon_custom',
            'label' => 'Icon name',
            'name' => 'icon_custom',
            'type' => 'text',
            'instructions' => 'Any icon name from fonts.google.com/icons, e.g. "kayaking".',
            'placeholder' => 'e.g. kayaking',
            'wrapper' => ['width' => '30'],
            'conditional_logic' => [[['field' => 'field_nera_psa_safety_item_icon', 'operator' => '==', 'value' => '__custom']]],
          ],
        ],
      ],

      // ========================================
      // Tab: Add-ons
      // ========================================
      [
        'key' => 'field_nera_psa_tab_addons',
        'label' => 'Add-ons',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_nera_psa_addons_enabled',
        'label' => 'Show Add-ons',
        'name' => 'addons_enabled',
        'type' => 'true_false',
        'instructions' => 'Let customers buy optional paid extras with their tickets. Charged once per draw, however many tickets they buy, and not refunded if they do not win.',
        'default_value' => 0,
        'ui' => 1,
      ],
      [
        'key' => 'field_nera_psa_addons_title',
        'label' => 'Title',
        'name' => 'addons_title',
        'type' => 'text',
        'placeholder' => 'e.g. The extras that matter',
        'conditional_logic' => [[['field' => 'field_nera_psa_addons_enabled', 'operator' => '==', 'value' => '1']]],
      ],
      [
        'key' => 'field_nera_psa_addons_description',
        'label' => 'Description',
        'name' => 'addons_description',
        'type' => 'textarea',
        'rows' => 2,
        'new_lines' => '',
        'placeholder' => "e.g. Cover your first year's running costs.",
        'conditional_logic' => [[['field' => 'field_nera_psa_addons_enabled', 'operator' => '==', 'value' => '1']]],
      ],
      [
        'key' => 'field_nera_psa_addons_items',
        'label' => 'Options',
        'name' => 'addons_items',
        'type' => 'repeater',
        'instructions' => 'Each option has a fixed price above 0 (free items belong in Safety). Price changes apply to new orders only.',
        'layout' => 'block',
        'button_label' => 'Add option',
        'min' => 0,
        'conditional_logic' => [[['field' => 'field_nera_psa_addons_enabled', 'operator' => '==', 'value' => '1']]],
        'sub_fields' => [
          [
            // Stable ID so reordering or renaming options never breaks older
            // orders or the "Purchased" lock. Generated on save, hidden in admin.
            'key' => 'field_nera_psa_addon_option_id',
            'label' => 'ID',
            'name' => 'option_id',
            'type' => 'text',
            'readonly' => 1,
          ],
          [
            'key' => 'field_nera_psa_addon_title',
            'label' => 'Option',
            'name' => 'title',
            'type' => 'text',
            'required' => 1,
            'placeholder' => 'e.g. First year storage',
            'wrapper' => ['width' => '70'],
          ],
          [
            'key' => 'field_nera_psa_addon_price',
            'label' => 'Price',
            'name' => 'price',
            'type' => 'number',
            'required' => 1,
            'min' => 0.01,
            'step' => 0.01,
            'prepend' => $nera_psa_currency,
            'wrapper' => ['width' => '30'],
          ],
          [
            'key' => 'field_nera_psa_addon_description',
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
        'key' => 'field_nera_psa_addons_bundle_price',
        'label' => 'Full bundle price',
        'name' => 'addons_bundle_price',
        'type' => 'number',
        'instructions' => 'Optional. Charged instead of the options total when a customer selects every option in one purchase. Must be lower than the options total. Leave empty for no bundle discount.',
        'min' => 0.01,
        'step' => 0.01,
        'prepend' => $nera_psa_currency,
        'wrapper' => ['width' => '30'],
        'conditional_logic' => [[['field' => 'field_nera_psa_addons_enabled', 'operator' => '==', 'value' => '1']]],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'product',
        ],
      ],
    ],
    'menu_order' => 1,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
    'description' => 'Per-giveaway Safety list and paid Add-ons shown on the prize page.',
  ]);

  unset($nera_psa_currency);
}
