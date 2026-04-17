<?php
/**
 * WooCommerce Customizations
 * Custom hooks and modifications for WooCommerce integration
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Custom template loader for lottery products
 * Uses our custom lottery.php template for lottery product type
 * Supports multiple template styles via ACF field
 */
function nera_lottery_template_loader($template)
{
  if (is_product()) {
    global $post;
    $product = wc_get_product($post->ID);

    if ($product && $product->is_type('lottery')) {
      // Check for template style selection (ACF field)
      $template_style = function_exists('get_field')
        ? get_field('competition_template_style', $post->ID)
        : 'default';

      // Select template based on style
      if ($template_style === 'hybrid-premium') {
        $custom_template = locate_template('woocommerce/single-product/competitions.php');
        if ($custom_template) {
          return $custom_template;
        }
      }

      // Default: use standard lottery template
      $custom_template = locate_template('woocommerce/single-product/lottery.php');
      if ($custom_template) {
        return $custom_template;
      }
    }
  }

  return $template;
}
// add_filter('template_include', 'nera_lottery_template_loader', 99);

/**
 * Add body class for single lottery products
 */
function nera_lottery_body_class($classes)
{
  if (is_product()) {
    global $post;
    $product = wc_get_product($post->ID);

    if ($product && $product->is_type('lottery')) {
      $classes[] = 'nera-single-lottery';
      $classes[] = 'nera-competition-product';
    }
  }

  return $classes;
}
add_filter('body_class', 'nera_lottery_body_class');

/**
 * Enqueue single product scripts with localized data
 */
function nera_enqueue_single_product_scripts()
{
  if (!is_product()) {
    return;
  }

  global $post;
  $product = wc_get_product($post->ID);

  if (!$product || !$product->is_type('lottery')) {
    return;
  }

  // Get lottery product data for JavaScript
  $lottery_data = nera_get_lottery_product_data($product);

  // Localize script with product data
  wp_localize_script('swiper', 'neraLotteryProduct', $lottery_data);
}
add_action('wp_enqueue_scripts', 'nera_enqueue_single_product_scripts', 20);

/**
 * Helper function to get lottery product data
 *
 * @param WC_Product|int $product Product object or ID
 * @return array Lottery product data
 */
function nera_get_lottery_product_data($product = null)
{
  if (!$product) {
    global $post;
    $product = wc_get_product($post->ID);
  }

  if (is_int($product)) {
    $product = wc_get_product($product);
  }

  if (!$product) {
    return [];
  }

  $product_id = $product->get_id();

  // Basic product info
  $data = [
    'id' => $product_id,
    'name' => $product->get_name(),
    'price' => $product->get_price(),
    'priceHtml' => $product->get_price_html(),
    'currency' => get_woocommerce_currency_symbol(),
    'addToCartUrl' => $product->add_to_cart_url(),
  ];

  // Lottery specific data
  $max_tickets = get_post_meta($product_id, '_lty_maximum_tickets', true);
  $sold_tickets = method_exists($product, 'get_purchased_ticket_count')
    ? $product->get_purchased_ticket_count()
    : 0;
  $end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);

  // Get correct meta keys from lottery plugin
  $order_max = get_post_meta($product_id, '_lty_order_maximum_tickets', true);
  $user_max = get_post_meta($product_id, '_lty_user_maximum_tickets', true);
  $user_min = get_post_meta($product_id, '_lty_user_minimum_tickets', true);

  // Prefer product methods when available (handles guest mode, stock, etc.)
  $min_per_order = method_exists($product, 'get_min_purchase_quantity')
    ? $product->get_min_purchase_quantity()
    : (intval($user_min) > 0 ? intval($user_min) : 1);

  $max_per_order = method_exists($product, 'get_max_purchase_quantity')
    ? $product->get_max_purchase_quantity()
    : (intval($order_max) > 0 ? intval($order_max) : 100);

  // Calculate remaining tickets
  $remaining = $max_tickets ? max(0, intval($max_tickets) - intval($sold_tickets)) : 0;
  $progress = $max_tickets ? min(100, round(($sold_tickets / $max_tickets) * 100)) : 0;

  // Merge lottery data
  $data = array_merge($data, [
    'maxTickets' => intval($max_tickets),
    'soldTickets' => intval($sold_tickets),
    'remainingTickets' => $remaining,
    'progress' => $progress,
    'maxPerOrder' => intval($max_per_order),
    'minPerOrder' => intval($min_per_order),
    'maxPerUser' => intval($user_max) ?: 0, // Keep for display purposes
    'minTickets' => intval($min_per_order), // Keep for backward compatibility
    'endDate' => $end_date_gmt,
    'endTimestamp' => $end_date_gmt ? strtotime($end_date_gmt) * 1000 : 0,
  ]);

  // ACF fields (if available)
  if (function_exists('get_field')) {
    $data['galleryBadge'] = [
      'text' => get_field('gallery_badge_text', $product_id),
      'color' => get_field('gallery_badge_color', $product_id) ?: 'primary',
    ];
    $data['showTrustpilot'] = get_field('show_trustpilot', $product_id);
    $data['trustpilotScore'] = get_field('trustpilot_score', $product_id) ?: '4.8';
  }

  // Add nonce for AJAX
  $data['nonce'] = wp_create_nonce('nera_lottery_nonce');
  $data['ajaxUrl'] = admin_url('admin-ajax.php');

  return $data;
}

/**
 * Whether a lottery product has no tickets left (sold out).
 *
 * @param WC_Product     $product      Product object.
 * @param array          $lottery_data Optional; same shape as nera_get_lottery_product_data().
 * @return bool True when max tickets are set and remaining is zero.
 */
function nera_lottery_product_is_sold_out($product, $lottery_data = [])
{
  if (!$product || !is_a($product, 'WC_Product')) {
    return false;
  }

  if ($product->get_type() !== 'lottery') {
    return false;
  }

  if (method_exists($product, 'get_lty_maximum_tickets') && method_exists($product, 'get_remaining_ticket_count')) {
    $max = (int) $product->get_lty_maximum_tickets();
    if ($max <= 0) {
      return false;
    }

    return (int) $product->get_remaining_ticket_count() <= 0;
  }

  $max = isset($lottery_data['maxTickets']) ? (int) $lottery_data['maxTickets'] : 0;
  if ($max <= 0) {
    return false;
  }

  $remaining = isset($lottery_data['remainingTickets']) ? (int) $lottery_data['remainingTickets'] : -1;

  return $remaining <= 0;
}

/**
 * Block add to cart when lottery tickets are sold out (defense in depth).
 *
 * @param bool $passed Validation passed.
 * @param int  $product_id Product ID.
 * @param int  $quantity Quantity.
 * @return bool
 */
function nera_validate_lottery_not_sold_out($passed, $product_id, $quantity)
{
  if (!$passed) {
    return false;
  }

  $product = wc_get_product($product_id);
  if (!$product || $product->get_type() !== 'lottery') {
    return $passed;
  }

  $lottery_data = nera_get_lottery_product_data($product);
  if (!nera_lottery_product_is_sold_out($product, $lottery_data)) {
    return true;
  }

  wc_add_notice(__('This competition is sold out. No tickets are available.', 'nera-competitions'), 'error');

  return false;
}
add_filter('woocommerce_add_to_cart_validation', 'nera_validate_lottery_not_sold_out', 10, 3);

/**
 * Get product gallery images
 *
 * @param WC_Product $product Product object
 * @return array Array of image data
 */
