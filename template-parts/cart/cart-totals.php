<?php
/**
 * Cart Totals Template Part
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
} ?>

<div class="nera-cart-totals">
  <h3 class="text-xl font-bold text-text-primary mb-6">
    <?php _e('Summary', 'nera-competitions'); ?>
  </h3>

  <!-- Line Items -->
  <div class="space-y-3 mb-6">
    <div class="flex justify-between items-center text-text-secondary">
      <span>
        <?php _e('Subtotal', 'nera-competitions'); ?>
      </span>
      <span class="font-semibold text-text-primary">
        <?php wc_cart_totals_subtotal_html(); ?>
      </span>
    </div>

    <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
      <div
        class="flex justify-between items-center text-success cart-discount coupon-<?php echo esc_attr(
          sanitize_title($code),
        ); ?>">
        <div class="flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">local_offer</span>
          <span>
            <?php wc_cart_totals_coupon_label($coupon); ?>
          </span>
        </div>
        <div class="font-semibold" data-title="<?php echo esc_attr(
          wc_cart_totals_coupon_label($coupon, false),
        ); ?>">
          <?php wc_cart_totals_coupon_html($coupon); ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()): ?>
      <?php if ('itemized' === get_option('woocommerce_tax_total_display')): ?>
        <?php foreach (WC()->cart->get_tax_totals() as $code => $tax): ?>
          <div class="flex justify-between items-center text-text-secondary">
            <span>
              <?php echo esc_html($tax->label); ?>
            </span>
            <span class="font-semibold text-text-primary">
              <?php echo wp_kses_post($tax->formatted_amount); ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="flex justify-between items-center text-text-secondary">
          <span>
            <?php echo esc_html(WC()->countries->tax_or_vat()); ?>
          </span>
          <span class="font-semibold text-text-primary">
            <?php wc_cart_totals_taxes_total_html(); ?>
          </span>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="h-px bg-gray-100 my-4"></div>

  <!-- Total -->
  <div class="flex justify-between items-baseline mb-8">
    <span class="text-lg font-bold text-text-primary">
      <?php _e('Total', 'nera-competitions'); ?>
    </span>
    <span class="text-3xl font-bold text-primary">
      <?php wc_cart_totals_order_total_html(); ?>
    </span>
  </div>

  <!-- Checkout Button -->
  <div class="mb-6">
    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>"
      class="btn-checkout group relative overflow-hidden text-white">
      <span
        class="absolute inset-0 bg-surface/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></span>
      <span class="material-symbols-outlined relative z-10">lock</span>
      <span class="relative z-10">
        <?php _e('Secure Checkout', 'nera-competitions'); ?>
      </span>
    </a>
  </div>

  <!-- Trust / Payment Methods -->
  <div class="text-center">
    <p class="text-xs text-text-secondary mb-3 flex items-center justify-center gap-1">
      <span class="material-symbols-outlined text-sm text-success">verified_user</span>
      <?php _e('SSL Encrypted Payment', 'nera-competitions'); ?>
    </p>

    <div class="flex justify-center gap-2 opacity-70 grayscale hover:grayscale-0 transition-all duration-300">
      <?php
      // Use existing helper if available, otherwise fallback
      $logos = function_exists('nera_get_default_payment_logos')
        ? nera_get_default_payment_logos()
        : [];
      foreach ($logos as $logo): ?>
        <div title="<?php echo esc_attr($logo['name']); ?>">
          <?php echo $logo['svg']; ?>
        </div>
      <?php endforeach;
      ?>
    </div>
  </div>
</div>