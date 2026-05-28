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

// Shared hero: same ACF + fallback behavior as former template-parts/contact/page-heading.php
if (function_exists('get_field')) {
  $heading = get_field('contact_heading');
  $description = get_field('contact_description');
} else {
  $heading = __('Contact Us', 'nera-competitions');
  $description = __(
    'We\'d love to hear from you regarding the competition. Our team is ready to answer any questions.',
    'nera-competitions',
  );
}

$heading = $heading !== null && $heading !== false ? (string) $heading : '';
$description = $description !== null && $description !== false ? (string) $description : '';

$hero_args = [
  'title' => $heading,
  'variant' => 'default',
];
if ($description !== '') {
  $hero_args['description'] = $description;
}
?>

<main id="main" class="nera-contact-page bg-gray-50" role="main">
  <?php get_template_part('template-parts/components/shared/page-hero', null, $hero_args); ?>
    <div class="max-w-7xl mx-auto px-4 lg:px-0 py-12 lg:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Left: Contact Info -->
            <?php get_template_part('template-parts/contact/contact-info'); ?>

            <!-- Right: Form -->
            <?php get_template_part('template-parts/contact/form-section'); ?>
        </div>
    </div>
</main>

<?php get_footer();