function nera_get_product_gallery_images($product)
{
  $images = [];

  // Get main product image
  $main_image_id = $product->get_image_id();
  if ($main_image_id) {
    $images[] = [
      'id' => $main_image_id,
      'full' => wp_get_attachment_image_url($main_image_id, 'full'),
      'large' => wp_get_attachment_image_url($main_image_id, 'large'),
      'thumbnail' => wp_get_attachment_image_url($main_image_id, 'thumbnail'),
      'alt' => get_post_meta($main_image_id, '_wp_attachment_image_alt', true),
    ];
  }

  // Get gallery images
  $gallery_ids = $product->get_gallery_image_ids();
  foreach ($gallery_ids as $image_id) {
    $images[] = [
      'id' => $image_id,
      'full' => wp_get_attachment_image_url($image_id, 'full'),
      'large' => wp_get_attachment_image_url($image_id, 'large'),
      'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
      'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
    ];
  }

  return $images;
}

/**
 * Whether the product has the WooCommerce product tag with slug "featured".
 *
 * @param WC_Product|null $product Product object.
 * @return bool
 */
function nera_product_has_featured_tag($product)
{
  if (!$product || !is_a($product, 'WC_Product')) {
    return false;
  }

  return has_term('featured', 'product_tag', $product->get_id());
}

/**
 * Get countdown time parts
 *
 * @param string $end_date_gmt End date in GMT
 * @return array Time parts (days, hours, minutes, seconds)
 */
function nera_get_countdown_parts($end_date_gmt)
{
  if (!$end_date_gmt) {
    return [
      'days' => 0,
      'hours' => 0,
      'minutes' => 0,
      'seconds' => 0,
      'expired' => true,
    ];
  }

  $end_timestamp = strtotime($end_date_gmt);
  $now = time();
  $diff = $end_timestamp - $now;

  if ($diff <= 0) {
    return [
      'days' => 0,
      'hours' => 0,
      'minutes' => 0,
      'seconds' => 0,
      'expired' => true,
    ];
  }

  return [
    'days' => floor($diff / 86400),
    'hours' => floor(($diff % 86400) / 3600),
    'minutes' => floor(($diff % 3600) / 60),
    'seconds' => floor($diff % 60),
    'expired' => false,
    'urgent' => $diff < 3600, // Less than 1 hour
  ];
}

/**
 * Get badge color classes
 *
 * @param string $color Color key
 * @return string Tailwind classes
 */
function nera_get_badge_color_classes($color = 'primary')
{
  $colors = [
    'primary' => 'bg-primary text-white',
    'success' => 'bg-success text-white',
    'danger' => 'bg-danger text-white',
    'warning' => 'bg-warning text-white',
  ];

  return isset($colors[$color]) ? $colors[$color] : $colors['primary'];
}

/**
 * Format draw date for display
 *
 * @param string $date_gmt Date in GMT
 * @return string Formatted date
 */
function nera_format_draw_date($date_gmt)
{
  if (!$date_gmt) {
    return '';
  }

  $timestamp = strtotime($date_gmt);
  return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
}

/**
 * Meta query to restrict lottery product queries to active statuses only.
 * Excludes: lty_lottery_failed, lty_lottery_finished, lty_lottery_closed.
 * Includes: lty_lottery_not_started, lty_lottery_started, or missing meta.
 *
 * @return array Meta query array (relation AND with NOT EXISTS + IN).
 */
function nera_active_lottery_meta_query()
{
  $now_gmt = current_time('mysql', 1);

  return [
    'relation' => 'AND',
    [
      'relation' => 'OR',
      ['key' => '_lty_start_date_gmt', 'compare' => 'NOT EXISTS'],
      ['key' => '_lty_start_date_gmt', 'value' => '', 'compare' => '='],
      ['key' => '_lty_start_date_gmt', 'value' => $now_gmt, 'type' => 'DATETIME', 'compare' => '<='],
    ],
    [
      'relation' => 'OR',
      ['key' => '_lty_lottery_status', 'compare' => 'NOT EXISTS'],
      [
        'key' => '_lty_lottery_status',
        'value' => ['lty_lottery_not_started', 'lty_lottery_started'],
        'compare' => 'IN',
      ],
    ],
  ];
}

/**
 * Count published lottery products in a category that pass active-lottery rules
 * (same as homepage categories grid: lottery type + nera_active_lottery_meta_query).
 *
 * @param int $term_id product_cat term ID.
 * @return int
 */
function nera_count_active_lottery_products_in_category($term_id)
{
  $term_id = (int) $term_id;
  if ($term_id < 1) {
    return 0;
  }

  $query = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'no_found_rows' => false,
    'tax_query' => [
      'relation' => 'AND',
      [
        'taxonomy' => 'product_type',
        'field' => 'slug',
        'terms' => 'lottery',
      ],
      [
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $term_id,
      ],
    ],
    'meta_query' => function_exists('nera_active_lottery_meta_query') ? nera_active_lottery_meta_query() : [],
  ]);

  return (int) $query->found_posts;
}

/**
 * Get related lottery products
 *
 * @param int $product_id Current product ID
 * @param int $limit Number of products to return
 * @return array Array of product IDs
 */
function nera_get_related_lottery_products($product_id, $limit = 4)
{
  // Check for manually selected related products
  $manual_related = get_field('related_products_manual', $product_id);

  if ($manual_related && !empty($manual_related)) {
    return array_slice($manual_related, 0, $limit);
  }

  // Fall back to auto-related products
  $product_cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);

  $args = [
    'post_type' => 'product',
    'posts_per_page' => $limit,
    'post_status' => 'publish',
    'post__not_in' => [$product_id],
    'tax_query' => [
      [
        'taxonomy' => 'product_type',
        'field' => 'slug',
        'terms' => 'lottery',
      ],
    ],
    'fields' => 'ids',
    'meta_query' => [
      [
        'key' => '_lty_end_date_gmt',
        'value' => current_time('mysql', 1),
        'compare' => '>',
        'type' => 'DATETIME',
      ],
    ],
  ];

  // Add category filter if available
  if (!empty($product_cats)) {
    $args['tax_query'][] = [
      'taxonomy' => 'product_cat',
      'field' => 'term_id',
      'terms' => $product_cats,
    ];
    $args['tax_query']['relation'] = 'AND';
  }

  // Order by ending soon
  $args['meta_key'] = '_lty_end_date_gmt';
  $args['orderby'] = 'meta_value';
  $args['order'] = 'ASC';

  $query = new WP_Query($args);
  $related_ids = $query->posts;

  // Fallback: If no related products found by category, get latest competitions
  if (empty($related_ids)) {
    $fallback_args = [
      'post_type' => 'product',
      'posts_per_page' => $limit,
      'post_status' => 'publish',
      'post__not_in' => [$product_id],
      'tax_query' => [
        [
          'taxonomy' => 'product_type',
          'field' => 'slug',
          'terms' => 'lottery',
        ],
      ],
      'fields' => 'ids',
      'meta_key' => '_lty_end_date_gmt',
      'orderby' => 'meta_value',
      'order' => 'ASC',
      'meta_query' => [
        [
          'key' => '_lty_end_date_gmt',
          'value' => current_time('mysql', 1),
          'compare' => '>',
          'type' => 'DATETIME',
        ],
      ],
    ];

    $fallback_query = new WP_Query($fallback_args);
    $related_ids = $fallback_query->posts;
  }

  return $related_ids;
}

/**
 * Remove default WooCommerce actions for lottery products
 */
