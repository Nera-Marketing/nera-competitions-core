<?php
/**
 * Wallet Partial Payment — a third option in the checkout payment method list.
 *
 * Rendered in two slots:
 *
 *   slot 'option' — selectable (optional) or disabled (zero_disabled / blocked) radio
 *                   INSIDE <ul class="wc_payment_methods">.
 *   slot 'notice' — forced Auto Deduct notice AFTER the </ul> only.
 *
 * When Partial Payment Enabled is off, both slots render nothing (ADR 0008).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (!function_exists('nera_wallet_partial_state')) {
  return;
}

$nera_slot = isset($args['slot']) ? $args['slot'] : 'option';
$nera_wallet = nera_wallet_partial_state();
$nera_state = $nera_wallet['state'];

// Hidden entirely: no partial setting, wallet-only, or nothing to show.
if (
  in_array($nera_state, ['no_balance', 'full_only', 'card_only'], true)
) {
  return;
}

/** The card gateway that collects the remainder. */
$nera_card_gateway = 'cashflows_card';

if ('option' === $nera_slot):

  if ('optional' === $nera_state):
    $nera_selected = false;
    $nera_default = floor($nera_wallet['max'] * 50) / 100;
    ?>

  <li class="ncs-payment-method wc_payment_method payment_method_nera_wallet_split"
    data-nera-wallet-partial
    data-state="<?php echo esc_attr($nera_state); ?>"
    data-max="<?php echo esc_attr(number_format($nera_wallet['max'], 2, '.', '')); ?>"
    data-basket="<?php echo esc_attr(number_format($nera_wallet['basket'], 2, '.', '')); ?>"
    data-currency="<?php echo esc_attr(get_woocommerce_currency_symbol()); ?>"
    data-wallet-gateway="wallet"
    data-card-gateway="<?php echo esc_attr($nera_card_gateway); ?>">

    <input type="hidden"
      name="nera_wallet_partial"
      value="0"
      data-nera-wallet-partial-flag>

    <div class="flex items-center gap-3">
      <input
        id="payment_method_nera_wallet_split"
        type="radio"
        class="input-radio"
        name="payment_method"
        value="<?php echo esc_attr($nera_card_gateway); ?>"
        data-nera-wallet-radio
        <?php checked($nera_selected, true); ?>
      />

      <label for="payment_method_nera_wallet_split" class="min-h-11 flex-1">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-text-primary transition-colors duration-300">
          <span class="material-symbols-outlined text-[20px] leading-none">account_balance_wallet</span>
        </span>
        <span class="gateway-title flex-1">
          <?php esc_html_e('Part wallet, part card', 'nera-competitions'); ?>
        </span>
      </label>
    </div>

    <p class="mt-2 mb-0 text-xs text-text-secondary" data-nera-wallet-flash hidden></p>

    <div class="payment_box payment_method_nera_wallet_split" <?php if (!$nera_selected): ?>style="display:none;"<?php endif; ?>>
      <p class="mb-3 mt-0 text-xs text-text-secondary">
        <?php echo wp_kses_post(
          sprintf(
            /* translators: %s: wallet balance available */
            __(
              'You have %s in your wallet. Choose how much to put towards this order — the rest is charged to your card.',
              'nera-competitions',
            ),
            '<strong>' . wc_price($nera_wallet['balance']) . '</strong>',
          ),
        ); ?>
      </p>

      <label class="mb-1 block text-xs font-semibold text-text-primary" for="nera-wallet-amount">
        <?php esc_html_e('Amount to take from wallet', 'nera-competitions'); ?>
      </label>

      <div class="flex items-center gap-2">
        <span class="text-sm font-semibold text-text-secondary">
          <?php echo esc_html(get_woocommerce_currency_symbol()); ?>
        </span>
        <input type="number"
          id="nera-wallet-amount"
          name="nera_wallet_amount"
          class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/30"
          data-nera-wallet-amount
          inputmode="decimal"
          step="0.01"
          min="0"
          max="<?php echo esc_attr(number_format($nera_wallet['max'], 2, '.', '')); ?>"
          value="<?php echo esc_attr(number_format($nera_default, 2, '.', '')); ?>">
        <span class="text-xs text-text-secondary inline-flex items-center gap-1">
          <?php esc_html_e('of', 'nera-competitions'); ?>
          <button
            type="button"
            class="nera-wallet-fill-amount cursor-pointer border-0 bg-transparent p-0 font-semibold text-primary underline decoration-dotted underline-offset-2 hover:decoration-solid"
            data-nera-wallet-fill="<?php echo esc_attr(number_format($nera_wallet['max'], 2, '.', '')); ?>"
            title="<?php esc_attr_e('Use maximum wallet amount for this order', 'nera-competitions'); ?>">
            <?php echo wp_kses_post(wc_price($nera_wallet['basket'])); ?>
          </button>
        </span>
      </div>

      <p class="mt-2 mb-0 text-xs text-text-secondary" data-nera-wallet-summary>
        <?php echo wp_kses_post(
          sprintf(
            /* translators: 1: wallet contribution, 2: amount charged to card */
            __('%1$s from your wallet, %2$s charged to your card.', 'nera-competitions'),
            '<strong>' . wc_price($nera_default) . '</strong>',
            '<strong>' . wc_price(max(0, $nera_wallet['basket'] - $nera_default)) . '</strong>',
          ),
        ); ?>
      </p>

      <p class="mt-1 mb-0 text-xs text-text-secondary" data-nera-wallet-error hidden></p>
    </div>
  </li>

  <?php
  elseif (in_array($nera_state, ['zero_disabled', 'blocked'], true)):
    $nera_disabled_msg =
      'zero_disabled' === $nera_state
        ? __(
          'Insufficient balance — your wallet is empty, so part wallet, part card cannot be used.',
          'nera-competitions',
        )
        : sprintf(
          /* translators: 1: wallet balance, 2: basket total */
          __(
            'Partial payment is not available for this order. Your balance of %1$s does not cover %2$s, and automatic wallet use is turned off.',
            'nera-competitions',
          ),
          wc_price($nera_wallet['balance']),
          wc_price($nera_wallet['basket']),
        );
    ?>

  <li class="ncs-payment-method wc_payment_method payment_method_nera_wallet_split nera-wallet-partial-disabled opacity-60 pointer-events-none">
    <div class="flex items-center gap-3">
      <input
        id="payment_method_nera_wallet_split_disabled"
        type="radio"
        class="input-radio"
        disabled
        aria-disabled="true"
        tabindex="-1"
      />

      <label for="payment_method_nera_wallet_split_disabled" class="min-h-11 flex-1 cursor-not-allowed">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-text-secondary transition-colors duration-300">
          <span class="material-symbols-outlined text-[20px] leading-none">account_balance_wallet</span>
        </span>
        <span class="gateway-title flex-1 text-text-secondary">
          <?php esc_html_e('Part wallet, part card', 'nera-competitions'); ?>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-text-secondary ml-2">
            <?php esc_html_e('Unavailable', 'nera-competitions'); ?>
          </span>
        </span>
      </label>
    </div>

    <div class="mt-2 rounded-xl border border-gray-200 bg-surface p-3">
      <p class="m-0 text-xs text-text-secondary">
        <?php echo wp_kses_post($nera_disabled_msg); ?>
      </p>
    </div>
  </li>

  <?php
  endif;
  // forced / other states: no option radio (forced uses notice slot).

else:

  // Notice slot — Auto Deduct forced explanation only (ADR 0007).
  if ('forced' !== $nera_state) {
    return;
  }
  ?>

  <div class="nera-wallet-partial-notice mt-4">
    <div class="rounded-xl border border-primary/20 bg-gradient-to-r from-primary/5 to-secondary p-4">
      <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-primary text-xl">account_balance_wallet</span>
        <div class="min-w-0 flex-1">
          <p class="mb-1 text-sm font-semibold text-text-primary">
            <?php esc_html_e('Your wallet credit will be used first', 'nera-competitions'); ?>
          </p>
          <p class="m-0 text-xs text-text-secondary">
            <?php echo wp_kses_post(
              sprintf(
                /* translators: 1: wallet contribution, 2: amount charged to card */
                __(
                  '%1$s will be taken from your wallet and the remaining %2$s will be charged to your card.',
                  'nera-competitions',
                ),
                '<strong>' . wc_price($nera_wallet['contribution']) . '</strong>',
                '<strong>' . wc_price($nera_wallet['remainder']) . '</strong>',
              ),
            ); ?>
          </p>
        </div>
      </div>
    </div>
  </div>

<?php endif; ?>
