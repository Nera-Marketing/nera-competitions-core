<?php
/**
 * Competition Shortcodes for Gutenberg Patterns
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Render Competition Status Badge
 * Usage: [competition_badge]
 */
function nera_shortcode_competition_badge()
{
  global $product;
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  if (!$product) {
    return '';
  }

  $product_id = $product->get_id();

  // Get lottery specific data
  $max_tickets = get_post_meta($product_id, '_lty_maximum_tickets', true);
  $sold_tickets = method_exists($product, 'get_purchased_ticket_count')
    ? $product->get_purchased_ticket_count()
    : 0;

  // Calculate progress
  $progress = $max_tickets ? min(100, round(($sold_tickets / $max_tickets) * 100)) : 0;
  $remaining = $max_tickets ? $max_tickets - $sold_tickets : 0;

  // Get end date for countdown
  $end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
  $days_left = 0;
  $hours_left = 0;

  if ($end_date_gmt) {
    $end_timestamp = strtotime($end_date_gmt);
    $now = time();
    $diff = $end_timestamp - $now;

    if ($diff > 0) {
      $days_left = floor($diff / 86400);
      $hours_left = floor(($diff % 86400) / 3600);
    }
  }

  // Determine status badge
  $badge_text = '';
  $badge_class = 'bg-danger';
  if ($max_tickets && $remaining <= 0) {
    $badge_text = __('Sold Out', 'nera-competitions');
  } elseif ($remaining > 0 && $remaining <= 50) {
    $badge_text = sprintf(__('Last %d Tickets', 'nera-competitions'), $remaining);
  } elseif ($days_left <= 1 && ($days_left > 0 || $hours_left > 0)) {
    $badge_text = __('Ending Soon', 'nera-competitions');
  } elseif ($progress >= 90) {
    $badge_text = __('Almost Gone', 'nera-competitions');
    $badge_class = 'bg-warning';
  }

  if (!$badge_text) {
    return '';
  }

  ob_start();
  ?>
  <div
    class="absolute top-4 left-4 z-10 <?php echo esc_attr(
      $badge_class,
    ); ?> text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
    <?php echo esc_html($badge_text); ?>
  </div>
  <?php return ob_get_clean();
}
add_shortcode('competition_badge', 'nera_shortcode_competition_badge');

/**
 * Render Competition Price Badge
 * Usage: [competition_price]
 */
function nera_shortcode_competition_price()
{
  global $product;
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  if (!$product) {
    return '';
  }

  $price = $product->get_price();

  ob_start();
  ?>
  <div
    class="absolute bottom-4 right-4 z-10 bg-surface/90 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20 shadow-sm">
    <span class="text-xs font-bold text-primary">
      <?php echo wc_price($price); ?>
      <?php _e('per entry', 'nera-competitions'); ?>
    </span>
  </div>
  <?php return ob_get_clean();
}
add_shortcode('competition_price', 'nera_shortcode_competition_price');

/**
 * Render Competition Image with Links
 * Usage: [competition_image]
 */
function nera_shortcode_competition_image()
{
  global $product;
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  if (!$product) {
    return '';
  }

  $image_id = $product->get_image_id();
  $permalink = get_permalink($product->get_id());

  ob_start();
  ?>
  <a href="<?php echo esc_url($permalink); ?>" class="block w-full h-full">
    <?php if ($image_id): ?>
      <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
      <div
        class="w-full h-full bg-center bg-no-repeat bg-cover transform group-hover:scale-110 transition-transform duration-700"
        style="background-image: url('<?php echo esc_url($image_url); ?>');">
      </div>
    <?php else: ?>
      <div class="w-full h-full flex items-center justify-center bg-gray-100">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
          class="text-gray-300">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
          <circle cx="8.5" cy="8.5" r="1.5" />
          <polyline points="21 15 16 10 5 21" />
        </svg>
      </div>
    <?php endif; ?>
  </a>
  <?php return ob_get_clean();
}
add_shortcode('competition_image', 'nera_shortcode_competition_image');

/**
 * Render Competition Progress Bar
 * Usage: [competition_progress]
 */
function nera_shortcode_competition_progress()
{
  global $product;
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  if (!$product) {
    return '';
  }

  $product_id = $product->get_id();
  $max_tickets = get_post_meta($product_id, '_lty_maximum_tickets', true);

  if (!$max_tickets) {
    return '';
  }

  $sold_tickets = method_exists($product, 'get_purchased_ticket_count')
    ? $product->get_purchased_ticket_count()
    : 0;

  $progress = min(100, round(($sold_tickets / $max_tickets) * 100));

  ob_start();
  ?>
  <div class="space-y-3">
    <div class="flex justify-between items-center text-xs font-bold">
      <span class="text-text-secondary uppercase tracking-tighter">
        <?php _e('Tickets Sold', 'nera-competitions'); ?>
      </span>
      <span class="text-primary">
        <?php echo esc_html($progress); ?>%
      </span>
    </div>

    <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
      <div class="h-full bg-primary rounded-full transition-all duration-500"
        style="width: <?php echo esc_attr($progress); ?>%"></div>
    </div>
  </div>
  <?php return ob_get_clean();
}
add_shortcode('competition_progress', 'nera_shortcode_competition_progress');

/**
 * Render Competition Countdown and Actions
 * Usage: [competition_actions]
 */
function nera_shortcode_competition_actions()
{
  global $product;
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  if (!$product) {
    return '';
  }

  $product_id = $product->get_id();
  $end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);

  $days_left = 0;
  $hours_left = 0;
  $mins_left = 0;

  if ($end_date_gmt) {
    $end_timestamp = strtotime($end_date_gmt);
    $now = time();
    $diff = $end_timestamp - $now;

    if ($diff > 0) {
      $days_left = floor($diff / 86400);
      $hours_left = floor(($diff % 86400) / 3600);
      $mins_left = floor(($diff % 3600) / 60);
    }
  }

  ob_start();
  ?>
  <div class="flex items-center justify-between pt-4">
    <?php if ($end_date_gmt): ?>
      <div class="flex items-center gap-1 text-text-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2" class="opacity-60">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span class="text-xs font-semibold uppercase">
          <?php printf('%dd : %02dh : %02dm', $days_left, $hours_left, $mins_left); ?>
        </span>
      </div>
    <?php else: ?>
      <div></div>
    <?php endif; ?>

    <a href="<?php the_permalink(); ?>"
      class="bg-primary/5 hover:bg-primary text-primary hover:!text-white text-xs font-extrabold px-4 py-2 rounded-lg transition-all duration-300">
      <?php _e('ENTER', 'nera-competitions'); ?>
    </a>
  </div>
  <?php return ob_get_clean();
}
add_shortcode('competition_actions', 'nera_shortcode_competition_actions');

/**
 * Render Entire Featured Competitions Section
 * Usage: [featured_competitions]
 */
function nera_shortcode_featured_competitions()
{
  ob_start();
  get_template_part('template-parts/homepage/featured-competitions');
  return ob_get_clean();
}
add_shortcode('featured_competitions', 'nera_shortcode_featured_competitions');
