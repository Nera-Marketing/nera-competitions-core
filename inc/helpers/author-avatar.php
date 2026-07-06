<?php
/**
 * Author avatar helpers — ACF profile picture only (no Gravatar).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Resolve the best image URL for a user's profile picture.
 *
 * @param int $user_id User ID.
 * @param int $size    Requested display size in pixels.
 * @return string Image URL or empty string.
 */
function nera_get_user_profile_picture_url($user_id, $size = 96)
{
  $user_id = (int) $user_id;
  if ($user_id <= 0 || !function_exists('get_field')) {
    return '';
  }

  $image = get_field('author_profile_picture', 'user_' . $user_id);
  if (empty($image)) {
    return '';
  }

  if (is_numeric($image)) {
    $url = wp_get_attachment_image_url((int) $image, nera_author_avatar_image_size($size));
    return $url ? $url : '';
  }

  if (!is_array($image)) {
    return is_string($image) ? $image : '';
  }

  $size_name = nera_author_avatar_image_size($size);
  if (!empty($image['sizes'][$size_name])) {
    return (string) $image['sizes'][$size_name];
  }

  if (!empty($image['url'])) {
    return (string) $image['url'];
  }

  return '';
}

/**
 * Map a pixel size to the closest registered WordPress image size.
 *
 * @param int $size Requested size in pixels.
 * @return string Image size slug.
 */
function nera_author_avatar_image_size($size)
{
  $size = (int) $size;

  if ($size <= 48) {
    return 'thumbnail';
  }

  if ($size <= 150) {
    return 'medium';
  }

  return 'medium_large';
}

/**
 * Build initials from a user's display name.
 *
 * @param int $user_id User ID.
 * @return string Up to two uppercase initials.
 */
function nera_get_author_initials($user_id)
{
  $user = get_userdata((int) $user_id);
  if (!$user || $user->display_name === '') {
    return '?';
  }

  $parts = preg_split('/\s+/', trim($user->display_name));
  if (!$parts) {
    return '?';
  }

  if (count($parts) === 1) {
    return strtoupper(mb_substr($parts[0], 0, 1));
  }

  return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
}

/**
 * Render an author avatar from ACF or initials placeholder.
 *
 * @param int   $user_id User ID.
 * @param int   $size    Display size in pixels.
 * @param array $args {
 *     @type string $class Extra CSS classes for the outer element.
 * }
 * @return string HTML markup.
 */
function nera_render_author_avatar($user_id, $size = 48, $args = [])
{
  $user_id = (int) $user_id;
  $size = max(16, (int) $size);
  $class = isset($args['class']) ? (string) $args['class'] : '';
  $url = nera_get_user_profile_picture_url($user_id, $size);
  $user = get_userdata($user_id);
  $alt = $user && $user->display_name !== '' ? $user->display_name : __('Author', 'nera-competitions');

  if ($url !== '') {
    return sprintf(
      '<img src="%s" alt="%s" width="%d" height="%d" class="%s" loading="lazy" decoding="async" />',
      esc_url($url),
      esc_attr($alt),
      $size,
      $size,
      esc_attr($class),
    );
  }

  $initials = nera_get_author_initials($user_id);
  $placeholder_class = trim(
    'inline-flex items-center justify-center bg-gradient-to-br from-primary to-primary-dark text-white font-bold shrink-0 ' .
      $class,
  );

  $font_size = $size <= 32 ? 'text-xs' : ($size <= 48 ? 'text-sm' : 'text-base');

  return sprintf(
    '<span class="%s %s" aria-hidden="true">%s</span>',
    esc_attr($placeholder_class),
    esc_attr($font_size),
    esc_html($initials),
  );
}
