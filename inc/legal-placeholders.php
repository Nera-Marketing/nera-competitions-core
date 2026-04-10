<?php
/**
 * Legal Placeholders - Dynamic T&C and Privacy Policy fields
 *
 * Admin enters business details once; placeholders in legal page content
 * are automatically replaced with these values when displayed.
 *
 * Placeholders: [Business Name], [Business Address], [Postal Entry Address],
 * [Email], [Website Name]
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

  // Add Legal Placeholders sub-page
  acf_add_options_sub_page([
    'page_title' => 'Legal Placeholders',
    'menu_title' => 'Legal Placeholders',
    'parent_slug' => 'theme-settings',
  ]);
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_neracompetitions_legal_placeholders',
    'title' => 'Legal Placeholders',
    'fields' => [
      // Message
      [
        'key' => 'field_legal_placeholders_message',
        'label' => 'How to use',
        'name' => '',
        'type' => 'message',
        'message' => 'Enter your business details below. Use these placeholders in your Terms & Conditions and Privacy Policy pages: [Business Name], [Business Address], [Postal Entry Address], [Email], [Website Name]. They will be replaced automatically on the front end.',
        'wrapper' => [
          'width' => '',
          'class' => '',
          'id' => '',
        ],
      ],
      // Business Name
      [
        'key' => 'field_legal_business_name',
        'label' => 'Business Name',
        'name' => 'legal_business_name',
        'type' => 'text',
        'instructions' => 'Replaces [Business Name]',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. Acme Competitions Ltd',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Business Address
      [
        'key' => 'field_legal_business_address',
        'label' => 'Business Address',
        'name' => 'legal_business_address',
        'type' => 'textarea',
        'instructions' => 'Registered address. Replaces [Business Address]',
        'required' => 0,
        'default_value' => '',
        'placeholder' => "e.g. 123 High Street\nLondon\nSW1A 1AA",
        'rows' => 3,
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Postal Entry Address
      [
        'key' => 'field_legal_postal_entry_address',
        'label' => 'Postal Entry Address',
        'name' => 'legal_postal_entry_address',
        'type' => 'textarea',
        'instructions' => 'Address for postal competition entries. Replaces [Postal Entry Address]',
        'required' => 0,
        'default_value' => '',
        'placeholder' => "e.g. PO Box 456\nLondon\nSW1A 2BB",
        'rows' => 3,
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Contact Email
      [
        'key' => 'field_legal_contact_email',
        'label' => 'Contact Email',
        'name' => 'legal_contact_email',
        'type' => 'email',
        'instructions' => 'Replaces [Email]',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. info@example.com',
        'wrapper' => [
          'width' => '50',
          'class' => '',
          'id' => '',
        ],
      ],
      // Website Name
      [
        'key' => 'field_legal_website_name',
        'label' => 'Website Name',
        'name' => 'legal_website_name',
        'type' => 'text',
        'instructions' => 'Domain or site name. Replaces [Website Name]',
        'required' => 0,
        'default_value' => '',
        'placeholder' => 'e.g. example.com',
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
          'value' => 'acf-options-legal-placeholders',
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

/**
 * Replace legal placeholders in content with stored ACF values.
 *
 * @param string $content Page content containing placeholders.
 * @return string Content with placeholders replaced.
 */
function nera_replace_legal_placeholders($content)
{
  if (!function_exists('get_field')) {
    return $content;
  }

  $placeholders = [
    '[Business Name]' => get_field('legal_business_name', 'option') ?: '',
    '[Business Address]' => get_field('legal_business_address', 'option') ?: '',
    '[Postal Entry Address]' =>
      get_field('legal_postal_entry_address', 'option') ?: '',
    '[Email]' => get_field('legal_contact_email', 'option') ?: '',
    '[Website Name]' => get_field('legal_website_name', 'option') ?: '',
  ];

  return str_replace(array_keys($placeholders), array_values($placeholders), $content);
}

/**
 * Filter legal page content to replace placeholders.
 *
 * @param string $content The post content.
 * @return string Filtered content.
 */
function nera_filter_legal_page_placeholders($content)
{
  if (!is_singular('page')) {
    return $content;
  }

  $current_id = get_queried_object_id();
  $terms_page_id = 0;
  $privacy_page_id = (int) get_option('wp_page_for_privacy_policy', 0);

  if (function_exists('wc_terms_and_conditions_page_id')) {
    $terms_page_id = wc_terms_and_conditions_page_id();
  }

  if ($current_id !== $terms_page_id && $current_id !== $privacy_page_id) {
    return $content;
  }

  return nera_replace_legal_placeholders($content);
}
add_filter('the_content', 'nera_filter_legal_page_placeholders', 5);