function nera_remove_default_wc_actions()
{
  if (is_product()) {
    global $post;
    $product = wc_get_product($post->ID);

    if ($product && $product->is_type('lottery')) {
      // Remove default product summary elements
      remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
      remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
      remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
      remove_action(
        'woocommerce_single_product_summary',
        'woocommerce_template_single_excerpt',
        20,
      );
      remove_action(
        'woocommerce_single_product_summary',
        'woocommerce_template_single_add_to_cart',
        30,
      );
      remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
      remove_action(
        'woocommerce_single_product_summary',
        'woocommerce_template_single_sharing',
        50,
      );

      // Remove default tabs
      remove_action(
        'woocommerce_after_single_product_summary',
        'woocommerce_output_product_data_tabs',
        10,
      );
      remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
      remove_action(
        'woocommerce_after_single_product_summary',
        'woocommerce_output_related_products',
        20,
      );
    }
  }
}
add_action('wp', 'nera_remove_default_wc_actions');

/**
 * Default payment logos (SVG icons)
 */
function nera_get_default_payment_logos()
{
  return [
    [
      'name' => 'Visa',
      'svg' =>
        '<svg viewBox="0 0 48 48" fill="currentColor" class="h-6 w-auto"><path d="M44 12H4c-2.21 0-4 1.79-4 4v16c0 2.21 1.79 4 4 4h40c2.21 0 4-1.79 4-4V16c0-2.21-1.79-4-4-4z" fill="#1A1F71"/><path d="M19.03 29.09l1.59-9.77h2.54l-1.59 9.77h-2.54zm12.69-9.53c-.5-.2-1.29-.41-2.27-.41-2.51 0-4.28 1.33-4.29 3.24-.02 1.41 1.26 2.2 2.22 2.67 1 .48 1.33.79 1.33 1.22-.01.66-.8.96-1.54.96-1.03 0-1.58-.15-2.42-.52l-.34-.16-.36 2.24c.6.28 1.71.52 2.87.53 2.67 0 4.41-1.32 4.43-3.36.01-1.12-.67-1.97-2.14-2.68-.89-.46-1.44-.76-1.43-1.22 0-.41.46-.85 1.46-.85.84-.01 1.44.18 1.91.38l.23.11.35-2.15zm6.55-.24h-1.96c-.61 0-1.06.17-1.33.81l-3.76 8.97h2.66s.44-1.21.54-1.47h3.26c.07.35.31 1.47.31 1.47h2.35l-2.07-9.78zm-3.13 6.31c.21-.57 1.02-2.76 1.02-2.76-.02.03.21-.57.34-.94l.17.85s.49 2.37.6 2.85h-2.13zm-18.64-6.31l-2.49 6.67-.27-1.35c-.46-1.57-1.9-3.27-3.51-4.12l2.27 8.55h2.69l4-9.75h-2.69z" fill="#fff"/></svg>',
    ],
    [
      'name' => 'Mastercard',
      'svg' =>
        '<svg viewBox="0 0 48 48" fill="currentColor" class="h-6 w-auto"><rect width="48" height="36" rx="4" y="6" fill="#fff"/><circle cx="19" cy="24" r="10" fill="#EB001B"/><circle cx="29" cy="24" r="10" fill="#F79E1B"/><path d="M24 17.5a10 10 0 0 0 0 13 10 10 0 0 0 0-13z" fill="#FF5F00"/></svg>',
    ],
    [
      'name' => 'Apple Pay',
      'svg' =>
        '<svg viewBox="0 0 48 48" fill="currentColor" class="h-6 w-auto"><rect width="48" height="36" rx="4" y="6" fill="#000"/><path d="M16.5 18.5c-.5.6-.9 1.3-.8 2.1.7 0 1.5-.5 2-1.1.5-.5.8-1.2.8-2-.7.1-1.5.5-2 1zm.2 2.3c-1.1 0-2.1.6-2.6.6-.6 0-1.5-.6-2.4-.6-1.2 0-2.4.7-3 1.8-1.3 2.2-.3 5.5.9 7.3.6.9 1.4 1.9 2.4 1.9.9 0 1.3-.6 2.4-.6 1.1 0 1.4.6 2.4.6 1 0 1.7-.9 2.3-1.8.7-1 1-2.1 1-2.1 0 0-2-.8-2-3 0-1.9 1.5-2.7 1.6-2.8-.9-1.3-2.3-1.4-2.7-1.4-1.2.1-2.2.7-2.3.1z" fill="#fff"/><path d="M26 22v8.5h1.3v-2.9h1.8c1.7 0 2.9-1.2 2.9-2.8s-1.2-2.8-2.8-2.8H26zm1.3 1.1h1.5c1.1 0 1.7.6 1.7 1.7s-.6 1.7-1.7 1.7h-1.5v-3.4zm6.3 5.6c.8 0 1.6-.4 2-.9v.7h1.2v-5.1c0-1.2-.9-2-2.4-2-1.4 0-2.4.8-2.5 1.9h1.2c.1-.5.6-.9 1.3-.9.8 0 1.3.4 1.3 1.1v.5l-1.7.1c-1.6.1-2.5.8-2.5 1.9-.1 1.1.8 1.7 2.1 1.7zm.3-1c-.7 0-1.1-.3-1.1-.9 0-.5.4-.8 1.2-.9l1.5-.1v.5c0 .8-.7 1.4-1.6 1.4z" fill="#fff"/></svg>',
    ],
    [
      'name' => 'Google Pay',
      'svg' =>
        '<svg viewBox="0 0 48 48" fill="currentColor" class="h-6 w-auto"><rect width="48" height="36" rx="4" y="6" fill="#fff" stroke="#E5E7EB"/><path d="M24.2 23.5v3h4.7c-.2 1-.8 1.9-1.6 2.5l2.6 2c1.5-1.4 2.4-3.5 2.4-6 0-.6 0-1.1-.1-1.6h-8v.1z" fill="#4285F4"/><path d="M17.2 25.3l-.6.5-2.1 1.6c1.3 2.6 4 4.4 7.2 4.4 2.2 0 4-.7 5.3-1.9l-2.6-2c-.7.5-1.6.8-2.7.8-2.1 0-3.9-1.4-4.5-3.4z" fill="#34A853"/><path d="M12.5 20.5c-.3.7-.5 1.5-.5 2.3s.2 1.6.5 2.3l2.7-2.1c-.2-.4-.3-.9-.3-1.4s.1-1 .3-1.4l-2.7-2.1z" fill="#FABB05"/><path d="M21.7 17.8c1.2 0 2.2.4 3.1 1.2l2.3-2.3c-1.4-1.3-3.2-2.1-5.4-2.1-3.2 0-5.9 1.8-7.2 4.4l2.7 2.1c.6-1.9 2.4-3.3 4.5-3.3z" fill="#E94235"/></svg>',
    ],
    [
      'name' => 'PayPal',
      'svg' =>
        '<svg viewBox="0 0 48 48" fill="currentColor" class="h-6 w-auto"><rect width="48" height="36" rx="4" y="6" fill="#fff" stroke="#E5E7EB"/><path d="M19.5 31.7h-2.8c-.2 0-.3-.1-.3-.3l1.9-12c0-.1.1-.2.3-.2h4.6c1.5 0 2.6.3 3.3 1 .6.5.9 1.3.8 2.2-.3 2.6-2.2 4-5.1 4h-1.3c-.2 0-.4.2-.4.4l-.5 3.2c-.1.2-.3.4-.5.4l-.1.3zm6.8-9.5c.2-1.1-.1-1.9-.8-2.5-.8-.7-2-1-3.6-1h-3c-.4 0-.7.3-.8.7l-1.5 9.5c0 .2.1.4.3.4h2.2l.5-3.4c.1-.4.4-.6.8-.6h.6c2.5 0 4.4-1 5-3.8.1-.1.2-.2.3-.3z" fill="#003087"/><path d="M30.4 22c-.2 1.6-1.1 2.9-2.5 3.7-.7.4-1.5.6-2.5.6h-1.1c-.4 0-.7.3-.8.6l-.6 3.8c0 .2.1.4.3.4h2.2c.3 0 .6-.2.7-.5l.4-2.7c.1-.3.3-.5.6-.5h.4c2.1 0 3.8-.9 4.3-3.4.2-1.1 0-2-.5-2.6l-.9.6z" fill="#009CDE"/></svg>',
    ],
  ];

  return $methods;
}

