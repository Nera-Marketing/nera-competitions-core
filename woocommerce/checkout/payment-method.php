<?php
/**
 * Output a single payment method
 *
 * Theme override for consistent checkout UI.
 *
 * @package Nera_Competitions
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
  exit();
}

$gateway_icon_name = 'payments';
if ($gateway->id === 'wallet') {
  $gateway_icon_name = 'account_balance_wallet';
} elseif ($gateway->id === 'cod') {
  $gateway_icon_name = 'local_shipping';
}
?>
<li class="ncs-payment-method wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?>">
  <div class="flex items-center gap-3">
    <input
      id="payment_method_<?php echo esc_attr($gateway->id); ?>"
      type="radio"
      class="input-radio"
      name="payment_method"
      value="<?php echo esc_attr($gateway->id); ?>"
      <?php checked($gateway->chosen, true); ?>
      data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>"
    />

    <label for="payment_method_<?php echo esc_attr($gateway->id); ?>" class="min-h-11 flex-1">
    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-text-primary transition-colors duration-300">
      <span class="material-symbols-outlined text-[20px] leading-none"><?php echo esc_html(
        $gateway_icon_name,
      ); ?></span>
    </span>
    <span class="gateway-title flex-1">
      <?php echo $gateway->get_title();
// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
?>
    </span>
    <?php if ($gateway->get_icon()): ?>
      <span class="gateway-brand-icons ml-2 opacity-75">
        <?php echo $gateway->get_icon();
      // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
      ?>
      </span>
    <?php endif; ?>
    </label>
  </div>

  <?php if ($gateway->has_fields() || $gateway->get_description()): ?>
    <div class="payment_box payment_method_<?php echo esc_attr($gateway->id); ?>" <?php if (
  !$gateway->chosen
): ?>style="display:none;"<?php endif; ?>>
      <?php $gateway->payment_fields(); ?>
    </div>
  <?php endif; ?>
</li>
