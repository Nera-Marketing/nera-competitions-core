<?php
/**
 * Advanced Custom Fields - Homepage Field Group
 *
 * Registers the "Home Page Group" for the Nera Homepage template.
 *
 * @package Nera_Competitions
 */

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_homepage_content',
    'title' => 'Home Page Group',
    'fields' => [
      // Tab: Section Layout
      [
        'key' => 'field_tab_section_layout',
        'label' => 'Section Layout',
        'name' => '',
        'type' => 'tab',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'placement' => 'top',
        'endpoint' => 0,
      ],
      [
        'key' => 'field_homepage_sections',
        'label' => 'Homepage Sections',
        'name' => 'homepage_sections',
        'type' => 'repeater',
        'instructions' =>
          'Drag to reorder sections. Toggle visibility with the switch. Leave empty to use default order with all sections visible.',
        'layout' => 'table',
        'button_label' => 'Add Section',
        'sub_fields' => [
          [
            'key' => 'field_section_slug',
            'label' => 'Section',
            'name' => 'section',
            'type' => 'select',
            'choices' => [
              'hero' => 'Hero',
              'credibility' => 'Credibility / Trust Bar',
              'stats' => 'Stats',
              'featured_competitions' => 'Ending Soon',
              'promo_banner' => 'Follow Us on Socials',
              'testimonials' => 'Testimonials',
              'winners' => 'Winners',
              'quick_guide' => 'How to Play',
              'about' => 'About / Who We Are',
              'categories' => 'Categories',
              'faq' => 'FAQ',
            ],
            'required' => 1,
          ],
          [
            'key' => 'field_section_visible',
            'label' => 'Visible',
            'name' => 'show_section',
            'type' => 'true_false',
            'default_value' => 1,
            'ui' => 1,
            'ui_on_text' => 'Show',
            'ui_off_text' => 'Hide',
          ],
        ],
      ],

      // Tab: Hero Section
      [
        'key' => 'field_tab_hero',
        'label' => 'Hero Section',
        'name' => '',
        'type' => 'tab',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'placement' => 'top',
        'endpoint' => 0,
      ],
      [
        'key' => 'field_hero_title',
        'label' => 'Title',
        'name' => 'hero_title',
        'type' => 'text',
        'instructions' => 'Main heading for the hero section.',
        'default_value' => 'Win Your Dream',
      ],
      [
        'key' => 'field_hero_highlight',
        'label' => 'Highlight Text',
        'name' => 'hero_highlight',
        'type' => 'text',
        'instructions' => 'Highlighted part of the title (gradient text).',
        'default_value' => 'Lifestyle.',
      ],
      [
        'key' => 'field_hero_description',
        'label' => 'Description',
        'name' => 'hero_description',
        'type' => 'textarea',
        'instructions' => 'Subtitle description.',
        'default_value' =>
          'Experience the thrill of high-end giveaways with the UK\'s most exclusive prize competition platform.',
        'rows' => 3,
      ],
      [
        'key' => 'field_hero_cta_text',
        'label' => 'Primary CTA Text',
        'name' => 'hero_cta_text',
        'type' => 'text',
        'default_value' => 'View Active Giveaways',
      ],
      [
        'key' => 'field_hero_cta_url',
        'label' => 'Primary CTA URL',
        'name' => 'hero_cta_url',
        'type' => 'text',
        'default_value' => '/shop/',
      ],
      [
        'key' => 'field_hero_secondary_text',
        'label' => 'Secondary CTA Text',
        'name' => 'hero_secondary_text',
        'type' => 'text',
        'default_value' => 'Recent Winners',
      ],
      [
        'key' => 'field_hero_secondary_url',
        'label' => 'Secondary CTA URL',
        'name' => 'hero_secondary_url',
        'type' => 'text',
        'default_value' => '#winners',
      ],
      [
        'key' => 'field_hero_image',
        'label' => 'Hero Image',
        'name' => 'hero_image',
        'type' => 'image',
        'return_format' => 'url',
        'preview_size' => 'medium',
        'library' => 'all',
      ],
      [
        'key' => 'field_last_winner_name',
        'label' => 'Last Winner Name',
        'name' => 'last_winner_name',
        'type' => 'text',
        'default_value' => 'Sarah M.',
      ],
      [
        'key' => 'field_last_winner_prize',
        'label' => 'Last Winner Prize',
        'name' => 'last_winner_prize',
        'type' => 'text',
        'default_value' => 'Won This Prize',
      ],

      // Tab: Credibility Section
      [
        'key' => 'field_tab_credibility',
        'label' => 'Credibility Section',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_credibility_items',
        'label' => 'Trust Items',
        'name' => 'credibility_items',
        'type' => 'repeater',
        'instructions' =>
          'Add trust/credibility items shown in the dark bar under the hero. Uses Material Symbols icon names (e.g., "lock", "verified", "visibility"). Leave empty to use defaults.',
        'layout' => 'table',
        'button_label' => 'Add Item',
        'max' => 6,
        'sub_fields' => [
          [
            'key' => 'field_credibility_icon',
            'label' => 'Icon',
            'name' => 'icon',
            'type' => 'text',
            'instructions' => 'Material Symbol name (e.g., "lock", "verified", "emoji_events")',
            'default_value' => 'check_circle',
          ],
          [
            'key' => 'field_credibility_label',
            'label' => 'Label',
            'name' => 'label',
            'type' => 'text',
            'instructions' => 'Short trust label (e.g., "Secure Payments")',
          ],
        ],
      ],

      // Tab: Stats Section (hidden — template commented out in homepage-template.php)
      // array(
      //   'key' => 'field_tab_stats',
      //   'label' => 'Stats Section',
      //   'name' => '',
      //   'type' => 'tab',
      // ),
      // array(
      //   'key' => 'field_stat_winners',
      //   'label' => 'Total Winners',
      //   'name' => 'stat_winners',
      //   'type' => 'number',
      //   'default_value' => 150,
      // ),
      // array(
      //   'key' => 'field_stat_value',
      //   'label' => 'Total Value Given (Millions)',
      //   'name' => 'stat_value',
      //   'type' => 'number',
      //   'instructions' => 'e.g., 2 for £2M+',
      //   'default_value' => 2,
      // ),
      // array(
      //   'key' => 'field_stat_secure',
      //   'label' => 'Secure Entry %',
      //   'name' => 'stat_secure',
      //   'type' => 'number',
      //   'default_value' => 100,
      // ),
      // array(
      //   'key' => 'field_tp_score',
      //   'label' => 'Trustpilot Score',
      //   'name' => 'tp_score',
      //   'type' => 'text',
      //   'default_value' => '4.8',
      // ),
      // array(
      //   'key' => 'field_tp_reviews',
      //   'label' => 'Trustpilot Reviews Count',
      //   'name' => 'tp_reviews',
      //   'type' => 'text',
      //   'default_value' => '1,250',
      // ),

      // Tab: Featured Competitions (Ending Soon)
      [
        'key' => 'field_tab_featured',
        'label' => 'Ending Soon',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_featured_title',
        'label' => 'Section Title',
        'name' => 'featured_title',
        'type' => 'text',
        'default_value' => 'Ending Soon',
      ],
      [
        'key' => 'field_featured_subtitle',
        'label' => 'Section Subtitle',
        'name' => 'featured_subtitle',
        'type' => 'text',
        'default_value' => 'Grab your tickets before they\'re gone forever.',
      ],

      // Tab: Follow Us on Socials
      [
        'key' => 'field_tab_promo',
        'label' => 'Follow Us on Socials',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_promo_badge',
        'label' => 'Badge Text',
        'name' => 'promo_badge',
        'type' => 'text',
        'default_value' => 'Stay Connected',
      ],
      [
        'key' => 'field_promo_title',
        'label' => 'Title',
        'name' => 'promo_title',
        'type' => 'text',
        'default_value' => 'Follow us on socials',
      ],
      [
        'key' => 'field_promo_description',
        'label' => 'Description',
        'name' => 'promo_description',
        'type' => 'textarea',
        'rows' => 3,
        'default_value' => 'Follow us for updates, new competitions and giveaways.',
      ],
      [
        'key' => 'field_promo_social_links',
        'label' => 'Social Links',
        'name' => 'promo_social_links',
        'type' => 'repeater',
        'instructions' => 'Add your social media links. Choose a platform and enter the URL.',
        'layout' => 'table',
        'button_label' => 'Add Social Link',
        'sub_fields' => [
          [
            'key' => 'field_social_platform',
            'label' => 'Platform',
            'name' => 'platform',
            'type' => 'select',
            'choices' => [
              'facebook' => 'Facebook',
              'instagram' => 'Instagram',
              'twitter' => 'Twitter/X',
              'youtube' => 'YouTube',
              'tiktok' => 'TikTok',
            ],
          ],
          [
            'key' => 'field_social_url',
            'label' => 'URL',
            'name' => 'url',
            'type' => 'url',
          ],
        ],
      ],
      [
        'key' => 'field_promo_bg_image',
        'label' => 'Background Image',
        'name' => 'promo_bg_image',
        'type' => 'image',
        'return_format' => 'url',
        'instructions' => 'Optional background image for the social section.',
      ],

      // Tab: Testimonials
      [
        'key' => 'field_tab_testimonials',
        'label' => 'Testimonials',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_testimonials_title',
        'label' => 'Section Title',
        'name' => 'testimonials_title',
        'type' => 'text',
        'default_value' => 'Stories of the Circle',
      ],
      [
        'key' => 'field_testimonials_subtitle',
        'label' => 'Section Subtitle',
        'name' => 'testimonials_subtitle',
        'type' => 'textarea',
        'rows' => 2,
        'default_value' =>
          'Step inside the lives of those who dared to dream. Real people, life-changing moments.',
      ],
      [
        'key' => 'field_testimonials_repeater',
        'label' => 'Testimonials List',
        'name' => 'testimonials_list',
        'type' => 'repeater',
        'layout' => 'block',
        'button_label' => 'Add Testimonial',
        'sub_fields' => [
          [
            'key' => 'field_testi_name',
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
          ],
          [
            'key' => 'field_testi_avatar',
            'label' => 'Avatar',
            'name' => 'avatar',
            'type' => 'image',
            'return_format' => 'url',
          ],
          [
            'key' => 'field_testi_quote',
            'label' => 'Quote',
            'name' => 'quote',
            'type' => 'textarea',
            'rows' => 3,
          ],
          [
            'key' => 'field_testi_prize',
            'label' => 'Prize Won',
            'name' => 'prize',
            'type' => 'text',
          ],
        ],
      ],

      // Tab: Quick Guide
      [
        'key' => 'field_tab_guide',
        'label' => 'Quick Guide',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_guide_title',
        'label' => 'Section Title',
        'name' => 'guide_title',
        'type' => 'text',
        'default_value' => 'How to Play',
      ],
      [
        'key' => 'field_guide_subtitle',
        'label' => 'Section Subtitle',
        'name' => 'guide_subtitle',
        'type' => 'text',
        'default_value' => 'Win your dream prizes in just three simple steps',
      ],
      [
        'key' => 'field_guide_steps',
        'label' => 'Steps',
        'name' => 'guide_steps',
        'type' => 'repeater',
        'layout' => 'block',
        'max' => 3,
        'button_label' => 'Add Step',
        'sub_fields' => [
          [
            'key' => 'field_step_title',
            'label' => 'Title',
            'name' => 'title',
            'type' => 'text',
          ],
          [
            'key' => 'field_step_desc',
            'label' => 'Description',
            'name' => 'description',
            'type' => 'textarea',
            'rows' => 2,
          ],
          [
            'key' => 'field_step_number',
            'label' => 'Number',
            'name' => 'number',
            'type' => 'text',
            'default_value' => '01',
          ],
          [
            'key' => 'field_step_icon',
            'label' => 'SVG Icon Code',
            'name' => 'icon',
            'type' => 'textarea',
            'rows' => 3,
            'instructions' => 'Paste SVG code here.',
          ],
        ],
      ],

      // Tab: About/Who We Are
      [
        'key' => 'field_tab_about',
        'label' => 'About/Who We Are',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_about_badge',
        'label' => 'Badge Label',
        'name' => 'about_badge',
        'type' => 'text',
        'instructions' => 'Optional badge text (e.g., "Who We Are", "Our Story").',
        'default_value' => 'Who We Are',
      ],
      [
        'key' => 'field_about_title',
        'label' => 'Section Title',
        'name' => 'about_title',
        'type' => 'text',
        'instructions' => 'Main heading for the About section.',
        'default_value' => 'Your Trusted Partner in Premium Giveaways',
        'required' => 0,
      ],
      [
        'key' => 'field_about_subtitle',
        'label' => 'Subtitle',
        'name' => 'about_subtitle',
        'type' => 'text',
        'instructions' => 'Brief tagline or subtitle.',
        'default_value' => 'Bringing dreams to life, one competition at a time.',
      ],
      [
        'key' => 'field_about_description',
        'label' => 'Description',
        'name' => 'about_description',
        'type' => 'wysiwyg',
        'instructions' => 'Main descriptive text (supports paragraphs).',
        'default_value' =>
          'We\'re passionate about creating life-changing moments through fair, transparent, and exciting prize competitions. With over 150 winners and £2M+ in prizes awarded, we\'ve built a trusted community of dreamers and winners.',
        'media_upload' => 0,
        'toolbar' => 'basic',
        'rows' => 6,
      ],
      [
        'key' => 'field_about_features',
        'label' => 'Feature List',
        'name' => 'about_features',
        'type' => 'repeater',
        'instructions' => 'Key features or values (optional, up to 6 recommended).',
        'layout' => 'table',
        'button_label' => 'Add Feature',
        'max' => 6,
        'sub_fields' => [
          [
            'key' => 'field_feature_icon',
            'label' => 'Icon',
            'name' => 'icon',
            'type' => 'text',
            'instructions' => 'Material Symbol icon name (e.g., "verified", "shield", "star").',
            'default_value' => 'check_circle',
          ],
          [
            'key' => 'field_feature_text',
            'label' => 'Text',
            'name' => 'text',
            'type' => 'text',
            'default_value' => 'Feature text here',
          ],
        ],
      ],
      [
        'key' => 'field_about_show_cta',
        'label' => 'Show CTA Button',
        'name' => 'about_show_cta',
        'type' => 'true_false',
        'default_value' => 1,
        'ui' => 1,
      ],
      [
        'key' => 'field_about_cta_text',
        'label' => 'CTA Button Text',
        'name' => 'about_cta_text',
        'type' => 'text',
        'default_value' => 'Learn More About Us',
        'conditional_logic' => [
          [
            [
              'field' => 'field_about_show_cta',
              'operator' => '==',
              'value' => '1',
            ],
          ],
        ],
      ],
      [
        'key' => 'field_about_cta_url',
        'label' => 'CTA Button URL',
        'name' => 'about_cta_url',
        'type' => 'text',
        'default_value' => '/about/',
        'conditional_logic' => [
          [
            [
              'field' => 'field_about_show_cta',
              'operator' => '==',
              'value' => '1',
            ],
          ],
        ],
      ],
      [
        'key' => 'field_about_image',
        'label' => 'Section Image',
        'name' => 'about_image',
        'type' => 'image',
        'instructions' =>
          'Main image for the About section (recommended: 800x1000px or larger, 4:5 ratio).',
        'return_format' => 'array',
        'preview_size' => 'medium',
        'library' => 'all',
      ],
      [
        'key' => 'field_about_image_position',
        'label' => 'Image Position',
        'name' => 'about_image_position',
        'type' => 'select',
        'instructions' => 'Choose whether image appears on left or right side.',
        'choices' => [
          'right' => 'Right (Text Left)',
          'left' => 'Left (Text Right)',
        ],
        'default_value' => 'right',
      ],
      [
        'key' => 'field_about_background',
        'label' => 'Background Style',
        'name' => 'about_background',
        'type' => 'select',
        'instructions' => 'Section background color.',
        'choices' => [
          'white' => 'White',
          'gray' => 'Light Gray',
          'gradient' => 'Gradient (White to Blue) - Recommended',
        ],
        'default_value' => 'gradient',
      ],

      // Tab: FAQ
      [
        'key' => 'field_tab_faq',
        'label' => 'FAQ',
        'name' => '',
        'type' => 'tab',
      ],
      [
        'key' => 'field_faq_title',
        'label' => 'Section Title',
        'name' => 'faq_title',
        'type' => 'text',
        'default_value' => 'Frequently Asked Questions',
      ],
      [
        'key' => 'field_faq_list',
        'label' => 'FAQs',
        'name' => 'faq_list',
        'type' => 'repeater',
        'layout' => 'row',
        'button_label' => 'Add Question',
        'sub_fields' => [
          [
            'key' => 'field_faq_q',
            'label' => 'Question',
            'name' => 'question',
            'type' => 'text',
          ],
          [
            'key' => 'field_faq_a',
            'label' => 'Answer',
            'name' => 'answer',
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
          'value' => 'page-templates/homepage-template.php',
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
