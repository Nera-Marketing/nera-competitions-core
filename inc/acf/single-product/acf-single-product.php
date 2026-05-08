<?php
/**
 * Advanced Custom Fields - Single Product Competition Settings
 *
 * Registers the ACF field group for lottery/competition product customization.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_single_product_competition',
    'title' => 'Single Product - Competition Settings',
    'fields' => [
      // ========================================
      // Tab: Template
      // ========================================
      [
        'key' => 'field_sp_tab_template',
        'label' => 'Template',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_sp_template_style',
        'label' => 'Template Style',
        'name' => 'competition_template_style',
        'type' => 'select',
        'instructions' => 'Choose the layout style for this competition page.',
        'choices' => [
          'default' => 'Default (Standard Layout)',
          'hybrid-premium' => 'Hybrid Premium (Unified Hero)',
        ],
        'default_value' => 'default',
        'ui' => 1,
      ],
      [
        'key' => 'field_sp_product_specifications',
        'label' => 'Product Specifications',
        'name' => 'product_specifications',
        'type' => 'repeater',
        'instructions' => 'Add product specifications (displayed in Hybrid Premium template).',
        'layout' => 'table',
        'button_label' => 'Add Specification',
        'max' => 8,
        'sub_fields' => [
          [
            'key' => 'field_sp_spec_label',
            'label' => 'Label',
            'name' => 'label',
            'type' => 'text',
            'placeholder' => 'Engine',
            'wrapper' => ['width' => '40'],
          ],
          [
            'key' => 'field_sp_spec_value',
            'label' => 'Value',
            'name' => 'value',
            'type' => 'text',
            'placeholder' => '4.0L V8 Twin-Turbo',
            'wrapper' => ['width' => '60'],
          ],
        ],
        'conditional_logic' => [
          [
            [
              'field' => 'field_sp_template_style',
              'operator' => '==',
              'value' => 'hybrid-premium',
            ],
          ],
        ],
      ],

      // ========================================
      // Tab: Gallery
      // ========================================
      [
        'key' => 'field_sp_tab_gallery',
        'label' => 'Gallery',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_sp_gallery_badge_text',
        'label' => 'Badge Text',
        'name' => 'gallery_badge_text',
        'type' => 'text',
        'instructions' =>
          'Optional badge overlay text on the gallery (e.g., "Hot", "Limited", "Selling Fast").',
        'placeholder' => 'e.g., Limited Edition',
      ],
      [
        'key' => 'field_sp_gallery_badge_color',
        'label' => 'Badge Color',
        'name' => 'gallery_badge_color',
        'type' => 'select',
        'instructions' => 'Choose the badge color scheme.',
        'choices' => [
          'primary' => 'Primary (Brand Color)',
          'success' => 'Success (Green)',
          'danger' => 'Danger (Red)',
          'warning' => 'Warning (Orange)',
        ],
        'default_value' => 'primary',
        'conditional_logic' => [
          [
            [
              'field' => 'field_sp_gallery_badge_text',
              'operator' => '!=empty',
            ],
          ],
        ],
      ],
      [
        'key' => 'field_sp_gallery_video_url',
        'label' => 'Video URL',
        'name' => 'gallery_video_url',
        'type' => 'url',
        'instructions' => 'Optional video URL (YouTube or Vimeo) to display in the gallery.',
        'placeholder' => 'https://www.youtube.com/watch?v=...',
      ],

      // ========================================
      // Tab: Info Icons
      // ========================================
      [
        'key' => 'field_sp_tab_info_icons',
        'label' => 'Info Icons',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_sp_show_tickets_available',
        'label' => 'Show Tickets Available',
        'name' => 'show_tickets_available',
        'type' => 'true_false',
        'instructions' => 'Display the number of tickets available.',
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_sp_show_max_per_user',
        'label' => 'Show Max Per User',
        'name' => 'show_max_per_user',
        'type' => 'true_false',
        'instructions' => 'Display the maximum tickets allowed per user.',
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_sp_show_draw_date',
        'label' => 'Show Draw Date',
        'name' => 'show_draw_date',
        'type' => 'true_false',
        'instructions' => 'Display the draw date.',
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_sp_custom_info_icons',
        'label' => 'Custom Info Icons',
        'name' => 'custom_info_icons',
        'type' => 'repeater',
        'instructions' => 'Add custom info icons to display additional information.',
        'layout' => 'table',
        'button_label' => 'Add Icon',
        'max' => 6,
        'sub_fields' => [
          [
            'key' => 'field_sp_custom_icon',
            'label' => 'Icon',
            'name' => 'icon',
            'type' => 'text',
            'instructions' => 'Material Symbol icon name (e.g., "schedule", "person", "star").',
            'placeholder' => 'schedule',
            'wrapper' => ['width' => '25'],
          ],
          [
            'key' => 'field_sp_custom_label',
            'label' => 'Label',
            'name' => 'label',
            'type' => 'text',
            'placeholder' => 'Draw Time',
            'wrapper' => ['width' => '35'],
          ],
          [
            'key' => 'field_sp_custom_value',
            'label' => 'Value',
            'name' => 'value',
            'type' => 'text',
            'placeholder' => '8:00 PM',
            'wrapper' => ['width' => '40'],
          ],
        ],
      ],

      // ========================================
      // Tab: Trust Elements
      // ========================================
      [
        'key' => 'field_sp_tab_trust',
        'label' => 'Trust Elements',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_sp_show_payment_logos',
        'label' => 'Show Payment Logos',
        'name' => 'show_payment_logos',
        'type' => 'true_false',
        'instructions' => 'Display payment method logos (Visa, Mastercard, etc.).',
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_sp_payment_logos',
        'label' => 'Payment Logos',
        'name' => 'payment_logos',
        'type' => 'gallery',
        'instructions' => 'Upload payment method logos. Leave empty to use defaults.',
        'return_format' => 'array',
        'preview_size' => 'thumbnail',
        'library' => 'all',
        'max' => 8,
        'conditional_logic' => [
          [
            [
              'field' => 'field_sp_show_payment_logos',
              'operator' => '==',
              'value' => 1,
            ],
          ],
        ],
      ],
      [
        'key' => 'field_sp_show_trustpilot',
        'label' => 'Show Trustpilot Badge',
        'name' => 'show_trustpilot',
        'type' => 'true_false',
        'instructions' => 'Display Trustpilot score badge.',
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_sp_trustpilot_score',
        'label' => 'Trustpilot Score',
        'name' => 'trustpilot_score',
        'type' => 'text',
        'instructions' => 'Trustpilot rating score (e.g., "4.8").',
        'default_value' => '4.8',
        'placeholder' => '4.8',
        'conditional_logic' => [
          [
            [
              'field' => 'field_sp_show_trustpilot',
              'operator' => '==',
              'value' => 1,
            ],
          ],
        ],
      ],
      [
        'key' => 'field_sp_trustpilot_reviews',
        'label' => 'Trustpilot Reviews Count',
        'name' => 'trustpilot_reviews',
        'type' => 'text',
        'instructions' => 'Number of reviews (e.g., "1,250").',
        'default_value' => '1,250',
        'placeholder' => '1,250',
        'conditional_logic' => [
          [
            [
              'field' => 'field_sp_show_trustpilot',
              'operator' => '==',
              'value' => 1,
            ],
          ],
        ],
      ],
      [
        'key' => 'field_sp_trust_badges',
        'label' => 'Trust Badges',
        'name' => 'trust_badges',
        'type' => 'repeater',
        'instructions' =>
          'Add custom trust badges (e.g., "Secure Checkout", "Money Back Guarantee").',
        'layout' => 'table',
        'button_label' => 'Add Badge',
        'max' => 4,
        'sub_fields' => [
          [
            'key' => 'field_sp_trust_icon',
            'label' => 'Icon',
            'name' => 'icon',
            'type' => 'text',
            'instructions' => 'Material Symbol icon name.',
            'placeholder' => 'verified_user',
            'wrapper' => ['width' => '30'],
          ],
          [
            'key' => 'field_sp_trust_text',
            'label' => 'Text',
            'name' => 'text',
            'type' => 'text',
            'placeholder' => 'Secure Checkout',
            'wrapper' => ['width' => '70'],
          ],
        ],
      ],

      // ========================================
      // Tab: Content
      // ========================================
      [
        'key' => 'field_sp_tab_content',
        'label' => 'Content',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_sp_competition_rules',
        'label' => 'Competition Rules',
        'name' => 'competition_rules',
        'type' => 'wysiwyg',
        'instructions' =>
          'Enter the competition rules and terms. This will appear in the "Rules" tab.',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 0,
      ],
      [
        'key'               => 'field_sp_stw_how_it_works_copy',
        'label'             => 'Spin to Win — How It Works Copy',
        'name'              => 'stw_how_it_works_copy',
        'type'              => 'textarea',
        'instructions'      => 'Text shown in the "How it works" tab. Only applies to products in the "spin-to-win" category.',
        'required'          => 0,
        'conditional_logic' => 0,
        'default_value'     => "Purchase your tickets and you'll see a Spin the Wheel button on your order confirmation. Click it, then use the Spin and Turbo Spin buttons to play. Your result is revealed instantly.",
        'placeholder'       => '',
        'rows'              => 4,
        'new_lines'         => 'wpautop',
      ],
      [
        'key' => 'field_sp_product_faqs',
        'label' => 'Product FAQs',
        'name' => 'product_faqs',
        'type' => 'repeater',
        'instructions' => 'Add frequently asked questions specific to this product.',
        'layout' => 'block',
        'button_label' => 'Add FAQ',
        'sub_fields' => [
          [
            'key' => 'field_sp_faq_question',
            'label' => 'Question',
            'name' => 'question',
            'type' => 'text',
            'placeholder' => 'How do I enter this competition?',
          ],
          [
            'key' => 'field_sp_faq_answer',
            'label' => 'Answer',
            'name' => 'answer',
            'type' => 'textarea',
            'rows' => 3,
            'placeholder' => 'Simply select your desired number of tickets...',
          ],
        ],
      ],

      [
        'key' => 'field_sp_show_entry_list_tab',
        'label' => 'Show Entry List Tab',
        'name' => 'show_entry_list_tab',
        'type' => 'true_false',
        'instructions' => 'Toggle the Entry List tab on the product page.',
        'default_value' => 1,
        'ui' => 1,
        'ui_on_text' => 'Visible',
        'ui_off_text' => 'Hidden',
      ],

      // ========================================
      // Tab: Related Products
      // ========================================
      [
        'key' => 'field_sp_tab_related',
        'label' => 'Related',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
      ],
      [
        'key' => 'field_sp_related_section_title',
        'label' => 'Related Section Title',
        'name' => 'related_section_title',
        'type' => 'text',
        'instructions' => 'Title for the related competitions section.',
        'default_value' => 'More Competitions You Might Like',
        'placeholder' => 'More Competitions You Might Like',
      ],
      [
        'key' => 'field_sp_related_products_manual',
        'label' => 'Manual Related Products',
        'name' => 'related_products_manual',
        'type' => 'relationship',
        'instructions' => 'Manually select related products. Leave empty to auto-populate.',
        'post_type' => ['product'],
        'filters' => ['search', 'taxonomy'],
        'taxonomy' => ['product_cat'],
        'return_format' => 'id',
        'min' => 0,
        'max' => 8,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'product',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
    'description' => 'Competition-specific settings for single product pages.',
  ]);

  add_filter( 'acf/prepare_field/key=field_sp_stw_how_it_works_copy', function( $field ) {
    $post_id = get_the_ID();
    if ( $post_id && ! has_term( 'spin-to-win', 'product_cat', $post_id ) ) {
      return false;
    }
    return $field;
  } );
}
