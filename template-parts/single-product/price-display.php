<?php
/**
 * Price Display Template Part
 *
 * Shows the price per entry in a prominent display.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;

if (!$product) {
  return;
}

$price = $product->get_price();
$currency_symbol = get_woocommerce_currency_symbol();
?>

<div class="price-display bg-gradient-to-r from-primary/5 to-primary/5 rounded-2xl p-4">
  <div class="flex items-baseline gap-2">
    <span class="text-3xl lg:text-4xl font-bold text-primary">
      <?php echo esc_html($currency_symbol . number_format(floatval($price), 2)); ?>
    </span>
    <span class="text-text-secondary text-lg font-medium">
      <?php _e('Per Entry', 'nera-competitions'); ?>
    </span>
  </div>
</div>