/**
 * ============================================
 * Toast Notification System Integration
 * ============================================
 */

/**
 * Suppress default WooCommerce notice rendering
 * Toasts will handle all notices via JavaScript
 */
function nera_suppress_wc_notices()
{
  // Only suppress if we're on the frontend and not in admin
  if (is_admin()) {
    return;
  }

  // Remove notice display from templates
  remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
  remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10);
  remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10);
  remove_action('woocommerce_before_cart', 'woocommerce_output_all_notices', 10);
}
add_action('wp_head', 'nera_suppress_wc_notices', 1);

/**
 * Collect WooCommerce notices for toast system
 *
 * @return array Formatted notices array
 */
function nera_get_toast_notices()
{
  if (!function_exists('wc_get_notices')) {
    return [];
  }

  $notices = wc_get_notices();
  $formatted = [];

  foreach ($notices as $type => $messages) {
    foreach ($messages as $message) {
      // Convert WC 'notice' type to 'info' for toast
      $toast_type = $type === 'notice' ? 'info' : $type;

      // Get message text
      $message_text =
        is_array($message) && isset($message['notice']) ? $message['notice'] : $message;

      // Format for toast
      $formatted[] = [
        'type' => $toast_type,
        'message' => wp_kses_post($message_text),
        'id' => 'wc-' . md5($message_text . $type . time()),
        'data' => [
          'cart_url' => wc_get_cart_url(),
        ],
      ];
    }
  }

  // Clear notices after collection
  wc_clear_notices();

  return $formatted;
}

/**
 * Output toast data as inline script in footer
 */
function nera_output_toast_data()
{
  $notices = nera_get_toast_notices();

  if (empty($notices)) {
    return;
  }
  ?>
  <script>
    document.addEventListener('alpine:initialized', function () {
      <?php foreach ($notices as $notice): ?>
          Alpine.store('toast').<?php echo esc_js($notice['type']); ?>(
          <?php echo json_encode(wp_kses_post($notice['message'])); ?>
          <?php if ($notice['type'] === 'success' && !empty($notice['data']['cart_url'])): ?>
              , {
              label: 'View Cart',
              callback: function() { window.location.href = <?php echo json_encode(
                $notice['data']['cart_url'],
              ); ?>; }
              }
          <?php endif; ?>
          );
      <?php endforeach; ?>
    });
  </script>
  <?php
}
add_action('wp_footer', 'nera_output_toast_data', 999);

/**
 * Override Shop Page Template
 *
 * Uses the Product Listing template logic for the main Shop page
 * satisfying the "only for shop" requirement.
 */
function nera_shop_template_loader($template)
{
  if (is_shop() && !is_search()) {
    $custom_template = locate_template('page-templates/shop-template-bridge.php');
    if ($custom_template) {
      return $custom_template;
    }
  }
  return $template;
}
add_filter('template_include', 'nera_shop_template_loader', 20);

/**
 * ============================================
 * Checkout Customizations
 * ============================================
 */

/**
 * Add body class for checkout page
 */
function nera_checkout_body_class($classes)
{
  if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
    $classes[] = 'nera-checkout-page';
  }
  if (function_exists('is_order_received_page') && is_order_received_page()) {
    $classes[] = 'nera-order-received-page';
  }
  return $classes;
}
add_filter('body_class', 'nera_checkout_body_class');

/**
 * Ensure WooCommerce checkout script is loaded
 * Custom checkout template may cause is_checkout() to fail during script enqueue
 */
function nera_enqueue_checkout_scripts()
{
  if (is_checkout() && !is_order_received_page()) {
    wp_enqueue_script('wc-checkout');
  }
}
add_action('wp_enqueue_scripts', 'nera_enqueue_checkout_scripts', 25);

/**
 * Remove shipping fields for lottery-only carts
 * Competition entries are digital — no shipping required
 */
function nera_remove_shipping_for_lottery($fields)
{
  if (!WC()->cart) {
    return $fields;
  }

  $has_shippable = false;
  foreach (WC()->cart->get_cart() as $cart_item) {
    $product = $cart_item['data'];
    if ($product && !$product->is_type('lottery') && $product->needs_shipping()) {
      $has_shippable = true;
      break;
    }
  }

  if (!$has_shippable) {
    unset($fields['shipping']);
  }

  return $fields;
}
add_filter('woocommerce_checkout_fields', 'nera_remove_shipping_for_lottery');

/**
 * Customize default address fields — priorities and labels
 * Runs before locale overrides so our order is preserved
 */
function nera_customize_default_address_fields($fields)
{
  $priorities = [
    'first_name' => 10,
    'last_name' => 20,
    'country' => 50,
    'address_1' => 60,
    'company' => 70,
    'address_2' => 80,
    'city' => 90,
    'state' => 100,
    'postcode' => 110,
  ];

  foreach ($priorities as $key => $priority) {
    if (isset($fields[$key])) {
      $fields[$key]['priority'] = $priority;
    }
  }

  // Ensure address_2 has a visible label
  if (isset($fields['address_2'])) {
    $fields['address_2']['label'] = __('Apartment, suite, unit, etc.', 'nera-competitions');
    $fields['address_2']['label_class'] = [];
  }

  return $fields;
}
add_filter('woocommerce_default_address_fields', 'nera_customize_default_address_fields', 20);

/**
 * Customize billing field widths and non-address field priorities
 * Creates a balanced 2-column grid layout with proper field pairing
 */
function nera_customize_billing_fields($fields)
{
  $half_width = [
    'billing_first_name',
    'billing_last_name',
    'billing_email',
    'billing_phone',
    'billing_city',
    'billing_state',
  ];
  $full_width = [
    'billing_country',
    'billing_address_1',
    'billing_address_2',
    'billing_company',
    'billing_postcode',
  ];

  // Email and phone priorities (not covered by default_address_fields)
  if (isset($fields['billing']['billing_email'])) {
    $fields['billing']['billing_email']['priority'] = 30;
  }
  if (isset($fields['billing']['billing_phone'])) {
    $fields['billing']['billing_phone']['priority'] = 40;
    $fields['billing']['billing_phone']['required'] = false;
  }
  if (isset($fields['billing']['billing_company'])) {
    $fields['billing']['billing_company']['required'] = false;
  }

  foreach ($fields['billing'] as $key => &$field) {
    if (in_array($key, $half_width)) {
      $field['class'][] = 'form-row-half';
    } elseif (in_array($key, $full_width)) {
      $field['class'][] = 'form-row-full';
    }
  }

  return $fields;
}
add_filter('woocommerce_checkout_fields', 'nera_customize_billing_fields');

/**
 * Add required field indicators and autocomplete attributes
 */
