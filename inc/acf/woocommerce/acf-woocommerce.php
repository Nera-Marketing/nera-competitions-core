<?php
/**
 * ACF WooCommerce Settings
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_options_page')) {
  if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
    acf_add_options_page([
      'page_title' => 'Theme Settings',
      'menu_title' => 'Theme Settings',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect' => false,
    ]);
  }

  acf_add_options_sub_page([
    'page_title' => 'WooCommerce Settings',
    'menu_title' => 'WooCommerce',
    'parent_slug' => 'theme-settings',
  ]);
}

if (function_exists('acf_add_local_field_group')) {
  $woocommerce_fields = [
    [
      'key' => 'field_add_to_cart_success_message',
      'label' => 'Add to Cart Success Message',
      'name' => 'add_to_cart_success_message',
      'type' => 'text',
      'instructions' => 'Message shown to customers after successfully adding tickets to the cart.',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => [
        'width' => '',
        'class' => '',
        'id' => '',
      ],
      'default_value' => 'Tickets added to cart!',
      'placeholder' => 'Tickets added to cart!',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
    ],
  ];

  if (class_exists('Nera_STW_ACF_Copy_Settings')) {
    $woocommerce_fields = array_merge(
      $woocommerce_fields,
      Nera_STW_ACF_Copy_Settings::get_woocommerce_accordion_fields(),
    );
  }

  acf_add_local_field_group([
    'key' => 'group_neracompetitions_woocommerce',
    'title' => 'WooCommerce Settings',
    'fields' => $woocommerce_fields,
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'acf-options-woocommerce',
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
