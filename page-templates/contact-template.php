<?php
/**
 * Template Name: Nera Contact Page
 * Template Post Type: page
 *
 * Contact page template with ACF fields and Fluent Forms integration.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

get_header();
?>

<main id="main" class="nera-contact-page bg-gray-50" role="main">
    <?php // Simple page heading at top

get_template_part('template-parts/contact/page-heading');
// Two-column grid: contact info + form side-by-side
?>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 lg:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Left: Contact Info -->
            <?php get_template_part('template-parts/contact/contact-info'); ?>

            <!-- Right: Form -->
            <?php get_template_part('template-parts/contact/form-section'); ?>
        </div>
    </div>
</main>

<?php get_footer();
