<?php
/**
 * Shop Page Template Bridge
 *
 * Uses the Product Listing Template logic but ensures correct context
 * for the Shop page (checking Shop Page ID for ACF fields).
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();

// Get the Shop Page ID for content context
$shop_page_id = wc_get_page_id('shop');
?>

<main id="main" class="nera-product-listing nera-shop-page" role="main">

  <?php
  // 1. Hero Section - Pass Shop Page ID to get title/tagline from the Shop page settings
  get_template_part('template-parts/product-listing/hero-section', null, [
    'post_id' => $shop_page_id,
  ]);

  // 2. Filter Bar - Category, Price, Status, Sort dropdowns
  // This component queries products internally, so it doesn't need the ID context
  get_template_part('template-parts/homepage/categories-filter');

  // 3. Trust Features - Pass Shop Page ID to get ACF fields from the Shop page
  get_template_part('template-parts/product-listing/trust-features', null, [
    'post_id' => $shop_page_id,
  ]);
  ?>

</main>

<?php get_footer();
