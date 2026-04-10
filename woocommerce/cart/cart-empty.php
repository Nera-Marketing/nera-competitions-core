<?php
/**
 * Empty Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * @package Nera_Competitions
 */

defined('ABSPATH') || exit();

/*
 * Use our custom template part for the empty cart state.
 */
?>
<div class="w-full flex items-center justify-center px-4 py-8 md:py-12 min-h-[calc(100vh-9rem)] lg:min-h-[calc(100vh-10rem)]">
  <?php get_template_part('template-parts/cart/cart-empty'); ?>
</div>
