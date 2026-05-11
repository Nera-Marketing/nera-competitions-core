<?php
/**
 * Giveaway for WooCommerce Plugin Customizations
 * Custom hooks and modifications for the lottery/giveaway plugin
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Add body classes for lottery product states.
 *
 * - nera-lucky-dip  : lucky dip competitions so CSS/JS can style the ticket selector differently.
 * - nera-lottery-sold-out : all tickets sold, used to show a sold-out overlay or hide the add-to-cart form.
 */
function nera_giveaway_product_body_classes($classes)
{
  if (!is_product()) {
    return $classes;
  }

  $product = wc_get_product(get_the_ID());
  if (!$product || !$product->is_type('lottery')) {
    return $classes;
  }

  if (method_exists($product, 'is_lucky_dip') && $product->is_lucky_dip()) {
    $classes[] = 'nera-lucky-dip';
  }

  // Mirror the ticket-count check used in the advanced filter (woocommerce.php).
  $max_tickets = (int) get_post_meta(get_the_ID(), '_lty_maximum_tickets', true);
  $sold_tickets = method_exists($product, 'get_purchased_ticket_count')
    ? (int) $product->get_purchased_ticket_count()
    : 0;

  if ($max_tickets > 0 && $sold_tickets >= $max_tickets) {
    $classes[] = 'nera-lottery-sold-out';
  }

  return $classes;
}
add_filter('body_class', 'nera_giveaway_product_body_classes');

/**
 * Customise the lottery winner notification email subject.
 *
 * The hook name follows the WooCommerce pattern woocommerce_email_subject_{email_id}.
 * The Lottery for WooCommerce plugin registers the winner email with id 'lty_winner_notification'.
 */
function nera_lottery_winner_email_subject($subject, $email)
{
  $blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
  return sprintf(
    /* translators: %s: site name */
    __("Congratulations - You've won a prize from %s!", 'nera-competitions'),
    $blog_name,
  );
}
add_filter('woocommerce_email_subject_lty_winner_notification', 'nera_lottery_winner_email_subject', 10, 2);

/**
 * Customise the lottery winner notification email heading.
 */
function nera_lottery_winner_email_heading($heading, $email)
{
  return __("You're a Winner!", 'nera-competitions');
}
add_filter('woocommerce_email_heading_lty_winner_notification', 'nera_lottery_winner_email_heading', 10, 2);
