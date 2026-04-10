<?php
/**
 * Product Listing Filter Bar Template Part
 *
 * Displays filter dropdowns for category, price, status, and sort
 * Based on Stitch design "Competition Listings Minimalist Light"
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get product categories
$categories = get_terms([
  'taxonomy' => 'product_cat',
  'hide_empty' => true,
  'exclude' => get_option('default_product_cat'), // Exclude "Uncategorized"
]);
?>

<section class="py-6 bg-white border-b border-gray-100 sticky top-[72px] z-30" data-product-filters>
  <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

      <!-- Filter Dropdowns -->
      <div class="flex flex-wrap items-center gap-3">

        <!-- Category Filter -->
        <div class="relative">
          <select name="category" data-filter="category"
            class="appearance-none bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-text-primary cursor-pointer hover:border-primary focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
            <option value=""><?php _e('All Categories', 'nera-competitions'); ?></option>
            <?php if (!empty($categories) && !is_wp_error($categories)): ?>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo esc_attr($category->slug); ?>">
                  <?php echo esc_html($category->name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- Price Filter -->
        <div class="relative">
          <select name="price" data-filter="price"
            class="appearance-none bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-text-primary cursor-pointer hover:border-primary focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
            <option value=""><?php _e('All Prices', 'nera-competitions'); ?></option>
            <option value="0-5"><?php _e('Under £5', 'nera-competitions'); ?></option>
            <option value="5-10"><?php _e('£5 - £10', 'nera-competitions'); ?></option>
            <option value="10-25"><?php _e('£10 - £25', 'nera-competitions'); ?></option>
            <option value="25+"><?php _e('£25+', 'nera-competitions'); ?></option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="relative">
          <select name="status" data-filter="status"
            class="appearance-none bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-text-primary cursor-pointer hover:border-primary focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
            <option value=""><?php _e('All Status', 'nera-competitions'); ?></option>
            <option value="ending-soon"><?php _e('Ending Soon', 'nera-competitions'); ?></option>
            <option value="last-tickets"><?php _e('Last Tickets', 'nera-competitions'); ?></option>
            <option value="new"><?php _e('New', 'nera-competitions'); ?></option>
          </select>
        </div>

        <!-- Clear Filters Button (hidden by default) -->
        <button type="button" data-clear-filters
          class="hidden items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
          <?php _e('Clear', 'nera-competitions'); ?>
        </button>
      </div>

      <!-- Sort Dropdown -->
      <div class="relative">
        <select name="sort" data-filter="sort"
          class="appearance-none bg-slate-50 border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-text-primary cursor-pointer hover:border-primary focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all">
          <option value="ending-soon"><?php _e('Ending Soon', 'nera-competitions'); ?></option>
          <option value="newest"><?php _e('Newest First', 'nera-competitions'); ?></option>
          <option value="price-low"><?php _e('Price: Low to High', 'nera-competitions'); ?></option>
          <option value="price-high"><?php _e(
            'Price: High to Low',
            'nera-competitions',
          ); ?></option>
          <option value="popularity"><?php _e('Most Popular', 'nera-competitions'); ?></option>
        </select>
      </div>

    </div>
  </div>
</section>