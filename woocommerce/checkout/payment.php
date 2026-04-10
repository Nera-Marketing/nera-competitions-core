<?php
/**
 * Checkout Payment Section (fragment-safe override)
 *
 * This override intentionally excludes terms/place-order markup because
 * the custom checkout template renders a single unified footer CTA.
 *
 * @package Nera_Competitions
 * @version 1.0.0
 */

defined('ABSPATH') || exit();

if (!wp_doing_ajax()) {
  do_action('woocommerce_review_order_before_payment');
}
?>
<div id="payment" class="woocommerce-checkout-payment" role="radiogroup" aria-label="<?php esc_attr_e(
  'Payment Methods',
  'nera-competitions',
); ?>">
  <?php if (WC()->cart && WC()->cart->needs_payment()): ?>
    <ul class="wc_payment_methods payment_methods methods">
      <?php if (!empty($available_gateways)) {
        foreach ($available_gateways as $gateway) {
          wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
        }
      } else {
        echo '<li>';
        wc_print_notice(
          apply_filters(
            'woocommerce_no_available_payment_methods_message',
            WC()->customer->get_billing_country()
              ? esc_html__(
                'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.',
                'woocommerce',
              )
              : esc_html__(
                'Please fill in your details above to see available payment methods.',
                'woocommerce',
              ),
          ),
          'notice',
        );
        echo '</li>';
      } ?>
    </ul>
  <?php endif; ?>
</div>
<?php if (!wp_doing_ajax()) {
  do_action('woocommerce_review_order_after_payment');
}
