<?php
/**
 * Edit address form
 *
 * @package Nera Competitions Standard
 */

defined('ABSPATH') || exit();

$page_title =
  $load_address === 'billing'
    ? esc_html__('Billing address', 'woocommerce')
    : esc_html__('Shipping address', 'woocommerce');

do_action('woocommerce_before_edit_address_form_' . $load_address);
?>

<div class="nera-edit-address">
  
  <!-- Page Header -->
  <div class="mb-8">
    <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" 
       class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary transition-colors mb-4">
      <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
      <?php esc_html_e('Back to addresses', 'nera-competitions-standard'); ?>
    </a>
    
    <h2 class="text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-primary text-4xl">
        <?php echo $load_address === 'billing' ? 'receipt_long' : 'local_shipping'; ?>
      </span>
      <?php echo esc_html($page_title); ?>
    </h2>
    <p class="text-gray-600">
      <?php esc_html_e('Update your address information', 'nera-competitions-standard'); ?>
    </p>
  </div>

  <form method="post" class="woocommerce-EditAddressForm">

    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
      
      <div class="space-y-6">
        <?php do_action('woocommerce_before_edit_address_form_' . $load_address); ?>

        <?php foreach ($address as $key => $field): ?>
          <div class="woocommerce-address-fields__field-wrapper">
            <?php
            // Add custom classes to form fields
            $field['class'][] = 'w-full';
            $field['input_class'] = [
              'w-full',
              'px-4',
              'py-3',
              'border-2',
              'border-gray-200',
              'rounded-xl',
              'focus:border-primary',
              'focus:ring-2',
              'focus:ring-primary/20',
              'transition-all',
            ];
            $field['label_class'] = ['block', 'text-sm', 'font-semibold', 'text-gray-700', 'mb-2'];

            woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
            ?>
          </div>
        <?php endforeach; ?>

        <?php do_action('woocommerce_after_edit_address_form_' . $load_address); ?>
      </div>

    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3">
      <p>
        <button type="submit" 
                class="woocommerce-Button button inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-primary to-indigo-600 text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-sm hover:shadow-md w-full sm:w-auto" 
                name="save_address" 
                value="<?php esc_attr_e('Save address', 'woocommerce'); ?>">
          <span class="material-symbols-outlined text-xl">save</span>
          <?php esc_html_e('Save address', 'woocommerce'); ?>
        </button>
        <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
        <input type="hidden" name="action" value="edit_address" />
      </p>

      <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" 
         class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-surface border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-primary hover:text-primary transition-all w-full sm:w-auto">
        <span class="material-symbols-outlined text-xl">cancel</span>
        <?php esc_html_e('Cancel', 'nera-competitions-standard'); ?>
      </a>
    </div>

  </form>

</div>

<?php do_action('woocommerce_after_edit_address_form_' . $load_address); ?>
