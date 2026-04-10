<?php
/**
 * Checkout Order Review Sidebar
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
} ?>

<div class="nera-cart-totals">

  <h3 class="text-xl font-bold text-text-primary mb-6">
    <?php esc_html_e('Order Summary', 'nera-competitions'); ?>
  </h3>

  <!-- Cart Items -->
  <div class="space-y-4 mb-6">
    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):

      $_product = apply_filters(
        'woocommerce_cart_item_product',
        $cart_item['data'],
        $cart_item,
        $cart_item_key,
      );

      if (!$_product || !$_product->exists() || $cart_item['quantity'] <= 0) {
        continue;
      }
      if (
        !apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)
      ) {
        continue;
      }

      $product_name = apply_filters(
        'woocommerce_cart_item_name',
        $_product->get_name(),
        $cart_item,
        $cart_item_key,
      );
      $quantity = $cart_item['quantity'];
      $thumbnail = $_product->get_image([56, 56], ['class' => 'w-full h-full object-cover']);
      ?>

      <div class="flex gap-3 items-start">
        <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-50 flex-shrink-0 border border-gray-100">
          <?php echo $thumbnail; ?>
        </div>
        <div class="flex-1 min-w-0">
          <h4 class="font-semibold text-sm text-text-primary leading-tight mb-1 line-clamp-2">
            <?php echo wp_kses_post($product_name); ?>
          </h4>
          <div class="flex justify-between items-center">
            <span class="text-xs text-text-secondary">
              <?php // Show ticket count for lottery products

      if ($_product->get_type() === 'lottery') {
                $tickets_per_entry = 1; // Default, may vary by lottery plugin config
                $total_tickets = $quantity * $tickets_per_entry;
                echo esc_html(
                  sprintf(
                    _n('%d ticket', '%d tickets', $total_tickets, 'nera-competitions'),
                    $total_tickets,
                  ),
                );
              } else {
                echo esc_html($quantity);
              } ?> &times; <?php echo wp_kses_post(wc_price($_product->get_price())); ?>
            </span>
            <span class="text-sm font-bold text-text-primary">
              <?php echo apply_filters(
                'woocommerce_cart_item_subtotal',
                WC()->cart->get_product_subtotal($_product, $quantity),
                $cart_item,
                $cart_item_key,
              ); ?>
            </span>
          </div>

          <?php // Show draw date for lottery products

      if ($_product->get_type() === 'lottery') {
            $end_date = get_post_meta($_product->get_id(), '_lty_end_date_gmt', true);
            if ($end_date) {
              $formatted_date = date_i18n(
                get_option('date_format') . ' \a\t ' . get_option('time_format'),
                strtotime($end_date),
              ); ?>
              <div class="text-xs text-text-secondary mt-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">event</span>
                <span><?php printf(__('Draw: %s', 'nera-competitions'), $formatted_date); ?></span>
              </div>
          <?php
            }
          } ?>
        </div>
      </div>

    <?php
    endforeach; ?>
  </div>

  <div class="h-px bg-gray-100 my-4"></div>

  <!-- Wallet Balance Section -->
  <?php get_template_part('template-parts/checkout/wallet-balance'); ?>

  <!-- Wallet Partial Payment Section -->
  <?php
  $show_wallet_partial_payment = false;
  $wallet_partial_payment_amount = 0;
  $wallet_partial_payment_remaining = 0;
  $wallet_partial_auto_deduct = false;
  $wallet_partial_checked = false;

  if (
    is_user_logged_in() &&
    function_exists('woo_wallet') &&
    is_object(woo_wallet()) &&
    is_object(woo_wallet()->wallet) &&
    function_exists('is_full_payment_through_wallet') &&
    function_exists('is_wallet_rechargeable_cart') &&
    function_exists('is_wallet_account_locked') &&
    function_exists('get_woowallet_cart_total')
  ) {
    $wallet_partial_enabled =
      'on' ===
      woo_wallet()->settings_api->get_option(
        'is_enable_partial_payment',
        '_wallet_settings_general',
        'on',
      );

    if (
      $wallet_partial_enabled &&
      !is_full_payment_through_wallet() &&
      !is_wallet_rechargeable_cart() &&
      !is_wallet_account_locked()
    ) {
      $wallet_balance = woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit');
      $session_partial_amount = !is_null(WC()->session)
        ? WC()->session->get('partial_payment_amount', 0)
        : 0;
      $wallet_partial_payment_amount = apply_filters(
        'woo_wallet_partial_payment_amount',
        $session_partial_amount && $wallet_balance >= $session_partial_amount
          ? $session_partial_amount
          : $wallet_balance,
      );

      if ($wallet_partial_payment_amount > 0) {
        $wallet_cart_total = (float) get_woowallet_cart_total();
        $wallet_partial_payment_remaining = max(
          0,
          $wallet_cart_total - (float) $wallet_partial_payment_amount,
        );
        $wallet_partial_auto_deduct =
          'on' ===
          woo_wallet()->settings_api->get_option(
            'is_auto_deduct_for_partial_payment',
            '_wallet_settings_general',
          );
        $wallet_partial_checked = function_exists('is_enable_wallet_partial_payment')
          ? is_enable_wallet_partial_payment()
          : false;
        $show_wallet_partial_payment = true;
      }
    }
  }
  ?>
  <?php if ($show_wallet_partial_payment): ?>
    <div class="mb-6 rounded-xl border border-primary/20 bg-gradient-to-r from-primary/5 to-indigo-50 p-4">
      <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-text-primary mb-1">
            <?php esc_html_e('Wallet partial payment', 'nera-competitions'); ?>
          </p>
          <?php if ($wallet_partial_auto_deduct): ?>
            <p class="text-xs text-text-secondary">
              <?php echo wp_kses_post(
                sprintf(
                  __(
                    '%1$s will be debited from your wallet and %2$s will be paid through another payment method.',
                    'nera-competitions',
                  ),
                  '<strong>' . wc_price($wallet_partial_payment_amount) . '</strong>',
                  '<strong>' . wc_price($wallet_partial_payment_remaining) . '</strong>',
                ),
              ); ?>
            </p>
          <?php else: ?>
            <label class="mt-2 inline-flex items-center gap-2 text-xs text-text-secondary">
              <input
                type="checkbox"
                class="partial_pay_through_wallet h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/40"
                <?php checked($wallet_partial_checked, true, true); ?>>
              <span>
                <?php echo wp_kses_post(
                  sprintf(
                    __('Use %1$s from wallet, pay %2$s with another method.', 'nera-competitions'),
                    '<strong>' . wc_price($wallet_partial_payment_amount) . '</strong>',
                    '<strong>' . wc_price($wallet_partial_payment_remaining) . '</strong>',
                  ),
                ); ?>
              </span>
            </label>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                const checkbox = document.querySelector('.partial_pay_through_wallet');
                if (!checkbox) {
                  return;
                }

                checkbox.addEventListener('change', function(event) {
                  event.stopImmediatePropagation();

                  if (!window.jQuery) {
                    return;
                  }

                  window.jQuery.post('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    action: 'woo_wallet_partial_payment_update_session',
                    checked: checkbox.checked
                  }, function() {
                    window.jQuery(document.body).trigger('update_checkout');

                    // This checkout uses custom templates and may not use default review table fragments.
                    if (!document.querySelector('.woocommerce-checkout-review-order-table')) {
                      window.location.reload();
                    }
                  });
                });
              });
            </script>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="h-px bg-gray-100 my-4"></div>
  <?php endif; ?>

  <!-- Totals -->
  <div class="space-y-3 mb-6">
    <div class="flex justify-between items-center text-text-secondary">
      <span><?php esc_html_e('Subtotal', 'nera-competitions'); ?></span>
      <span class="font-semibold text-text-primary">
        <?php wc_cart_totals_subtotal_html(); ?>
      </span>
    </div>

    <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
      <div class="flex justify-between items-center text-success coupon-<?php echo esc_attr(
        sanitize_title($code),
      ); ?>">
        <div class="flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">local_offer</span>
          <span><?php wc_cart_totals_coupon_label($coupon); ?></span>
        </div>
        <div class="font-semibold" data-title="<?php echo esc_attr(
          wc_cart_totals_coupon_label($coupon, false),
        ); ?>">
          <?php wc_cart_totals_coupon_html($coupon); ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php foreach (WC()->cart->get_fees() as $fee): ?>
      <div class="flex justify-between items-center text-text-secondary">
        <span><?php echo esc_html($fee->name); ?></span>
        <span class="font-semibold text-text-primary">
          <?php wc_cart_totals_fee_html($fee); ?>
        </span>
      </div>
    <?php endforeach; ?>

    <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()): ?>
      <?php if ('itemized' === get_option('woocommerce_tax_total_display')): ?>
        <?php foreach (WC()->cart->get_tax_totals() as $code => $tax): ?>
          <div class="flex justify-between items-center text-text-secondary">
            <span><?php echo esc_html($tax->label); ?></span>
            <span class="font-semibold text-text-primary">
              <?php echo wp_kses_post($tax->formatted_amount); ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="flex justify-between items-center text-text-secondary">
          <span><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
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
      <?php esc_html_e('Total', 'nera-competitions'); ?>
    </span>
    <span class="text-3xl font-bold text-primary">
      <?php wc_cart_totals_order_total_html(); ?>
    </span>
  </div>

  <!-- Trust / Payment Methods -->
  <div class="text-center">
    <p class="text-xs text-text-secondary mb-3 flex items-center justify-center gap-1">
      <span class="material-symbols-outlined text-sm text-green-500">verified_user</span>
      <?php esc_html_e('SSL Encrypted Payment', 'nera-competitions'); ?>
    </p>

    <div class="flex justify-center gap-2 opacity-70 grayscale hover:grayscale-0 transition-all duration-300">
      <?php
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