function nera_add_required_indicators_and_autocomplete($fields)
{
  // Add autocomplete attributes for better browser autofill
  if (isset($fields['billing']['billing_email'])) {
    $fields['billing']['billing_email']['autocomplete'] = 'email';
    $fields['billing']['billing_email']['custom_attributes'] = ['inputmode' => 'email'];
  }

  if (isset($fields['billing']['billing_phone'])) {
    $fields['billing']['billing_phone']['autocomplete'] = 'tel';
    $fields['billing']['billing_phone']['custom_attributes'] = ['inputmode' => 'tel'];
    $fields['billing']['billing_phone']['type'] = 'tel';
  }

  if (isset($fields['billing']['billing_first_name'])) {
    $fields['billing']['billing_first_name']['autocomplete'] = 'given-name';
  }

  if (isset($fields['billing']['billing_last_name'])) {
    $fields['billing']['billing_last_name']['autocomplete'] = 'family-name';
  }

  if (isset($fields['billing']['billing_address_1'])) {
    $fields['billing']['billing_address_1']['autocomplete'] = 'address-line1';
  }

  if (isset($fields['billing']['billing_address_2'])) {
    $fields['billing']['billing_address_2']['autocomplete'] = 'address-line2';
  }

  if (isset($fields['billing']['billing_city'])) {
    $fields['billing']['billing_city']['autocomplete'] = 'address-level2';
  }

  if (isset($fields['billing']['billing_state'])) {
    $fields['billing']['billing_state']['autocomplete'] = 'address-level1';
  }

  if (isset($fields['billing']['billing_postcode'])) {
    $fields['billing']['billing_postcode']['autocomplete'] = 'postal-code';
    $fields['billing']['billing_postcode']['custom_attributes'] = ['inputmode' => 'text'];
  }

  if (isset($fields['billing']['billing_country'])) {
    $fields['billing']['billing_country']['autocomplete'] = 'country';
  }

  return $fields;
}
add_filter('woocommerce_checkout_fields', 'nera_add_required_indicators_and_autocomplete', 20);

/**
 * ============================================================================
 * TERA WALLET INTEGRATION
 * ============================================================================
 */

/**
 * Add mobile-only back to dashboard link on the wallet page.
 * Uses the plugin's action hook since the template override path (woo-wallet/) differs
 * from the theme's woocommerce/woo-wallet/ location.
 */
function nera_wallet_back_to_dashboard()
{
  if (!is_account_page()) {
    return;
  }
  ?>
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>"
     class="lg:hidden inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary transition-colors mb-4">
    <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
    <?php esc_html_e('Back to Dashboard', 'nera-competitions'); ?>
  </a>
  <?php
}
add_action('woo_wallet_before_my_wallet_content', 'nera_wallet_back_to_dashboard');

/**
 * Map woo_wallet_is_enable_add to topup setting
 * Plugin template checks woo_wallet_is_enable_add but plugin only hooks woo_wallet_is_enable_top_up.
 * This ensures the Wallet topup tab and form are hidden when disabled in settings.
 */
function nera_wallet_is_enable_add_filter($is_enable)
{
  return apply_filters('woo_wallet_is_enable_top_up', true);
}
add_filter('woo_wallet_is_enable_add', 'nera_wallet_is_enable_add_filter');

/**
 * Customize wallet payment gateway icon
 * Replaces default icon with Material Symbols icon matching theme design
 */
function nera_customize_wallet_gateway_icon($icon, $gateway_id)
{
  if ($gateway_id !== 'wallet') {
    return $icon;
  }

  // Return empty - payment-method template already shows wallet icon; avoid purple-tinted duplicate
  return '';
}
add_filter('woocommerce_gateway_icon', 'nera_customize_wallet_gateway_icon', 10, 2);

/**
 * Customize wallet payment gateway title
 * Adds visual emphasis and helpful messaging
 */
function nera_customize_wallet_gateway_title($title, $gateway_id)
{
  if ($gateway_id !== 'wallet') {
    return $title;
  }

  // Check if user has sufficient balance for full payment
  if (!is_user_logged_in()) {
    return $title;
  }

  $user_id = get_current_user_id();
  if (function_exists('woo_wallet') && is_object(woo_wallet()) && is_object(woo_wallet()->wallet)) {
    // Use 'edit' to get raw number - 'view' returns HTML and breaks numeric comparisons
    $balance = (float) woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');
    $cart_total = WC()->cart ? (float) WC()->cart->total : 0;

    if ($balance >= $cart_total && $balance > 0) {
      // Add badge for full payment capability
      $title .=
        ' <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-success-bg text-success-text ml-2">✓ ' .
        esc_html__('Sufficient Balance', 'nera-competitions') .
        '</span>';
    }
  }

  return $title;
}
add_filter('woocommerce_gateway_title', 'nera_customize_wallet_gateway_title', 10, 2);

/**
 * Customize wallet payment gateway description
 * Adds balance information and helpful instructions
 */
function nera_customize_wallet_gateway_description($description, $gateway_id)
{
  if ($gateway_id !== 'wallet') {
    return $description;
  }

  if (!is_user_logged_in()) {
    return $description;
  }

  $user_id = get_current_user_id();
  if (function_exists('woo_wallet') && is_object(woo_wallet()) && is_object(woo_wallet()->wallet)) {
    // Use 'edit' to get raw number - 'view' returns HTML and breaks display
    $balance = (float) woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');

    if ($balance > 0) {
      $custom_description =
        '<div class="nera-wallet-gateway-description mt-2 rounded-xl border border-gray-200 bg-surface p-3 transition-all duration-300">';
      $custom_description .= '<div class="flex items-start gap-2">';
      $custom_description .=
        '<span class="material-symbols-outlined mt-0.5 text-[18px] text-text-secondary">info</span>';
      $custom_description .= '<div class="min-w-0 flex-1">';
      $custom_description .=
        '<p class="mb-1 text-[13px] font-semibold text-text-primary">' .
        esc_html__('Available Balance:', 'nera-competitions') .
        ' <strong class="text-text-primary">' .
        wc_price($balance) .
        '</strong></p>';

      $cart_total = WC()->cart ? (float) WC()->cart->total : 0;
      if ($balance >= $cart_total) {
        $custom_description .=
          '<p class="m-0 text-xs text-text-secondary">' .
          esc_html__(
            'Your wallet balance is sufficient to complete this purchase.',
            'nera-competitions',
          ) .
          '</p>';
      } else {
        $remaining = $cart_total - $balance;
        $custom_description .=
          '<p class="m-0 text-xs text-text-secondary">' .
          sprintf(
            esc_html__(
              'Your wallet will cover %1$s. Remaining amount %2$s will be charged to another payment method.',
              'nera-competitions',
            ),
            '<strong class="text-text-primary">' . wc_price($balance) . '</strong>',
            '<strong class="text-text-primary">' . wc_price($remaining) . '</strong>',
          ) .
          '</p>';
      }

      $custom_description .= '</div></div></div>';

      return $custom_description;
    }
  }

  return $description;
}
add_filter('woocommerce_gateway_description', 'nera_customize_wallet_gateway_description', 10, 2);

/**
 * Add wallet balance indicator via JavaScript (to avoid HTML escaping issues)
 * Adds a badge next to "My Wallet" menu item showing current balance
 */
