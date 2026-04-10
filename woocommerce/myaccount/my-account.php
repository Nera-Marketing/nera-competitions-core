<?php
/**
 * My Account page
 *
 * Main wrapper template for My Account area with modern design
 *
 * @package Nera Competitions Standard
 */

defined('ABSPATH') || exit();

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
do_action('woocommerce_account_navigation');
?>

<div class="woocommerce-MyAccount-content">
  <?php do_action('woocommerce_account_content'); ?>
</div>
