<?php
/**
 * ACF Field Group: Contact Page Content
 *
 * Registers custom fields for the Contact page template.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

/**
 * Register ACF field group for Contact page
 */
function nera_register_contact_fields()
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_contact_page',
    'title' => __('Contact Page Content', 'nera-competitions'),
    'fields' => [
      // Hero Section Tab
      [
        'key' => 'field_contact_tab_hero',
        'label' => __('Hero Section', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_contact_heading',
        'label' => __('Page Heading', 'nera-competitions'),
        'name' => 'contact_heading',
        'type' => 'text',
        'instructions' => __('Main heading for the contact page', 'nera-competitions'),
        'default_value' => __('Contact Us', 'nera-competitions'),
        'placeholder' => __('Contact Us', 'nera-competitions'),
      ],
      [
        'key' => 'field_contact_subheading',
        'label' => __('Subheading', 'nera-competitions'),
        'name' => 'contact_subheading',
        'type' => 'text',
        'instructions' => __('Optional subtitle (appears above main heading)', 'nera-competitions'),
        'placeholder' => __('Get in Touch', 'nera-competitions'),
      ],
      [
        'key' => 'field_contact_description',
        'label' => __('Description', 'nera-competitions'),
        'name' => 'contact_description',
        'type' => 'textarea',
        'instructions' => __('Supporting text below the heading', 'nera-competitions'),
        'default_value' => __(
          'We\'d love to hear from you regarding the competition. Our team is ready to answer any questions.',
          'nera-competitions',
        ),
        'rows' => 3,
      ],

      // Contact Information Tab
      [
        'key' => 'field_contact_tab_info',
        'label' => __('Contact Information', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_show_contact_cards',
        'label' => __('Show Contact Info Cards', 'nera-competitions'),
        'name' => 'show_contact_cards',
        'type' => 'true_false',
        'instructions' => __('Toggle visibility of contact information cards', 'nera-competitions'),
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_contact_address',
        'label' => __('Physical Address', 'nera-competitions'),
        'name' => 'contact_address',
        'type' => 'textarea',
        'instructions' => __(
          'Your business address (supports multiple lines)',
          'nera-competitions',
        ),
        'default_value' => "123 Innovation Blvd\nTech District, NY 10012",
        'rows' => 3,
        'conditional_logic' => [
          [
            [
              'field' => 'field_show_contact_cards',
              'operator' => '==',
              'value' => '1',
            ],
          ],
        ],
      ],
      [
        'key' => 'field_contact_email',
        'label' => __('Email Address', 'nera-competitions'),
        'name' => 'contact_email',
        'type' => 'email',
        'instructions' => __('Primary contact email address', 'nera-competitions'),
        'default_value' => 'support@competition.com',
        'placeholder' => 'support@example.com',
        'conditional_logic' => [
          [
            [
              'field' => 'field_show_contact_cards',
              'operator' => '==',
              'value' => '1',
            ],
          ],
        ],
      ],
      [
        'key' => 'field_contact_phone',
        'label' => __('Phone Number', 'nera-competitions'),
        'name' => 'contact_phone',
        'type' => 'text',
        'instructions' => __('Primary contact phone number', 'nera-competitions'),
        'default_value' => '+1 (555) 012-3456',
        'placeholder' => '+1 (555) 123-4567',
        'conditional_logic' => [
          [
            [
              'field' => 'field_show_contact_cards',
              'operator' => '==',
              'value' => '1',
            ],
          ],
        ],
      ],

      // Form Section Tab
      [
        'key' => 'field_contact_tab_form',
        'label' => __('Form Section', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_form_heading',
        'label' => __('Form Heading', 'nera-competitions'),
        'name' => 'form_heading',
        'type' => 'text',
        'instructions' => __('Heading above the contact form', 'nera-competitions'),
        'default_value' => __('Send Us a Message', 'nera-competitions'),
        'placeholder' => __('Send Us a Message', 'nera-competitions'),
      ],
      [
        'key' => 'field_form_description',
        'label' => __('Form Description', 'nera-competitions'),
        'name' => 'form_description',
        'type' => 'textarea',
        'instructions' => __('Optional text above the form', 'nera-competitions'),
        'rows' => 2,
      ],
      [
        'key' => 'field_fluent_form_id',
        'label' => __('Fluent Form ID', 'nera-competitions'),
        'name' => 'fluent_form_id',
        'type' => 'number',
        'instructions' => __(
          'Enter the ID of your Fluent Form. Find this in Fluent Forms > All Forms.',
          'nera-competitions',
        ),
        'placeholder' => '1',
        'min' => 1,
      ],
      [
        'key' => 'field_form_success_message',
        'label' => __('Custom Success Message', 'nera-competitions'),
        'name' => 'form_success_message',
        'type' => 'textarea',
        'instructions' => __(
          'Optional custom message shown after successful form submission (leave empty to use Fluent Forms default)',
          'nera-competitions',
        ),
        'rows' => 2,
      ],

      // Social Media Tab
      [
        'key' => 'field_contact_tab_social',
        'label' => __('Social Media', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_social_links_note',
        'label' => __('Social Links Configuration', 'nera-competitions'),
        'type' => 'message',
        'message' => __(
          'Social media links are managed in <strong>Appearance > Customize > Footer Options</strong>. They will automatically appear in the footer of this page.',
          'nera-competitions',
        ),
      ],
    ],
    'location' => [
      [
        [
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'page-templates/contact-template.php',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => ['the_content', 'featured_image'],
    'active' => true,
    'description' => __('Custom fields for the Contact page template', 'nera-competitions'),
  ]);
}
add_action('acf/init', 'nera_register_contact_fields');
