<?php
/**
 * ACF Listing Visibility Settings (Theme Settings → WooCommerce)
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_listing_visibility',
    'title' => __('Listing Visibility', 'nera-competitions'),
    'fields' => [
      [
        'key' => 'field_hide_ended_competitions',
        'label' => __('Hide Ended Competitions', 'nera-competitions'),
        'name' => 'hide_ended_competitions',
        'type' => 'true_false',
        'instructions' => __('When ON, competitions whose end date has passed are hidden from the homepage grids and the All Competitions listing.', 'nera-competitions'),
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => [
          'width' => '100',
          'class' => '',
          'id' => '',
        ],
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_hide_sold_out_competitions',
        'label' => __('Hide Sold Out Competitions', 'nera-competitions'),
        'name' => 'hide_sold_out_competitions',
        'type' => 'true_false',
        'instructions' => __('When ON, competitions whose tickets are fully sold are hidden from the homepage grids and the All Competitions listing.', 'nera-competitions'),
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => [
          'width' => '100',
          'class' => '',
          'id' => '',
        ],
        'default_value' => 1,
        'ui' => 1,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'acf-options-woocommerce',
        ],
      ],
    ],
    'menu_order' => 10,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
  ]);
}
