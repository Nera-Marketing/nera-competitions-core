<?php
/**
 * Template Name: Nera Winners Page (Manual Entry)
 * Template Post Type: page
 *
 * Winners page template with ACF fields and AlpineJS filtering.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

get_header();
?>

<main id="main" class="nera-winners-page bg-gray-50" role="main">
    <?php
    // Hero section with heading and description
    get_template_part('template-parts/winners/hero-section');

    // Winners grid with filtering and pagination
    get_template_part('template-parts/winners/winners-grid');
    ?>
</main>

<?php get_footer();
