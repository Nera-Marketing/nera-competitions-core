<?php
/**
 * Cart Coupon Template Part
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
} ?>

<div class="bg-surface rounded-2xl border border-gray-100 p-4 mb-6 shadow-sm"
  x-data="{ open: <?php echo WC()->cart->has_discount() ? 'true' : 'false'; ?> }">
  <button type="button"
    class="flex w-full items-center justify-between text-text-primary font-semibold hover:text-primary transition-colors"
    @click="open = !open">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary">local_offer</span>
      <span>
        <?php _e('Have a coupon code?', 'nera-competitions'); ?>
      </span>
    </div>
    <span class="material-symbols-outlined transition-transform duration-300"
      :class="{ 'rotate-180': open }">expand_more</span>
  </button>

  <div x-show="open" x-collapse class="mt-4 pt-4 border-t border-gray-100">

    <form class="flex gap-2 coupon-form" action="<?php echo esc_url(
      wc_get_cart_url(),
    ); ?>" method="post">
      <div class="relative flex-grow">
        <input type="text" name="coupon_code"
          class="w-full h-12 pl-4 pr-4 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm font-medium"
          placeholder="<?php esc_attr_e(
            'Enter discount code',
            'nera-competitions',
          ); ?>" id="coupon_code" value="" />
      </div>
      <button type="submit"
        class="h-12 px-6 rounded-xl bg-background-dark text-white font-bold text-sm hover:bg-primary transition-colors flex-shrink-0"
        name="apply_coupon" value="<?php esc_attr_e('Apply', 'nera-competitions'); ?>">
        <?php _e('Apply', 'nera-competitions'); ?>
      </button>
    </form>

    <!-- Display Applied Coupons (if any) -->
    <?php if (WC()->cart->has_discount()): ?>
      <div class="mt-4 flex flex-wrap gap-2">
        <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
          <div
            class="inline-flex items-center gap-2 bg-success-bg text-success-text px-3 py-1.5 rounded-lg border border-success-bg text-sm font-medium">
            <span>
              <?php echo esc_html($code); ?>
            </span>
            <a href="<?php echo esc_url(
              wc_get_cart_url() . '?remove_coupon=' . urlencode($code),
            ); ?>"
              class="flex items-center justify-center w-5 h-5 rounded-full bg-success-border hover:bg-success-border text-success-text transition-colors"
              aria-label="<?php esc_attr_e('Remove coupon', 'nera-competitions'); ?>">
              <span class="material-symbols-outlined !text-xs !font-bold">close</span>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>