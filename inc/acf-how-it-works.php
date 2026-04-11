<?php
/**
 * ACF Field Group: How It Works Page
 *
 * Registers fields for the How It Works page template.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Register ACF field group for How It Works page
 */
function nera_register_how_it_works_fields()
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_how_it_works_page',
    'title' => __('How It Works Page Content', 'nera-competitions'),
    'fields' => [
      [
        'key' => 'field_hiw_tab_hero',
        'label' => __('Hero (4 steps)', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_hiw_hero_title',
        'label' => __('Hero Title', 'nera-competitions'),
        'name' => 'hiw_hero_title',
        'type' => 'text',
        'instructions' => __('Main heading above the step cards. Defaults to the page title.', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_hero_subtitle',
        'label' => __('Hero Subtitle', 'nera-competitions'),
        'name' => 'hiw_hero_subtitle',
        'type' => 'textarea',
        'rows' => 2,
      ],
      [
        'key' => 'field_hiw_hero_badge',
        'label' => __('Hero Badge', 'nera-competitions'),
        'name' => 'hiw_hero_badge',
        'type' => 'text',
        'instructions' => __('Short label next to the pulse dot (e.g. Simple & Fair).', 'nera-competitions'),
        'default_value' => __('Simple & Fair', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_tab_draw',
        'label' => __('Draw Process', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_hiw_draw_title',
        'label' => __('Section Title', 'nera-competitions'),
        'name' => 'hiw_draw_title',
        'type' => 'text',
        'default_value' => __('The Draw Process', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_draw_content',
        'label' => __('Content', 'nera-competitions'),
        'name' => 'hiw_draw_content',
        'type' => 'wysiwyg',
        'instructions' => __('Leave empty to use the built-in default copy.', 'nera-competitions'),
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 1,
      ],
      [
        'key' => 'field_hiw_draw_image',
        'label' => __('Image', 'nera-competitions'),
        'name' => 'hiw_draw_image',
        'type' => 'image',
        'return_format' => 'array',
        'preview_size' => 'medium',
        'library' => 'all',
      ],
      [
        'key' => 'field_hiw_tab_postal',
        'label' => __('Postal Entry', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_hiw_postal_title',
        'label' => __('Section Title', 'nera-competitions'),
        'name' => 'hiw_postal_title',
        'type' => 'text',
        'default_value' => __('Free Postal Entry Route', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_postal_steps',
        'label' => __('Steps', 'nera-competitions'),
        'name' => 'hiw_postal_steps',
        'type' => 'repeater',
        'instructions' => __(
          'Add at least one step with body text. Empty repeaters fall back to default copy.',
          'nera-competitions',
        ),
        'layout' => 'block',
        'button_label' => __('Add Step', 'nera-competitions'),
        'sub_fields' => [
          [
            'key' => 'field_hiw_postal_step_number',
            'label' => __('Step Number', 'nera-competitions'),
            'name' => 'number',
            'type' => 'text',
            'placeholder' => '1',
          ],
          [
            'key' => 'field_hiw_postal_step_title',
            'label' => __('Title', 'nera-competitions'),
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'field_hiw_postal_step_icon',
            'label' => __('Material Icon Name', 'nera-competitions'),
            'name' => 'icon',
            'type' => 'text',
            'instructions' => __(
              'Material SymbolsOutlined ligature name, e.g. mail, contact_page, verified_user.',
              'nera-competitions',
            ),
            'placeholder' => 'mail',
          ],
          [
            'key' => 'field_hiw_postal_step_text',
            'label' => __('Text', 'nera-competitions'),
            'name' => 'text',
            'type' => 'textarea',
            'rows' => 3,
          ],
        ],
      ],
      [
        'key' => 'field_hiw_postal_note',
        'label' => __('Footnote', 'nera-competitions'),
        'name' => 'hiw_postal_note',
        'type' => 'textarea',
        'rows' => 2,
      ],
      [
        'key' => 'field_hiw_tab_transparency',
        'label' => __('Transparency', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_hiw_transparency_title',
        'label' => __('Section Title', 'nera-competitions'),
        'name' => 'hiw_transparency_title',
        'type' => 'text',
        'default_value' => __('Transparency & Fairness', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_transparency_features',
        'label' => __('Features', 'nera-competitions'),
        'name' => 'hiw_transparency_features',
        'type' => 'repeater',
        'layout' => 'row',
        'button_label' => __('Add Feature', 'nera-competitions'),
        'sub_fields' => [
          [
            'key' => 'field_hiw_transparency_icon',
            'label' => __('Material Icon Name', 'nera-competitions'),
            'name' => 'icon',
            'type' => 'text',
            'placeholder' => 'verified_user',
          ],
          [
            'key' => 'field_hiw_transparency_feature_title',
            'label' => __('Title', 'nera-competitions'),
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'field_hiw_transparency_feature_description',
            'label' => __('Description', 'nera-competitions'),
            'name' => 'description',
            'type' => 'textarea',
            'rows' => 3,
          ],
        ],
      ],
    ],
    'location' => [
      [
        [
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'page-templates/how-it-works-template.php',
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
    'description' => __('Custom fields for the How It Works page template', 'nera-competitions'),
  ]);
}
add_action('acf/init', 'nera_register_how_it_works_fields');
