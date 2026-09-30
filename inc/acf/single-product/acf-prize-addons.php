<?php
/**
 * Advanced Custom Fields - Prize Safety & Add-ons
 *
 * A prize does not define Safety items or Add-on options: it picks them from the
 * Catalog in Theme Settings → WooCommerce → Add-ons Bundles (search, add, drag to
 * reorder). What it can set for itself is its own title and description (empty uses
 * the settings default) and the Full bundle price. Validation and the Full bundle
 * price behaviour live in inc/prize-addons.php.
 *
 * The box only exists while the Add-ons Bundles switch is on.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_local_field_group') && function_exists('nera_prize_addons_site_enabled')) {
  $nera_psa_safety_on = [[['field' => 'field_nera_psa_safety_enabled', 'operator' => '==', 'value' => '1']]];
  $nera_psa_addons_on = [[['field' => 'field_nera_psa_addons_enabled', 'operator' => '==', 'value' => '1']]];
  $nera_psa_currency = nera_prize_addons_currency_symbol();

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
        'placeholder' => 'Uses the default from Theme Settings',
        'instructions' => 'Optional. Leave empty to use the default title.',
        'conditional_logic' => $nera_psa_safety_on,
      ],
      [
        'key' => 'field_nera_psa_safety_description',
        'label' => 'Description',
        'name' => 'safety_description',
        'type' => 'textarea',
        'rows' => 2,
        'new_lines' => '',
        'placeholder' => 'Uses the default from Theme Settings',
        'instructions' => 'Optional. Leave empty to use the default description.',
        'conditional_logic' => $nera_psa_safety_on,
      ],
      [
        'key' => NERA_PRIZE_ADDON_PICK_SAFETY,
        'label' => 'Items',
        'name' => 'safety_item_ids',
        'type' => 'select',
        'instructions' => 'Search the catalog and add. Drag to reorder. Items are created and edited in Theme Settings → WooCommerce → Add-ons Bundles.',
        'choices' => [],
        'multiple' => 1,
        'ui' => 1,
        'ajax' => 0,
        'allow_null' => 0,
        'return_format' => 'value',
        'placeholder' => 'Search safety items…',
        'conditional_logic' => $nera_psa_safety_on,
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
        'placeholder' => 'Uses the default from Theme Settings',
        'instructions' => 'Optional. Leave empty to use the default title.',
        'conditional_logic' => $nera_psa_addons_on,
      ],
      [
        'key' => 'field_nera_psa_addons_description',
        'label' => 'Description',
        'name' => 'addons_description',
        'type' => 'textarea',
        'rows' => 2,
        'new_lines' => '',
        'placeholder' => 'Uses the default from Theme Settings',
        'instructions' => 'Optional. Leave empty to use the default description.',
        'conditional_logic' => $nera_psa_addons_on,
      ],
      [
        'key' => NERA_PRIZE_ADDON_PICK_ADDONS,
        'label' => 'Options',
        'name' => 'addon_option_ids',
        'type' => 'select',
        'instructions' => 'Search the catalog and add. Drag to reorder. Options and their prices are set in Theme Settings → WooCommerce → Add-ons Bundles.',
        'choices' => [],
        'multiple' => 1,
        'ui' => 1,
        'ajax' => 0,
        'allow_null' => 0,
        'return_format' => 'value',
        'placeholder' => 'Search add-on options…',
        'conditional_logic' => $nera_psa_addons_on,
      ],
      [
        // Locked until an option is chosen, and capped at their total
        // (assets/js/admin-prize-addons.js); checked again on save (inc/prize-addons.php).
        'key' => 'field_nera_psa_addons_bundle_price',
        'label' => 'Full bundle price',
        'name' => 'addons_bundle_price',
        'type' => 'number',
        'instructions' => 'Optional. Charged instead of the options total when a customer selects every option in one purchase. Cannot be higher than the total of the options above. Shown to customers only when it is lower than that total and there are at least two options.',
        'min' => 0.01,
        'step' => 0.01,
        'prepend' => $nera_psa_currency,
        'wrapper' => ['width' => '30', 'class' => 'nera-psa-money nera-psa-bundle-price'],
        'conditional_logic' => $nera_psa_addons_on,
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
    // Hidden while the site switch is off (Theme Settings → WooCommerce → Add-ons Bundles).
    'active' => nera_prize_addons_site_enabled(),
    'description' => 'Per-giveaway Safety list and paid Add-ons shown on the prize page.',
  ]);

  unset($nera_psa_safety_on, $nera_psa_addons_on, $nera_psa_currency);
}
