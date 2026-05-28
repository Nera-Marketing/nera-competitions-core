<?php
/**
 * Template Name: Nera Contact Page
 * Template Post Type: page
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit();
}

get_header();

if (nera_render_page_components()):
    // rendered via ACF Flexible Content
else:
    nera_render_component('Contact');
endif;

get_footer();
