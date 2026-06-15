<?php
/**
 * ACF Listing Visibility Settings
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_options_page')) {
  // Check if Theme Settings page exists, if not create it
  if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
    acf_add_options_page([
      'page_title' => 'Theme Settings',
      'menu_title' => 'Theme Settings',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect' => false,
    ]);
  }

  // Add Listing Visibility Subpage
  acf_add_options_sub_page([
    'page_title' => 'Listing Visibility',
    'menu_title' => 'Listing Visibility',
    'parent_slug' => 'theme-settings',
  ]);
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
          'value' => 'acf-options-listing-visibility',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
  ]);
}
