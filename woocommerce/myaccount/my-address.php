<?php
/**
 * My Addresses
 *
 * Shows customer shipping and billing addresses with modern card design.
 *
 * @package Nera Competitions Standard
 */

defined('ABSPATH') || exit();

$customer_id = get_current_user_id();

if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
  $get_addresses = apply_filters(
    'woocommerce_my_account_get_addresses',
    [
      'billing' => __('Billing address', 'woocommerce'),
      'shipping' => __('Shipping address', 'woocommerce'),
    ],
    $customer_id,
  );
} else {
  $get_addresses = apply_filters(
    'woocommerce_my_account_get_addresses',
    [
      'billing' => __('Billing address', 'woocommerce'),
    ],
    $customer_id,
  );
}

$col = 1;
?>

<div class="nera-account-addresses">
  
  <!-- Page Header -->
  <div class="mb-8">
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>"
       class="lg:hidden inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary transition-colors mb-4">
      <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
      <?php esc_html_e('Back to Dashboard', 'nera-competitions-standard'); ?>
    </a>

    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-primary text-3xl sm:text-4xl">location_on</span>
      <?php esc_html_e('Addresses', 'woocommerce'); ?>
    </h2>
    <p class="text-gray-600">
      <?php esc_html_e(
        'Manage your billing and shipping addresses',
        'nera-competitions-standard',
      ); ?>
    </p>
  </div>

  <?php if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()): ?>
    <p class="mb-6 text-gray-600">
      <?php echo apply_filters(
        'woocommerce_my_account_my_address_description',
        esc_html__(
          'The following addresses will be used on the checkout page by default.',
          'woocommerce',
        ),
      ); ?>
    </p>
  <?php endif; ?>

  <!-- Address Cards Grid -->
  <div class="grid grid-cols-1 <?php echo count($get_addresses) > 1
    ? 'lg:grid-cols-2'
    : ''; ?> gap-6">

    <?php foreach ($get_addresses as $name => $address_title): ?>
      <?php
      $address = wc_get_account_formatted_address($name);
      $col = $col * -1;

      // Icon mapping
      $address_icons = [
        'billing' => 'receipt_long',
        'shipping' => 'local_shipping',
      ];
      $icon = isset($address_icons[$name]) ? $address_icons[$name] : 'location_on';

      // Icon colors
      $icon_colors = [
        'billing' => 'from-blue-500 to-blue-600',
        'shipping' => 'from-green-500 to-green-600',
      ];
      $icon_color = isset($icon_colors[$name]) ? $icon_colors[$name] : 'from-primary to-indigo-600';
      ?>

      <!-- Address Card -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100/50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-gradient-to-br <?php echo esc_attr(
                $icon_color,
              ); ?> rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-2xl"><?php echo esc_html(
                  $icon,
                ); ?></span>
              </div>
              <h3 class="text-xl font-bold text-gray-900">
                <?php echo esc_html($address_title); ?>
              </h3>
            </div>
            
            <?php if (!$address): ?>
              <span class="px-3 py-1 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-full">
                Not set
              </span>
            <?php else: ?>
              <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 rounded-full">
                Active
              </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-6">
          
          <address class="not-italic">
            <?php if ($address): ?>
              <div class="space-y-2 mb-6">
                <?php
                $address_lines = explode('<br/>', $address);
                foreach ($address_lines as $line):
                  $line = trim(strip_tags($line));
                  if (!empty($line)): ?>
                  <p class="text-gray-700 flex items-start gap-2">
                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">location_on</span>
                    <span><?php echo esc_html($line); ?></span>
                  </p>
                <?php endif;
                endforeach;
                ?>
              </div>
            <?php else: ?>
              <div class="py-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                  <span class="material-symbols-outlined text-gray-400 text-3xl">add_location</span>
                </div>
                <p class="text-gray-600 mb-1 font-medium">
                  <?php esc_html_e(
                    'You have not set up this type of address yet.',
                    'woocommerce',
                  ); ?>
                </p>
                <p class="text-sm text-gray-500">
                  <?php esc_html_e(
                    'Add your address for faster checkout',
                    'nera-competitions-standard',
                  ); ?>
                </p>
              </div>
            <?php endif; ?>
          </address>

          <!-- Edit Button -->
          <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', $name)); ?>" 
             class="inline-flex items-center justify-center gap-2 w-full px-6 py-3 <?php echo $address
               ? 'bg-white border-2 border-gray-200 text-gray-700 hover:border-primary hover:text-primary'
               : 'bg-gradient-to-r from-primary to-indigo-600 text-white hover:opacity-90 shadow-sm hover:shadow-md'; ?> font-semibold rounded-xl transition-all">
            <span class="material-symbols-outlined text-base"><?php echo $address
              ? 'edit'
              : 'add'; ?></span>
            <?php if ($address) {
              printf(
                /* translators: %s: address title */
                esc_html__('Edit %s', 'woocommerce'),
                esc_html($address_title),
              );
            } else {
              printf(
                /* translators: %s: address title */
                esc_html__('Add %s', 'woocommerce'),
                esc_html($address_title),
              );
            } ?>
          </a>

        </div>

      </div>

    <?php endforeach; ?>

  </div>

  <!-- Additional Info Card -->
  <div class="mt-6 bg-blue-50 border border-blue-100 rounded-2xl p-6">
    <div class="flex gap-4">
      <div class="flex-shrink-0">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-blue-600 text-xl">info</span>
        </div>
      </div>
      <div>
        <h4 class="font-semibold text-blue-900 mb-1">
          <?php esc_html_e('Address Information', 'nera-competitions-standard'); ?>
        </h4>
        <p class="text-sm text-blue-700">
          <?php esc_html_e(
            'These addresses will be pre-filled during checkout. Make sure they are accurate to ensure smooth delivery of your prizes.',
            'nera-competitions-standard',
          ); ?>
        </p>
      </div>
    </div>
  </div>

</div>

<?php
/**
 * Deprecated woocommerce_after_my_account hook.
 *
 * @deprecated 2.6.0
 */
do_action('woocommerce_after_my_account');