function nera_add_wallet_balance_script()
{
  if (!is_account_page() || !is_user_logged_in()) {
    return;
  }

  if (
    !function_exists('woo_wallet') ||
    !is_object(woo_wallet()) ||
    !is_object(woo_wallet()->wallet)
  ) {
    return;
  }

  $user_id = get_current_user_id();
  // Use 'edit' to get raw number - 'view' returns HTML and causes number_format() to receive string
  $balance = (float) woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');

  if ($balance <= 0) {
    return;
  }

  // Get formatted balance (just the number part)
  $currency_symbol = html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8');
  $formatted_balance = number_format($balance, 2);
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Find the My Wallet menu item
    const walletMenuItem = document.querySelector('.woocommerce-MyAccount-navigation-link--woo-wallet a');
    
    if (walletMenuItem && !walletMenuItem.querySelector('.wallet-balance-badge')) {
      // Create badge element
      const badge = document.createElement('span');
      badge.className = 'wallet-balance-badge';
      badge.textContent = '<?php echo esc_js($currency_symbol . $formatted_balance); ?>';
      
      // Apply inline styles
      badge.style.cssText = 'display: inline-flex; align-items: center; justify-content: center; padding: 2px 8px; margin-left: 8px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); color: var(--color-on-primary); border-radius: 12px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 4px color-mix(in srgb, var(--color-primary) 30%, transparent); vertical-align: middle;';
      
      // Append badge to menu item
      walletMenuItem.appendChild(badge);
    }
  });
  </script>
  <?php
}
add_action('wp_footer', 'nera_add_wallet_balance_script');

/**
 * Remove Downloads tab from My Account navigation
 */
function nera_remove_downloads_from_account_menu($items)
{
  unset($items['downloads']);
  return $items;
}
add_filter('woocommerce_account_menu_items', 'nera_remove_downloads_from_account_menu', 10);

/**
 * Get Material Symbol icon for My Account menu items
 * Maps WooCommerce account endpoints to appropriate icons
 *
 * @param string $endpoint The WooCommerce endpoint slug
 * @return string Icon name for Material Symbols
 */
function nera_get_account_menu_icon($endpoint)
{
  $icons = [
    'dashboard' => 'home',
    'orders' => 'receipt_long',
    'downloads' => 'download',
    'edit-address' => 'location_on',
    'edit-account' => 'person',
    'woo-wallet' => 'account_balance_wallet',
    'customer-logout' => 'logout',
    'payment-methods' => 'credit_card',
    'subscriptions' => 'autorenew',
  ];

  return isset($icons[$endpoint]) ? $icons[$endpoint] : 'chevron_right';
}

/**
 * Add icons to My Account navigation menu items via JavaScript
 * Uses JavaScript to avoid HTML escaping issues
 */
function nera_add_account_menu_icons_script()
{
  if (!is_account_page()) {
    return;
  } ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const iconMap = {
      'dashboard': 'home',
      'orders': 'receipt_long',
      'downloads': 'download',
      'edit-address': 'location_on',
      'edit-account': 'person',
      'woo-wallet': 'account_balance_wallet',
      'customer-logout': 'logout',
      'payment-methods': 'credit_card',
      'subscriptions': 'autorenew'
    };

    // Find all navigation links
    const navLinks = document.querySelectorAll('.woocommerce-MyAccount-navigation-link');
    
    navLinks.forEach(link => {
      // Get the endpoint from the class name
      const classList = Array.from(link.classList);
      const endpointClass = classList.find(cls => cls.includes('woocommerce-MyAccount-navigation-link--'));
      
      if (endpointClass) {
        const endpoint = endpointClass.replace('woocommerce-MyAccount-navigation-link--', '');
        const icon = iconMap[endpoint] || 'chevron_right';
        
        // Find the anchor tag
        const anchor = link.querySelector('a');
        if (anchor && !anchor.querySelector('.material-symbols-outlined')) {
          // Create icon element
          const iconSpan = document.createElement('span');
          iconSpan.className = 'material-symbols-outlined';
          iconSpan.textContent = icon;
          
          // Prepend icon to anchor
          anchor.insertBefore(iconSpan, anchor.firstChild);
        }
      }
    });
  });
  </script>
  <?php
}
add_action('wp_footer', 'nera_add_account_menu_icons_script');

/**
 * Add custom styles for My Account page
 * Creates modern card-based navigation with icons and hover effects
 */
function nera_my_account_styles()
{
  if (!is_account_page()) {
    return;
  } ?>
  <style>
    /* My Account Page - Main Container */
    body.woocommerce-account.logged-in .site-main,
    body.woocommerce-account.logged-in .content-area,
    body.woocommerce-account.logged-in article {
      max-width: 1280px;
      margin: 0 auto;
      padding: 2rem 1rem;
    }

    /* Two Column Grid Layout - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce {
      display: grid !important;
      grid-template-columns: 1fr !important;
      gap: 2rem;
      width: 100%;
      max-width: 100%;
      align-items: start;
    }

    @media (min-width: 1024px) {
      body.woocommerce-account.logged-in .woocommerce,
      body.woocommerce-account.logged-in .content-area .woocommerce,
      body.woocommerce-account.logged-in article .woocommerce,
      .woocommerce-account.logged-in .woocommerce {
        grid-template-columns: 280px minmax(0, 1280px) !important;
        max-width: calc(280px + 1280px + 2rem) !important;
      }
    }

    /* Navigation Sidebar - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation {
      background: transparent;
      border: none;
      padding: 0;
      width: 100%;
      min-width: 0;
      grid-column: 1;
    }
    
    @media (min-width: 1024px) {
      body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation {
        position: sticky !important;
        top: 2rem;
        min-width: 280px !important;
        width: 280px !important;
        max-width: 280px !important;
      }
    }

    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation ul {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      width: 100%;
    }

    /* Navigation Link Cards - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link {
      margin: 0;
      border-radius: 1rem;
      overflow: hidden;
      transition: all 0.3s ease;
      width: 100%;
    }

    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link a {
      display: flex;
      align-items: center;
      padding: 1rem 1.25rem;
      background: var(--color-surface);
      color: var(--color-text-secondary);
      text-decoration: none;
      font-weight: 500;
      border: 1px solid var(--color-gray-100);
      border-radius: 1rem;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      width: 100%;
      box-sizing: border-box;
    }

    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link a:hover {
      background: var(--color-gray-50);
      border-color: var(--color-gray-200);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      transform: translateX(4px);
    }

    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link.is-active a {
      background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
      color: var(--color-on-primary);
      border-color: transparent;
      box-shadow: 0 10px 15px -3px color-mix(in srgb, var(--color-primary) 30%, transparent);
    }

    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link.is-active a:hover {
      transform: translateX(4px);
      box-shadow: 0 20px 25px -5px color-mix(in srgb, var(--color-primary) 40%, transparent);
    }

    /* Icon Styling - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link a .material-symbols-outlined {
      font-size: 1.5rem;
      line-height: 1;
      flex-shrink: 0;
      margin-right: 0.75rem;
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    /* Content Area - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content {
      background: var(--color-surface);
      border-radius: 1.5rem;
      border: 1px solid var(--color-gray-100);
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
      padding: 2rem;
      width: 100%;
      min-width: 0;
      max-width: 100%;
      overflow: hidden;
      grid-column: 2;
      box-sizing: border-box;
    }
    
    @media (min-width: 1024px) {
      body.woocommerce-account.logged-in .woocommerce-MyAccount-content {
        width: 100% !important;
        max-width: 1280px !important;
        min-width: 0 !important;
        flex-grow: 1 !important;
      }
    }

    @media (max-width: 640px) {
      body.woocommerce-account.logged-in .woocommerce-MyAccount-content {
        padding: 1.5rem;
      }
    }
    
    /* Ensure dashboard content doesn't overflow - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content .nera-account-dashboard,
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content .nera-account-orders,
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content .nera-account-addresses,
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content .nera-edit-account,
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content .nera-edit-address,
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content .nera-view-order {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    /* Force content area children to respect container width - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content > * {
      max-width: 100%;
      box-sizing: border-box;
    }

    /* Page Title - Only for logged-in users */
    body.woocommerce-account.logged-in .entry-header,
    body.woocommerce-account.logged-in .page-header {
      margin-bottom: 2rem;
      max-width: 1280px;
      margin-left: auto;
      margin-right: auto;
      padding: 0 1rem;
    }
    
    body.woocommerce-account.logged-in .entry-title,
    body.woocommerce-account.logged-in .page-title {
      font-size: 2.25rem;
      font-weight: 700;
      color: var(--color-text-primary);
      margin-bottom: 0.5rem;
    }

    /* Dashboard Heading - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce-MyAccount-content > h2 {
      color: var(--color-text-primary);
      font-size: 1.875rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    /* Ensure Material Symbols icons render correctly */
    .material-symbols-outlined {
      font-family: 'Material Symbols Outlined';
      font-weight: normal;
      font-style: normal;
      display: inline-block;
      line-height: 1;
      text-transform: none;
      letter-spacing: normal;
      word-wrap: normal;
      white-space: nowrap;
      direction: ltr;
    }
    
    /* Fix any grid/flex container issues - Only for logged-in users */
    body.woocommerce-account.logged-in .woocommerce > * {
      min-width: 0;
      box-sizing: border-box;
    }

    /* Mobile: Stack navigation as icon+label grid - Only for logged-in users */
    @media (max-width: 1023px) {
      body.woocommerce-account.logged-in .woocommerce {
        grid-template-columns: 1fr !important;
      }

      /* Fix grid-column values so both nav and content sit in the single column */
      body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation {
        grid-column: 1 !important;
      }

      body.woocommerce-account.logged-in .woocommerce-MyAccount-content {
        grid-column: 1 !important;
      }

      /* 2-column icon+label grid nav */
      body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation ul {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        flex-direction: unset;
        overflow-x: unset;
        gap: 0.5rem;
        padding-bottom: 0;
      }

      body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link {
        flex-shrink: unset;
      }

      body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link a {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0.875rem 0.75rem;
        white-space: normal;
        font-size: 0.8125rem;
        min-height: 4.5rem;
      }

      body.woocommerce-account.logged-in .woocommerce-MyAccount-navigation-link a .material-symbols-outlined {
        font-size: 1.5rem;
        margin-right: 0;
        margin-bottom: 0.25rem;
      }
    }

    @media (max-width: 480px) {
      body.woocommerce-account.logged-in .woocommerce-MyAccount-content {
        padding: 1rem;
        border-radius: 1rem;
      }
    }

    /* Address form: stack first/last name fields on mobile */
    @media (max-width: 767px) {
      .woocommerce-account .form-row-first,
      .woocommerce-account .form-row-last,
      .woocommerce-account .form-row--first,
      .woocommerce-account .form-row--last {
        float: none !important;
        width: 100% !important;
        clear: both;
      }
    }
    
    /* Ensure proper spacing and no overflow - Only for logged-in users */
    body.woocommerce-account.logged-in {
      overflow-x: hidden;
    }
    
    /* Force grid layout override - highest specificity - Only for logged-in users */
    @media (min-width: 1024px) {
      body.woocommerce-account.logged-in article .woocommerce,
      body.woocommerce-account.logged-in .content-area > .woocommerce,
      body.woocommerce-account.logged-in main .woocommerce {
        grid-template-columns: 280px minmax(0, 1280px) !important;
      }
    }
  </style>
  <?php
}
add_action('wp_head', 'nera_my_account_styles');

