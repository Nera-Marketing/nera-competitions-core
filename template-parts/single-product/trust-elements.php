<?php
/**
 * Trust Elements Template Part
 *
 * Payment logos, Trustpilot badge, and security badges.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;

if (!$product) {
  return;
}

$product_id = $product->get_id();

// ACF settings
$show_payment_logos = get_field('show_payment_logos', $product_id);
$payment_logos = get_field('payment_logos', $product_id);
$show_trustpilot = get_field('show_trustpilot', $product_id);
$trustpilot_score = get_field('trustpilot_score', $product_id) ?: '4.8';
$trustpilot_reviews = get_field('trustpilot_reviews', $product_id) ?: '1,250';
$trust_badges = get_field('trust_badges', $product_id);

// Default to showing if not set
if ($show_payment_logos === null) {
  $show_payment_logos = true;
}
if ($show_trustpilot === null) {
  $show_trustpilot = true;
}

// Get default payment logos if none set
$default_logos = nera_get_default_payment_logos();
?>

<div class="trust-elements space-y-4 pt-4 border-t border-gray-100">

  <!-- Payment Logos -->
  <?php if ($show_payment_logos): ?>
    <div class="payment-logos">
      <p class="text-xs text-text-secondary text-center mb-2">
        <?php _e('Secure payments accepted', 'nera-competitions'); ?>
      </p>
      <div class="flex items-center justify-center gap-3 flex-wrap">
        <?php if (!empty($payment_logos)): ?>
          <?php foreach ($payment_logos as $logo): ?>
            <img
              src="<?php echo esc_url($logo['sizes']['thumbnail']); ?>"
              alt="<?php echo esc_attr($logo['alt'] ?: $logo['title']); ?>"
              class="h-6 w-auto grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all"
            />
          <?php endforeach; ?>
        <?php else: ?>
          <?php foreach ($default_logos as $logo): ?>
            <div class="payment-logo grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all" title="<?php echo esc_attr(
              $logo['name'],
            ); ?>">
              <?php echo $logo['svg']; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Trustpilot Badge -->
  <?php if ($show_trustpilot): ?>
    <div class="trustpilot-badge">
      <div class="flex items-center justify-center gap-3 bg-gray-50 rounded-xl p-3">
        <!-- Trustpilot Stars -->
        <div class="flex items-center gap-1">
          <?php
          $full_stars = floor(floatval($trustpilot_score));
          $has_half = floatval($trustpilot_score) - $full_stars >= 0.5;
          ?>
          <?php for ($i = 0; $i < $full_stars; $i++): ?>
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="#00B67A">
              <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
            </svg>
          <?php endfor; ?>
          <?php if ($has_half): ?>
            <svg class="w-5 h-5" viewBox="0 0 24 24">
              <defs>
                <linearGradient id="half-star">
                  <stop offset="50%" stop-color="#00B67A"/>
                  <stop offset="50%" stop-color="#dcdce6"/>
                </linearGradient>
              </defs>
              <path fill="url(#half-star)" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
            </svg>
          <?php endif; ?>
        </div>

        <div class="text-sm">
          <span class="font-bold text-text-primary"><?php echo esc_html(
            $trustpilot_score,
          ); ?></span>
          <span class="text-text-secondary">/ 5</span>
          <span class="text-text-secondary mx-1">|</span>
          <span class="text-text-secondary"><?php echo esc_html($trustpilot_reviews); ?> <?php _e(
   'reviews',
   'nera-competitions',
 ); ?></span>
        </div>

        <!-- Trustpilot Logo -->
        <svg class="h-5 w-auto" viewBox="0 0 126 31" fill="none">
          <path d="M33.3 13.8h-4v-3.4h12.5v3.4h-4v12.1h-4.5V13.8zm7.8-3.4h4.5v6h.1c.7-1.4 2.2-2.3 4-2.3 2.5 0 4.5 1.6 4.5 5v7.2h-4.5v-6.2c0-1.5-.6-2.4-1.8-2.4-1.3 0-2.2 1-2.2 2.6v6h-4.5l-.1-15.9zm18.9 6.3c-1.2 0-2 .5-2 1.4 0 .8.6 1.2 1.6 1.4l2.1.4c3.1.6 4.6 2 4.6 4.4 0 3-2.6 4.8-6.3 4.8-3.8 0-6.5-1.7-6.8-4.5h4c.3 1.1 1.2 1.7 2.8 1.7 1.4 0 2.2-.5 2.2-1.4 0-.8-.7-1.2-2-1.5l-2.1-.4c-2.8-.5-4.3-2-4.3-4.3 0-2.8 2.5-4.6 6-4.6 3.6 0 6 1.7 6.3 4.4h-3.8c-.2-1-1-1.6-2.3-1.8zm5.1 4.8v-4.8h1.6v-3.2h4.5v3.2h2.4v3.5h-2.4v4.4c0 1 .5 1.4 1.4 1.4h1v3.5c-.4.1-1 .2-1.9.2-3.2 0-5-1.5-5-4.4l-1.6-3.8z" fill="#191919"/>
          <path d="M75.6 25.9h-4.5v-1h-.1c-.7 1.1-2 1.4-3.4 1.4-3 0-5.5-2.5-5.5-6.5s2.5-6.5 5.5-6.5c1.4 0 2.6.4 3.4 1.3h.1v-4.2h4.5v15.5zm-5.7-3.3c1.5 0 2.7-1.2 2.7-3s-1.2-3-2.7-3c-1.6 0-2.7 1.2-2.7 3s1.1 3 2.7 3zm8.2-8.1h4.5v1.2h.1c.8-1.2 2-1.6 3.5-1.6 2.7 0 4.5 1.6 4.5 5v6.8h-4.5v-6c0-1.5-.6-2.3-1.8-2.3-1.3 0-2.2.9-2.2 2.4v5.9H78l.1-11.4zm13.4 5.6c0-4.1 2.8-6.8 6.9-6.8 4 0 6.7 2.6 6.7 6.5v1.3h-9c.2 1.6 1.2 2.5 2.7 2.5 1 0 1.7-.4 2.1-1.1h4.3c-.6 2.6-3 4.3-6.5 4.3-4.2 0-7.2-2.7-7.2-6.7zm9.2-1.6c-.2-1.3-1.1-2.1-2.4-2.1-1.4 0-2.3.8-2.5 2.1h4.9zm5.7 4.6h4.2c.3.9 1 1.4 2.2 1.4 1 0 1.5-.3 1.5-.9 0-.6-.5-.9-1.5-1l-2.3-.4c-2.6-.4-4-1.8-4-3.9 0-2.6 2.3-4.2 5.7-4.2 3.4 0 5.5 1.4 5.9 3.9h-4.1c-.2-.8-.9-1.2-1.9-1.2-.9 0-1.4.3-1.4.8 0 .5.5.8 1.4 1l2.3.4c2.8.5 4.2 1.8 4.2 4 0 2.8-2.4 4.4-6 4.4-3.7 0-5.9-1.6-6.2-4.3z" fill="#191919"/>
          <path d="M21.1 12.6l-3-9.2L15 12.6H5.2l7.9 5.8-3 9.2 7.9-5.8 7.9 5.8-3-9.2 7.9-5.8h-9.8z" fill="#00B67A"/>
          <path d="M24 20.2l-.7-2.2-5.3 3.9 6-1.7z" fill="#005128"/>
        </svg>
      </div>
    </div>
  <?php endif; ?>

  <!-- Trust Badges -->
  <?php if (!empty($trust_badges)): ?>
    <div class="custom-trust-badges">
      <div class="flex flex-wrap items-center justify-center gap-4">
        <?php foreach ($trust_badges as $badge): ?>
          <?php if (!empty($badge['icon']) && !empty($badge['text'])): ?>
            <div class="flex items-center gap-2 text-sm text-text-secondary">
              <span class="material-symbols-outlined text-green-500 text-lg">
                <?php echo esc_html($badge['icon']); ?>
              </span>
              <span><?php echo esc_html($badge['text']); ?></span>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php else: ?>
    <!-- Default Trust Badges -->
    <div class="default-trust-badges">
      <div class="flex flex-wrap items-center justify-center gap-4">
        <div class="flex items-center gap-2 text-sm text-text-secondary">
          <span class="material-symbols-outlined text-green-500 text-lg">verified_user</span>
          <span><?php _e('SSL Encrypted', 'nera-competitions'); ?></span>
        </div>
        <div class="flex items-center gap-2 text-sm text-text-secondary">
          <span class="material-symbols-outlined text-green-500 text-lg">shield</span>
          <span><?php _e('Secure Checkout', 'nera-competitions'); ?></span>
        </div>
        <div class="flex items-center gap-2 text-sm text-text-secondary">
          <span class="material-symbols-outlined text-green-500 text-lg">support_agent</span>
          <span><?php _e('24/7 Support', 'nera-competitions'); ?></span>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>
