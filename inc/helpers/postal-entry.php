<?php
/**
 * Postal entry option resolvers.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Destination URL for the single-product postal entry link.
 *
 * Empty when the click action is the dialog, or when "Go to URL" is selected
 * without a URL. Callers fall back to the postal entry dialog in that case.
 *
 * @return string
 */
function nera_postal_entry_destination_url(): string
{
  if (!function_exists('get_field')) {
    return '';
  }

  $action = (string) get_field('postal_entry_click_action', 'option');
  if ($action !== 'url') {
    return '';
  }

  $url = trim((string) get_field('postal_entry_url', 'option'));
  if ($url === '') {
    return '';
  }

  return esc_url_raw($url);
}
