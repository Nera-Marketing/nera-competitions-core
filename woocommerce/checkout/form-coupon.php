<?php
/**
 * Checkout coupon form (Theme Override)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined('ABSPATH') || exit();

if (!wc_coupons_enabled()) {
  return;
}
?>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 lg:p-6 mb-6" x-data="{ open: false }">

  <button type="button"
    class="flex w-full items-center justify-between text-text-primary font-semibold hover:text-primary transition-colors"
    @click="open = !open">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary">local_offer</span>
      <span><?php esc_html_e('Have a coupon code?', 'nera-competitions'); ?></span>
    </div>
    <span class="material-symbols-outlined transition-transform duration-300"
      :class="{ 'rotate-180': open }">expand_more</span>
  </button>

  <div x-show="open" 
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 -translate-y-2"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-2"
       class="mt-4 pt-4 border-t border-gray-100 bg-gray-50/30 -mx-4 lg:-mx-6 px-4 lg:px-6 pb-4 rounded-b-xl">

    <form class="checkout_coupon woocommerce-form-coupon !block !p-0 !border-0" method="post" action="<?php echo esc_url(
      wc_get_checkout_url(),
    ); ?>">

      <div class="flex flex-col gap-2">
        <div class="flex gap-2">
          <div class="flex-1 relative">
            <label for="coupon_code" class="sr-only"><?php esc_html_e(
              'Coupon:',
              'woocommerce',
            ); ?></label>
            <input type="text"
              name="coupon_code"
              id="coupon_code"
              class="w-full h-12 pl-4 pr-4 rounded-xl border border-gray-200 text-sm font-medium focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-400"
              placeholder="<?php esc_attr_e('Enter your discount code', 'nera-competitions'); ?>"
              value="" />
          </div>
          <button type="submit"
            class="h-12 px-6 rounded-xl bg-gray-900 text-white font-bold text-sm hover:bg-primary hover:shadow-lg active:scale-95 transition-all duration-200 shrink-0 flex items-center gap-2"
            name="apply_coupon"
            value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
            <span><?php esc_html_e('Apply', 'nera-competitions'); ?></span>
            <span class="material-symbols-outlined text-lg">check_circle</span>
          </button>
        </div>
      </div>

    </form>

    <!-- Display Applied Coupons (if any) -->
    <?php
    $applied_coupons = WC()->cart->get_applied_coupons();
    if (!empty($applied_coupons)): ?>
      <div class="mt-4 space-y-2" id="checkout-applied-coupons">
        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2"><?php esc_html_e(
          'Applied Coupons:',
          'nera-competitions',
        ); ?></p>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($applied_coupons as $code): ?>
            <div class="inline-flex items-center gap-2 bg-green-50 text-green-700 px-3 py-1.5 rounded-lg border border-green-100 text-sm font-medium">
              <span class="material-symbols-outlined text-base">local_offer</span>
              <span><?php echo esc_html($code); ?></span>
              <a href="#"
                data-coupon="<?php echo esc_attr($code); ?>"
                class="remove-coupon flex items-center justify-center w-5 h-5 rounded-full bg-green-200 hover:bg-green-300 text-green-800 transition-colors"
                aria-label="<?php esc_attr_e('Remove coupon', 'nera-competitions'); ?>"
                role="button">
                <span class="material-symbols-outlined !text-xs">close</span>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif;
    ?>

  </div>

</div>
