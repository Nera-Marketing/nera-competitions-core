<?php
/**
 * Add to Cart Button Template Part
 *
 * Main add to cart button for lottery products.
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
$remaining = $lottery_data['remainingTickets'] ?? 0;
$is_in_stock = $remaining > 0;

// Check if competition has ended
$end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
$is_ended = $end_date_gmt && strtotime($end_date_gmt) < time();
?>

<div class="add-to-cart-section">
  <?php if ($is_ended): ?>
    <!-- Competition Ended -->
    <button
      type="button"
      class="w-full py-4 px-6 rounded-2xl bg-gray-300 text-gray-600 font-bold text-lg cursor-not-allowed"
      disabled
    >
      <span class="flex items-center justify-center gap-2">
        <span class="material-symbols-outlined">event_busy</span>
        <?php _e('Competition Ended', 'nera-competitions'); ?>
      </span>
    </button>
  <?php
    // Allow lottery plugin to add its hidden fields
    // Allow lottery plugin to add content after button
    // Allow lottery plugin to add its hidden fields
    // Allow lottery plugin to add content after button
    // Allow lottery plugin to add its hidden fields
    // Allow lottery plugin to add content after button
    // Allow lottery plugin to add its hidden fields
    // Allow lottery plugin to add content after button
    elseif (!$is_in_stock): ?>
    <!-- Sold Out -->
    <button
      type="button"
      class="w-full py-4 px-6 rounded-2xl bg-gray-300 text-gray-600 font-bold text-lg cursor-not-allowed"
      disabled
    >
      <span class="flex items-center justify-center gap-2">
        <span class="material-symbols-outlined">remove_shopping_cart</span>
        <?php _e('Sold Out', 'nera-competitions'); ?>
      </span>
    </button>
  <?php else: ?>
    <!-- Add to Cart Form -->
    <form class="cart" action="<?php echo esc_url(
      $product->add_to_cart_url(),
    ); ?>" method="post" enctype="multipart/form-data" data-add-to-cart-form>
      <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
      <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
      <input type="hidden" name="quantity" value="1" class="form-quantity-input">

      <?php do_action('woocommerce_before_add_to_cart_button'); ?>

      <button
        type="submit"
        class="add-to-cart-btn w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-primary to-indigo-500 text-white font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
        data-product-id="<?php echo esc_attr($product_id); ?>"
      >
        <span class="flex items-center justify-center gap-2">
          <span class="material-symbols-outlined">shopping_cart</span>
          <span class="btn-text"><?php _e('Add to Cart', 'nera-competitions'); ?></span>
        </span>
      </button>

      <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>

    <!-- Quick Buy Notice -->
    <p class="mt-3 text-xs text-text-secondary text-center flex items-center justify-center gap-1">
      <span class="material-symbols-outlined text-green-500 text-sm">lock</span>
      <?php _e('Secure checkout powered by Stripe', 'nera-competitions'); ?>
    </p>
  <?php endif; ?>
</div>
