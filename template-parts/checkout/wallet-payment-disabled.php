<?php
/**
 * Disabled Full Wallet Payment radio (ADR 0007 / 0008).
 *
 * Shown when the wallet gateway is not selectable (Zero Balance or Short Balance with
 * Partial Payment Enabled) but buyers should still see why Full Wallet Payment is unavailable.
 * Must not use name="payment_method" — disabled fields are skipped on submit, but keeping it
 * out of the group avoids any browser radio quirks.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (!function_exists('nera_wallet_show_disabled_full_wallet') || !nera_wallet_show_disabled_full_wallet()) {
  return;
}

$nera_wallet = nera_wallet_partial_state();
$nera_reason = nera_wallet_disabled_full_wallet_reason();
?>

<li class="ncs-payment-method wc_payment_method payment_method_wallet nera-wallet-payment-disabled opacity-60 pointer-events-none">
  <div class="flex items-center gap-3">
    <input
      id="payment_method_wallet_disabled"
      type="radio"
      class="input-radio"
      disabled
      aria-disabled="true"
      tabindex="-1"
    />

    <label for="payment_method_wallet_disabled" class="min-h-11 flex-1 cursor-not-allowed">
      <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-text-secondary transition-colors duration-300">
        <span class="material-symbols-outlined text-[20px] leading-none">account_balance_wallet</span>
      </span>
      <span class="gateway-title flex-1 text-text-secondary">
        <?php esc_html_e('Wallet payment', 'nera-competitions'); ?>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-text-secondary ml-2">
          <?php esc_html_e('Insufficient Balance', 'nera-competitions'); ?>
        </span>
      </span>
    </label>
  </div>

  <div class="payment_box payment_method_wallet mt-2" style="display:block;">
    <div class="rounded-xl border border-gray-200 bg-surface p-3">
      <div class="flex items-start gap-2">
        <span class="material-symbols-outlined mt-0.5 text-[18px] text-text-secondary">info</span>
        <div class="min-w-0 flex-1">
          <p class="mb-1 text-[13px] font-semibold text-text-primary">
            <?php esc_html_e('Available Balance:', 'nera-competitions'); ?>
            <strong><?php echo wp_kses_post(wc_price($nera_wallet['balance'])); ?></strong>
          </p>
          <p class="m-0 text-xs text-text-secondary">
            <?php if ('zero' === $nera_reason): ?>
              <?php esc_html_e(
                'Insufficient balance — your wallet is empty, so Wallet payment cannot be used for this order.',
                'nera-competitions',
              ); ?>
            <?php else: ?>
              <?php echo wp_kses_post(
                sprintf(
                  /* translators: 1: wallet balance, 2: basket total */
                  __(
                    'Insufficient balance — your Wallet Balance of %1$s does not cover this Basket Total of %2$s.',
                    'nera-competitions',
                  ),
                  '<strong>' . wc_price($nera_wallet['balance']) . '</strong>',
                  '<strong>' . wc_price($nera_wallet['basket']) . '</strong>',
                ),
              ); ?>
            <?php endif; ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</li>
