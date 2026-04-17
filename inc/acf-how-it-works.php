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
        'key' => 'field_hiw_hero_steps',
        'label' => __('Hero Steps', 'nera-competitions'),
        'name' => 'hiw_hero_steps',
        'type' => 'repeater',
        'instructions' => __(
          'Up to four steps. Leave empty to use built-in copy and icons. Card gradients follow row order (1–4). Optionally upload a step icon (PNG, SVG, or WebP); leave empty to use the default SVG for that position.',
          'nera-competitions',
        ),
        'layout' => 'row',
        'button_label' => __('Add Step', 'nera-competitions'),
        'min' => 0,
        'max' => 4,
        'sub_fields' => [
          [
            'key' => 'field_hiw_hero_step_title',
            'label' => __('Title', 'nera-competitions'),
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'field_hiw_hero_step_description',
            'label' => __('Description', 'nera-competitions'),
            'name' => 'description',
            'type' => 'textarea',
            'rows' => 3,
          ],
          [
            'key' => 'field_hiw_hero_step_icon',
            'label' => __('Step icon', 'nera-competitions'),
            'name' => 'step_icon',
            'type' => 'image',
            'instructions' => __(
              'Optional. Upload PNG, SVG, or WebP. If empty, the theme uses the built-in icon for this step order.',
              'nera-competitions',
            ),
            'return_format' => 'array',
            'preview_size' => 'thumbnail',
            'library' => 'all',
            'mime_types' => 'png,jpg,jpeg,webp,svg',
          ],
        ],
      ],
      [
        'key' => 'field_hiw_cta_button_link',
        'label' => __('CTA Button', 'nera-competitions'),
        'name' => 'hiw_cta_button_link',
        'type' => 'link',
        'return_format' => 'array',
        'instructions' => __(
          'URL, label, and optional “open in new tab”. Leave empty to use the default label (“Start Winning Today”) and the WooCommerce shop URL (or site home if the shop page is not set).',
          'nera-competitions',
        ),
      ],
      [
        'key' => 'field_hiw_cta_footer_text',
        'label' => __('CTA Footer Line', 'nera-competitions'),
        'name' => 'hiw_cta_footer_text',
        'type' => 'text',
        'instructions' => __('Small text under the button (e.g. trust line).', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_tab_draw',
        'label' => __('Draw Process', 'nera-competitions'),
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_hiw_draw_eyebrow',
        'label' => __('Eyebrow Label', 'nera-competitions'),
        'name' => 'hiw_draw_eyebrow',
        'type' => 'text',
        'instructions' => __('Small uppercase label above the section title.', 'nera-competitions'),
        'default_value' => __('Fair & Transparent', 'nera-competitions'),
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
        'default_value' =>
          '<p>' .
          __(
            'Our draws are conducted with absolute transparency. We use the <strong>Google Random Number Generator</strong> to ensure every entry has an equal and fair chance of winning.',
            'nera-competitions',
          ) .
          '</p><p>' .
          __(
            'Join us live on our social media channels for every draw! We broadcast the entire process in real-time, announcing winners as they happen and celebrating with our community.',
            'nera-competitions',
          ) .
          '</p>',
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
        'key' => 'field_hiw_draw_placeholder_title',
        'label' => __('No-Image Placeholder — Title', 'nera-competitions'),
        'name' => 'hiw_draw_placeholder_title',
        'type' => 'text',
        'instructions' => __('Shown in the image area when no image is set.', 'nera-competitions'),
        'default_value' => __('Live Draw Streams', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_draw_placeholder_text',
        'label' => __('No-Image Placeholder — Subtitle', 'nera-competitions'),
        'name' => 'hiw_draw_placeholder_text',
        'type' => 'text',
        'default_value' => __('Watch us live on Facebook and Instagram', 'nera-competitions'),
      ],
      [
        'key' => 'field_hiw_draw_placeholder_icon',
        'label' => __('No-Image Placeholder — Material Icon', 'nera-competitions'),
        'name' => 'hiw_draw_placeholder_icon',
        'type' => 'text',
        'instructions' => __('Material SymbolsOutlined ligature name (e.g. videocam).', 'nera-competitions'),
        'default_value' => 'videocam',
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
        'key' => 'field_hiw_postal_intro',
        'label' => __('Intro Text', 'nera-competitions'),
        'name' => 'hiw_postal_intro',
        'type' => 'textarea',
        'rows' => 2,
        'instructions' => __('Line of text below the section heading.', 'nera-competitions'),
        'default_value' => __(
          'We offer a free entry route via post for all of our competitions.',
          'nera-competitions',
        ),
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
        'key' => 'field_hiw_transparency_subtitle',
        'label' => __('Intro Text', 'nera-competitions'),
        'name' => 'hiw_transparency_subtitle',
        'type' => 'textarea',
        'rows' => 3,
        'instructions' => __('Paragraph below the section heading.', 'nera-competitions'),
        'default_value' => __(
          'We pride ourselves on being a registered UK business that operates with full integrity and a passion for giving back.',
          'nera-competitions',
        ),
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
