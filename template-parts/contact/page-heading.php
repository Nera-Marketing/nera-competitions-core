<?php
/**
 * Contact Page - Page Heading
 *
 * Simple centered heading and description (no hero section).
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

// Get ACF field values with fallbacks
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

<div class="max-w-4xl mx-auto px-4 lg:px-8 text-center pt-12 lg:pt-16 pb-8">
    <h1 class="font-heading text-4xl lg:text-5xl font-bold text-text-primary mb-4">
        <?php echo esc_html($heading); ?>
    </h1>

    <?php if ($description): ?>
        <p class="text-lg lg:text-xl text-primary leading-relaxed">
            <?php echo esc_html($description); ?>
        </p>
    <?php endif; ?>
</div>