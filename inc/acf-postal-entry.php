<?php
/**
 * ACF Postal Entry Settings
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

  // Add Postal Entry Settings Subpage
  acf_add_options_sub_page([
    'page_title' => 'Postal Entry Settings',
    'menu_title' => 'Postal Entry',
    'parent_slug' => 'theme-settings',
  ]);
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_neracompetitions_postal_entry',
    'title' => 'Postal Entry Settings',
    'fields' => [
      // Instruction Text
      [
        'key' => 'field_postal_instruction_text',
        'label' => 'Instruction Text',
        'name' => 'postal_instruction_text',
        'type' => 'textarea',
        'instructions' => 'The instruction text shown at the top of the postal entry modal.',
        'required' => 0,
        'default_value' =>
          'To enter this competition by post, send an unenclosed postcard with sufficient postage (1st or 2nd class stamp) to:',
        'rows' => 3,
        'wrapper' => [
          'width' => '',
          'class' => '',
          'id' => '',
        ],
      ],
      // Company Name
      [
        'key' => 'field_postal_company_name',
        'label' => 'Company Name',
        'name' => 'postal_company_name',
        'type' => 'text',
        'instructions' => 'The company name for the postal address.',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. Company Ltd',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Address Line 1
      [
        'key' => 'field_postal_address_line_1',
        'label' => 'Street Address',
        'name' => 'postal_address_line_1',
        'type' => 'text',
        'instructions' => '',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. 1 High Street',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Town/City
      [
        'key' => 'field_postal_town_city',
        'label' => 'Town / City',
        'name' => 'postal_town_city',
        'type' => 'text',
        'instructions' => '',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. Manchester',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Postcode
      [
        'key' => 'field_postal_postcode',
        'label' => 'Postcode',
        'name' => 'postal_postcode',
        'type' => 'text',
        'instructions' => '',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. M1 1AA',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Required Information Items (Repeater)
      [
        'key' => 'field_postal_required_items',
        'label' => 'Required Information Items',
        'name' => 'postal_required_items',
        'type' => 'repeater',
        'instructions' => 'List of information the entrant must include on their postcard.',
        'required' => 0,
        'min' => 0,
        'max' => 0,
        'layout' => 'table',
        'button_label' => 'Add Item',
        'sub_fields' => [
          [
            'key' => 'field_postal_required_item_label',
            'label' => 'Item',
            'name' => 'item_label',
            'type' => 'text',
            'required' => 0,
            'wrapper' => [
              'width' => '',
              'class' => '',
              'id' => '',
            ],
          ],
        ],
      ],
      // Terms Text
      [
        'key' => 'field_postal_terms_text',
        'label' => 'Terms Text',
        'name' => 'postal_terms_text',
        'type' => 'text',
        'instructions' => 'Text displayed before the terms and conditions link.',
        'required' => 0,
        'default_value' => 'All postal entries are subject to our',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Terms Page URL
      [
        'key' => 'field_postal_terms_url',
        'label' => 'Terms & Conditions URL',
        'name' => 'postal_terms_url',
        'type' => 'url',
        'instructions' =>
          'URL to the terms and conditions page. Leave blank to use /terms-and-conditions/.',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'https://example.com/terms-and-conditions/',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'acf-options-postal-entry',
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
