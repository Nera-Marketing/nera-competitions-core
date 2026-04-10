<?php
/**
 * Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-customer.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Nera_Competitions
 * @version 8.7.0
 */

defined('ABSPATH') || exit();

$show_shipping = !wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>

<section class="woocommerce-customer-details space-y-6 lg:col-span-1">
  <?php if ($show_shipping): ?>
    <div class="space-y-6 woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses">
  <?php endif; ?>

  <!-- Billing Address Card -->
  <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 woocommerce-column woocommerce-column--1 woocommerce-column--billing-address">
    <h2 class="woocommerce-column__title text-lg font-bold text-gray-900 flex items-center gap-2 mb-4">
      <span class="material-symbols-outlined text-primary">receipt_long</span>
      <?php esc_html_e('Billing address', 'woocommerce'); ?>
    </h2>
    <address class="not-italic text-gray-700 text-sm space-y-1">
      <?php echo wp_kses_post(
        $order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce')),
      ); ?>
    </address>
    <?php if ($order->get_billing_phone()): ?>
      <p class="mt-4 pt-4 border-t border-gray-200 flex items-center gap-2 woocommerce-customer-details--phone">
        <span class="material-symbols-outlined text-gray-500 text-lg">call</span>
        <a href="tel:<?php echo esc_attr(
          preg_replace('/[^0-9+]/', '', $order->get_billing_phone()),
        ); ?>" class="text-sm text-gray-900 hover:text-primary transition-colors">
          <?php echo esc_html($order->get_billing_phone()); ?>
        </a>
      </p>
    <?php endif; ?>
    <?php if ($order->get_billing_email()): ?>
      <p class="mt-2 flex items-center gap-2 woocommerce-customer-details--email">
        <span class="material-symbols-outlined text-gray-500 text-lg">mail</span>
        <a href="mailto:<?php echo esc_attr(
          $order->get_billing_email(),
        ); ?>" class="text-sm text-gray-900 hover:text-primary transition-colors break-all">
          <?php echo esc_html($order->get_billing_email()); ?>
        </a>
      </p>
    <?php endif; ?>
    <?php do_action('woocommerce_order_details_after_customer_address', 'billing', $order); ?>
  </div>

  <?php if ($show_shipping): ?>
    <!-- Shipping Address Card -->
    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address">
      <h2 class="woocommerce-column__title text-lg font-bold text-gray-900 flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined text-primary">local_shipping</span>
        <?php esc_html_e('Shipping address', 'woocommerce'); ?>
      </h2>
      <address class="not-italic text-gray-700 text-sm space-y-1">
        <?php echo wp_kses_post(
          $order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce')),
        ); ?>
      </address>
      <?php if ($order->get_shipping_phone()): ?>
        <p class="mt-4 pt-4 border-t border-gray-200 flex items-center gap-2 woocommerce-customer-details--phone">
          <span class="material-symbols-outlined text-gray-500 text-lg">call</span>
          <a href="tel:<?php echo esc_attr(
            preg_replace('/[^0-9+]/', '', $order->get_shipping_phone()),
          ); ?>" class="text-sm text-gray-900 hover:text-primary transition-colors">
            <?php echo esc_html($order->get_shipping_phone()); ?>
          </a>
        </p>
      <?php endif; ?>
      <?php do_action('woocommerce_order_details_after_customer_address', 'shipping', $order); ?>
    </div>
  </div>
  <?php endif; ?>

  <?php do_action('woocommerce_order_details_after_customer_details', $order); ?>
</section>
