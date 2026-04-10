<?php
/**
 * Contact Page - Form Section
 *
 * Displays the contact form using Fluent Forms integration with lavender background.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get ACF field values
$form_heading = function_exists('get_field') ? get_field('form_heading') : __('Send Us a Message', 'nera-competitions');
$form_description = function_exists('get_field') ? get_field('form_description') : '';
$form_id = function_exists('get_field') ? get_field('fluent_form_id') : 0;
?>

<div class="bg-surface rounded-2xl p-6 lg:p-8 shadow-md">
    <!-- Form Header -->
    <?php if ($form_heading): ?>
        <h2 class="text-2xl font-heading font-bold text-text-primary mb-6 text-center">
            <?php echo esc_html($form_heading); ?>
        </h2>
    <?php endif; ?>

    <?php if ($form_description): ?>
        <p class="text-text-secondary mb-6">
            <?php echo esc_html($form_description); ?>
        </p>
    <?php endif; ?>

    <!-- Form Content -->
    <?php
    // Check if Fluent Forms is active and form ID is set
    if ($form_id && function_exists('wpFluentForm')) {
        // Render the Fluent Form
        echo do_shortcode('[fluentform id="' . absint($form_id) . '"]');
    } elseif ($form_id && !function_exists('wpFluentForm')) {
        // Fluent Forms plugin not active
        ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <span class="material-symbols-outlined text-yellow-600 text-4xl mb-3 block">warning</span>
            <p class="text-yellow-800 font-semibold mb-2">
                <?php _e('Fluent Forms Plugin Required', 'nera-competitions'); ?>
            </p>
            <p class="text-yellow-700 text-sm">
                <?php _e('Please install and activate the Fluent Forms plugin to display the contact form.', 'nera-competitions'); ?>
            </p>
        </div>
        <?php
    } else {
        // No form ID configured
        ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <span class="material-symbols-outlined text-blue-600 text-4xl mb-3 block">info</span>
            <p class="text-blue-800 font-semibold mb-2">
                <?php _e('Contact Form Not Configured', 'nera-competitions'); ?>
            </p>
            <p class="text-blue-700 text-sm mb-3">
                <?php _e('Please configure a Fluent Form ID in the page settings to display the contact form.', 'nera-competitions'); ?>
            </p>
            <?php if (current_user_can('edit_pages')): ?>
                <a href="<?php echo esc_url(get_edit_post_link()); ?>"
                    class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors text-sm font-medium">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    <?php _e('Edit Page Settings', 'nera-competitions'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
    ?>
</div>