<?php
/**
 * My Account Dashboard
 *
 * Custom dashboard with modern card-based design
 * Shows welcome message, stats, quick actions, and recent orders
 *
 * @package Nera Competitions Standard
 */

defined('ABSPATH') || exit();

$user = wp_get_current_user();
$customer_orders = wc_get_orders([
  'customer' => get_current_user_id(),
  'limit' => 5,
  'orderby' => 'date',
  'order' => 'DESC',
]);

// Get wallet balance if available (use 'edit' for raw number - 'view' returns HTML)
$wallet_balance = 0;
if (function_exists('woo_wallet') && is_object(woo_wallet()) && is_object(woo_wallet()->wallet)) {
  $wallet_balance = (float) woo_wallet()->wallet->get_wallet_balance(get_current_user_id(), 'edit');
}

$total_orders = count(
  wc_get_orders([
    'customer' => get_current_user_id(),
    'limit' => -1,
  ]),
);
?>

<div class="nera-account-dashboard">
  
  <!-- Welcome Hero Card -->
  <div class="bg-gradient-to-br from-primary via-indigo-600 to-indigo-700 rounded-2xl shadow-[0_20px_25px_-5px_rgba(99,102,241,0.3)] p-5 sm:p-8 mb-8 relative overflow-hidden">
    <!-- Decorative background pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-0 right-0 w-64 h-64 bg-surface rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-surface rounded-full blur-3xl transform -translate-x-1/2 translate-y-1/2"></div>
    </div>
    
    <div class="relative z-10">
      <div class="flex items-center gap-3 sm:gap-4">
        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-surface/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex-shrink-0 flex items-center justify-center border border-white/30">
          <span class="material-symbols-outlined text-white text-2xl sm:text-4xl">person</span>
        </div>
        <div class="min-w-0">
          <h1 class="text-xl sm:text-3xl font-bold text-white mb-0.5 sm:mb-1 leading-tight">
            Welcome back, <?php echo esc_html($user->display_name); ?>!
          </h1>
          <p class="text-indigo-100 text-sm sm:text-lg">
             <?php printf(
               esc_html__(
                 'Ready to win big? Check out our latest competitions!',
                 'nera-competitions-standard',
               ),
             ); ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Total Orders Card -->
    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined text-white text-2xl">receipt_long</span>
        </div>
        <span class="text-3xl font-bold text-gray-900"><?php echo esc_html($total_orders); ?></span>
      </div>
      <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Orders</h3>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" 
         class="mt-3 inline-flex items-center text-sm font-medium text-primary hover:text-indigo-700 transition-colors">
        View all
        <span class="material-symbols-outlined text-base ml-1">arrow_forward</span>
      </a>
    </div>

    <!-- Wallet Balance Card -->
    <?php if (function_exists('woo_wallet')): ?>
    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined text-white text-2xl">account_balance_wallet</span>
        </div>
        <span class="text-3xl font-bold text-gray-900"><?php echo wc_price(
          $wallet_balance,
        ); ?></span>
      </div>
      <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Wallet Balance</h3>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('woo-wallet')); ?>" 
         class="mt-3 inline-flex items-center text-sm font-medium text-primary hover:text-indigo-700 transition-colors">
        Manage wallet
        <span class="material-symbols-outlined text-base ml-1">arrow_forward</span>
      </a>
    </div>
    <?php endif; ?>

    <!-- Account Status Card -->
    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined text-white text-2xl">verified_user</span>
        </div>
        <span class="text-sm font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Active</span>
      </div>
      <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Account Status</h3>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" 
         class="mt-3 inline-flex items-center text-sm font-medium text-primary hover:text-indigo-700 transition-colors">
        Edit details
        <span class="material-symbols-outlined text-base ml-1">arrow_forward</span>
      </a>
    </div>

  </div>

  <!-- Quick Actions Card -->
  <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
      <span class="material-symbols-outlined text-primary">bolt</span>
      Quick Actions
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" 
         class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-indigo-600 text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-sm hover:shadow-md">
        <span class="material-symbols-outlined">shopping_bag</span>
        <span>Browse Competitions</span>
      </a>

      <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" 
         class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-surface border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-primary hover:text-primary transition-all">
        <span class="material-symbols-outlined">receipt_long</span>
        <span>My Orders</span>
      </a>

      <?php if (function_exists('woo_wallet')): ?>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('woo-wallet')); ?>" 
         class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-surface border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-primary hover:text-primary transition-all">
        <span class="material-symbols-outlined">account_balance_wallet</span>
        <span>My Wallet</span>
      </a>
      <?php endif; ?>

      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" 
         class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-surface border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-primary hover:text-primary transition-all">
        <span class="material-symbols-outlined">settings</span>
        <span>Settings</span>
      </a>

    </div>
  </div>

  <!-- Recent Orders Card -->
  <?php if (!empty($customer_orders)): ?>
  <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-6 gap-3">
      <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2 shrink-0">
        <span class="material-symbols-outlined text-primary">schedule</span>
        Recent Orders
      </h2>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" 
         class="text-sm font-medium text-primary hover:text-indigo-700 transition-colors whitespace-nowrap">
        View all
      </a>
    </div>

    <div class="space-y-4">
      <?php foreach ($customer_orders as $order):

        $order_id = $order->get_id();
        $order_status = $order->get_status();
        $order_date = $order->get_date_created();
        $order_total = $order->get_total();
        $item_count = $order->get_item_count();

        // Status badge colors
        $status_colors = [
          'completed' => 'bg-green-50 text-green-700 border-green-200',
          'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
          'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
          'on-hold' => 'bg-orange-50 text-orange-700 border-orange-200',
          'cancelled' => 'bg-red-50 text-red-700 border-red-200',
          'refunded' => 'bg-gray-50 text-gray-700 border-gray-200',
          'failed' => 'bg-red-50 text-red-700 border-red-200',
        ];
        $status_class = isset($status_colors[$order_status])
          ? $status_colors[$order_status]
          : 'bg-gray-50 text-gray-700 border-gray-200';
        ?>
      <a href="<?php echo esc_url($order->get_view_order_url()); ?>"
         class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
          <span class="material-symbols-outlined text-primary text-xl">receipt</span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap mb-0.5">
            <h3 class="font-semibold text-gray-900 whitespace-nowrap">
              Order #<?php echo esc_html($order_id); ?>
            </h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full border <?php echo esc_attr(
              $status_class,
            ); ?>">
              <?php echo esc_html(wc_get_order_status_name($order_status)); ?>
            </span>
          </div>
          <p class="text-sm text-gray-600 truncate">
            <?php echo esc_html($item_count); ?> item<?php echo $item_count > 1 ? 's' : ''; ?> •
            <?php echo esc_html($order_date->date_i18n(get_option('date_format'))); ?>
          </p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <p class="text-base font-bold text-gray-900"><?php echo wp_kses_post(
            $order->get_formatted_order_total(),
          ); ?></p>
          <span class="material-symbols-outlined text-gray-400 text-lg group-hover:text-primary transition-colors">chevron_right</span>
        </div>
      </a>
      <?php
      endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
      <span class="material-symbols-outlined text-gray-400 text-4xl">shopping_cart</span>
    </div>
    <h3 class="text-xl font-bold text-gray-900 mb-2">No orders yet</h3>
    <p class="text-gray-600 mb-6">Start browsing our exciting competitions and place your first order!</p>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" 
       class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary to-indigo-600 text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-sm hover:shadow-md">
      <span class="material-symbols-outlined">shopping_bag</span>
      <span>Browse Competitions</span>
    </a>
  </div>
  <?php endif; ?>

</div>

<?php
/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_before_my_account');

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_after_my_account');

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

