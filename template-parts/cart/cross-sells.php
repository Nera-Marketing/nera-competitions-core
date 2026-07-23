<?php
/**
 * Cart Cross-sells Template Part
 *
 * Grid of cross-sell competitions shown beneath the cart. Rendered by
 * nera_render_cart_crosssells() on the custom cart's `woocommerce_after_cart` hook.
 * Every card carries the "Recommended" highlight badge (matches the product-page
 * upsell treatment); ids are already visibility-filtered and capped upstream.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$crosssell_ids = isset($args['crosssell_ids']) ? (array) $args['crosssell_ids'] : [];
$title = isset($args['title']) && $args['title'] !== ''
  ? $args['title']
  : __('Add More Chances to Win', 'nera-competitions');

if (empty($crosssell_ids)) {
  return;
}
?>

<section class="nera-cart-crosssells mt-12">
  <!-- Section Header -->
  <div class="text-center mb-8">
    <h2 class="text-2xl lg:text-3xl font-bold text-text-primary">
      <?php echo esc_html($title); ?>
    </h2>
    <p class="mt-2 text-text-secondary">
      <?php _e('Add another competition before you check out', 'nera-competitions'); ?>
    </p>
  </div>

  <!-- Products Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($crosssell_ids as $crosssell_id) {
      $crosssell_product = wc_get_product($crosssell_id);

      if (!$crosssell_product) {
        continue;
      }

      // Reuse the shared competition card; badge every cross-sell.
      get_template_part('template-parts/product-listing/product-card', null, [
        'product' => $crosssell_product,
        'cta_mode' => 'link',
        'highlight_badge' => __('Recommended', 'nera-competitions'),
      ]);
    } ?>
  </div>
</section>
