<?php
/**
 * Categories Competitions Section Template Part
 *
 * Displays competitions organized by category with filterable tabs
 * Styled with TailwindCSS utility classes
 * Uses AlpineJS for client-side filtering
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get all active product categories
$categories = get_terms([
  'taxonomy' => 'product_cat',
  'hide_empty' => true,
  'exclude' => get_option('default_product_cat'),
]);

$show_view_all = isset($args['show_view_all']) ? $args['show_view_all'] : true;

// Query competitions - 9 products for 3×3 grid
$categories_competitions_args = [
  'post_type' => 'product',
  'posts_per_page' => 9,
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

$competitions = new WP_Query($categories_competitions_args);

// Category color mapping (single source: nera_advanced_filter_category_colors + filters)
$category_colors = function_exists('nera_advanced_filter_category_colors')
  ? nera_advanced_filter_category_colors()
  : [];

// Category icon mapping (Material Symbols)
$category_icons = [
  'cars' => 'directions_car',
  'cash' => 'payments',
  'luxury' => 'diamond',
  'electronics' => 'devices',
  'travel' => 'flight',
  'tech' => 'computer',
  'gadgets' => 'watch',
  'watches' => 'schedule',
  'lifestyle' => 'spa',
];
?>

<section class="py-24 bg-surface relative overflow-hidden" id="categories-competitions" data-aos="fade-up"
  x-data="{ activeCategory: 'all' }">

  <!-- Decorative Background Elements -->
  <div class="absolute inset-0 pointer-events-none overflow-hidden">
    <div
      class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-bl from-primary/5 to-transparent rounded-full blur-3xl">
    </div>
    <div
      class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gradient-to-tr from-accent/5 to-transparent rounded-full blur-3xl">
    </div>
  </div>

  <div class="container mx-auto px-4 relative z-10">

    <!-- Section Header -->
    <div class="mb-16 text-center">
      <!-- Badge Label -->
      <div
        class="inline-flex items-center gap-2 mb-5 px-4 py-2 rounded-full bg-gradient-to-r from-primary/10 to-accent/10 border border-primary/20">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="text-primary">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
        <span class="text-xs font-bold uppercase tracking-widest text-primary">Browse by Category</span>
      </div>

      <!-- Main Title -->
      <h2 class="text-5xl md:text-6xl font-black text-text-primary tracking-tight leading-[0.95] mb-4">
        <?php echo esc_html(
          get_field('categories_section_title') ?: __('Find Your Dream Prize', 'nera-competitions'),
        ); ?>
      </h2>

      <!-- Subtitle with Decorative Line -->
      <div class="flex items-center justify-center gap-4 max-w-2xl mx-auto">
        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
        <p class="text-lg text-text-secondary font-medium">
          <?php echo esc_html(
            get_field('categories_section_subtitle') ?:
            __('Curated collections of extraordinary experiences', 'nera-competitions'),
          ); ?>
        </p>
        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
      </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="mb-12 relative">
      <div class="flex items-center justify-center overflow-hidden">
        <nav class="relative p-2 w-full overflow-x-auto hide-scrollbar" role="tablist" aria-label="Competition categories">
          <div class="flex flex-nowrap sm:flex-wrap items-center sm:justify-center gap-3 px-1 pb-2 sm:pb-0">

            <!-- "All" Tab -->
            <button @click.prevent="activeCategory = 'all'"
              class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold rounded-full shadow-sm transition-all duration-300 cursor-pointer hover:shadow-lg hover:scale-105 whitespace-nowrap shrink-0"
              :class="activeCategory === 'all' 
                ? 'bg-gradient-to-r from-primary to-primary text-white shadow-lg border-primary' 
                : 'bg-surface border-2 border-gray-200 text-text-secondary hover:border-gray-300'" role="tab"
              :aria-selected="activeCategory === 'all'" aria-controls="categories-grid">
              <span class="flex items-center justify-center w-5 h-5">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7" />
                  <rect x="14" y="3" width="7" height="7" />
                  <rect x="14" y="14" width="7" height="7" />
                  <rect x="3" y="14" width="7" height="7" />
                </svg>
              </span>
              <span class="font-semibold">All Categories</span>
              <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full text-xs font-bold"
                :class="activeCategory === 'all' ? 'bg-surface/25 text-white' : 'bg-black/6 text-text-secondary'">
                <?php echo $competitions->found_posts; ?>
              </span>
            </button>

            <!-- Dynamic Category Tabs -->
            <?php if (!empty($categories)): ?>
              <?php foreach ($categories as $category):

                $category_slug = $category->slug;
                $category_name = $category->name;
                $category_count = function_exists('nera_count_active_lottery_products_in_category')
                  ? nera_count_active_lottery_products_in_category((int) $category->term_id)
                  : (int) $category->count;
                if ($category_count < 1) {
                  continue;
                }
                $icon = isset($category_icons[$category_slug])
                  ? $category_icons[$category_slug]
                  : 'category';

                // Using a fallback for color in JS, but could also pass as CSS var if needed
                ?>
                <button @click.prevent="activeCategory = '<?php echo esc_js($category_slug); ?>'"
                  class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold rounded-full shadow-sm transition-all duration-300 cursor-pointer hover:shadow-lg hover:scale-105 whitespace-nowrap shrink-0"
                  :class="activeCategory === '<?php echo esc_js($category_slug); ?>'
                    ? 'bg-gradient-to-r from-primary to-primary text-white shadow-lg border-primary'
                    : 'bg-surface border-2 border-gray-200 text-text-secondary hover:border-gray-300'" role="tab"
                  :aria-selected="activeCategory === '<?php echo esc_attr($category_slug); ?>'"
                  aria-controls="categories-grid">
                  <span class="flex items-center justify-center w-5 h-5">
                    <span class="material-symbols-outlined" style="font-size: 20px;"><?php echo esc_html(
                      $icon,
                    ); ?></span>
                  </span>
                  <span class="font-semibold"><?php echo esc_html($category_name); ?></span>
                  <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full text-xs font-bold"
                    :class="activeCategory === '<?php echo esc_js(
                      $category_slug,
                    ); ?>' ? 'bg-surface/25 text-white' : 'bg-black/6 text-text-secondary'">
                    <?php echo esc_html($category_count); ?>
                  </span>
                </button>
              <?php
              endforeach; ?>
            <?php endif; ?>

          </div>
        </nav>
      </div>
    </div>

    <!-- Competitions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="categories-grid" role="tabpanel">

      <?php if ($competitions->have_posts()): ?>
        <?php
        $card_index = 0;
        while ($competitions->have_posts()):

          $competitions->the_post();
          global $product;
          $product_id = get_the_ID();

          // Get categories for filtering logic
          $product_categories = wp_get_post_terms($product_id, 'product_cat');
          $category_slugs = array_map(function ($cat) {
            return $cat->slug;
          }, $product_categories);
          $cats_js_array = "['" . implode("','", $category_slugs) . "']";

          // Pass arguments to the reusable component
          $card_args = [
            'product_id'      => get_the_ID(),
            'x_show'          => "activeCategory === 'all' || " . $cats_js_array . '.includes(activeCategory)',
            'category_colors' => $category_colors,
          ];
          get_template_part('template-parts/components/competition-card', null, $card_args);
          ?>
        <?php
        endwhile;
        ?>
      <?php else: ?>
        <!-- Empty State -->
        <div class="col-span-full text-center py-20">
          <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
              class="text-gray-400">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <polyline points="21 15 16 10 5 21" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-text-primary mb-2">No competitions found</h3>
          <p class="text-text-secondary">Check back soon for new amazing prizes!</p>
        </div>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>

    <!-- View All Link -->
    <?php if ($show_view_all): ?>
      <div class="text-center mt-16">
        <a href="<?php echo esc_url(home_url('/all-competitions')); ?>"
          class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-primary to-accent text-white font-bold rounded-full shadow-lg transition-all duration-300 hover:shadow-2xl hover:shadow-primary/30 hover:-translate-y-0.5">
          <span>View All Competitions</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="transition-transform group-hover:translate-x-1">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
        </a>
      </div>
    <?php endif; ?>

  </div>
</section>