/**
 * ============================================
 * Cart Quantity Update Validation
 * ============================================
 */

/**
 * Validate cart quantity updates for lottery products
 * Prevents invalid quantities from being set before check_cart_items removes them
 *
 * @param bool $passed Validation status
 * @param string $cart_item_key Cart item key
 * @param array $values Cart item values
 * @param int $quantity New quantity
 * @return bool
 */
function nera_validate_cart_quantity_update($passed, $cart_item_key, $values, $quantity)
{
  // Skip non-lottery products
  if (!isset($values['data']) || !$values['data']->is_type('lottery')) {
    return $passed;
  }

  $product = $values['data'];
  $product_id = $product->get_id();

  // Get min/max using same logic as theme data builder
  $min_per_order = method_exists($product, 'get_min_purchase_quantity')
    ? $product->get_min_purchase_quantity()
    : 1;

  $max_per_order = method_exists($product, 'get_max_purchase_quantity')
    ? $product->get_max_purchase_quantity()
    : 100;

  // Validate minimum
  if ($quantity < $min_per_order) {
    wc_add_notice(
      sprintf(
        __('The minimum quantity for %1$s is %2$d.', 'nera-competitions'),
        $product->get_name(),
        $min_per_order,
      ),
      'error',
    );
    return false;
  }

  // Validate maximum
  if ($quantity > $max_per_order) {
    wc_add_notice(
      sprintf(
        __('The maximum quantity for %1$s is %2$d.', 'nera-competitions'),
        $product->get_name(),
        $max_per_order,
      ),
      'error',
    );
    return false;
  }

  return $passed;
}
add_filter('woocommerce_update_cart_validation', 'nera_validate_cart_quantity_update', 10, 4);

/**
 * Replace [Remove] text with trash icon in cart/checkout coupon totals
 * Applies to Summary (Cart) and Order Summary (Checkout)
 *
 * @param string   $coupon_html         The coupon HTML from WooCommerce
 * @param WC_Coupon $coupon              The coupon object
 * @param string   $discount_amount_html The discount amount HTML
 * @return string Modified coupon HTML
 */
function nera_coupon_remove_trash_icon($coupon_html, $coupon, $discount_amount_html)
{
  // Add remove-coupon class for checkout.js AJAX interception
  // Style as inline button matching cart/form coupon remove buttons
  $coupon_html = str_replace(
    'class="woocommerce-remove-coupon"',
    'class="woocommerce-remove-coupon remove-coupon inline-flex items-center justify-center w-5 h-5 rounded-full bg-danger-bg hover:bg-danger-border text-danger ml-1 transition-colors"',
    $coupon_html
  );

  // Replace [Remove] text with trash icon (works with translations)
  $coupon_html = preg_replace(
    '/>([^<]*)<\/a>\s*$/',
    '><span class="material-symbols-outlined !text-xs !font-bold text-danger">delete</span></a>',
    $coupon_html
  );

  return $coupon_html;
}
add_filter('woocommerce_cart_totals_coupon_html', 'nera_coupon_remove_trash_icon', 10, 3);

/**
 * AJAX handler to get applied coupons HTML for checkout page
 * Returns the updated applied coupons display when coupons are applied/removed
 */
function nera_get_applied_coupons_html()
{
  // Get applied coupons
  $applied_coupons = WC()->cart->get_applied_coupons();

  ob_start();
  if (!empty($applied_coupons)) {
    ?>
    <div class="mt-4 space-y-2" id="checkout-applied-coupons">
      <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
        <?php esc_html_e('Applied Coupons:', 'nera-competitions'); ?>
      </p>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($applied_coupons as $code): ?>
          <div class="inline-flex items-center gap-2 bg-success-bg text-success-text px-3 py-1.5 rounded-lg border border-success-bg text-sm font-medium">
            <span class="material-symbols-outlined text-base">local_offer</span>
            <span><?php echo esc_html($code); ?></span>
            <a href="#"
              data-coupon="<?php echo esc_attr($code); ?>"
              class="remove-coupon flex items-center justify-center w-5 h-5 rounded-full bg-success-border hover:bg-success-border text-success-text transition-colors"
              aria-label="<?php esc_attr_e('Remove coupon', 'nera-competitions'); ?>"
              role="button">
              <span class="material-symbols-outlined !text-xs">close</span>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
  }

  wp_send_json_success(
    array(
      'html' => ob_get_clean(),
      'has_coupons' => !empty($applied_coupons),
    ),
  );
}
add_action('wp_ajax_get_applied_coupons', 'nera_get_applied_coupons_html');
add_action('wp_ajax_nopriv_get_applied_coupons', 'nera_get_applied_coupons_html');

