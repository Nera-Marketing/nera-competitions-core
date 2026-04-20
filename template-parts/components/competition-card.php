<?php
/**
 * Competition Card Component Template Part
 *
 * Reusable card for displaying competition products.
 * Handles display logic, badges, progress bars, and countdowns.
 *
 * @package Nera_Competitions
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type string $x_show AlpineJS expression for visibility toggling.
 *     @type string $extra_attributes Additional HTML attributes for the article tag.
 *     @type array  $category_colors Associative array of category slugs to hex colors.
 * }
 */

if (!defined('ABSPATH')) {
  exit();
}

global $product;

// If we don't have a product global, try to get it from args or current post
if (!$product && isset($args['product'])) {
  $product = $args['product'];
} elseif (!$product) {
  $product = wc_get_product(get_the_ID());
}

if (!$product) {
  return;
}

$product_id = $product->get_id();
$image_id = $product->get_image_id();
$price = $product->get_price();

// Default Category Colors if not provided (filterable for child themes / brands)
$category_colors = isset($args['category_colors'])
  ? $args['category_colors']
  : apply_filters(
    'nera_competition_card_category_colors',
    [
      'cars' => '#3B82F6',
      'cash' => '#10B981',
      'luxury' => '#8B5CF6',
      'electronics' => '#F59E0B',
      'travel' => '#EC4899',
      'tech' => '#06B6D4',
      'gadgets' => '#F97316',
      'watches' => '#6366F1',
      'lifestyle' => '#14B8A6',
    ],
  );

// Lottery-specific data
$max_tickets = get_post_meta($product_id, '_lty_maximum_tickets', true);
$sold_tickets = method_exists($product, 'get_purchased_ticket_count')
  ? $product->get_purchased_ticket_count()
  : 0;

$progress = $max_tickets ? min(100, round(($sold_tickets / $max_tickets) * 100)) : 0;
$remaining = $max_tickets ? $max_tickets - $sold_tickets : 0;

// End date / countdown
$end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
$end_timestamp_ms = $end_date_gmt ? strtotime($end_date_gmt) * 1000 : 0;
$countdown_expired = $end_timestamp_ms && ($end_timestamp_ms < (time() * 1000));

$countdown_parts = $end_date_gmt ? nera_get_countdown_parts($end_date_gmt) : ['expired' => true];
$days_left = $countdown_parts['days'] ?? 0;
$hours_left = $countdown_parts['hours'] ?? 0;

// Status badge
$badge_text = '';
$badge_class = 'bg-gradient-to-r from-danger to-danger-text';
$is_urgent = !empty($countdown_parts['urgent']);

if ($max_tickets && $remaining <= 0) {
  $badge_text = __('Sold Out', 'nera-competitions');
  $is_urgent = true;
} elseif ($remaining > 0 && $remaining <= 50) {
  $badge_text = sprintf(__('Last %d Tickets', 'nera-competitions'), $remaining);
  $is_urgent = true;
} elseif ($days_left <= 1 && ($days_left > 0 || $hours_left > 0)) {
  $badge_text = __('Ending Soon', 'nera-competitions');
  $is_urgent = true;
} elseif ($progress >= 90) {
  $badge_text = __('Almost Gone', 'nera-competitions');
  $badge_class = 'bg-gradient-to-r from-warning to-warning';
}

// Product categories
$product_categories = wp_get_post_terms($product_id, 'product_cat');
$category_slugs = array_map(function ($cat) {
  return $cat->slug;
}, $product_categories);

$primary_category = !empty($product_categories) ? $product_categories[0]->slug : '';
$base_accent_color = isset($category_colors[$primary_category])
  ? $category_colors[$primary_category]
  : apply_filters('nera_competition_card_fallback_accent', '#1313ec');

// Parsing Args
$x_show_attr = !empty($args['x_show']) ? 'x-show="' . esc_attr($args['x_show']) . '"' : '';
$extra_attrs = !empty($args['extra_attributes']) ? $args['extra_attributes'] : '';

// Data attributes for JS filtering
$data_attributes = sprintf(
  'data-price="%s" data-end-date="%s" data-posted-date="%s" data-popularity="%s" data-categories="%s"',
  esc_attr($price),
  esc_attr($end_date_gmt ? strtotime($end_date_gmt) : '9999999999'),
  esc_attr(get_the_date('U')),
  esc_attr(get_post_meta($product_id, 'total_sales', true) ?: '0'),
  esc_attr(json_encode($category_slugs)),
);
?>

