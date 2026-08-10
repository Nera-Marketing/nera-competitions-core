<?php
/**
 * Checkout Wallet Balance Display
 * Shows user's wallet balance at checkout
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

// Only show for logged-in users
if (!is_user_logged_in()) {
  return;
}

// Check if wallet plugin is active
if (
  !function_exists('woo_wallet') ||
  !is_object(woo_wallet()) ||
  !is_object(woo_wallet()->wallet)
) {
  return;
}

$user_id = get_current_user_id();
// Use 'edit' context to get raw numeric balance - 'view' returns HTML and breaks comparisons/display
$balance = (float) woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');

// Only display if user has balance
if ($balance <= 0) {
  return;
}

// Hide for wallet topup/recharge carts - you cannot use wallet to pay for adding to wallet
if (function_exists('is_wallet_rechargeable_cart') && is_wallet_rechargeable_cart()) {
  return;
}

// Basket Total for comparison — NOT WC()->cart->total, which already has any Wallet
// Contribution fee subtracted. Using it here reported the remainder short by the whole
// contribution (£20 basket, £5 credit showed "pay £10" instead of £15). See docs/adr/0001.
$cart_total = function_exists('nera_wallet_basket_total')
  ? nera_wallet_basket_total()
  : (float) WC()->cart->total;
$can_pay_full = $balance >= $cart_total;
$wallet_partial_enabled = 'on' === woo_wallet()->settings_api->get_option(
  'is_enable_partial_payment',
  '_wallet_settings_general',
  'on',
);
?>

<div class="ncs-wallet-balance bg-gradient-to-br from-primary/5 to-secondary rounded-2xl border-2 border-primary/20 shadow-sm p-6 mb-6 hover:shadow-md hover:border-primary/30 transition-all duration-300">
  
  <!-- Header -->
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-primary text-2xl">account_balance_wallet</span>
      <h3 class="text-lg font-bold text-text-primary">
        <?php esc_html_e('Your Wallet Balance', 'nera-competitions'); ?>
      </h3>
    </div>
    <span class="text-2xl font-bold text-primary">
      <?php echo wc_price($balance); ?>
    </span>
  </div>

  <!-- Balance Info -->
  <div class="bg-surface/80 backdrop-blur-sm rounded-xl p-4 mb-4">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mt-0.5">
        <span class="material-symbols-outlined text-primary text-xl">info</span>
      </div>
      <div class="flex-1 min-w-0">
        <?php if ($can_pay_full): ?>
          <p class="text-sm font-semibold text-text-primary mb-1">
            <?php esc_html_e(
              'Great news! You can pay for this order entirely with your wallet credit.',
              'nera-competitions',
            ); ?>
          </p>
          <p class="text-xs text-text-secondary">
            <?php esc_html_e(
              'Select "Wallet" as your payment method below to use your credit.',
              'nera-competitions',
            ); ?>
          </p>
        <?php elseif ($wallet_partial_enabled): ?>
          <p class="text-sm font-semibold text-text-primary mb-1">
            <?php esc_html_e(
              'You can use your wallet credit towards this purchase.',
              'nera-competitions',
            ); ?>
          </p>
          <p class="text-xs text-text-secondary">
            <?php
            $remaining = $cart_total - $balance;
            printf(
              esc_html__(
                'Your wallet will cover %1$s. You\'ll need to pay the remaining %2$s with another payment method.',
                'nera-competitions',
              ),
              '<strong>' . wc_price($balance) . '</strong>',
              '<strong>' . wc_price($remaining) . '</strong>',
            );
            ?>
          </p>
        <?php else: ?>
          <p class="text-sm font-semibold text-text-primary mb-1">
            <?php
            printf(
              esc_html__(
                'You have %1$s in your wallet.',
                'nera-competitions',
              ),
              '<strong>' . wc_price($balance) . '</strong>',
            );
            ?>
          </p>
          <p class="text-xs text-text-secondary">
            <?php esc_html_e(
              'Partial payment is currently disabled. To use your balance, top up your wallet to cover the full order total, or enable partial payment in wallet settings.',
              'nera-competitions',
            ); ?>
          </p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="flex items-center justify-between text-xs">
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('woo-wallet')); ?>" 
       class="inline-flex items-center gap-1 text-primary hover:text-primary-dark font-medium transition-colors">
      <span class="material-symbols-outlined text-sm">history</span>
      <?php esc_html_e('View Transaction History', 'nera-competitions'); ?>
    </a>
    
    <?php if (apply_filters('woo_wallet_is_enable_top_up', true)): ?>
      <a href="<?php echo esc_url(
        add_query_arg('action', 'add', wc_get_account_endpoint_url('woo-wallet')),
      ); ?>" 
         class="inline-flex items-center gap-1 text-primary hover:text-primary-dark font-medium transition-colors">
        <span class="material-symbols-outlined text-sm">add_circle</span>
        <?php esc_html_e('Top Up Wallet', 'nera-competitions'); ?>
      </a>
    <?php endif; ?>
  </div>

  <!-- Visual Indicator -->
  <div class="mt-4 pt-4 border-t border-primary/10">
    <div class="flex items-center justify-center gap-2 text-xs text-text-secondary">
      <?php if ($can_pay_full): ?>
        <span class="material-symbols-outlined text-sm text-success">verified_user</span>
        <?php esc_html_e(
          'Select Wallet as your payment method below to use your balance.',
          'nera-competitions',
        ); ?>
      <?php elseif ($wallet_partial_enabled): ?>
        <span class="material-symbols-outlined text-sm text-success">verified_user</span>
        <?php esc_html_e(
          'Wallet credit will be applied at checkout.',
          'nera-competitions',
        ); ?>
      <?php else: ?>
        <span class="material-symbols-outlined text-sm text-text-secondary">info</span>
        <?php esc_html_e(
          'To use wallet for this order, top up to cover the full amount.',
          'nera-competitions',
        ); ?>
      <?php endif; ?>
    </div>
  </div>

</div>
