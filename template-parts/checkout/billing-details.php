<?php
/**
 * Checkout Billing Details
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$checkout = WC()->checkout();
?>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-8" id="customer_details">

  <h2 class="text-xl font-bold text-text-primary mb-6 flex items-center gap-2">
    <span class="material-symbols-outlined text-primary">person</span>
    <?php esc_html_e('Billing Details', 'nera-competitions'); ?>
  </h2>

  <div class="woocommerce-billing-fields">
    <?php do_action('woocommerce_before_checkout_billing_form', $checkout); ?>

    <!-- Grid container with proper gap and column rules -->
    <div class="woocommerce-billing-fields__field-wrapper grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
      <?php
      $fields = $checkout->get_checkout_fields('billing');
      foreach ($fields as $key => $field):
        // Determine column span based on field class
        $field_classes = isset($field['class']) ? $field['class'] : [];
        $is_full_width = in_array('form-row-full', $field_classes);

        if ($is_full_width) {
          $field['class'][] = 'md:col-span-2';
        }

        woocommerce_form_field($key, $field, $checkout->get_value($key));
      endforeach;
      ?>
    </div>

    <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
  </div>

  <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()): ?>
    <div class="mt-8 pt-6 border-t border-gray-100">
      <h2 class="text-xl font-bold text-text-primary mb-6 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">local_shipping</span>
        <?php esc_html_e('Shipping Details', 'nera-competitions'); ?>
      </h2>

      <div class="woocommerce-shipping-fields">
        <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

        <div class="woocommerce-shipping-fields__field-wrapper grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
          <?php
          $shipping_fields = $checkout->get_checkout_fields('shipping');
          foreach ($shipping_fields as $key => $field):
            // Mirror billing field width logic for shipping
            $billing_key = str_replace('shipping_', 'billing_', $key);
            $field_classes = isset($field['class']) ? $field['class'] : [];

            // Check if corresponding billing field is full-width
            $billing_field = isset($fields[$billing_key]) ? $fields[$billing_key] : null;
            if ($billing_field && in_array('form-row-full', $billing_field['class'])) {
              $field['class'][] = 'md:col-span-2';
            }

            woocommerce_form_field($key, $field, $checkout->get_value($key));
          endforeach;
          ?>
        </div>

        <?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>
      </div>
    </div>
  <?php endif; ?>

  <?php do_action('woocommerce_checkout_after_customer_details'); ?>

  <?php if ($checkout->get_checkout_fields('order')): ?>
    <div class="mt-8 pt-6 border-t border-gray-100">
      <h3 class="text-lg font-bold text-text-primary mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">edit_note</span>
        <?php esc_html_e('Additional Information', 'nera-competitions'); ?>
      </h3>
      <div class="woocommerce-additional-fields__field-wrapper">
        <?php foreach ($checkout->get_checkout_fields('order') as $key => $field):
          woocommerce_form_field($key, $field, $checkout->get_value($key));
        endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>
