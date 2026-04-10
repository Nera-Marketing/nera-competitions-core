<?php
/**
 * Tabs template part for Single Competition
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$product = $args['product'] ?? null;
$specifications = $args['specifications'] ?? [];
$end_date_gmt = $args['end_date_gmt'] ?? '';

if (!$product) {
  return;
}

$product_id = $product->get_id();
$has_instant_wins = false;

$show_entry_list_tab = get_field('show_entry_list_tab', $product_id);
if ($show_entry_list_tab === null) {
  $show_entry_list_tab = true;
}

if (
  function_exists('lty_is_lottery_product') &&
  lty_is_lottery_product($product) &&
  method_exists($product, 'is_instant_winner') &&
  $product->is_instant_winner()
) {
  $has_instant_wins = true;
}
?>

<!-- Tabs Section -->
<div class="mt-8" data-product-tabs>
  <!-- Tab Navigation -->
  <div class="flex border-b border-gray-200">
    <button class="tab-btn px-6 py-3 text-sm font-semibold text-primary border-b-2 border-primary -mb-px"
      data-tab="prize-details">
      <?php _e('Prize Details', 'nera-competitions'); ?>
    </button>
    <?php if ($show_entry_list_tab): ?>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-text-secondary hover:text-primary transition-colors"
      data-tab="entry-list">
      <?php _e('Entry List', 'nera-competitions'); ?>
    </button>
    <?php endif; ?>
    <button class="tab-btn px-6 py-3 text-sm font-medium text-text-secondary hover:text-primary transition-colors"
      data-tab="draw-info">
      <?php _e('Draw Information', 'nera-competitions'); ?>
    </button>
  </div>

  <!-- Tab Content -->
  <div class="tab-panel mt-6" data-tab-panel="prize-details">
    <!-- Specifications Grid -->
    <?php if (!empty($specifications)): ?>
      <div class="mb-6">
        <h3 class="text-lg font-bold text-text-primary mb-4">
          <?php _e('Specifications', 'nera-competitions'); ?>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 sm:gap-x-8 gap-y-3">
          <?php foreach ($specifications as $spec): ?>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-1 gap-0.5 sm:gap-0">
              <span class="text-text-secondary">
                <?php echo esc_html($spec['label']); ?>
              </span>
              <span class="font-semibold text-text-primary">
                <?php echo esc_html($spec['value']); ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Product Description -->
    <div class="prose prose-sm max-w-none text-text-secondary leading-relaxed">
      <?php echo apply_filters('the_content', $product->get_description()); ?>
    </div>
  </div>

  <?php if ($show_entry_list_tab): ?>
  <div class="tab-panel mt-6 hidden" data-tab-panel="entry-list">
    <?php do_action('lty_lottery_entry_list_content', $product); ?>
  </div>
  <?php endif; ?>

  <div class="tab-panel mt-6 hidden" data-tab-panel="draw-info">
    <?php
    $draw_date = nera_format_draw_date($end_date_gmt);
    if ($draw_date): ?>
      <p class="text-text-secondary">
        <?php printf(
          __('The draw will take place on %s.', 'nera-competitions'),
          '<strong>' . esc_html($draw_date) . '</strong>',
        ); ?>
      </p>
    <?php endif;
    ?>

    <?php if (function_exists('get_field')): ?>
      <?php $competition_rules = get_field('competition_rules', $product_id); ?>
      <?php if ($competition_rules): ?>
        <div class="mt-4 prose prose-sm max-w-none text-text-secondary">
          <?php echo wp_kses_post($competition_rules); ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>