<article class="group min-w-0 transition-all duration-400 ease-out" <?php echo $data_attributes; ?>
  <?php echo $extra_attrs; ?>
  <?php echo $x_show_attr; ?>
  x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
  x-transition:enter-end="opacity-100 scale-100">

  <!-- Card Inner Wrapper -->
  <div
    class="bg-surface rounded-xl sm:rounded-2xl overflow-hidden shadow-lg border border-gray-100 h-full flex flex-col transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:border-gray-200">

    <!-- Image Container -->
    <div class="relative aspect-5/3 sm:aspect-4/3 overflow-hidden bg-gray-100">

      <!-- Status Badge (Top Left) -->
      <?php if ($badge_text): ?>
        <div
          class="absolute top-2 left-2 z-10 sm:top-4 sm:left-4 <?php echo esc_attr(
            $badge_class,
          ); ?> text-white text-[9px] sm:text-[10px] font-extrabold px-2 py-1 sm:px-3 sm:py-1.5 rounded-full uppercase tracking-widest shadow-lg <?php echo $is_urgent
   ? 'animate-pulse'
   : ''; ?>">
          <?php echo esc_html($badge_text); ?>
        </div>
      <?php endif; ?>

      <!-- Category Badges (Top Right) -->
      <?php if (!empty($product_categories)): ?>
        <div class="absolute top-2 right-2 z-10 flex max-w-[calc(100%-4.5rem)] items-center gap-1 sm:top-4 sm:right-4 sm:max-w-none sm:gap-1.5">
          <?php
          // 1. Primary Category Badge
          $primary_cat = $product_categories[0];
          $cat_accent = isset($category_colors[$primary_cat->slug])
            ? $category_colors[$primary_cat->slug]
            : $base_accent_color;
          ?>
          <div
            class="max-w-full truncate px-2 py-1 text-[10px] font-bold rounded-full backdrop-blur-sm border-[1.5px] shadow-sm transform transition-transform hover:scale-105 sm:px-3 sm:py-1.5 sm:text-xs"
            style="background-color: <?php echo esc_attr(
              $cat_accent,
            ); ?>; color: #fff; border-color: <?php echo esc_attr(
  $cat_accent,
); ?>;">
            <?php echo esc_html($primary_cat->name); ?>
          </div>

          <?php
          // 2. "+N" Counter with Tooltip
          $remaining_cats_count = count($product_categories) - 1;
          if ($remaining_cats_count > 0):
            $other_cats = array_slice($product_categories, 1); ?>
            <div class="relative group/tooltip">
              <!-- Counter Badge -->
              <div
                class="shrink-0 px-1.5 py-1 text-[10px] font-bold rounded-full bg-surface/90 backdrop-blur-sm border border-gray-200 text-text-secondary shadow-sm cursor-help hover:bg-surface transition-colors sm:px-2 sm:py-1.5 sm:text-xs">
                +<?php echo esc_html($remaining_cats_count); ?>
              </div>

              <!-- Tooltip -->
              <div
                class="absolute right-0 top-full mt-2 w-max max-w-[150px] p-2 bg-gray-900/95 backdrop-blur-md text-white text-[10px] font-medium rounded-lg shadow-xl opacity-0 translate-y-2 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:translate-y-0 group-hover/tooltip:visible transition-all duration-200 z-50 flex flex-col gap-1 text-right">
                <?php foreach ($other_cats as $other_cat): ?>
                  <span class="block px-1.5 py-0.5 rounded hover:bg-surface/10 transition-colors">
                    <?php echo esc_html($other_cat->name); ?>
                  </span>
                <?php endforeach; ?>
                <!-- Arrow pointer -->
                <div class="absolute -top-1 right-2.5 w-2 h-2 bg-gray-900/95 rotate-45"></div>
              </div>
            </div>
          <?php
          endif;
          ?>
        </div>
      <?php endif; ?>

      <!-- Product Image -->
      <a href="<?php the_permalink(); ?>" class="block w-full h-full">
        <?php if ($image_id): ?>
          <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
          <div
            class="w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-500 ease-out group-hover:scale-110"
            style="background-image: url('<?php echo esc_url($image_url); ?>');">
          </div>
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
              class="text-gray-300">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <polyline points="21 15 16 10 5 21" />
            </svg>
          </div>
        <?php endif; ?>
      </a>

      <!-- Price Badge (Bottom Right, Glassmorphism) -->
      <div
        class="absolute bottom-2 right-2 z-10 max-w-[min(100%,11rem)] px-2.5 py-1.5 bg-surface/95 backdrop-blur-md rounded-lg border-[1.5px] border-white/80 shadow-lg sm:bottom-4 sm:right-4 sm:max-w-none sm:rounded-xl sm:px-4 sm:py-2.5"
        style="border-color: <?php echo esc_attr($base_accent_color); ?>30;">
        <div class="text-xs font-extrabold leading-none mb-0.5 sm:text-sm"
          style="color: <?php echo esc_attr($base_accent_color); ?>;">
          <?php echo wc_price($price); ?>
        </div>
        <div class="text-[9px] font-semibold text-text-secondary uppercase tracking-wide sm:text-[10px]">
          per entry
        </div>
      </div>
    </div>

    <!-- Card Content -->
    <div class="p-3 sm:p-5 md:p-6 flex-1 flex flex-col min-w-0">

      <!-- Title -->
      <h3 class="text-sm sm:text-xl font-bold text-text-primary mb-2 sm:mb-4 leading-tight min-h-0 sm:min-h-14 line-clamp-2">
        <a href="<?php the_permalink(); ?>" class="hover:text-primary transition-colors">
          <?php the_title(); ?>
        </a>
      </h3>

      <!-- Progress Section -->
      <div class="space-y-2 sm:space-y-3 mb-auto">
        <?php if ($max_tickets): ?>
          <div class="flex justify-between items-center gap-2">
            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-text-secondary shrink-0">
              Tickets Sold
            </span>
            <span class="text-xs sm:text-sm font-black tabular-nums shrink-0" style="color: <?php echo esc_attr(
              $base_accent_color,
            ); ?>;">
              <?php echo esc_html($progress); ?>%
            </span>
          </div>

          <div class="relative h-1.5 sm:h-2 w-full bg-gray-100 rounded-full overflow-hidden shadow-inner">
            <div class="h-full rounded-full transition-all duration-500"
              style="width: <?php echo esc_attr(
                $progress,
              ); ?>%; background: linear-gradient(90deg, <?php echo esc_attr(
  $base_accent_color,
); ?>, <?php echo esc_attr($base_accent_color); ?>CC);">
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Footer: Countdown & CTA -->
      <div
        class="flex flex-col items-stretch gap-2 pt-3 mt-3 border-t border-gray-100 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:pt-5 sm:mt-4">

        <!-- Countdown Timer -->
        <?php if ($end_date_gmt && !$countdown_expired): ?>
          <div
            class="flex w-full items-center justify-center gap-1 text-text-secondary sm:w-auto sm:justify-start sm:gap-1.5"
            x-data="countdown('<?php echo esc_attr($end_timestamp_ms); ?>')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="hidden h-3.5 w-3.5 shrink-0 opacity-60 sm:block sm:h-3.5 sm:w-3.5" aria-hidden="true">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span class="text-center text-[10px] font-bold uppercase leading-snug tabular-nums sm:text-left sm:text-xs">
              <span x-text="days">00</span>d : <span x-text="hours">00</span>h : <span x-text="minutes">00</span>m : <span x-text="seconds">00</span>s
            </span>
          </div>
        <?php endif; ?>

        <!-- CTA Button -->
        <a href="<?php the_permalink(); ?>"
          class="inline-flex w-full min-h-[44px] items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-[11px] font-extrabold uppercase tracking-wide text-white shadow-md transition-all duration-300 hover:shadow-xl hover:scale-105 sm:w-auto sm:min-h-0 sm:px-5 sm:py-2.5 sm:text-xs sm:ml-auto"
          style="background: linear-gradient(135deg, <?php echo esc_attr(
            $base_accent_color,
          ); ?> 0%, <?php echo esc_attr($base_accent_color); ?> 100%);">
          <span><?php echo esc_html(__('ENTER NOW', 'nera-competitions')); ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="h-3.5 w-3.5 shrink-0 transition-transform group-hover:translate-x-0.5 sm:h-3.5 sm:w-3.5" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
        </a>
      </div>

    </div>
  </div>
</article>