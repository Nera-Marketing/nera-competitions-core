<?php
/**
 * Contact Page - Hero Section
 *
 * Displays the page heading, subheading, and description.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

// Get ACF field values with fallbacks
$subheading = function_exists('get_field') ? get_field('contact_subheading') : '';
$heading = function_exists('get_field')
  ? get_field('contact_heading')
  : __('Contact Us', 'nera-competitions');
$description = function_exists('get_field')
  ? get_field('contact_description')
  : __(
    'We\'d love to hear from you regarding the competition. Our team is ready to answer any questions.',
    'nera-competitions',
  );
?>

<section class="nera-contact-hero py-12 lg:py-16 bg-gradient-to-b from-white via-gray-50 to-indigo-50">
    <div class="max-w-4xl mx-auto px-4 lg:px-8 text-center">
        <?php if ($subheading): ?>
            <p class="text-primary font-semibold text-sm uppercase tracking-wider mb-3">
                <?php echo esc_html($subheading); ?>
            </p>
        <?php endif; ?>

        <h1 class="font-heading text-4xl lg:text-5xl font-bold text-text-primary mb-4">
            <?php echo esc_html($heading); ?>
        </h1>

        <?php if ($description): ?>
            <p class="text-lg lg:text-xl text-text-secondary max-w-2xl mx-auto leading-relaxed">
                <?php echo esc_html($description); ?>
            </p>
        <?php endif; ?>
    </div>
</section>
