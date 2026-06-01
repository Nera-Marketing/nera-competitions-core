<?php
/**
 * Checkout Form
 * Custom checkout template for Nera Competitions
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Nera_Competitions
 */

defined('ABSPATH') || exit();

// Get checkout object
$checkout = WC()->checkout();

get_header();
?>

<!-- Page Header -->
<div class="relative left-1/2 -translate-x-1/2 w-screen max-w-none bg-gray-50 border-b border-gray-200 py-10 lg:py-16">
  <div class="container mx-auto px-4 lg:px-0">
    <div class="flex items-center gap-4 mb-8">
      <div class="w-12 h-12 rounded-xl bg-surface shadow-sm flex items-center justify-center text-primary">
        <span class="material-symbols-outlined text-2xl">lock</span>
      </div>
      <div class="text-text-secondary text-sm">
        <?php printf(
          esc_html(
            _n(
              '%d item in your order',
              '%d items in your order',
              WC()->cart->get_cart_contents_count(),
              'nera-competitions',
            ),
          ),
          WC()->cart->get_cart_contents_count(),
        ); ?>
      </div>
    </div>

    <!-- Progress Indicator -->
    <?php $steps = [
      [
        'label' => __('Cart', 'nera-competitions'),
        'icon' => 'shopping_cart',
        'status' => 'complete',
      ],
      ['label' => __('Checkout', 'nera-competitions'), 'icon' => 'payment', 'status' => 'active'],
      [
        'label' => __('Complete', 'nera-competitions'),
        'icon' => 'check_circle',
        'status' => 'pending',
      ],
    ]; ?>
    <div class="flex items-center justify-center max-w-md mx-auto">
      <?php foreach ($steps as $i => $step): ?>
        <div class="flex items-center <?php echo $i < count($steps) - 1 ? 'flex-1' : ''; ?>">
          <div class="flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1.5 transition-all duration-300
              <?php if ($step['status'] === 'complete') {
                echo 'bg-success text-white';
              } elseif ($step['status'] === 'active') {
                echo 'bg-primary text-white shadow-primary';
              } else {
                echo 'bg-gray-200 text-gray-400';
              } ?>">
              <span class="material-symbols-outlined text-xl"><?php echo esc_html(
                $step['icon'],
              ); ?></span>
            </div>
            <span class="text-xs font-semibold whitespace-nowrap
              <?php echo $step['status'] === 'active'
                ? 'text-primary'
                : ($step['status'] === 'complete'
                  ? 'text-success'
                  : 'text-gray-400'); ?>">
              <?php echo esc_html($step['label']); ?>
            </span>
          </div>

          <?php if ($i < count($steps) - 1): ?>
            <div class="h-px flex-1 mx-4 <?php echo $step['status'] === 'complete'
              ? 'bg-success'
              : 'bg-gray-200'; ?>"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="py-12 lg:py-20">
  <div class="container mx-auto px-6 lg:px-0">

    <?php do_action('woocommerce_before_checkout_form', $checkout); ?>

    <?php if (!is_user_logged_in()): ?>

      <!-- Login / Register Required -->
      <div class="max-w-lg mx-auto bg-surface rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-6">
          <span class="material-symbols-outlined text-4xl text-primary">lock</span>
        </div>
        <h2 class="text-2xl font-bold text-text-primary mb-2">
          <?php esc_html_e('Sign in to complete your order', 'nera-competitions'); ?>
        </h2>
        <p class="text-text-secondary mb-8">
          <?php esc_html_e(
            'Please log in to your account or create a new one to proceed to checkout.',
            'nera-competitions',
          ); ?>
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <a href="<?php echo esc_url(
            add_query_arg(
              'redirect_to',
              rawurlencode(wc_get_checkout_url()),
              wc_get_page_permalink('myaccount'),
            ),
          ); ?>"
            class="btn-checkout inline-flex !w-auto px-8 text-white">
            <span class="material-symbols-outlined">login</span>
            <?php esc_html_e('Log In', 'nera-competitions'); ?>
          </a>
          <a href="<?php echo esc_url(
            add_query_arg(
              ['action' => 'register', 'redirect_to' => rawurlencode(wc_get_checkout_url())],
              wc_get_page_permalink('myaccount'),
            ),
          ); ?>"
            class="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl border-2 border-primary text-primary font-semibold hover:bg-primary/5 transition-colors duration-200">
            <span class="material-symbols-outlined">person_add</span>
            <?php esc_html_e('Create Account', 'nera-competitions'); ?>
          </a>
        </div>
      </div>

    <?php else: ?>

      <form name="checkout" method="post"
        class="checkout woocommerce-checkout"
        action="<?php echo esc_url(wc_get_checkout_url()); ?>"
        enctype="multipart/form-data"
        x-data="neraCheckout()">

        <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-start">

          <!-- Left Column: Billing & Payment -->
          <div class="lg:col-span-7 space-y-6">

            <?php get_template_part('template-parts/checkout/billing-details'); ?>

            <?php get_template_part('template-parts/checkout/payment-section'); ?>

          </div>

          <!-- Right Column: Order Review -->
          <div class="lg:col-span-5 mt-8 lg:mt-0 order-last lg:order-none">
            <div class="nera-checkout-order-review-wrapper">
              <?php get_template_part('template-parts/checkout/order-review'); ?>
            </div>
          </div>

        </div>

      </form>

    <?php endif; ?>

    <?php do_action('woocommerce_after_checkout_form', $checkout); ?>

  </div>
</div>
