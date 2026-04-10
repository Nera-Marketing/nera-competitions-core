<?php
/**
 * Cart Page
 *
 * @package Nera_Competitions
 */

defined('ABSPATH') || exit();
// Cart content only - page.php provides header/footer so footer stays outside article
?>
<div class="relative left-1/2 -translate-x-1/2 w-screen max-w-none bg-gray-50 border-b border-gray-200 py-10 lg:py-16">
  <div class="max-w-7xl mx-auto px-4 lg:px-8">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-primary">
        <span class="material-symbols-outlined text-2xl">shopping_cart</span>
      </div>
      <div>
        <h1 class="text-3xl font-bold text-text-primary mb-1">
          <?php _e('Shopping Cart', 'nera-competitions'); ?>
        </h1>
        <span class="text-text-secondary text-sm">
          <?php printf(
            _n(
              '%d item in your cart',
              '%d items in your cart',
              WC()->cart->get_cart_contents_count(),
              'nera-competitions',
            ),
            WC()->cart->get_cart_contents_count(),
          ); ?>
        </span>
      </div>
    </div>
  </div>
</div>

<div class="py-12 lg:py-20">
  <div class="container mx-auto px-4">

    <!-- Messages -->
    <?php do_action('woocommerce_before_cart'); ?>

    <?php if (WC()->cart->is_empty()): ?>

      <!-- Empty Cart State -->
      <?php get_template_part('template-parts/cart/cart-empty'); ?>

    <?php else: ?>

      <!-- Cart Layout -->
      <form class="woocommerce-cart-form block" action="<?php echo esc_url(
        wc_get_cart_url(),
      ); ?>" method="post">

        <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-start">

          <!-- Cart Items (Left Column) -->
          <div class="lg:col-span-8 space-y-4">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
              <h2 class="text-xl font-bold text-text-primary mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">list_alt</span>
                <?php _e('Your Selections', 'nera-competitions'); ?>
              </h2>

              <!-- Loop through cart items -->
              <div class="space-y-4 divide-y divide-gray-100 -mx-6 px-6 md:divide-y-0 md:mx-0 md:px-0">
              <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters(
                  'woocommerce_cart_item_product',
                  $cart_item['data'],
                  $cart_item,
                  $cart_item_key,
                );

                if (
                  $_product &&
                  $_product->exists() &&
                  $cart_item['quantity'] > 0 &&
                  apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)
                ) {
                  get_template_part('template-parts/cart/cart-item', null, [
                    'cart_item_key' => $cart_item_key,
                    'cart_item' => $cart_item,
                    'product' => $_product,
                  ]);
                }
              } ?>
              </div>

              <!-- Update Cart Button (Visible for manual updates) -->
              <div class="flex justify-end mt-6">
                <button type="submit"
                  class="px-6 py-2.5 rounded-xl border border-gray-200 font-bold text-text-primary hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm"
                  name="update_cart" value="<?php esc_attr_e(
                    'Update cart',
                    'nera-competitions',
                  ); ?>">
                  <?php _e('Update Cart', 'nera-competitions'); ?>
                </button>
              </div>
              <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            </div>

            <!-- Coupon Section (Mobile specific placement can be handled here or sidebar) -->
            <div class="lg:hidden">
              <?php get_template_part('template-parts/cart/cart-coupon'); ?>
            </div>

          </div>

          <!-- Sidebar (Right Column) -->
          <div class="lg:col-span-4 mt-8 lg:mt-0">

            <!-- Coupon Section (Desktop) -->
            <div class="hidden lg:block">
              <?php get_template_part('template-parts/cart/cart-coupon'); ?>
            </div>

            <?php do_action('woocommerce_before_cart_collaterals'); ?>

            <!-- Cart Totals -->
            <div class="cart-collaterals">
              <?php get_template_part('template-parts/cart/cart-totals'); ?>
            </div>

          </div>

        </div>
      </form>

    <?php endif; ?>

    <?php do_action('woocommerce_after_cart'); ?>

  </div>
</div>

<script>
  // Disable inputs in hidden cart-item block so only visible block's values submit
  function neraCartUpdateInputStates() {
    const isDesktop = window.matchMedia('(min-width: 768px)').matches;
    document.querySelectorAll('.cart-item-mobile input').forEach((i) => (i.disabled = isDesktop));
    document.querySelectorAll('.cart-item-desktop input').forEach((i) => (i.disabled = !isDesktop));
  }
  document.addEventListener('DOMContentLoaded', () => {
    neraCartUpdateInputStates();
    window.addEventListener('resize', neraCartUpdateInputStates);
    if (typeof NeraCart === 'undefined' && <?php echo WC()->cart->is_empty()
      ? 'false'
      : 'true'; ?>) {
      console.warn('NeraCart logic not loaded. Interactive features may be limited.');
    }
  });
</script>
