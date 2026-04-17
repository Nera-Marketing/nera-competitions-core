<?php
/**
 * "Order received" message.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-received.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Nera_Competitions
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit();

$message = apply_filters(
  'woocommerce_thankyou_order_received_text',
  esc_html(__('Thank you. Your order has been received.', 'woocommerce')),
  $order,
);
?>

<div class="bg-success-bg border border-success-border rounded-2xl p-6 mb-8 flex items-center gap-4">
  <div class="w-12 h-12 flex-shrink-0 rounded-full bg-success flex items-center justify-center">
    <span class="material-symbols-outlined text-white text-2xl">check</span>
  </div>
  <p class="text-lg font-semibold text-gray-900 woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received !mb-0">
    <?php echo wp_kses_post($message); ?>
  </p>
</div>
