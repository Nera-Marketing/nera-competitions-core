<?php
/**
 * Quantity Selector Template Part
 *
 * Quick-select buttons and custom input for ticket quantity.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;
$lottery_data = isset($args['lottery_data']) ? $args['lottery_data'] : [];

if (!$product) {
  return;
}

$product_id = $product->get_id();
$price = floatval($product->get_price());
$currency_symbol = get_woocommerce_currency_symbol();

$min_per_order = $lottery_data['minPerOrder'] ?? 1;
$max_per_order = $lottery_data['maxPerOrder'] ?? 100;
$remaining = $lottery_data['remainingTickets'] ?? 100;

// Calculate max quantity user can buy
$max_quantity = min($max_per_order, $remaining);

// Quick select amounts
$quick_amounts = [5, 10, 25, 50];

// Filter amounts to only show those within max
$quick_amounts = array_filter($quick_amounts, function ($amount) use ($max_quantity) {
  return $amount <= $max_quantity;
});
?>

<div class="quantity-selector" data-quantity-selector data-price="<?php echo esc_attr($price); ?>">
  <!-- Quick Select Buttons -->
  <?php if (!empty($quick_amounts)): ?>
    <div class="mb-4">
      <label class="block text-sm font-semibold text-text-secondary mb-2">
        <?php _e('Quick Select', 'nera-competitions'); ?>
      </label>
      <div class="grid grid-cols-4 gap-2">
        <?php foreach ($quick_amounts as $amount): ?>
          <button
            type="button"
            class="quick-select-btn py-3 px-4 rounded-xl border-2 border-gray-200 text-center font-bold text-text-primary hover:border-primary hover:bg-primary/5 transition-all focus:outline-none focus:ring-2 focus:ring-primary/50"
            data-quantity="<?php echo esc_attr($amount); ?>"
          >
            <?php echo esc_html($amount); ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Custom Quantity Input -->
  <div class="mb-4">
    <label class="block text-sm font-semibold text-text-secondary mb-2" for="ticket-quantity">
      <?php _e('Number of Tickets', 'nera-competitions'); ?>
    </label>
    <div class="flex items-center gap-2">
      <!-- Decrease Button -->
      <button
        type="button"
        class="quantity-btn quantity-decrease w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-text-primary hover:border-primary hover:bg-primary/5 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        data-action="decrease"
        aria-label="<?php esc_attr_e('Decrease quantity', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined">remove</span>
      </button>

      <!-- Quantity Input -->
      <input
        type="number"
        id="ticket-quantity"
        name="quantity"
        class="quantity-input flex-1 h-12 rounded-xl border-2 border-gray-200 text-center text-lg font-bold text-text-primary focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
        value="<?php echo esc_attr($min_per_order); ?>"
        min="<?php echo esc_attr($min_per_order); ?>"
        max="<?php echo esc_attr($max_quantity); ?>"
        step="1"
        data-min="<?php echo esc_attr($min_per_order); ?>"
        data-max="<?php echo esc_attr($max_quantity); ?>"
      />

      <!-- Increase Button -->
      <button
        type="button"
        class="quantity-btn quantity-increase w-12 h-12 rounded-xl border-2 border-gray-200 flex items-center justify-center text-text-primary hover:border-primary hover:bg-primary/5 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        data-action="increase"
        aria-label="<?php esc_attr_e('Increase quantity', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined">add</span>
      </button>
    </div>
    <p class="mt-1 text-xs text-text-secondary text-center">
      <?php printf(
        __('Minimum %d, Maximum %d per order', 'nera-competitions'),
        $min_per_order,
        $max_quantity,
      ); ?>
    </p>
  </div>

  <!-- Total Price Display -->
  <div class="total-price-display bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4">
    <div class="flex items-center justify-between">
      <span class="text-text-secondary font-medium">
        <?php _e('Total:', 'nera-competitions'); ?>
      </span>
      <span class="total-amount text-2xl font-bold text-primary" data-total>
        <?php echo esc_html($currency_symbol . number_format($price * $min_per_order, 2)); ?>
      </span>
    </div>
  </div>
</div>
