<?php
/**
 * ACF Field Group: Nera About Us Page
 *
 * Registers fields for the Nera About Us page template.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

/**
 * Register ACF field group for About Us template
 */
function nera_register_about_us_fields()
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_about_us_page',
    'title' => __('About Us Page Content', 'nera-competitions'),
    'fields' => [
      [
        'key' => 'field_about_tab_hero',
        'label' => __('Hero', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_about_hero_eyebrow',
        'label' => __('Eyebrow label', 'nera-competitions'),
        'name' => 'about_hero_eyebrow',
        'type' => 'text',
        'instructions' => __('Small label above the main heading.', 'nera-competitions'),
        'default_value' => __('About us', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_title',
        'label' => __('Heading', 'nera-competitions'),
        'name' => 'about_title',
        'type' => 'text',
        'instructions' => __('Defaults to the page title if empty.', 'nera-competitions'),
        'placeholder' => __('About us', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_hero_tagline',
        'label' => __('Tagline', 'nera-competitions'),
        'name' => 'about_hero_tagline',
        'type' => 'textarea',
        'rows' => 3,
        'default_value' => __(
          'Building a community rooted in transparency, trust, and exciting opportunities for everyone.',
          'nera-competitions',
        ),
      ],
      [
        'key' => 'field_about_hero_image',
        'label' => __('Hero image', 'nera-competitions'),
        'name' => 'about_hero_image',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
        'library' => 'all',
      ],

      [
        'key' => 'field_about_tab_narrative',
        'label' => __('Narrative', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_about_narrative',
        'label' => __('Main content', 'nera-competitions'),
        'name' => 'about_narrative',
        'type' => 'wysiwyg',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 1,
      ],

      [
        'key' => 'field_about_tab_stories',
        'label' => __('Two columns', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_about_story_left_title',
        'label' => __('Left column title', 'nera-competitions'),
        'name' => 'about_story_left_title',
        'type' => 'text',
        'default_value' => __('Our story', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_story_left_content',
        'label' => __('Left column content', 'nera-competitions'),
        'name' => 'about_story_left_content',
        'type' => 'wysiwyg',
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 0,
      ],
      [
        'key' => 'field_about_story_right_title',
        'label' => __('Right column title', 'nera-competitions'),
        'name' => 'about_story_right_title',
        'type' => 'text',
        'default_value' => __('What drives us', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_story_right_content',
        'label' => __('Right column content', 'nera-competitions'),
        'name' => 'about_story_right_content',
        'type' => 'wysiwyg',
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 0,
      ],

      [
        'key' => 'field_about_tab_cta',
        'label' => __('Call to action', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_about_cta_heading',
        'label' => __('Heading', 'nera-competitions'),
        'name' => 'about_cta_heading',
        'type' => 'text',
        'default_value' => __('Join the community', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_cta_description',
        'label' => __('Description', 'nera-competitions'),
        'name' => 'about_cta_description',
        'type' => 'textarea',
        'rows' => 3,
        'default_value' => __(
          'Be part of a transparent, supportive journey where everyone has a chance to win.',
          'nera-competitions',
        ),
      ],
      [
        'key' => 'field_about_cta_primary_btn_text',
        'label' => __('Primary button label', 'nera-competitions'),
        'name' => 'about_cta_primary_btn_text',
        'type' => 'text',
        'default_value' => __('Explore competitions', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_cta_primary_btn_url',
        'label' => __('Primary button URL', 'nera-competitions'),
        'name' => 'about_cta_primary_btn_url',
        'type' => 'url',
        'placeholder' => '/shop/',
      ],
      [
        'key' => 'field_about_cta_secondary_btn_text',
        'label' => __('Secondary button label', 'nera-competitions'),
        'name' => 'about_cta_secondary_btn_text',
        'type' => 'text',
        'default_value' => __('Get in touch', 'nera-competitions'),
      ],
      [
        'key' => 'field_about_cta_secondary_btn_url',
        'label' => __('Secondary button URL', 'nera-competitions'),
        'name' => 'about_cta_secondary_btn_url',
        'type' => 'url',
        'placeholder' => '/contact/',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'page-templates/about-us-template.php',
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
    'description' => __('Content for the Nera About Us page template.', 'nera-competitions'),
  ]);
}
add_action('acf/init', 'nera_register_about_us_fields');
