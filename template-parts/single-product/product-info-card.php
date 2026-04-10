<?php
/**
 * Product Info Card Template Part
 *
 * Main product information container with all interactive elements.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;
$lottery_data = isset($args['lottery_data']) ? $args['lottery_data'] : [];
$countdown = isset($args['countdown']) ? $args['countdown'] : [];

if (!$product) {
  return;
}

$product_id = $product->get_id();
$is_sold_out = function_exists('nera_lottery_product_is_sold_out')
  ? nera_lottery_product_is_sold_out($product, $lottery_data)
  : false;
?>

<div class="bg-white rounded-3xl shadow-xl p-6 lg:p-8 space-y-6">

  <!-- Product Title -->
  <div>
    <div class="flex flex-wrap items-center gap-2 mb-1">
      <h1 class="text-2xl lg:text-3xl font-bold text-text-primary leading-tight">
        <?php echo esc_html($product->get_name()); ?>
      </h1>
      <?php if ($is_sold_out): ?>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.65rem] font-extrabold uppercase tracking-widest bg-gray-200 text-text-secondary">
          <?php esc_html_e('Sold Out', 'nera-competitions'); ?>
        </span>
      <?php endif; ?>
    </div>
    <?php if (!$is_sold_out && $product->get_short_description()): ?>
      <p class="mt-2 text-text-secondary text-sm">
        <?php echo wp_kses_post($product->get_short_description()); ?>
      </p>
    <?php endif; ?>
  </div>

  <?php if ($is_sold_out): ?>

    <!-- Progress Bar only when sold out -->
    <?php get_template_part('template-parts/single-product/progress-bar', null, [
      'product' => $product,
      'lottery_data' => $lottery_data,
    ]); ?>

  <?php else: ?>

    <!-- Price Display -->
    <?php get_template_part('template-parts/single-product/price-display', null, [
      'product' => $product,
    ]); ?>

    <!-- Countdown Timer -->
    <?php get_template_part('template-parts/single-product/countdown-timer', null, [
      'product' => $product,
      'countdown' => $countdown,
    ]); ?>

    <!-- Progress Bar -->
    <?php get_template_part('template-parts/single-product/progress-bar', null, [
      'product' => $product,
      'lottery_data' => $lottery_data,
    ]); ?>

    <!-- Competition Info Icons -->
    <?php get_template_part('template-parts/single-product/competition-icons', null, [
      'product' => $product,
      'lottery_data' => $lottery_data,
    ]); ?>

    <!-- Quantity Selector -->
    <?php get_template_part('template-parts/single-product/quantity-selector', null, [
      'product' => $product,
      'lottery_data' => $lottery_data,
    ]); ?>

    <!-- Add to Cart (skill Q&A hooks fire inside this template when purchasable) -->
    <?php get_template_part('template-parts/single-product/add-to-cart', null, [
      'product' => $product,
      'lottery_data' => $lottery_data,
    ]); ?>

    <!-- Trust Elements -->
    <?php get_template_part('template-parts/single-product/trust-elements', null, [
      'product' => $product,
    ]); ?>

  <?php endif; ?>

</div>