/**
 * Register custom checkout fragments for AJAX updates
 * This ensures the Order Summary section updates automatically when
 * coupons are added/removed or cart contents change
 *
 * @param array $fragments Fragments to refresh via AJAX
 * @return array Modified fragments array
 */
function nera_checkout_order_review_fragment($fragments)
{
  ob_start();
  ?>
  <div class="nera-checkout-order-review-wrapper">
    <?php get_template_part('template-parts/checkout/order-review'); ?>
  </div>
  <?php
  $fragments['.nera-checkout-order-review-wrapper'] = ob_get_clean();
  return $fragments;
}
add_filter('woocommerce_update_order_review_fragments', 'nera_checkout_order_review_fragment');

/**
 * Server-side validation for the custom registration form.
 * Checks that confirm password matches, and that both required checkboxes are ticked.
 *
 * Hooked to `woocommerce_process_registration_errors` which is the correct filter
 * in WooCommerce's process_registration() handler.
 *
 * @param WP_Error $validation_error Existing errors object.
 * @param string   $username         Submitted (or auto-generated) username.
 * @param string   $password         Submitted password.
 * @param string   $email            Submitted email.
 * @return WP_Error
 */
function nera_validate_registration($validation_error, $username, $password, $email)
{
  // Full name required
  if (empty(trim(isset($_POST['billing_first_name']) ? $_POST['billing_first_name'] : ''))) {
    $validation_error->add(
      'name_required',
      __('Please enter your full name.', 'woocommerce'),
    );
  }

  // Confirm password match
  if (
    isset($_POST['password'], $_POST['password2']) &&
    $_POST['password'] !== $_POST['password2']
  ) {
    $validation_error->add(
      'password_mismatch',
      __('The passwords you entered do not match. Please try again.', 'woocommerce'),
    );
  }

  // Terms & Conditions checkbox
  if (empty($_POST['terms_conditions'])) {
    $validation_error->add(
      'terms_required',
      __('You must agree to the Terms &amp; Conditions to register.', 'woocommerce'),
    );
  }

  // Age confirmation checkbox
  if (empty($_POST['age_confirm'])) {
    $validation_error->add(
      'age_required',
      __('You must confirm that you are over the age of 18 to register.', 'woocommerce'),
    );
  }

  return $validation_error;
}
add_filter('woocommerce_process_registration_errors', 'nera_validate_registration', 10, 4);

/**
 * Save the full name entered at registration to user meta.
 * Splits the value on the first space to populate first_name and last_name,
 * and mirrors the values into billing meta so the My Account address is pre-filled.
 *
 * @param int $customer_id Newly created customer user ID.
 */
function nera_save_registration_full_name($customer_id)
{
  if (empty($_POST['billing_first_name'])) {
    return;
  }

  $full_name = sanitize_text_field(wp_unslash($_POST['billing_first_name']));
  $parts     = explode(' ', $full_name, 2);

  $first_name = $parts[0];
  $last_name  = isset($parts[1]) ? $parts[1] : '';

  update_user_meta($customer_id, 'first_name', $first_name);
  update_user_meta($customer_id, 'last_name', $last_name);
  update_user_meta($customer_id, 'billing_first_name', $first_name);
  update_user_meta($customer_id, 'billing_last_name', $last_name);

  wp_update_user([
    'ID'           => $customer_id,
    'display_name' => $full_name,
  ]);
}
add_action('woocommerce_created_customer', 'nera_save_registration_full_name');

/**
 * Force WooCommerce to always use the submitted password during registration.
 *
 * Without this, if the WooCommerce "Automatically generate a customer password"
 * setting is enabled, WooCommerce discards the form password and emails an
 * auto-generated one instead — causing the entered password to silently fail at login.
 */
add_filter('option_woocommerce_registration_generate_password', function ($value) {
  if (isset($_POST['register'], $_POST['woocommerce-register-nonce'])) {
    return 'no';
  }
  return $value;
});

/**
 * Require login to access the checkout page.
 *
 * Redirects unauthenticated visitors to the login/register page with
 * the checkout URL as the redirect destination. This works as a
 * belt-and-suspenders guard alongside the template-level check in
 * woocommerce/checkout/form-checkout.php.
 */
add_action('template_redirect', function () {
  if (is_checkout() && !is_user_logged_in() && !is_wc_endpoint_url('order-received')) {
    wp_safe_redirect(add_query_arg('redirect_to', rawurlencode(wc_get_checkout_url()), wc_get_page_permalink('myaccount')));
    exit;
  }
});

/**
 * Self-service permanent account deletion from Edit Account (danger zone form).
 *
 * Verifies nonce and that the user only deletes their own account. Does not rely on
 * `delete_users` capability (customers typically lack it).
 */
function nera_handle_deactivate_account_request()
{
  if (!isset($_POST['action']) || $_POST['action'] !== 'nera_deactivate_account') {
    return;
  }

  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    return;
  }

  if (!is_user_logged_in()) {
    return;
  }

  $nonce = isset($_POST['nera-deactivate-account-nonce'])
    ? sanitize_text_field(wp_unslash($_POST['nera-deactivate-account-nonce']))
    : '';

  if (!$nonce || !wp_verify_nonce($nonce, 'nera_deactivate_account')) {
    wc_add_notice(
      __('Security check failed. Please try again.', 'nera-competitions-standard'),
      'error',
    );
    wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));
    exit;
  }

  $user_id = get_current_user_id();
  $posted_id = isset($_POST['nera_deactivate_user_id']) ? absint($_POST['nera_deactivate_user_id']) : 0;

  if ($posted_id !== $user_id || $user_id < 1) {
    wc_add_notice(__('Invalid request.', 'nera-competitions-standard'), 'error');
    wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));
    exit;
  }

  /** @var WP_User $current */
  $current = wp_get_current_user();
  if ($current && $current->exists() && in_array('administrator', (array) $current->roles, true)) {
    wc_add_notice(
      __(
        'Administrator accounts cannot be deleted from this page. Please contact the site owner.',
        'nera-competitions-standard',
      ),
      'error',
    );
    wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));
    exit;
  }

  if ((int) $user_id === 1) {
    wc_add_notice(
      __('This account cannot be deleted from here.', 'nera-competitions-standard'),
      'error',
    );
    wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));
    exit;
  }

  require_once ABSPATH . 'wp-admin/includes/user.php';

  $deleted = wp_delete_user($user_id);

  if (!$deleted) {
    wc_add_notice(
      __('We could not delete your account. Please contact support.', 'nera-competitions-standard'),
      'error',
    );
    wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));
    exit;
  }

  wp_clear_auth_cookie();
  wp_set_current_user(0);

  wp_safe_redirect(add_query_arg('nera_account_closed', '1', home_url('/')));
  exit;
}
add_action('template_redirect', 'nera_handle_deactivate_account_request', 0);

/**
 * One-time success notice after account deletion (guest landing with query flag).
 */
function nera_account_closed_flash_notice()
{
  if (!isset($_GET['nera_account_closed']) || (string) $_GET['nera_account_closed'] !== '1') {
    return;
  }

  if (is_user_logged_in()) {
    return;
  }

  if (function_exists('wc_add_notice')) {
    wc_add_notice(
      __('Your account has been permanently deleted.', 'nera-competitions-standard'),
      'success',
    );
  }

  wp_safe_redirect(home_url('/'));
  exit;
}
add_action('template_redirect', 'nera_account_closed_flash_notice', 1);
