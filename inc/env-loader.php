<?php
/**
 * Load environment variables from .env.local
 * Theme-only config - no wp-config.php changes needed
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  return;
}

$theme_dir = dirname(__DIR__);
$env_file = $theme_dir . '/.env.local';

$nera_dev_mode = false;
$nera_vite_dev_server_url = 'http://localhost:5173';

if (file_exists($env_file) && is_readable($env_file)) {
  $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (is_array($lines)) {
    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '' || strpos($line, '#') === 0) {
        continue;
      }
      if (strpos($line, '=') === false) {
        continue;
      }
      [$key, $value] = explode('=', $line, 2);
      $key = trim($key);
      $value = trim($value);
      $value = trim($value, '"\'');
      if ($key === 'NERA_DEV_MODE') {
        $nera_dev_mode = filter_var($value, FILTER_VALIDATE_BOOLEAN);
      }
      if ($key === 'NERA_VITE_DEV_SERVER_URL') {
        $nera_vite_dev_server_url = $value ?: 'http://localhost:5173';
      }
    }
  }
}

// Production safety: never use dev mode when WP reports production
if (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'production') {
  $nera_dev_mode = false;
}

if (!defined('NERA_DEV_MODE')) {
  define('NERA_DEV_MODE', $nera_dev_mode);
}
if (!defined('NERA_VITE_DEV_SERVER_URL')) {
  define('NERA_VITE_DEV_SERVER_URL', $nera_vite_dev_server_url);
}
