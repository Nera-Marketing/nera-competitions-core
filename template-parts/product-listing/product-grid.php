<?php
/**
 * Product Listing Grid Template Part
 *
 * Displays the product grid with lottery competitions
 * Based on Stitch design "Competition Listings Minimalist Light"
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Products per page
$per_page = 12;

// Query lottery products
$competitions_args = [
  'post_type' => 'product',
  'posts_per_page' => $per_page,
  'post_status' => 'publish',
  'paged' => 1,
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
];

$competitions = new WP_Query($competitions_args);

// Fallback to regular products if no lottery products
if (!$competitions->have_posts()) {
  $competitions_args = [
    'post_type' => 'product',
    'posts_per_page' => $per_page,
    'post_status' => 'publish',
    'paged' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
  ];
  $competitions = new WP_Query($competitions_args);
}

$total_products = $competitions->found_posts;
$total_pages = $competitions->max_num_pages;
$current_showing = min($per_page, $total_products);
?>

<section class="py-12 md:py-16 bg-slate-50" data-product-listing>
  <div class="max-w-[1200px] mx-auto px-4 lg:px-8">

    <!-- Loading Overlay -->
    <div class="hidden fixed inset-0 bg-surface/80 backdrop-blur-sm z-50 items-center justify-center" data-loading-overlay>
      <div class="flex flex-col items-center gap-3">
        <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        <span class="text-sm font-medium text-text-secondary"><?php _e(
          'Loading competitions...',
          'nera-competitions',
        ); ?></span>
      </div>
    </div>

    <!-- Product Grid -->
    <div
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
      data-product-grid
      data-page="1"
      data-per-page="<?php echo esc_attr($per_page); ?>"
      data-total="<?php echo esc_attr($total_products); ?>">

      <?php if ($competitions->have_posts()): ?>
        <?php while ($competitions->have_posts()):
          $competitions->the_post();
          global $product;

          get_template_part('template-parts/product-listing/product-card', null, [
            'product' => $product,
          ]);
        endwhile; ?>
      <?php else: ?>
        <!-- No Products Found -->
        <div class="col-span-full text-center py-16">
          <div class="max-w-md mx-auto">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3 class="text-xl font-bold text-text-primary mb-2">
              <?php _e('No competitions found', 'nera-competitions'); ?>
            </h3>
            <p class="text-text-secondary">
              <?php _e('Check back soon for new exciting competitions!', 'nera-competitions'); ?>
            </p>
          </div>
        </div>
      <?php endif; ?>

      <?php wp_reset_postdata(); ?>
    </div>

    <!-- Load More Section -->
    <?php if ($total_pages > 1): ?>
      <div class="mt-12 text-center" data-load-more-section>
        <!-- Product Count -->
        <p class="text-sm text-text-secondary mb-4" data-product-count>
          <?php printf(
            __('Showing %1$d of %2$d competitions', 'nera-competitions'),
            $current_showing,
            $total_products,
          ); ?>
        </p>

        <!-- Load More Button -->
        <button
          type="button"
          class="inline-flex items-center gap-2 bg-surface border-2 border-gray-200 hover:border-primary text-text-primary hover:text-primary font-bold px-8 py-3 rounded-xl shadow-sm hover:shadow transition-all"
          data-load-more
          data-next-page="2">
          <span><?php _e('Load More', 'nera-competitions'); ?></span>
          <svg class="w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>

        <!-- Loading Spinner (hidden by default) -->
        <div class="hidden items-center justify-center gap-2 text-text-secondary" data-load-more-spinner>
          <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
          <span class="text-sm font-medium"><?php _e('Loading...', 'nera-competitions'); ?></span>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>
