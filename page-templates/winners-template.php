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

$_w_title       = function_exists('get_field') ? ((string) get_field('winners_heading') ?: __('Recent Winners', 'nera-competitions')) : __('Recent Winners', 'nera-competitions');
$_w_eyebrow     = function_exists('get_field') ? ((string) get_field('winners_subheading') ?: __('Our Lucky Winners', 'nera-competitions')) : __('Our Lucky Winners', 'nera-competitions');
$_w_description = function_exists('get_field') ? (string) get_field('winners_description') : '';
?>

<main id="main" class="nera-winners-page bg-gray-50" role="main">
    <?php
    get_template_part('template-parts/components/shared/page-hero', null, [
        'title'         => $_w_title,
        'description'   => $_w_description,
        'eyebrow_label' => $_w_eyebrow,
        'eyebrow_icon'  => 'emoji_events',
    ]);

    // Winners grid with filtering and pagination
    get_template_part('template-parts/winners/winners-grid');
    ?>
</main>

<?php get_footer();
