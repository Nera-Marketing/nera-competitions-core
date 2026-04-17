<?php
/**
 * Ending Soon Competitions Section Template Part
 *
 * Carousel display of competitions ending soon (Stitch Minimalist Light Design)
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get competitions - Query for lottery product type
$competitions_args = [
  'post_type' => 'product',
  'posts_per_page' => 6, // More items for carousel
  'post_status' => 'publish',
  'tax_query' => [
    [
      'taxonomy' => 'product_type',
      'field' => 'slug',
      'terms' => 'lottery',
    ],
  ],
  'meta_key' => '_lty_end_date_gmt',
  'orderby' => 'meta_value',
  'order' => 'ASC',
  'meta_query' => function_exists('nera_active_lottery_meta_query') ? nera_active_lottery_meta_query() : [],
];

$competitions = new WP_Query($competitions_args);

// Fallback to regular products if no lottery products
if (!$competitions->have_posts()) {
  $competitions_args = [
    'post_type' => 'product',
    'posts_per_page' => 6,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
  ];
  $competitions = new WP_Query($competitions_args);
}
?>

<section class="ending-soon-section py-20 bg-background-light" id="ending-soon" data-aos="fade-up">
  <div class="max-w-[1200px] mx-auto px-5 sm:px-6 lg:px-8">

    <!-- Section Header with Navigation Arrows -->
    <div class="flex items-end justify-between mb-10">
      <div>
        <h2 class="text-3xl font-extrabold text-text-primary tracking-tight">
          <?php echo esc_html(
            get_field('featured_title') ?: __('Ending Soon', 'nera-competitions'),
          ); ?>
        </h2>
        <p class="text-text-secondary font-medium mt-1">
          <?php echo esc_html(
            get_field('featured_subtitle') ?:
            __('Grab your tickets before they\'re gone forever.', 'nera-competitions'),
          ); ?>
        </p>
      </div>
      <div class="flex gap-2">
        <button id="endingSoonPrev"
          class="size-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-surface transition-all cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <button id="endingSoonNext"
          class="size-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-surface transition-all cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>

    <!-- Competitions Carousel -->
    <div id="endingSoonCarousel" class="flex overflow-x-auto gap-6 hide-scrollbar pb-8 scroll-smooth">
      <?php if ($competitions->have_posts()): ?>
        <?php while ($competitions->have_posts()):

          $competitions->the_post();
          global $product;
          $product_id = get_the_ID();
          $image_id = $product->get_image_id();
          $price = $product->get_price();

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
          ?>
          <article
            class="group w-[300px] md:w-[calc((100%-48px)/3)] bg-surface rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex-shrink-0">
            <div class="relative aspect-[4/3] overflow-hidden">
              <!-- Status Badge -->
              <?php if ($badge_text): ?>
                <div
                  class="absolute top-4 left-4 z-10 <?php echo esc_attr(
                    $badge_class,
                  ); ?> text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                  <?php echo esc_html($badge_text); ?>
                </div>
              <?php endif; ?>

              <!-- Product Image with Zoom Effect -->
              <a href="<?php the_permalink(); ?>" class="block w-full h-full">
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

              <!-- Price Badge (Bottom Right, Glassmorphism) -->
              <div
                class="absolute bottom-4 right-4 z-10 bg-surface/90 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20 shadow-sm">
                <span class="text-xs font-bold text-primary"><?php echo wc_price($price); ?>
                  <?php _e('per entry', 'nera-competitions'); ?>
                </span>
              </div>
            </div>

            <div class="p-6">
              <h3 class="text-lg font-bold text-text-primary mb-4">
                <a href="<?php the_permalink(); ?>" class="hover:text-primary transition-colors">
                  <?php the_title(); ?>
                </a>
              </h3>

              <div class="space-y-3">
                <!-- Progress Label -->
                <?php if ($max_tickets): ?>
                  <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-text-secondary uppercase tracking-tighter">
                      <?php _e('Tickets Sold', 'nera-competitions'); ?>
                    </span>
                    <span class="text-primary"><?php echo esc_html($progress); ?>%</span>
                  </div>

                  <!-- Progress Bar -->
                  <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-500"
                      style="width: <?php echo esc_attr($progress); ?>%"></div>
                  </div>
                <?php endif; ?>

                <!-- Countdown & CTA -->
                <div class="flex items-center justify-between pt-4">
                  <?php if ($end_date_gmt): ?>
                    <div class="flex items-center gap-1 text-text-secondary">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" class="opacity-60">
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
              </div>
            </div>
          </article>
        <?php
        endwhile; ?>
      <?php else: ?>
        <!-- Placeholder cards when no competitions -->
        <?php for ($i = 0; $i < 3; $i++): ?>
          <article
            class="w-[300px] md:w-[calc((100%-48px)/3)] bg-surface rounded-3xl overflow-hidden shadow-sm border border-gray-100 opacity-60 flex-shrink-0">
            <div class="relative aspect-[4/3] overflow-hidden">
              <div class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-300">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                  <circle cx="8.5" cy="8.5" r="1.5" />
                  <polyline points="21 15 16 10 5 21" />
                </svg>
              </div>
              <div
                class="absolute bottom-4 right-4 bg-surface/90 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20">
                <p class="text-xs font-bold text-primary">£0.99 per entry</p>
              </div>
            </div>
            <div class="p-6">
              <h3 class="text-lg font-bold text-text-primary mb-4"><?php _e(
                'Coming Soon',
                'nera-competitions',
              ); ?></h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center text-xs font-bold">
                  <span class="text-text-secondary uppercase tracking-tighter">Tickets Sold</span>
                  <span class="text-primary">0%</span>
                </div>
                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full bg-primary rounded-full w-0"></div>
                </div>
                <div class="flex items-center justify-between pt-4">
                  <span class="text-xs text-text-secondary"><?php _e(
                    'Coming soon',
                    'nera-competitions',
                  ); ?></span>
                  <span class="bg-gray-200 text-gray-400 text-xs font-extrabold px-4 py-2 rounded-lg cursor-not-allowed">
                    <?php _e('ENTER', 'nera-competitions'); ?>
                  </span>
                </div>
              </div>
            </div>
          </article>
        <?php endfor; ?>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>

  </div>
</section>