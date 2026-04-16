<?php
/**
 * Checkout Payment Section
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
} ?>

<!-- Payment Methods Card -->
<div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">

  <h2 class="text-xl font-bold text-text-primary mb-6 flex items-center gap-2">
    <span class="material-symbols-outlined text-primary">credit_card</span>
    <?php esc_html_e('Payment Method', 'nera-competitions'); ?>
  </h2>

  <div id="payment" class="woocommerce-checkout-payment" role="radiogroup" aria-label="<?php esc_attr_e(
    'Payment Methods',
    'nera-competitions',
  ); ?>">
    <?php if (WC()->cart->needs_payment()): ?>
      <ul class="wc_payment_methods payment_methods methods">
        <?php
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if (!empty($available_gateways)):
          foreach ($available_gateways as $gateway):
            wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
          endforeach;
        else:
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
        endif;
        ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="pt-6">
    <div class="flex flex-col gap-5">
      <!-- Terms & Conditions -->
      <div class="w-full">
      <?php do_action('woocommerce_checkout_before_terms_and_conditions'); ?>

      <?php
      $terms_page_id = wc_terms_and_conditions_page_id();
      if ($terms_page_id > 0 && apply_filters('woocommerce_checkout_show_terms', true)): ?>
        <div class="woocommerce-terms-and-conditions-wrapper">
          <label class="flex items-start gap-3 cursor-pointer text-sm leading-relaxed text-text-secondary">
            <input type="checkbox" name="terms" id="terms"
              class="mt-0.5 h-5 w-5 shrink-0 cursor-pointer rounded border-gray-300 text-primary focus:ring-primary/30 focus:ring-2 focus:ring-offset-2"
              required
              <?php checked(apply_filters('woocommerce_terms_is_checked_default', isset($_POST['terms'])), true); ?>>
            <span>
              <?php printf(
                wp_kses_post(__('I have read and agree to the website %s', 'woocommerce')),
                '<a href="' .
                  esc_url(get_permalink($terms_page_id)) .
                  '" class="font-semibold text-primary underline underline-offset-2 transition-colors hover:text-primary-dark" target="_blank">' .
                  esc_html__('terms and conditions', 'woocommerce') .
                  '</a>',
              ); ?>
            </span>
          </label>
          <input type="hidden" name="terms-field" value="1">
        </div>
      <?php endif;
      ?>

      <?php do_action('woocommerce_checkout_after_terms_and_conditions'); ?>
      </div>

      <!-- Place Order Button -->
      <div class="w-full shrink-0">
        <?php echo apply_filters(
          'woocommerce_order_button_html',
          '<button type="submit"
        class="nera-place-order-btn btn-checkout relative overflow-hidden !w-full"
        name="woocommerce_checkout_place_order"
        id="place_order"
        value="' .
            esc_attr__('Place order', 'woocommerce') .
            '"
        data-value="' .
            esc_attr__('Place order', 'woocommerce') .
            '">
        <span class="material-symbols-outlined">lock</span>
        <span>' .
            esc_html__('Place order', 'woocommerce') .
            '</span>
      </button>',
        ); ?>
      </div>
    </div>
    <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
  </div>
</div>
