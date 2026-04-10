<?php
/**
 * Single Product Lottery Template
 *
 * Custom template for lottery/competition products.
 * Overrides the default WooCommerce single product template.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
  $product = wc_get_product(get_the_ID());
}

if (!$product) {
  echo '<p>' . esc_html__('Product not found.', 'nera-competitions') . '</p>';
  get_footer();
  return;
}

$product_id = $product->get_id();

// Get lottery data
$lottery_data = nera_get_lottery_product_data($product);
$gallery_images = nera_get_product_gallery_images($product);
$end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
$countdown = nera_get_countdown_parts($end_date_gmt);

// ACF fields
$gallery_badge_text = get_field('gallery_badge_text', $product_id);
$gallery_badge_color = get_field('gallery_badge_color', $product_id) ?: 'primary';
$video_url = get_field('gallery_video_url', $product_id);
?>

<main id="primary" class="site-main bg-gray-50 min-h-screen">

  <?php do_action('woocommerce_before_single_product'); ?>

  <div id="product-<?php echo esc_attr($product_id); ?>" <?php wc_product_class('', $product); ?>>

    <!-- Hero Section: Gallery + Product Info -->
    <section class="py-8 lg:py-12">
      <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

          <!-- Left Column: Image Gallery -->
          <div class="relative">
            <?php get_template_part('template-parts/single-product/product-gallery', null, [
              'product' => $product,
              'images' => $gallery_images,
              'badge_text' => $gallery_badge_text,
              'badge_color' => $gallery_badge_color,
              'video_url' => $video_url,
            ]); ?>
          </div>

          <!-- Right Column: Product Info Card -->
          <div class="lg:sticky lg:top-8 lg:self-start">
            <?php get_template_part('template-parts/single-product/product-info-card', null, [
              'product' => $product,
              'lottery_data' => $lottery_data,
              'countdown' => $countdown,
            ]); ?>
          </div>

        </div>
      </div>
    </section>

    <!-- Product Tabs Section -->
    <section class="py-12 bg-surface">
      <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <?php get_template_part('template-parts/single-product/product-tabs', null, [
          'product' => $product,
        ]); ?>
      </div>
    </section>

    <!-- Related Competitions Section -->
    <?php
    $related_ids = nera_get_related_lottery_products($product_id, 4);
    if (!empty($related_ids)): ?>
      <section class="py-12 lg:py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
          <?php get_template_part('template-parts/single-product/related-competitions', null, [
            'product' => $product,
            'related_ids' => $related_ids,
          ]); ?>
        </div>
      </section>
    <?php endif;
    ?>

  </div>

  <?php do_action('woocommerce_after_single_product'); ?>

</main>

<?php get_footer();
