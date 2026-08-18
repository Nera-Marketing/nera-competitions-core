<?php
/**
 * ACF WooCommerce Settings
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_options_page')) {
  if (!function_exists('acf_get_options_page') || !acf_get_options_page('theme-settings')) {
    acf_add_options_page([
      'page_title' => 'Theme Settings',
      'menu_title' => 'Theme Settings',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect' => false,
    ]);
  }

  acf_add_options_sub_page([
    'page_title' => 'WooCommerce Settings',
    'menu_title' => 'WooCommerce',
    'parent_slug' => 'theme-settings',
  ]);
}

if (function_exists('acf_add_local_field_group')) {
  $woocommerce_fields = [
    [
      'key' => 'field_wc_quantity_selector_layout',
      'label' => 'Quantity Selector Layout',
      'name' => 'quantity_selector_layout',
      'type' => 'select',
      'instructions' =>
        'Site-wide default for choosing ticket quantity on the purchase card (auto-assign products only). Manual Browse & Choose products are unaffected. Products can override under Competition Settings.',
      'choices' => [
        'buttons' => 'Buttons (+ quick add)',
        'slider' => 'Slider',
      ],
      'default_value' => 'buttons',
      'ui' => 1,
      'wrapper' => [
        'width' => '50',
        'class' => '',
        'id' => '',
      ],
    ],
    [
      'key'          => 'field_wc_mobile_card_layout',
      'label'        => 'Mobile Purchase Card Layout',
      'name'         => 'mobile_card_layout',
      'type'         => 'select',
      'instructions' => 'Controls element order on mobile. "Details Above Image" shows the title, countdown and tickets sold above the gallery image on small screens. Desktop layout is unaffected. Products can override under Competition Settings.',
      'choices'      => [
        'default'       => 'Default (Image → Details)',
        'details_first' => 'Details Above Image (Mobile)',
      ],
      'default_value' => 'default',
      'ui'            => 1,
      'wrapper'       => [
        'width' => '50',
        'class' => '',
        'id'    => '',
      ],
    ],
    [
      'key' => 'field_add_to_cart_success_message',
      'label' => 'Add to Cart Success Message',
      'name' => 'add_to_cart_success_message',
      'type' => 'text',
      'instructions' => 'Message shown to customers after successfully adding tickets to the cart.',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => [
        'width' => '50',
        'class' => '',
        'id' => '',
      ],
      'default_value' => 'Tickets added to cart!',
      'placeholder' => 'Tickets added to cart!',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
    ],
    [
      'key' => 'field_wc_show_entry_list_tab',
      'label' => 'Show Entry List Tab',
      'name' => 'show_entry_list_tab',
      'type' => 'true_false',
      'instructions' => 'Site-wide default for the Entry List tab on competition product pages. Products can override under Competition Settings.',
      'default_value' => 1,
      'ui' => 1,
      'ui_on_text' => 'Visible',
      'ui_off_text' => 'Hidden',
    ],
    [
      'key' => 'field_wc_show_tickets_counter',
      'label' => 'Show Tickets Counter',
      'name' => 'show_tickets_counter',
      'type' => 'true_false',
      'instructions' => 'Sold-count line (“Tickets Sold”, X / Y). Hidden hides this line; Visible shows it. Where Hidden applies: the selected categories below, or every product if that list is empty. Visible shows the line again, including on selected categories.',
      'default_value' => 1,
      'ui' => 1,
      'ui_on_text' => 'Visible',
      'ui_off_text' => 'Hidden',
      'wrapper' => [
        'width' => '50',
      ],
    ],
    [
      'key' => 'field_wc_show_tickets_progress',
      'label' => 'Show Progress Bar',
      'name' => 'show_tickets_progress',
      'type' => 'true_false',
      'instructions' => 'Fill bar and percentage. Hidden hides this bar; Visible shows it. Where Hidden applies: the selected categories below, or every product if that list is empty. Visible shows the bar again, including on selected categories.',
      'default_value' => 1,
      'ui' => 1,
      'ui_on_text' => 'Visible',
      'ui_off_text' => 'Hidden',
      'wrapper' => [
        'width' => '50',
      ],
    ],
    [
      'key' => 'field_wc_hide_tickets_progress_categories',
      'label' => 'Hide Tickets Sold Progress on categories',
      'name' => 'hide_tickets_progress_categories',
      'type' => 'taxonomy',
      'instructions' => 'Works with the two toggles above. Empty + Hidden = hide Counter/Bar on every product. Empty + Visible = show on every product. Selected + Hidden = hide only those categories (and Remaining Tickets Hints). Selected + Visible = show again, including selected categories. Other products always show when the list is not empty. A ticket cap is still required. Per-product “show” cannot unhide a selected category while a toggle is Hidden.',
      'taxonomy' => 'product_cat',
      'field_type' => 'multi_select',
      'allow_null' => 1,
      'add_term' => 0,
      'save_terms' => 0,
      'load_terms' => 0,
      'return_format' => 'id',
      'multiple' => 1,
      'ui' => 1,
    ],
    [
      'key' => 'field_wc_single_left_col_span',
      'label' => 'Single product — left column span (of 12)',
      'name' => 'single_left_col_span',
      'type' => 'number',
      'instructions' =>
        'Width of the gallery (left) column on a 12-column grid for single competition pages. Left + right should total 12 or less. Default: 7.',
      'min' => 1,
      'max' => 11,
      'default_value' => 7,
      'placeholder' => '7',
      'wrapper' => [
        'width' => '50',
        'class' => '',
        'id' => '',
      ],
    ],
    [
      'key' => 'field_wc_single_right_col_span',
      'label' => 'Single product — right column span (of 12)',
      'name' => 'single_right_col_span',
      'type' => 'number',
      'instructions' =>
        'Width of the purchase card (right) column on a 12-column grid. Set higher than the left span to make the right side wider. Default: 5.',
      'min' => 1,
      'max' => 11,
      'default_value' => 5,
      'placeholder' => '5',
      'wrapper' => [
        'width' => '50',
        'class' => '',
        'id' => '',
      ],
    ],
    [
      'key' => 'field_wc_single_image_aspect_ratio',
      'label' => 'Single product — featured image aspect ratio',
      'name' => 'single_image_aspect_ratio',
      'type' => 'text',
      'instructions' =>
        'CSS aspect-ratio value for the main gallery image. Leave empty for the default 4/3. Examples: 4/5, 16/9, 1.25.',
      'required' => 0,
      'placeholder' => '4/3',
      'wrapper' => [
        'width' => '50',
        'class' => '',
        'id' => '',
      ],
    ],
    [
      'key' => 'field_wc_single_image_max_height',
      'label' => 'Single product — featured image max height',
      'name' => 'single_image_max_height',
      'type' => 'text',
      'instructions' =>
        'Caps the gallery image height so the left column stays compact (keeps the buy controls above the fold). CSS length: e.g. 70vh, 520px. Defaults to 70vh; type "none" to disable the cap.',
      'required' => 0,
      'default_value' => '70vh',
      'placeholder' => '70vh',
      'wrapper' => [
        'width' => '50',
        'class' => '',
        'id' => '',
      ],
    ],

    // ── Store behaviour ────────────────────────────────────────────────────
    // Moved here from WooCommerce → Settings → General (ADR 0009 / 0010). Never read these
    // directly — nera_basket_hold_minutes() and nera_show_giveaway_buyer_emails() own the
    // resolution, including the fallback to the pre-move option rows. Each field carries its
    // own full context because this group is a mixed bag and its title explains neither.
    [
      'key' => 'field_nera_basket_hold_minutes',
      'label' => 'Basket Hold (minutes)',
      'name' => 'nera_basket_hold_minutes',
      'type' => 'number',
      'instructions' =>
        'How long picked ticket numbers stay reserved in the cart before that line is removed (1–30). Only applies to Competitions where Ticket Generation Type is “User Chooses the Ticket” — automatic ticket Competitions are not timed. Set to 0 to disable Basket Hold entirely: no reserve, no countdown, no auto-remove. Default 5.',
      'required' => 0,
      'min' => 0,
      'max' => 30,
      'step' => 1,
      'default_value' => 5,
      'placeholder' => '5',
      'append' => '',
      'wrapper' => [
        'width' => '',
        'class' => 'nera-acf-field--compact-minutes',
        'id' => '',
      ],
    ],
    [
      'key' => 'field_nera_show_giveaway_buyer_emails',
      'label' => 'Show buyer emails on Giveaway lists',
      'name' => 'nera_show_giveaway_buyer_emails',
      'type' => 'true_false',
      'instructions' =>
        'Whether buyer email addresses appear beside usernames on the Giveaway → View screen (Tickets, Winners, and Instant Win Prizes). Show = the email appears next to the username. Hide = it is removed from those lists (default). Does not change Export CSV, order links, or the billing-name tooltip.',
      'required' => 0,
      'ui' => 1,
      'ui_on_text' => 'Show',
      'ui_off_text' => 'Hide',
      'default_value' => 0,
      'wrapper' => [
        'width' => '',
        'class' => 'nera-acf-field--toggle',
        'id' => '',
      ],
    ],
  ];

  if (class_exists('Nera_STW_ACF_Copy_Settings')) {
    $woocommerce_fields = array_merge(
      $woocommerce_fields,
      Nera_STW_ACF_Copy_Settings::get_woocommerce_accordion_fields(),
    );
  }

  acf_add_local_field_group([
    'key' => 'group_neracompetitions_woocommerce',
    'title' => 'WooCommerce Settings',
    'fields' => $woocommerce_fields,
    'location' => [
      [
        [
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'acf-options-woocommerce',
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
