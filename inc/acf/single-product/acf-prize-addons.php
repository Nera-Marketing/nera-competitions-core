<?php
/**
 * Advanced Custom Fields - Prize Safety & Add-ons
 *
 * Per-giveaway Safety list (free, display only) and paid Add-ons (fixed price
 * per option, optional Full bundle price). Hooks that normalise option IDs and
 * validate the bundle price live in inc/prize-addons.php. The fields themselves
 * come from inc/prize-addons-settings.php, which the Global default shares.
 *
 * The box only exists while the Add-ons Bundles switch is on.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_local_field_group') && function_exists('nera_prize_addons_acf_fields')) {
  $nera_psa_safety_on = [[['field' => 'field_nera_psa_safety_enabled', 'operator' => '==', 'value' => '1']]];
  $nera_psa_addons_on = [[['field' => 'field_nera_psa_addons_enabled', 'operator' => '==', 'value' => '1']]];
  $nera_psa_fields = nera_prize_addons_acf_fields(
    NERA_PRIZE_ADDON_KEY_PRODUCT,
    '',
    $nera_psa_safety_on,
    $nera_psa_addons_on
  );

  acf_add_local_field_group([
    'key' => 'group_nera_prize_safety_addons',
    'title' => 'Prize Safety & Add-ons',
    'fields' => array_merge(
      [
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
      ],
      $nera_psa_fields['safety'],
      [
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
      ],
      $nera_psa_fields['addons']
    ),
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

  unset($nera_psa_safety_on, $nera_psa_addons_on, $nera_psa_fields);
}
