<?php
/**
 * WordPress Customizer - Brand Customization
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Register customizer settings
 */
function nera_customize_register($wp_customize)
{
  // ===================================================================
  // PANEL: Nera Theme Options
  // ===================================================================
  $wp_customize->add_panel('nera_theme_options', [
    'title' => __('Nera Theme Options', 'nera-competitions'),
    'priority' => 25,
  ]);

  // ===================================================================
  // SECTION: Typography
  // ===================================================================
  $wp_customize->add_section('nera_typography', [
    'title' => __('Typography', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 20,
  ]);

  // Heading Font
  $wp_customize->add_setting('nera_heading_font', [
    'default' => 'Outfit',
    'sanitize_callback' => 'sanitize_text_field',
    'transport' => 'refresh',
  ]);

  $wp_customize->add_control('nera_heading_font', [
    'label' => __('Heading Font', 'nera-competitions'),
    'description' => __('Font family for all headings (H1-H6).', 'nera-competitions'),
    'section' => 'nera_typography',
    'type' => 'select',
    'choices' => nera_get_google_fonts(),
  ]);

  // Body Font
  $wp_customize->add_setting('nera_body_font', [
    'default' => 'Inter',
    'sanitize_callback' => 'sanitize_text_field',
    'transport' => 'refresh',
  ]);

  $wp_customize->add_control('nera_body_font', [
    'label' => __('Body Font', 'nera-competitions'),
    'description' => __('Font family for body text and paragraphs.', 'nera-competitions'),
    'section' => 'nera_typography',
    'type' => 'select',
    'choices' => nera_get_google_fonts(),
  ]);

  // Heading Font Weight
  $wp_customize->add_setting('nera_heading_weight', [
    'default' => '700',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_heading_weight', [
    'label' => __('Heading Font Weight', 'nera-competitions'),
    'section' => 'nera_typography',
    'type' => 'select',
    'choices' => [
      '300' => __('Light (300)', 'nera-competitions'),
      '400' => __('Regular (400)', 'nera-competitions'),
      '500' => __('Medium (500)', 'nera-competitions'),
      '600' => __('Semi-Bold (600)', 'nera-competitions'),
      '700' => __('Bold (700)', 'nera-competitions'),
      '800' => __('Extra-Bold (800)', 'nera-competitions'),
    ],
  ]);

  // ===================================================================
  // SECTION: Layout Options
  // ===================================================================
  $wp_customize->add_section('nera_layout', [
    'title' => __('Layout Options', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 30,
  ]);

  // Container Width
  $wp_customize->add_setting('nera_container_width', [
    'default' => '1280',
    'sanitize_callback' => 'absint',
  ]);

  $wp_customize->add_control('nera_container_width', [
    'label' => __('Container Max Width (px)', 'nera-competitions'),
    'section' => 'nera_layout',
    'type' => 'number',
    'input_attrs' => [
      'min' => 1000,
      'max' => 1920,
      'step' => 20,
    ],
  ]);

  // Border Radius Style
  $wp_customize->add_setting('nera_border_radius_style', [
    'default' => 'rounded',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_border_radius_style', [
    'label' => __('Border Radius Style', 'nera-competitions'),
    'section' => 'nera_layout',
    'type' => 'select',
    'choices' => [
      'sharp' => __('Sharp (no radius)', 'nera-competitions'),
      'subtle' => __('Subtle (4px)', 'nera-competitions'),
      'rounded' => __('Rounded (12px)', 'nera-competitions'),
      'pill' => __('Pill (24px)', 'nera-competitions'),
    ],
  ]);

  // Card Shadow Style
  $wp_customize->add_setting('nera_card_shadow', [
    'default' => 'medium',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_card_shadow', [
    'label' => __('Card Shadow Intensity', 'nera-competitions'),
    'section' => 'nera_layout',
    'type' => 'select',
    'choices' => [
      'none' => __('No Shadow', 'nera-competitions'),
      'subtle' => __('Subtle Shadow', 'nera-competitions'),
      'medium' => __('Medium Shadow', 'nera-competitions'),
      'strong' => __('Strong Shadow', 'nera-competitions'),
    ],
  ]);

  // ===================================================================
  // SECTION: Competition Settings
  // ===================================================================
  $wp_customize->add_section('nera_competition_settings', [
    'title' => __('Competition Settings', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 40,
  ]);

  // Countdown Timer Style
  $wp_customize->add_setting('nera_countdown_style', [
    'default' => 'cards',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_countdown_style', [
    'label' => __('Countdown Timer Style', 'nera-competitions'),
    'section' => 'nera_competition_settings',
    'type' => 'select',
    'choices' => [
      'cards' => __('Card Style', 'nera-competitions'),
      'minimal' => __('Minimal Style', 'nera-competitions'),
      'gradient' => __('Gradient Style', 'nera-competitions'),
      'flip' => __('Flip Animation', 'nera-competitions'),
    ],
  ]);

  // Progress Bar Style
  $wp_customize->add_setting('nera_progress_bar_style', [
    'default' => 'gradient',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_progress_bar_style', [
    'label' => __('Progress Bar Style', 'nera-competitions'),
    'section' => 'nera_competition_settings',
    'type' => 'select',
    'choices' => [
      'solid' => __('Solid Color', 'nera-competitions'),
      'gradient' => __('Gradient', 'nera-competitions'),
      'striped' => __('Striped Animation', 'nera-competitions'),
      'glow' => __('Glow Effect', 'nera-competitions'),
    ],
  ]);

  // Competition Card Style
  $wp_customize->add_setting('nera_card_style', [
    'default' => 'elevated',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_card_style', [
    'label' => __('Competition Card Style', 'nera-competitions'),
    'section' => 'nera_competition_settings',
    'type' => 'select',
    'choices' => [
      'flat' => __('Flat Style', 'nera-competitions'),
      'elevated' => __('Elevated Style', 'nera-competitions'),
      'bordered' => __('Bordered Style', 'nera-competitions'),
      'glass' => __('Glassmorphism', 'nera-competitions'),
    ],
  ]);

  // Show Ending Soon Badge Threshold (hours)
  $wp_customize->add_setting('nera_ending_soon_hours', [
    'default' => '24',
    'sanitize_callback' => 'absint',
  ]);

  $wp_customize->add_control('nera_ending_soon_hours', [
    'label' => __('Ending Soon Badge Threshold (hours)', 'nera-competitions'),
    'description' => __(
      'Show "Ending Soon" badge when less than X hours remain.',
      'nera-competitions',
    ),
    'section' => 'nera_competition_settings',
    'type' => 'number',
    'input_attrs' => [
      'min' => 1,
      'max' => 72,
      'step' => 1,
    ],
  ]);

  // ===================================================================
  // SECTION: Header Options
  // ===================================================================
  $wp_customize->add_section('nera_header_options', [
    'title' => __('Header Options', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 50,
  ]);

  // Header Style
  $wp_customize->add_setting('nera_header_style', [
    'default' => 'solid',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_header_style', [
    'label' => __('Header Style', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'select',
    'choices' => [
      'solid' => __('Solid Background', 'nera-competitions'),
      'transparent' => __('Transparent (Homepage only)', 'nera-competitions'),
      'gradient' => __('Gradient Background', 'nera-competitions'),
    ],
  ]);

  // Header Background Color
  $wp_customize->add_setting('nera_header_bg_color', [
    'default' => '#1F2937',
    'sanitize_callback' => 'sanitize_hex_color',
    'transport' => 'postMessage',
  ]);

  $wp_customize->add_control(
    new WP_Customize_Color_Control($wp_customize, 'nera_header_bg_color', [
      'label' => __('Header Background Color', 'nera-competitions'),
      'section' => 'nera_header_options',
      'settings' => 'nera_header_bg_color',
    ]),
  );

  // Sticky Header
  $wp_customize->add_setting('nera_sticky_header', [
    'default' => true,
    'sanitize_callback' => 'nera_sanitize_checkbox',
  ]);

  $wp_customize->add_control('nera_sticky_header', [
    'label' => __('Enable Sticky Header', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'checkbox',
  ]);

  // --- Header CTA Buttons ---

  // Secondary CTA - Text (Logged Out)
  $wp_customize->add_setting('nera_header_cta_secondary_text', [
    'default' => __('Sign In', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_header_cta_secondary_text', [
    'label' => __('Secondary CTA Text (Logged Out)', 'nera-competitions'),
    'description' => __('Text shown when user is not logged in.', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'text',
  ]);

  // Secondary CTA - Text (Logged In)
  $wp_customize->add_setting('nera_header_cta_secondary_logged_in_text', [
    'default' => __('My Account', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_header_cta_secondary_logged_in_text', [
    'label' => __('Secondary CTA Text (Logged In)', 'nera-competitions'),
    'description' => __('Text shown when user is logged in.', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'text',
  ]);

  // Secondary CTA - URL
  $wp_customize->add_setting('nera_header_cta_secondary_url', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);

  $wp_customize->add_control('nera_header_cta_secondary_url', [
    'label' => __('Secondary CTA URL', 'nera-competitions'),
    'description' => __('Leave empty to use WooCommerce My Account page.', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'url',
  ]);

  // Primary CTA - Text
  $wp_customize->add_setting('nera_header_cta_primary_text', [
    'default' => __('Enter Now', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_header_cta_primary_text', [
    'label' => __('Primary CTA Text', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'text',
  ]);

  // Primary CTA - URL
  $wp_customize->add_setting('nera_header_cta_primary_url', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);

  $wp_customize->add_control('nera_header_cta_primary_url', [
    'label' => __('Primary CTA URL', 'nera-competitions'),
    'description' => __('Leave empty to use WooCommerce Shop page.', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'url',
  ]);

  // Primary CTA - Show Arrow
  $wp_customize->add_setting('nera_header_cta_show_arrow', [
    'default' => true,
    'sanitize_callback' => 'nera_sanitize_checkbox',
  ]);

  $wp_customize->add_control('nera_header_cta_show_arrow', [
    'label' => __('Show Arrow on Primary CTA', 'nera-competitions'),
    'section' => 'nera_header_options',
    'type' => 'checkbox',
  ]);

  // ===================================================================
  // SECTION: Footer Options
  // ===================================================================
  $wp_customize->add_section('nera_footer_options', [
    'title' => __('Footer Options', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 60,
  ]);

  // Footer Background Color
  $wp_customize->add_setting('nera_footer_bg_color', [
    'default' => '#111827',
    'sanitize_callback' => 'sanitize_hex_color',
    'transport' => 'postMessage',
  ]);

  $wp_customize->add_control(
    new WP_Customize_Color_Control($wp_customize, 'nera_footer_bg_color', [
      'label' => __('Footer Background Color', 'nera-competitions'),
      'section' => 'nera_footer_options',
      'settings' => 'nera_footer_bg_color',
    ]),
  );

  // Footer Text Color
  $wp_customize->add_setting('nera_footer_text_color', [
    'default' => '#F3F4F6',
    'sanitize_callback' => 'sanitize_hex_color',
    'transport' => 'postMessage',
  ]);

  $wp_customize->add_control(
    new WP_Customize_Color_Control($wp_customize, 'nera_footer_text_color', [
      'label' => __('Footer Text Color', 'nera-competitions'),
      'section' => 'nera_footer_options',
      'settings' => 'nera_footer_text_color',
    ]),
  );

  // Copyright Text
  $wp_customize->add_setting('nera_copyright_text', [
    'default' => '© {year} {site_name}. All rights reserved.',
    'sanitize_callback' => 'wp_kses_post',
  ]);

  $wp_customize->add_control('nera_copyright_text', [
    'label' => __('Copyright Text', 'nera-competitions'),
    'description' => __(
      'Use {year} for current year and {site_name} for site name.',
      'nera-competitions',
    ),
    'section' => 'nera_footer_options',
    'type' => 'textarea',
  ]);

  // ===================================================================
  // SECTION: Animation Settings
  // ===================================================================
  $wp_customize->add_section('nera_animations', [
    'title' => __('Animations', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 70,
  ]);

  // Enable Animations
  $wp_customize->add_setting('nera_enable_animations', [
    'default' => true,
    'sanitize_callback' => 'nera_sanitize_checkbox',
  ]);

  $wp_customize->add_control('nera_enable_animations', [
    'label' => __('Enable Page Animations', 'nera-competitions'),
    'description' => __('Enable scroll animations and transitions.', 'nera-competitions'),
    'section' => 'nera_animations',
    'type' => 'checkbox',
  ]);

  // Animation Style
  $wp_customize->add_setting('nera_animation_style', [
    'default' => 'fade-up',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_animation_style', [
    'label' => __('Default Animation Style', 'nera-competitions'),
    'section' => 'nera_animations',
    'type' => 'select',
    'choices' => [
      'fade-up' => __('Fade Up', 'nera-competitions'),
      'fade-in' => __('Fade In', 'nera-competitions'),
      'slide-up' => __('Slide Up', 'nera-competitions'),
      'zoom-in' => __('Zoom In', 'nera-competitions'),
    ],
  ]);

  // Animation Speed
  $wp_customize->add_setting('nera_animation_speed', [
    'default' => 'normal',
    'sanitize_callback' => 'sanitize_key',
  ]);

  $wp_customize->add_control('nera_animation_speed', [
    'label' => __('Animation Speed', 'nera-competitions'),
    'section' => 'nera_animations',
    'type' => 'select',
    'choices' => [
      'slow' => __('Slow (600ms)', 'nera-competitions'),
      'normal' => __('Normal (400ms)', 'nera-competitions'),
      'fast' => __('Fast (200ms)', 'nera-competitions'),
    ],
  ]);

  // ===================================================================
  // SECTION: Homepage Settings
  // ===================================================================
  $wp_customize->add_section('nera_homepage', [
    'title' => __('Homepage', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 80,
  ]);

  // Hero Title
  $wp_customize->add_setting('nera_hero_title', [
    'default' => __('Win Life-Changing Prizes Every Week!', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_hero_title', [
    'label' => __('Hero Title', 'nera-competitions'),
    'description' => __('Main headline in the hero section.', 'nera-competitions'),
    'section' => 'nera_homepage',
    'type' => 'text',
  ]);

  // Hero Subtitle
  $wp_customize->add_setting('nera_hero_subtitle', [
    'default' => __(
      'Enter our exciting competitions for your chance to win cars, cash, holidays and more.',
      'nera-competitions',
    ),
    'sanitize_callback' => 'sanitize_textarea_field',
  ]);

  $wp_customize->add_control('nera_hero_subtitle', [
    'label' => __('Hero Subtitle', 'nera-competitions'),
    'section' => 'nera_homepage',
    'type' => 'textarea',
  ]);

  // Hero CTA Text
  $wp_customize->add_setting('nera_hero_cta_text', [
    'default' => __('View All Competitions', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_hero_cta_text', [
    'label' => __('Hero CTA Button Text', 'nera-competitions'),
    'section' => 'nera_homepage',
    'type' => 'text',
  ]);

  // Hero CTA URL
  $wp_customize->add_setting('nera_hero_cta_url', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);

  $wp_customize->add_control('nera_hero_cta_url', [
    'label' => __('Hero CTA Button URL', 'nera-competitions'),
    'description' => __('Leave empty to link to shop page.', 'nera-competitions'),
    'section' => 'nera_homepage',
    'type' => 'url',
  ]);

  // ===================================================================
  // SECTION: Social & Live Draw
  // ===================================================================
  $wp_customize->add_section('nera_social', [
    'title' => __('Social & Live Draw', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 85,
  ]);

  // Facebook Page URL
  $wp_customize->add_setting('nera_facebook_page_url', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);

  $wp_customize->add_control('nera_facebook_page_url', [
    'label' => __('Facebook Page URL', 'nera-competitions'),
    'description' => __('URL of your Facebook page for live draws.', 'nera-competitions'),
    'section' => 'nera_social',
    'type' => 'url',
  ]);

  // YouTube Channel URL
  $wp_customize->add_setting('nera_youtube_channel_url', [
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
  ]);

  $wp_customize->add_control('nera_youtube_channel_url', [
    'label' => __('YouTube Channel URL', 'nera-competitions'),
    'section' => 'nera_social',
    'type' => 'url',
  ]);

  // Live Draw Title
  $wp_customize->add_setting('nera_live_draw_title', [
    'default' => __('Watch Our Live Draws', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_live_draw_title', [
    'label' => __('Live Draw Section Title', 'nera-competitions'),
    'section' => 'nera_social',
    'type' => 'text',
  ]);

  // Live Draw Subtitle
  $wp_customize->add_setting('nera_live_draw_subtitle', [
    'default' => __(
      'Every winner is selected live on Facebook for complete transparency!',
      'nera-competitions',
    ),
    'sanitize_callback' => 'sanitize_textarea_field',
  ]);

  $wp_customize->add_control('nera_live_draw_subtitle', [
    'label' => __('Live Draw Section Subtitle', 'nera-competitions'),
    'section' => 'nera_social',
    'type' => 'textarea',
  ]);

  // Next Draw Date
  $wp_customize->add_setting('nera_next_draw_date', [
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_next_draw_date', [
    'label' => __('Next Draw Date', 'nera-competitions'),
    'description' => __(
      'Format: YYYY-MM-DD HH:MM:SS (e.g., 2024-01-20 20:00:00)',
      'nera-competitions',
    ),
    'section' => 'nera_social',
    'type' => 'text',
  ]);

  // ===================================================================
  // SECTION: Newsletter
  // ===================================================================
  $wp_customize->add_section('nera_newsletter', [
    'title' => __('Newsletter', 'nera-competitions'),
    'panel' => 'nera_theme_options',
    'priority' => 90,
  ]);

  // Newsletter Title
  $wp_customize->add_setting('nera_newsletter_title', [
    'default' => __('Get Exclusive Deals & Free Entries!', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_newsletter_title', [
    'label' => __('Newsletter Title', 'nera-competitions'),
    'section' => 'nera_newsletter',
    'type' => 'text',
  ]);

  // Newsletter Subtitle
  $wp_customize->add_setting('nera_newsletter_subtitle', [
    'default' => __(
      'Subscribe to our newsletter and get notified about new competitions, exclusive discounts, and free entry opportunities.',
      'nera-competitions',
    ),
    'sanitize_callback' => 'sanitize_textarea_field',
  ]);

  $wp_customize->add_control('nera_newsletter_subtitle', [
    'label' => __('Newsletter Subtitle', 'nera-competitions'),
    'section' => 'nera_newsletter',
    'type' => 'textarea',
  ]);

  // Newsletter Incentive
  $wp_customize->add_setting('nera_newsletter_incentive', [
    'default' => __('🎁 Sign up now and get 10% off your first entry!', 'nera-competitions'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('nera_newsletter_incentive', [
    'label' => __('Newsletter Incentive Text', 'nera-competitions'),
    'description' => __('Special offer text shown above the signup form.', 'nera-competitions'),
    'section' => 'nera_newsletter',
    'type' => 'text',
  ]);
}
add_action('customize_register', 'nera_customize_register');

/**
 * Get Google Fonts list
 */
function nera_get_google_fonts()
{
  return [
    'Outfit' => 'Outfit',
    'Inter' => 'Inter',
    'Playfair Display' => 'Playfair Display',
    'Lato' => 'Lato',
    'Roboto' => 'Roboto',
    'Open Sans' => 'Open Sans',
    'Montserrat' => 'Montserrat',
    'Poppins' => 'Poppins',
    'Space Grotesk' => 'Space Grotesk',
    'DM Sans' => 'DM Sans',
    'Nunito' => 'Nunito',
    'Raleway' => 'Raleway',
    'Source Sans Pro' => 'Source Sans Pro',
    'Oswald' => 'Oswald',
    'Merriweather' => 'Merriweather',
  ];
}

/**
 * Sanitize checkbox values
 */
function nera_sanitize_checkbox($input)
{
  return isset($input) && true == $input ? true : false;
}

/**
 * Output custom CSS based on customizer settings
 * Uses wp_add_inline_style to ensure CSS is output AFTER design-tokens.css
 */
function nera_customizer_css()
{
  // Use individual customizer values
  $primary_color = get_theme_mod('nera_primary_color', '#FF6B35');
  $secondary_color = get_theme_mod('nera_secondary_color', '#004E89');
  $accent_color = get_theme_mod('nera_accent_color', '#F7B801');
  $success_color = get_theme_mod('nera_success_color', '#2EC4B6');
  $danger_color = get_theme_mod('nera_danger_color', '#E71D36');
  $heading_font = get_theme_mod('nera_heading_font', 'Outfit');
  $body_font = get_theme_mod('nera_body_font', 'Inter');
  $border_radius_style = get_theme_mod('nera_border_radius_style', 'rounded');
  $card_shadow = get_theme_mod('nera_card_shadow', 'medium');
  $heading_weight = get_theme_mod('nera_heading_weight', '700');

  // Layout settings (container width is always from customizer as it's not in presets)
  $container_width = get_theme_mod('nera_container_width', '1280');

  // Header/Footer settings
  $header_bg_color = get_theme_mod('nera_header_bg_color', '#1F2937');
  $footer_bg_color = get_theme_mod('nera_footer_bg_color', '#111827');
  $footer_text_color = get_theme_mod('nera_footer_text_color', '#F3F4F6');

  // Map border radius style
  $radius_map = [
    'sharp' => '0',
    'subtle' => '0.25rem',
    'rounded' => '0.75rem',
    'pill' => '1.5rem',
  ];
  $border_radius = isset($radius_map[$border_radius_style])
    ? $radius_map[$border_radius_style]
    : '0.75rem';

  // Map shadow style
  $shadow_map = [
    'none' => 'none',
    'subtle' => '0 2px 8px 0 rgba(0, 0, 0, 0.05)',
    'medium' => '0 4px 16px 0 rgba(0, 0, 0, 0.1)',
    'strong' => '0 10px 40px 0 rgba(0, 0, 0, 0.15)',
  ];
  $card_shadow_value = isset($shadow_map[$card_shadow])
    ? $shadow_map[$card_shadow]
    : $shadow_map['medium'];

  // Build inline CSS string
  $custom_css = "
    :root {
      --nera-color-primary: {$primary_color};
      --nera-color-secondary: {$secondary_color};
      --nera-color-accent: {$accent_color};
      --nera-color-success: {$success_color};
      --nera-color-danger: {$danger_color};
      --nera-font-heading: '{$heading_font}', sans-serif;
      --nera-font-body: '{$body_font}', sans-serif;
      --nera-font-weight-heading: {$heading_weight};
      --nera-container-xl: {$container_width}px;
      --nera-radius-lg: {$border_radius};
      --nera-shadow-card: {$card_shadow_value};
      --nera-header-bg: {$header_bg_color};
      --nera-footer-bg: {$footer_bg_color};
      --nera-footer-text: {$footer_text_color};
    }

    h1, h2, h3, h4, h5, h6 {
      font-weight: var(--nera-font-weight-heading);
    }
  ";

  // Attach inline CSS to the theme stylesheet
  // This ensures our overrides come AFTER the default values
  wp_add_inline_style('nera-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'nera_customizer_css', 20);

/**
 * Enqueue Google Fonts dynamically
 */
function nera_enqueue_google_fonts()
{
  $heading_font = get_theme_mod('nera_heading_font', 'Outfit');
  $body_font = get_theme_mod('nera_body_font', 'Inter');

  // Format font names for Google Fonts URL
  $fonts = array_unique([$heading_font, $body_font]);
  $font_families = [];

  foreach ($fonts as $font) {
    $font_families[] = str_replace(' ', '+', $font) . ':wght@300;400;500;600;700;800';
  }

  $google_fonts_url =
    'https://fonts.googleapis.com/css2?family=' .
    implode('&family=', $font_families) .
    '&display=swap';

  wp_enqueue_style('nera-google-fonts', $google_fonts_url, [], null);
}
add_action('wp_enqueue_scripts', 'nera_enqueue_google_fonts', 5);

/**
 * Live preview JavaScript
 */
function nera_customizer_live_preview()
{
  wp_enqueue_script(
    'nera-customizer-preview',
    NERA_ASSETS_URI . '/js/customizer-preview.js',
    ['jquery', 'customize-preview'],
    NERA_VERSION,
    true,
  );
}
add_action('customize_preview_init', 'nera_customizer_live_preview');
