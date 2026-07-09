<?php
/**
 * Advanced Custom Fields - Product Listing Field Group
 *
 * Registers the "Product Listing Group" for the Nera Product Listing template.
 *
 * @package Nera_Competitions
 */

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_product_listing',
    'title' => 'Product Listing Group',
    'fields' => [
      // Tab: Trust Features
      [
        'key' => 'field_tab_trust',
        'label' => 'Trust Features',
        'name' => '',
        'type' => 'tab',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'placement' => 'top',
        'endpoint' => 0,
      ],
      [
        'key' => 'field_trust_title',
        'label' => 'Section Title',
        'name' => 'trust_title',
        'type' => 'text',
        'instructions' => 'Main heading for the Trust Features section.',
        'default_value' => 'Why Choose Us',
      ],
      [
        'key' => 'field_trust_subtitle',
        'label' => 'Section Subtitle',
        'name' => 'trust_subtitle',
        'type' => 'textarea',
        'instructions' => 'Subtitle description.',
        'default_value' =>
          'Join thousands of happy winners who trust us for fair and exciting competitions.',
        'rows' => 2,
      ],
      [
        'key' => 'field_trust_badges',
        'label' => 'Trust Badges',
        'name' => 'trust_badges',
        'type' => 'repeater',
        'instructions' => 'Add trust badges/features here.',
        'layout' => 'block',
        'button_label' => 'Add Feature',
        'sub_fields' => [
          [
            'key' => 'field_trust_icon',
            'label' => 'Icon (SVG)',
            'name' => 'icon',
            'type' => 'textarea',
            'instructions' => 'Paste the SVG code for the icon.',
            'rows' => 4,
          ],
          [
            'key' => 'field_trust_badge_title',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'field_trust_badge_desc',
            'label' => 'Description',
            'name' => 'description',
            'type' => 'textarea',
            'rows' => 2,
          ],
        ],
      ],
      // Tab: Empty state
      [
        'key' => 'field_tab_product_listing_empty',
        'label' => 'Empty state',
        'name' => '',
        'type' => 'tab',
        'placement' => 'top',
        'endpoint' => 0,
      ],
      [
        'key' => 'field_product_listing_empty_heading',
        'label' => 'Heading',
        'name' => 'product_listing_empty_heading',
        'type' => 'text',
        'instructions' => 'Shown on this page when there are no competitions at all.',
        'default_value' => 'No competitions found',
        'placeholder' => 'No competitions found',
      ],
      [
        'key' => 'field_product_listing_empty_description',
        'label' => 'Description',
        'name' => 'product_listing_empty_description',
        'type' => 'textarea',
        'instructions' => 'Supporting text below the empty-state heading.',
        'default_value' => 'Check back soon for new amazing prizes!',
        'rows' => 3,
        'new_lines' => '',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'page_template',
          'operator' => '==',
          'value' => 'page-templates/product-listing-template.php',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => [
      0 => 'the_content',
    ],
    'active' => true,
    'description' => '',
  ]);
}
