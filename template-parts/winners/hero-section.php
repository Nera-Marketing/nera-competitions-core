<?php
/**
 * Winners Page - Hero Section
 *
 * Centered heading section with badge pill, h1, and description.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly
}

// Get ACF field values with fallbacks
$heading = function_exists('get_field')
  ? get_field('winners_heading')
  : __('Recent Winners', 'nera-competitions');
$subheading = function_exists('get_field')
  ? get_field('winners_subheading')
  : __('Our Lucky Winners', 'nera-competitions');
$description = function_exists('get_field') ? get_field('winners_description') : '';

// Ensure defaults if fields are empty
$heading = $heading ?: __('Recent Winners', 'nera-competitions');
$subheading = $subheading ?: __('Our Lucky Winners', 'nera-competitions');
?>

<div class="max-w-4xl mx-auto px-5 sm:px-6 lg:px-8 text-center pt-12 lg:pt-20 pb-8 lg:pb-12">
    <!-- Badge Pill -->
    <?php if ($subheading): ?>
        <div class="flex justify-center mb-6" data-aos="fade-up">
            <span class="inline-flex items-center px-4 py-2 rounded-full bg-primary/10 text-primary text-sm font-semibold">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                <?php echo esc_html($subheading); ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Main Heading -->
    <h1 class="font-heading text-4xl lg:text-5xl xl:text-6xl font-bold text-text-primary mb-6" data-aos="fade-up" data-aos-delay="100">
        <?php echo esc_html($heading); ?>
    </h1>

    <!-- Description -->
    <?php if ($description): ?>
        <p class="text-lg lg:text-xl text-text-secondary leading-relaxed max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="200">
            <?php echo esc_html($description); ?>
        </p>
    <?php endif; ?>
</div>
