<?php
/**
 * Nera Competitions Standard Theme
 *
 * @package Nera_Competitions
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Load theme env from .env.local (no wp-config changes needed)
require_once __DIR__ . '/inc/env-loader.php';

// Define theme constants (template directory = parent theme; child-safe when used as a parent)
define('NERA_VERSION', '1.0.0');
define('NERA_DIR', get_template_directory());
define('NERA_URI', get_template_directory_uri());
define('NERA_FRONTEND_DIST_DIR', NERA_DIR . '/frontend/dist');
define('NERA_FRONTEND_DIST_URI', NERA_URI . '/frontend/dist');
define('NERA_ASSETS_URI', NERA_URI . '/frontend/assets');
define('NERA_ATTRIBUTION_ROUTE_SLUG', 'competition-website-by-nera-marketing');
define('NERA_ATTRIBUTION_TEMPLATE_PATH', NERA_DIR . '/page-templates/nera-marketing-attribution.php');

/**
 * Register virtual public route for the attribution PR page.
 */
function nera_register_attribution_route()
{
  add_rewrite_rule(
    '^' . NERA_ATTRIBUTION_ROUTE_SLUG . '/?$',
    'index.php?nera_attribution=1',
    'top',
  );
}
add_action('init', 'nera_register_attribution_route');

/**
 * Register query var used by attribution virtual route.
 */
function nera_register_attribution_query_var($vars)
{
  $vars[] = 'nera_attribution';
  return $vars;
}
add_filter('query_vars', 'nera_register_attribution_query_var');

/**
 * Flush rewrite rules when theme is switched.
 */
function nera_flush_rewrites_on_theme_switch()
{
  nera_register_attribution_route();
  flush_rewrite_rules();
}
add_action('after_switch_theme', 'nera_flush_rewrites_on_theme_switch');

/**
 * Ensure rewrite rules include attribution route on existing installs.
 */
function nera_maybe_flush_attribution_rewrites()
{
  $flush_flag = 'nera_attribution_route_rewrite_flushed';
  if (get_option($flush_flag)) {
    return;
  }

  nera_register_attribution_route();
  flush_rewrite_rules(false);
  update_option($flush_flag, 1, true);
}
add_action('init', 'nera_maybe_flush_attribution_rewrites', 20);

/**
 * Ensure Spin To Win rewrite exists on first theme load after plugin install.
 */
function nera_maybe_flush_stw_rewrites()
{
  if (!function_exists('nera_stw_get_spin_url')) {
    return;
  }
  $flag = 'nera_stw_rewrite_flushed_v1';
  if (get_option($flag)) {
    return;
  }
  if (class_exists('Nera_STW_Frontend')) {
    Nera_STW_Frontend::register_rewrite();
  }
  flush_rewrite_rules(false);
  update_option($flag, 1, true);
}
add_action('init', 'nera_maybe_flush_stw_rewrites', 21);

/**
 * Load attribution template for virtual route and force non-404 status.
 */
function nera_maybe_load_attribution_template($template)
{
  if ((int) get_query_var('nera_attribution') !== 1) {
    return $template;
  }

  if (!file_exists(NERA_ATTRIBUTION_TEMPLATE_PATH)) {
    return $template;
  }

  global $wp_query;
  $wp_query->is_404 = false;
  status_header(200);

  return NERA_ATTRIBUTION_TEMPLATE_PATH;
}
add_filter('template_include', 'nera_maybe_load_attribution_template', 99);

/**
 * Detect Lottery for WooCommerce entry-list archive context.
 *
 * The plugin sets this query var in pre_get_posts and also forces is_page false,
 * so this must be used instead of is_page() checks.
 *
 * @return bool
 */
function nera_is_entry_list_archive()
{
  return (bool) get_query_var('is_lottery_entry_list_archive', false);
}

/**
 * Replace the default WooCommerce archive output on /giveaway-entry-list/
 * with a custom themed listing template.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function nera_entry_list_template_loader($template)
{
  if (!nera_is_entry_list_archive()) {
    return $template;
  }

  // Let the plugin keep full control for /giveaway-entry-list/{product-slug}/.
  if (get_query_var('lottery_single_entry_list')) {
    return $template;
  }

  $custom = locate_template('page-templates/entry-list-listing-template.php');
  return $custom ?: $template;
}
add_filter('template_include', 'nera_entry_list_template_loader', 50);

/**
 * Set a stable SEO title for the virtual attribution route.
 */
function nera_attribution_document_title($title)
{
  if ((int) get_query_var('nera_attribution') !== 1) {
    return $title;
  }

  return __('Competition Website by Nera Marketing', 'nera-competitions');
}
add_filter('pre_get_document_title', 'nera_attribution_document_title');

/**
 * Check if Vite development server is running
 */
function nera_is_vite_dev_server_running()
{
  // Check if we're in development mode (you can set this via wp-config.php)
  if (!defined('NERA_DEV_MODE') || !NERA_DEV_MODE) {
    return false;
  }

  // Check if Vite dev server is accessible
  $dev_server_url = NERA_VITE_DEV_SERVER_URL;
  $response = @file_get_contents(
    $dev_server_url . '/@vite/client',
    false,
    stream_context_create([
      'http' => ['timeout' => 1],
    ]),
  );

  return $response !== false;
}

/**
 * Get Vite asset URL (production with manifest or development)
 */
function nera_get_vite_asset($entry_point)
{
  $dev_server_url = NERA_VITE_DEV_SERVER_URL;
  $manifest_path = NERA_FRONTEND_DIST_DIR . '/.vite/manifest.json';

  // Development mode with Vite dev server
  if (nera_is_vite_dev_server_running()) {
    return $dev_server_url . '/' . $entry_point;
  }

  // Production mode with manifest
  if (file_exists($manifest_path)) {
    $manifest = json_decode(file_get_contents($manifest_path), true);

    if (isset($manifest[$entry_point])) {
      return NERA_FRONTEND_DIST_URI . '/' . $manifest[$entry_point]['file'];
    }
  }

  return null;
}

/**
 * Get Vite CSS files from manifest
 */
function nera_get_vite_css_files($entry_point)
{
  $manifest_path = NERA_FRONTEND_DIST_DIR . '/.vite/manifest.json';
  $css_files = [];

  if (file_exists($manifest_path)) {
    $manifest = json_decode(file_get_contents($manifest_path), true);

    if (isset($manifest[$entry_point]['css'])) {
      foreach ($manifest[$entry_point]['css'] as $css_file) {
        $css_files[] = NERA_FRONTEND_DIST_URI . '/' . $css_file;
      }
    }
  }

  return $css_files;
}

/**
 * Enqueue theme styles - TailwindCSS
 */
function nera_enqueue_styles()
{
  // Google Fonts - Poppins
  wp_enqueue_style(
    'nera-google-fonts',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
    [],
    null,
  );

  // Google Fonts - Playfair Display & DM Sans (for Winners Modal)
  wp_enqueue_style(
    'nera-google-fonts-modal',
    'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap',
    [],
    null,
  );

  // Material Symbols Icons
  wp_enqueue_style(
    'nera-material-symbols',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
    [],
    null,
  );

  // AOS (Animate On Scroll) CSS
  wp_enqueue_style('aos', 'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css', [], '2.3.4');

  // Swiper CSS (homepage carousels + single product gallery)
  wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');

  // Child theme style.css (for WordPress theme info, minimal styles)
  wp_enqueue_style('nera-style', get_stylesheet_directory_uri() . '/style.css', [], NERA_VERSION);

  // Vite/TailwindCSS assets - PRIMARY STYLING
  if (nera_is_vite_dev_server_running()) {
    // Development mode - load from Vite dev server
    // CSS is injected via JS in dev mode, no separate enqueue needed
  } else {
    // Production mode - load CSS from manifest
    $css_files = nera_get_vite_css_files('src/main.js');
    foreach ($css_files as $index => $css_url) {
      wp_enqueue_style(
        'nera-vite-css-' . $index,
        $css_url,
        ['nera-style'], // Load after base style.css
        NERA_VERSION,
      );
    }
  }
}
add_action('wp_enqueue_scripts', 'nera_enqueue_styles', 15);

/**
 * Disable WordPress global styles inline CSS
 * Prevents WordPress from injecting inline CSS that overrides Tailwind classes
 */
function nera_disable_global_styles()
{
  // Remove global styles stylesheet
  wp_dequeue_style('global-styles');

  // Remove the actions that inject global styles
  remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
  remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
  remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

  // Remove core block library styles (optional - uncomment if needed)
  // wp_dequeue_style('wp-block-library');
  // wp_dequeue_style('wp-block-library-theme');
}
add_action('wp_enqueue_scripts', 'nera_disable_global_styles', 100);

/**
 * Completely disable WordPress theme.json global styles
 * This prevents WordPress from generating any global styles CSS
 */
add_filter('wp_theme_json_get_style_nodes', '__return_empty_array');

/**
 * Enqueue theme scripts
 */
function nera_enqueue_scripts()
{
  // AOS (Animate On Scroll) JS
  wp_enqueue_script('aos', 'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js', [], '2.3.4', true);

  // Swiper JS (homepage carousels + single product gallery; also used by inc/woocommerce.php localize)
  wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true);

  // Alpine Stores and Components (MUST load before AlpineJS initializes)
  // Load in specific order to ensure dependencies are met

  // 1. Toast store (includes postDialog)
  wp_enqueue_script(
    'nera-alpine-toast',
    NERA_ASSETS_URI . '/js/alpine-toast.js',
    [],
    NERA_VERSION,
    true,
  );

  // 2. Countdown component
  wp_enqueue_script(
    'nera-alpine-countdown',
    NERA_ASSETS_URI . '/js/alpine-countdown.js',
    [],
    NERA_VERSION,
    true,
  );

  // 3. Single product gallery: Swiper init + Alpine component (before Alpine core)
  if (is_product()) {
    wp_enqueue_script(
      'nera-product-gallery-init',
      NERA_ASSETS_URI . '/js/single-product-gallery.js',
      ['swiper'],
      NERA_VERSION,
      true,
    );
    wp_enqueue_script(
      'nera-alpine-product-gallery',
      NERA_ASSETS_URI . '/js/alpine-product-gallery.js',
      ['nera-alpine-countdown'],
      NERA_VERSION,
      true,
    );
  }

  // 4. Checkout component (must load before Alpine.js initializes)
  if (is_checkout() && !is_order_received_page()) {
    wp_enqueue_script(
      'nera-checkout',
      NERA_ASSETS_URI . '/js/checkout.js',
      [],
      NERA_VERSION,
      true,
    );
  }

  // 5. Winners page Alpine component (must load before Alpine.js initializes)
  if (is_page_template('page-templates/winners-template.php')) {
    wp_enqueue_script(
      'nera-alpine-winners-page',
      NERA_ASSETS_URI . '/js/alpine-winners-page.js',
      ['nera-alpine-countdown'],
      NERA_VERSION,
      true,
    );
  }

  // 6. AlpineJS Collapse Plugin - loads AFTER stores/components
  $alpine_component_deps = ['nera-alpine-toast', 'nera-alpine-countdown'];
  if (is_page_template('page-templates/winners-template.php')) {
    $alpine_component_deps[] = 'nera-alpine-winners-page';
  }

  wp_enqueue_script(
    'alpinejs-collapse',
    'https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.1/dist/cdn.min.js',
    $alpine_component_deps,
    '3.14.1',
    true,
  );

  // 7. AlpineJS Core - loads LAST, after everything else
  wp_enqueue_script(
    'alpinejs',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js',
    ['alpinejs-collapse'],
    '3.14.1',
    true,
  );

  // Vite/TailwindCSS assets
  if (nera_is_vite_dev_server_running()) {
    // Development mode - load Vite client and main entry
    add_action('wp_head', function () {
      $url = NERA_VITE_DEV_SERVER_URL;
      echo '<script type="module" src="' . esc_url($url . '/@vite/client') . '"></script>';
      echo '<script type="module" src="' . esc_url($url . '/src/main.js') . '"></script>';
    });
  } else {
    // Production mode - load bundled JS from manifest
    $main_js = nera_get_vite_asset('src/main.js');
    if ($main_js) {
      wp_enqueue_script('nera-vite-main', $main_js, [], NERA_VERSION, true);
    }
  }

  // Localize script with theme settings
  wp_localize_script('alpinejs', 'neraSettings', [
    'themeUrl' => NERA_URI,
    'assetsUrl' => NERA_ASSETS_URI,
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nera_nonce'),
  ]);
}
add_action('wp_enqueue_scripts', 'nera_enqueue_scripts');

/**
 * Enqueue WooCommerce cart-fragments for real-time header cart icon updates
 * Required for NeraCart.removeItem and other cart changes to update the header badge
 */
function nera_enqueue_cart_fragments()
{
  if (function_exists('wc_get_cart_url')) {
    wp_enqueue_script('wc-cart-fragments');
  }
}
add_action('wp_enqueue_scripts', 'nera_enqueue_cart_fragments', 15);

/**
 * Add type="module" to Vite scripts
 * Vite builds ES modules that need to be loaded with type="module"
 */
function nera_add_module_type_to_scripts($tag, $handle, $src)
{
  // Only add type="module" to Vite-generated scripts (NOT Alpine or other CDN scripts)
  if (
    strpos($handle, 'nera-vite-') === 0 ||
    strpos($handle, 'nera-instant-wins-vue') === 0 ||
    strpos($handle, 'nera-vue-vendor') === 0
  ) {
    // Make sure we don't add type="module" twice
    if (strpos($tag, 'type="module"') === false) {
      $tag = str_replace('<script ', '<script type="module" ', $tag);
    }
  }
  return $tag;
}
add_filter('script_loader_tag', 'nera_add_module_type_to_scripts', 10, 3);

/**
 * Enqueue Instant Wins Scripts (Vue.js)
 * Only loads on lottery product pages with instant wins enabled
 * Supports Vite dev server and production build
 */
function nera_enqueue_instant_wins_vue()
{
  // Only load on single product pages
  if (!is_product()) {
    return;
  }

  // Always get the product directly, don't rely on global $product
  $product = wc_get_product(get_the_ID());

  if (!$product || !is_a($product, 'WC_Product')) {
    return;
  }

  // Check if product has instant wins
  if (!function_exists('lty_is_lottery_product') || !lty_is_lottery_product($product)) {
    return;
  }

  if (!method_exists($product, 'is_instant_winner') || !$product->is_instant_winner()) {
    return;
  }

  if (nera_is_vite_dev_server_running()) {
    add_action('wp_footer', function () {
      $url = NERA_VITE_DEV_SERVER_URL;
      echo '<script type="module" src="' . esc_url($url . '/instant-wins-vue-init.js') . '"></script>';
    }, 5);
    return;
  }

  // Production mode - load from manifest
  $manifest_path = NERA_FRONTEND_DIST_DIR . '/.vite/manifest.json';

  if (file_exists($manifest_path)) {
    $manifest = json_decode(file_get_contents($manifest_path), true);

    // Vue entry point
    $instant_wins_entry = 'instant-wins-vue-init.js';
    $vendor_chunk_name = 'vue-vendor';
    $handle_prefix = 'vue';

    if (isset($manifest[$instant_wins_entry])) {
      $instant_wins_file = $manifest[$instant_wins_entry]['file'];
      $deps = [];

      // Check for vendor chunk imports
      if (isset($manifest[$instant_wins_entry]['imports'])) {
        foreach ($manifest[$instant_wins_entry]['imports'] as $import_key) {
          // Check if this is the vendor chunk (starts with underscore)
          if (
            isset($manifest[$import_key]) &&
            strpos($import_key, '_' . $vendor_chunk_name) !== false
          ) {
            // Enqueue vendor chunk first with automatic versioning
            $vendor_file_path = NERA_FRONTEND_DIST_DIR . '/' . $manifest[$import_key]['file'];
            $vendor_handle = 'nera-' . $handle_prefix . '-vendor';

            // Register and enqueue as module script
            wp_enqueue_script(
              $vendor_handle,
              NERA_FRONTEND_DIST_URI . '/' . $manifest[$import_key]['file'],
              [],
              file_exists($vendor_file_path) ? filemtime($vendor_file_path) : NERA_VERSION,
              true, // in footer
            );

            $deps[] = $vendor_handle;
          }
        }
      }

      // Enqueue instant wins app with automatic versioning
      $instant_wins_file_path = NERA_FRONTEND_DIST_DIR . '/' . $instant_wins_file;
      $instant_wins_handle = 'nera-instant-wins-' . $handle_prefix;

      // Register and enqueue as module script
      wp_enqueue_script(
        $instant_wins_handle,
        NERA_FRONTEND_DIST_URI . '/' . $instant_wins_file,
        $deps,
        file_exists($instant_wins_file_path) ? filemtime($instant_wins_file_path) : NERA_VERSION,
        true, // in footer
      );
    }
  }
}
add_action('wp_enqueue_scripts', 'nera_enqueue_instant_wins_vue');

/**
 * Add type="module" to Vue vendor/app scripts
 */
add_filter(
  'script_loader_tag',
  function ($tag, $handle, $src) {
    // List of handles that need type="module"
    $module_handles = ['nera-vue-vendor', 'nera-instant-wins-vue'];

    if (in_array($handle, $module_handles)) {
      // Replace the script tag to add type="module"
      $tag = str_replace('<script ', '<script type="module" ', $tag);
    }

    return $tag;
  },
  10,
  3,
);

/**
 * Include required files
 */
// Menu Walker Classes
require_once NERA_DIR . '/inc/menu-walkers.php';

// Custom Competition Shortcodes
require_once get_template_directory() . '/inc/competition-shortcodes.php';

// ACF Homepage Fields
require_once get_template_directory() . '/inc/acf-homepage.php';

// ACF Single Product Competition Fields
require_once get_template_directory() . '/inc/acf-single-product.php';

// ACF Contact Page Fields
require_once get_template_directory() . '/inc/acf-contact.php';

// ACF About Us Page Fields
require_once get_template_directory() . '/inc/acf-about-us.php';

// How It Works hero defaults (shared by template + ACF merge)
require_once get_template_directory() . '/inc/how-it-works-defaults.php';

// ACF How It Works Page Fields
require_once get_template_directory() . '/inc/acf-how-it-works.php';

// ACF Product Listing Fields
require_once get_template_directory() . '/inc/acf-product-listing.php';

// ACF Postal Entry Fields
require_once get_template_directory() . '/inc/acf-postal-entry.php';

// ACF WooCommerce Settings
require_once get_template_directory() . '/inc/acf-woocommerce.php';

// Legal Placeholders for T&C and Privacy Policy
require_once get_template_directory() . '/inc/legal-placeholders.php';

// ACF Winners Page Fields
require_once get_template_directory() . '/inc/acf-winners.php';

// ACF Attribution Page Fields
require_once get_template_directory() . '/inc/acf-attribution.php';

// Winners dataset helpers for server rendering
require_once get_template_directory() . '/inc/winners-dataset.php';

/**
 * Enqueue scripts and styles.
 */
if (class_exists('WooCommerce')) {
  require_once NERA_DIR . '/inc/woocommerce.php';
}

// REST API for instant wins lazy loading
if (class_exists('WooCommerce')) {
  require_once NERA_DIR . '/inc/api/instant-wins-api.php';
}

// Giveaway plugin customizations
if (class_exists('WooCommerce_Lottery')) {
  require_once NERA_DIR . '/inc/giveaway-custom.php';
}

// Lottery for WooCommerce — thank-you page result overlays (instant win / prize draw)
if (function_exists('LTY') && class_exists('WooCommerce')) {
  require_once NERA_DIR . '/inc/lty-result-screens-loader.php';
}

// One-time: manually set one instant win prize as "won" for demo (admin only: ?nera_set_demo_instant_winner=1)
if (class_exists('WooCommerce')) {
  require_once NERA_DIR . '/inc/demo-instant-winner.php';
}

/**
 * Add theme support
 */
function nera_theme_support()
{
  // Add support for custom logo
  add_theme_support('custom-logo', [
    'height' => 100,
    'width' => 400,
    'flex-width' => true,
    'flex-height' => true,
  ]);

  // Add support for post thumbnails
  add_theme_support('post-thumbnails');

  // Add support for title tag
  add_theme_support('title-tag');

  // Add support for HTML5
  add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);

  // WooCommerce support
  add_theme_support('woocommerce');
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');

  // Register navigation menus
  register_nav_menus([
    'primary-menu' => __('Primary Menu', 'nera-competitions'),
  ]);
}
add_action('after_setup_theme', 'nera_theme_support');

/**
 * Register widget areas
 */
function nera_widgets_init()
{
  register_sidebar([
    'name' => __('Competition Sidebar', 'nera-competitions'),
    'id' => 'competition-sidebar',
    'description' => __(
      'Widgets in this area will be shown on competition pages.',
      'nera-competitions',
    ),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>',
  ]);

  // Footer Column 1 - Brand & Socials
  register_sidebar([
    'name' => __('Footer Column 1', 'nera-competitions'),
    'id' => 'footer-1',
    'description' => __(
      'First column of the footer. Typically for brand info and social links.',
      'nera-competitions',
    ),
    'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
    'after_widget' => '</div>',
    'before_title' =>
      '<h4 class="font-semibold text-text-primary mb-4 text-sm uppercase tracking-wide">',
    'after_title' => '</h4>',
  ]);

  // Footer Column 2 - Links
  register_sidebar([
    'name' => __('Footer Column 2', 'nera-competitions'),
    'id' => 'footer-2',
    'description' => __('Second column of the footer.', 'nera-competitions'),
    'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
    'after_widget' => '</div>',
    'before_title' =>
      '<h4 class="font-semibold text-text-primary mb-4 text-sm uppercase tracking-wide">',
    'after_title' => '</h4>',
  ]);

  // Footer Column 3 - Links
  register_sidebar([
    'name' => __('Footer Column 3', 'nera-competitions'),
    'id' => 'footer-3',
    'description' => __('Third column of the footer.', 'nera-competitions'),
    'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
    'after_widget' => '</div>',
    'before_title' =>
      '<h4 class="font-semibold text-text-primary mb-4 text-sm uppercase tracking-wide">',
    'after_title' => '</h4>',
  ]);

  // Footer Column 4 - Links
  register_sidebar([
    'name' => __('Footer Column 4', 'nera-competitions'),
    'id' => 'footer-4',
    'description' => __('Fourth column of the footer.', 'nera-competitions'),
    'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
    'after_widget' => '</div>',
    'before_title' =>
      '<h4 class="font-semibold text-text-primary mb-4 text-sm uppercase tracking-wide">',
    'after_title' => '</h4>',
  ]);
}
add_action('widgets_init', 'nera_widgets_init');

/**
 * Add body class for homepage template
 */
function nera_body_classes($classes)
{
  if (is_page_template('page-templates/homepage-template.php') || is_front_page()) {
    $classes[] = 'nera-homepage-template';
  }
  return $classes;
}
add_filter('body_class', 'nera_body_classes');

/**
 * Add body class for product listing template
 */
function nera_product_listing_body_classes($classes)
{
  if (is_page_template('page-templates/product-listing-template.php')) {
    $classes[] = 'nera-product-listing-template';
  }
  return $classes;
}
add_filter('body_class', 'nera_product_listing_body_classes');

/**
 * Add body class for How It Works template
 */
function nera_how_it_works_body_classes($classes)
{
  if (is_page_template('page-templates/how-it-works-template.php')) {
    $classes[] = 'nera-how-it-works-template';
  }
  return $classes;
}
add_filter('body_class', 'nera_how_it_works_body_classes');

/**
 * Category slug → hex colors for advanced filter chips and competition cards.
 *
 * @return array<string, string>
 */
function nera_advanced_filter_category_colors()
{
  return apply_filters(
    'nera_advanced_filter_category_colors',
    [
      'cars' => '#3B82F6',
      'cash' => '#10B981',
      'luxury' => '#8B5CF6',
      'electronics' => '#F59E0B',
      'travel' => '#EC4899',
      'tech' => '#06B6D4',
      'gadgets' => '#F97316',
      'watches' => '#6366F1',
      'lifestyle' => '#14B8A6',
    ],
  );
}

/**
 * Allowed product_cat slugs for advanced competitions filter (matches categories-filter.php).
 *
 * @return string[]
 */
function nera_advanced_filter_allowed_product_cat_slugs()
{
  $categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'exclude' => get_option('default_product_cat'),
  ]);
  if (empty($categories) || is_wp_error($categories)) {
    return [];
  }
  return array_map(
    static function ($t) {
      return $t->slug;
    },
    $categories,
  );
}

/**
 * Whitelist comma-separated category slugs for advanced filter.
 *
 * @param string $raw Comma-separated segments (GET/POST value).
 * @return string[]
 */
function nera_advanced_filter_whitelist_category_slugs($raw)
{
  $allowed = nera_advanced_filter_allowed_product_cat_slugs();
  $out = [];
  $segments = array_filter(
    array_map('trim', explode(',', (string) $raw)),
  );
  foreach ($segments as $seg) {
    $slug = sanitize_title($seg);
    if (
      $slug !== ''
      && in_array($slug, $allowed, true)
      && !in_array($slug, $out, true)
    ) {
      $out[] = $slug;
    }
  }
  return $out;
}

/**
 * Posts per page for advanced filter grid (pagination + Load More).
 *
 * @return int
 */
function nera_advanced_filter_get_posts_per_page()
{
  return (int) apply_filters('nera_advanced_filter_posts_per_page', 9);
}

/**
 * WP_Query args for advanced filter competitions grid (matches categories-filter.php).
 *
 * @param string[] $url_category_slugs Validated slugs (empty = no product_cat tax filter).
 * @param int      $paged             Page number (1-based).
 * @return array<string, mixed>
 */
function nera_advanced_filter_competitions_wp_query_args(array $url_category_slugs, $paged = 1)
{
  $filter_posts_per_page = nera_advanced_filter_get_posts_per_page();
  $paged = max(1, (int) $paged);
  if (!empty($url_category_slugs)) {
    $filter_tax_query = [
      'relation' => 'AND',
      [
        'taxonomy' => 'product_type',
        'field' => 'slug',
        'terms' => 'lottery',
      ],
      [
        'taxonomy' => 'product_cat',
        'field' => 'slug',
        'terms' => $url_category_slugs,
        'operator' => 'IN',
      ],
    ];
  } else {
    $filter_tax_query = [
      [
        'taxonomy' => 'product_type',
        'field' => 'slug',
        'terms' => 'lottery',
      ],
    ];
  }

  return [
    'post_type' => 'product',
    'posts_per_page' => $filter_posts_per_page,
    'paged' => $paged,
    'post_status' => 'publish',
    'tax_query' => $filter_tax_query,
    'meta_key' => '_lty_end_date_gmt',
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_query' => function_exists('nera_active_lottery_meta_query') ? nera_active_lottery_meta_query() : [],
  ];
}

/**
 * Competition cards HTML only (for Load More append).
 *
 * @param WP_Query $competitions      Query positioned at posts to render.
 * @param int      $card_index_offset Unused; reserved for AOS parity.
 */
function nera_advanced_filter_render_prize_cards_html(WP_Query $competitions, $card_index_offset = 0)
{
  ob_start();
  if (!$competitions->have_posts()) {
    return ob_get_clean();
  }
  $colors = nera_advanced_filter_category_colors();
  while ($competitions->have_posts()) {
    $competitions->the_post();
    $card_args = [
      'product' => wc_get_product(get_the_ID()),
      'x_show' => 'categoryMatch($el.dataset.categories) && priceMatch($el.dataset.price)',
      'category_colors' => $colors,
    ];
    get_template_part('template-parts/components/competition-card', null, $card_args);
  }

  return ob_get_clean();
}

/**
 * Inner HTML for #advanced-filter-grid: cards plus empty / no-match blocks.
 *
 * @param WP_Query $competitions Query after running advanced filter args.
 */
function nera_advanced_filter_render_grid_html(WP_Query $competitions)
{
  ob_start();
  if ($competitions->have_posts()) {
    echo nera_advanced_filter_render_prize_cards_html($competitions, 0);
    ?>
    <div id="advanced-filter-grid-append-sentinel" class="hidden" aria-hidden="true"></div>
    <div class="col-span-full text-center py-16"
      x-show="(selectedCategories.length > 0 || priceRange !== '') && !hasMatchingCards()">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-5">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
          class="text-gray-400">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <h3 class="text-xl font-bold text-text-primary mb-2"><?php esc_html_e('No competitions match your filters', 'nera-competitions'); ?></h3>
      <p class="text-text-secondary mb-4"><?php esc_html_e('Try adjusting your filters to see more results.', 'nera-competitions'); ?></p>
      <button type="button" @click="clearFilters()"
        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-primary hover:opacity-90 bg-primary/10 rounded-lg border border-primary/20 transition-all duration-200">
        <?php esc_html_e('Clear All Filters', 'nera-competitions'); ?>
      </button>
    </div>
    <?php
  } else {
    ?>
    <div class="col-span-full text-center py-20">
      <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
          class="text-gray-400">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
          <circle cx="8.5" cy="8.5" r="1.5" />
          <polyline points="21 15 16 10 5 21" />
        </svg>
      </div>
      <h3 class="text-2xl font-bold text-text-primary mb-2"><?php esc_html_e('No competitions found', 'nera-competitions'); ?></h3>
      <p class="text-text-secondary"><?php esc_html_e('Check back soon for new amazing prizes!', 'nera-competitions'); ?></p>
    </div>
    <?php
  }

  return ob_get_clean();
}

/**
 * AJAX: return advanced filter competitions grid HTML (full replace or append cards).
 */
function nera_ajax_advanced_filter_competitions()
{
  check_ajax_referer('nera_nonce', 'nonce');

  $raw = isset($_POST['product_cat']) ? wp_unslash($_POST['product_cat']) : '';
  $url_category_slugs = nera_advanced_filter_whitelist_category_slugs($raw);
  $paged = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
  $append = !empty($_POST['append']) && (string) $_POST['append'] === '1';

  $args = nera_advanced_filter_competitions_wp_query_args($url_category_slugs, $paged);
  $competitions = new WP_Query($args);
  $found_posts = (int) $competitions->found_posts;
  $max_num_pages = (int) $competitions->max_num_pages;

  if ($append && $paged >= 2) {
    $per_page = nera_advanced_filter_get_posts_per_page();
    $offset = ($paged - 1) * $per_page;
    $html = nera_advanced_filter_render_prize_cards_html($competitions, $offset);
    $has_more = $paged < $max_num_pages;
    wp_reset_postdata();

    wp_send_json_success([
      'html' => $html,
      'found_posts' => $found_posts,
      'max_num_pages' => $max_num_pages,
      'paged' => $paged,
      'has_more' => $has_more,
    ]);
    return;
  }

  $html = nera_advanced_filter_render_grid_html($competitions);
  wp_reset_postdata();

  wp_send_json_success([
    'html' => $html,
    'found_posts' => $found_posts,
    'max_num_pages' => $max_num_pages,
    'paged' => 1,
    'has_more' => $max_num_pages > 1,
  ]);
}
add_action('wp_ajax_nera_advanced_filter_competitions', 'nera_ajax_advanced_filter_competitions');
add_action('wp_ajax_nopriv_nera_advanced_filter_competitions', 'nera_ajax_advanced_filter_competitions');

/**
 * AJAX: append next page of cards for the Closed Prizes page.
 */
function nera_ajax_closed_prizes_load_more()
{
  check_ajax_referer('nera_nonce', 'nonce');

  $paged = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
  $args  = function_exists('nera_closed_prizes_wp_query_args')
    ? nera_closed_prizes_wp_query_args($paged)
    : [];

  $query = new WP_Query($args);

  ob_start();
  while ($query->have_posts()) {
    $query->the_post();
    get_template_part('template-parts/closed-prizes/closed-prize-card', null, [
      'product' => wc_get_product(get_the_ID()),
    ]);
  }
  $html = ob_get_clean();
  $has_more = $paged < (int) $query->max_num_pages;
  wp_reset_postdata();

  wp_send_json_success([
    'html'     => $html,
    'has_more' => $has_more,
  ]);
}
add_action('wp_ajax_nera_closed_prizes_load_more',        'nera_ajax_closed_prizes_load_more');
add_action('wp_ajax_nopriv_nera_closed_prizes_load_more', 'nera_ajax_closed_prizes_load_more');

/**
 * AJAX: append next page of cards for the Entry List archive.
 */
function nera_ajax_entry_list_load_more()
{
  check_ajax_referer('nera_nonce', 'nonce');

  $paged = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;
  $args  = function_exists('nera_entry_list_wp_query_args')
    ? nera_entry_list_wp_query_args($paged)
    : [];
  $query = new WP_Query($args);

  ob_start();
  while ($query->have_posts()) {
    $query->the_post();
    get_template_part('template-parts/entry-list/entry-list-card', null, [
      'product' => wc_get_product(get_the_ID()),
    ]);
  }
  $html = ob_get_clean();
  $has_more = $paged < (int) $query->max_num_pages;
  wp_reset_postdata();

  wp_send_json_success([
    'html'     => $html,
    'has_more' => $has_more,
  ]);
}
add_action('wp_ajax_nera_entry_list_load_more',        'nera_ajax_entry_list_load_more');
add_action('wp_ajax_nopriv_nera_entry_list_load_more', 'nera_ajax_entry_list_load_more');


/**
 * AJAX handler for filtering products
 */
function nera_ajax_filter_products()
{
  check_ajax_referer('nera_nonce', 'nonce');

  $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
  $price = isset($_POST['price']) ? sanitize_text_field($_POST['price']) : '';
  $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
  $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'ending-soon';
  $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
  $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 12;

  // Build query args
  $args = [
    'post_type' => 'product',
    'posts_per_page' => $per_page,
    'paged' => $page,
    'post_status' => 'publish',
    'tax_query' => [
      [
        'taxonomy' => 'product_type',
        'field' => 'slug',
        'terms' => 'lottery',
      ],
    ],
  ];

  // Only show competitions that have started AND have not yet ended.
  // Both groups are nested so price meta_query conditions appended below are AND'd correctly.
  $now_gmt = current_time('mysql', true);
  $args['meta_query'] = [
    'relation' => 'AND',
    // Group A: start date has been reached (or not set)
    [
      'relation' => 'OR',
      [ 'key' => '_lty_start_date_gmt', 'compare' => 'NOT EXISTS' ],
      [ 'key' => '_lty_start_date_gmt', 'value' => '', 'compare' => '=' ],
      [ 'key' => '_lty_start_date_gmt', 'value' => $now_gmt, 'type' => 'DATETIME', 'compare' => '<=' ],
    ],
    // Group B: end date has not yet passed (or not set)
    [
      'relation' => 'OR',
      [ 'key' => '_lty_end_date_gmt', 'compare' => 'NOT EXISTS' ],
      [ 'key' => '_lty_end_date_gmt', 'value' => $now_gmt, 'type' => 'DATETIME', 'compare' => '>=' ],
    ],
  ];

  // Category filter
  if (!empty($category)) {
    $args['tax_query'][] = [
      'taxonomy' => 'product_cat',
      'field' => 'slug',
      'terms' => $category,
    ];
    $args['tax_query']['relation'] = 'AND';
  }

  // Price filter
  if (!empty($price)) {
    $price_range = explode('-', $price);
    if (count($price_range) === 2) {
      $min_price = floatval($price_range[0]);
      $max_price = floatval($price_range[1]);
      $args['meta_query'][] = [
        'key' => '_price',
        'value' => [$min_price, $max_price],
        'type' => 'NUMERIC',
        'compare' => 'BETWEEN',
      ];
    } elseif (strpos($price, '+') !== false) {
      $min_price = floatval(str_replace('+', '', $price));
      $args['meta_query'][] = [
        'key' => '_price',
        'value' => $min_price,
        'type' => 'NUMERIC',
        'compare' => '>=',
      ];
    }
  }

  // Status filter
  if (!empty($status)) {
    $now = current_time('mysql', true);

    switch ($status) {
      case 'ending-soon':
        // Ending within 24 hours
        $args['meta_query'][] = [
          'key' => '_lty_end_date_gmt',
          'value' => [$now, date('Y-m-d H:i:s', strtotime('+24 hours'))],
          'type' => 'DATETIME',
          'compare' => 'BETWEEN',
        ];
        break;

      case 'last-tickets':
        // We'll filter these in PHP after query since it requires calculation
        break;

      case 'new':
        // Created within last 7 days
        $args['date_query'] = [
          [
            'after' => '7 days ago',
            'inclusive' => true,
          ],
        ];
        break;
    }
  }

  // Sorting
  switch ($sort) {
    case 'ending-soon':
      $args['meta_key'] = '_lty_end_date_gmt';
      $args['orderby'] = 'meta_value';
      $args['order'] = 'ASC';
      break;

    case 'newest':
      $args['orderby'] = 'date';
      $args['order'] = 'DESC';
      break;

    case 'price-low':
      $args['meta_key'] = '_price';
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'ASC';
      break;

    case 'price-high':
      $args['meta_key'] = '_price';
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'DESC';
      break;

    case 'popularity':
      $args['meta_key'] = 'total_sales';
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'DESC';
      break;
  }

  $query = new WP_Query($args);

  // Filter for last-tickets status (requires post-query filtering)
  $filtered_posts = [];
  if ($status === 'last-tickets' && $query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      global $product;

      $max_tickets = get_post_meta(get_the_ID(), '_lty_maximum_tickets', true);
      $sold_tickets = method_exists($product, 'get_purchased_ticket_count')
        ? $product->get_purchased_ticket_count()
        : 0;
      $remaining = $max_tickets ? max(0, $max_tickets - $sold_tickets) : 0;

      // Only include if 50 or fewer tickets remaining
      if ($remaining > 0 && $remaining <= 50) {
        $filtered_posts[] = get_the_ID();
      }
    }
    wp_reset_postdata();

    // Re-query with filtered IDs
    if (!empty($filtered_posts)) {
      $args['post__in'] = $filtered_posts;
      unset($args['meta_query']);
      $query = new WP_Query($args);
    } else {
      // No products match
      $query = new WP_Query(['post__in' => [0]]);
    }
  }

  // Build HTML output
  ob_start();

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      global $product;

      get_template_part('template-parts/product-listing/product-card', null, [
        'product' => $product,
      ]);
    }
  } else {
     ?>
        <div class="col-span-full text-center py-16">
            <div class="max-w-md mx-auto">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <h3 class="text-xl font-bold text-text-primary mb-2">
                    <?php _e('No competitions found', 'nera-competitions'); ?>
                </h3>
                <p class="text-text-secondary">
                    <?php _e(
                      'Try adjusting your filters or check back soon!',
                      'nera-competitions',
                    ); ?>
                </p>
            </div>
        </div>
        <?php
  }

  $html = ob_get_clean();
  wp_reset_postdata();

  // Calculate pagination info
  $total = $query->found_posts;
  $total_pages = $query->max_num_pages;
  $showing = min($page * $per_page, $total);
  $has_more = $page < $total_pages;

  wp_send_json_success([
    'html' => $html,
    'total' => $total,
    'showing' => $showing,
    'page' => $page,
    'total_pages' => $total_pages,
    'has_more' => $has_more,
    'next_page' => $has_more ? $page + 1 : null,
  ]);
}
add_action('wp_ajax_nera_filter_products', 'nera_ajax_filter_products');
add_action('wp_ajax_nopriv_nera_filter_products', 'nera_ajax_filter_products');

/**
 * Add header cart count fragments for AJAX cart updates
 */
function nera_add_header_cart_count_fragments($fragments)
{
  $cart_count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

  ob_start();
  ?>
    <span class="nera-header-cart-count-desktop-wrapper absolute -top-1 -right-1">
        <?php if ($cart_count > 0): ?>
            <span class="bg-primary text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center leading-none">
                <?php echo esc_html($cart_count); ?>
            </span>
        <?php endif; ?>
    </span>
    <?php
    $fragments['span.nera-header-cart-count-desktop-wrapper'] = ob_get_clean();

    ob_start();
    ?>
    <span class="nera-header-cart-count-mobile-wrapper">
        <?php if ($cart_count > 0): ?>
            <span class="bg-primary text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center leading-none">
                <?php echo esc_html($cart_count); ?>
            </span>
        <?php endif; ?>
    </span>
    <?php
    $fragments['span.nera-header-cart-count-mobile-wrapper'] = ob_get_clean();

    ob_start();
    ?>
    <span class="nera-header-cart-count-mobile-nav-wrapper absolute -top-1 -right-1">
        <?php if ($cart_count > 0): ?>
            <span class="bg-primary text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center leading-none">
                <?php echo esc_html($cart_count); ?>
            </span>
        <?php endif; ?>
    </span>
    <?php
    $fragments['span.nera-header-cart-count-mobile-nav-wrapper'] = ob_get_clean();

    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'nera_add_header_cart_count_fragments');

/**
 * Return the first WooCommerce error notice as a plain string and clear only
 * error notices (not success/info notices that may belong to other operations).
 */
function nera_pop_first_wc_error_notice(): string
{
  $error_notices = wc_get_notices('error');
  if (empty($error_notices)) {
    return '';
  }

  // Clear only error notices; preserve success/notice/warning types.
  $all_notices = wc_get_notices();
  unset($all_notices['error']);
  WC()->session->set('wc_notices', $all_notices ?: null);

  $first = reset($error_notices);
  return wp_strip_all_tags(is_array($first) ? ($first['notice'] ?? '') : $first);
}

/**
 * AJAX add to cart handler for WooCommerce
 * Ensures proper AJAX response for add to cart requests
 */
function nera_ajax_add_to_cart()
{
  $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
  $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

  if (!$product_id) {
    wp_send_json(['error' => true, 'message' => __('Invalid product.', 'nera-competitions')]);
  }

  $product = wc_get_product($product_id);

  if (!$product) {
    wp_send_json(['error' => true, 'message' => __('Product not found.', 'nera-competitions')]);
  }

  // Add to cart
  $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);

  if ($cart_item_key) {
    // Run cart-level validation immediately (e.g. lottery plugin ticket-limit checks).
    // Without this, the lottery plugin's woocommerce_check_cart_items hook would silently
    // remove the item on the cart page with no user-visible feedback.
    do_action('woocommerce_check_cart_items');

    if (!WC()->cart->get_cart_item($cart_item_key)) {
      // Validation removed the item — surface the plugin's error notice as a toast.
      if (WC()->session) {
        WC()->session->save_data();
      }
      $message = nera_pop_first_wc_error_notice() ?: __('Could not add to cart.', 'nera-competitions');
      wp_send_json(['error' => true, 'message' => $message]);
    }

    // Ensure session cookie is initialized for guest users.
    // Without this, server-level caches (e.g. SiteGround Dynamic Cache) won't see
    // the woocommerce_session cookie and may serve a stale empty cart page.
    if (!is_user_logged_in() && WC()->session && !WC()->session->has_session()) {
      WC()->session->set_customer_session_cookie(true);
    }

    // Fire the cart cookies action so woocommerce_items_in_cart cookie is set.
    // SiteGround Dynamic Cache (and similar Nginx caches) bypass caching when this
    // cookie is present, ensuring the cart page is served fresh rather than from cache.
    do_action('woocommerce_set_cart_cookies', true);

    // Flush session to DB before sending the JSON response so the session data
    // is available when the browser navigates to the cart page.
    if (WC()->session) {
      WC()->session->save_data();
    }

    // Get cart fragments for updating mini cart
    ob_start();
    woocommerce_mini_cart();
    $mini_cart = ob_get_clean();

    $fragments = [
      'div.widget_shopping_cart_content' =>
        '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
    ];

    // Apply WooCommerce fragments filter
    $fragments = apply_filters('woocommerce_add_to_cart_fragments', $fragments);

    wp_send_json([
      'error' => false,
      'message' => get_field('add_to_cart_success_message', 'option') ?: __('Tickets added to cart.', 'nera-competitions'),
      'cart_hash' => WC()->cart->get_cart_hash(),
      'cart_quantity' => WC()->cart->get_cart_contents_count(),
      'fragments' => $fragments,
    ]);
  } else {
    // add_to_cart() failed — the lottery plugin (or WooCommerce) added a specific notice;
    // surface it so the user sees the real reason instead of a generic message.
    $message = nera_pop_first_wc_error_notice() ?: __('Could not add to cart.', 'nera-competitions');
    wp_send_json(['error' => true, 'message' => $message]);
  }
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'nera_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'nera_ajax_add_to_cart');

/**
 * Send no-cache headers for the cart page
 *
 * WooCommerce already sends Cache-Control no-cache headers, but server-level caches
 * like SiteGround Dynamic Cache can bypass them. This adds a secondary layer including
 * the SiteGround-specific X-SG-No-Cache header and the WooCommerce cart/checkout/account
 * page exclusion header, ensuring the cart page is never served stale from cache.
 */
function nera_cart_no_cache_headers()
{
  if (function_exists('is_cart') && is_cart()) {
    nocache_headers();
    header('X-SG-No-Cache: 1');
  }
}
add_action('send_headers', 'nera_cart_no_cache_headers');

/**
 * Mask a username for public display in entry/ticket lists.
 * Shows roughly half the characters then 2–3 asterisks.
 * e.g. Adm1n → Adm**   Lewis → Lew**   NeraAccount → NeraAc***
 */
function nera_mask_username(string $username): string {
  $len = mb_strlen($username);
  if ($len <= 2) {
    return str_repeat('*', $len);
  }
  $visible   = (int) floor($len / 2) + 1;
  $asterisks = min($len - $visible, 3);
  return mb_substr($username, 0, $visible) . str_repeat('*', $asterisks);
}

/**
 * Populate Winners Page with Sample Data
 *
 * Temporary function to add sample winners data for testing.
 * Can be removed after populating real winner data.
 */
function nera_populate_sample_winners_data()
{
  // Find the Winners page
  $winners_page = get_posts([
    'post_type' => 'page',
    'meta_key' => '_wp_page_template',
    'meta_value' => 'page-templates/winners-template.php',
    'posts_per_page' => 1,
  ]);

  if (empty($winners_page)) {
    return [
      'success' => false,
      'message' => 'Winners page not found. Please create a page using the Winners template first.',
    ];
  }

  $page_id = $winners_page[0]->ID;

  // Sample winners data from screenshot
  $sample_winners = [
    [
      'name' => 'Donald Yeoman',
      'prize' => 'Target Omni Ultimate Home Dart Bundle',
      'date' => '2026-01-05',
      'quote' => "I can't believe I won! This is absolutely amazing, thank you so much!",
      'category' => 'live-draw',
    ],
    [
      'name' => 'Stephen Lamb',
      'prize' => '75" LG QNED EVO AI MiniLED 4K Smart TV',
      'date' => '2026-01-01',
      'quote' =>
        "What an incredible prize! I've always wanted a TV like this. Dreams do come true!",
      'category' => 'live-draw',
    ],
    [
      'name' => 'Stephanie Scott',
      'prize' => 'Free Entry - Bambu Lab P1S Combo',
      'date' => '2026-02-02',
      'quote' => 'Free entry and I actually won! This has made my entire year!',
      'category' => 'instant-win',
    ],
    [
      'name' => 'Marvin Chaplin',
      'prize' => 'Pokémon TCG Prismatic Evolutions Bundle',
      'date' => '2026-01-29',
      'quote' => 'My kids are going to be so excited! Best competition ever!',
      'category' => 'live-draw',
    ],
  ];

  // Update ACF fields
  if (function_exists('update_field')) {
    // Set hero section if empty
    if (!get_field('winners_heading', $page_id)) {
      update_field('winners_heading', 'Recent Winners', $page_id);
    }
    if (!get_field('winners_subheading', $page_id)) {
      update_field('winners_subheading', 'Our Lucky Winners', $page_id);
    }
    if (!get_field('winners_description', $page_id)) {
      update_field(
        'winners_description',
        'Congratulations to all our amazing winners! See who\'s been lucky recently and get inspired for your next entry.',
        $page_id,
      );
    }

    // Set display settings
    update_field('winners_per_page', 12, $page_id);
    update_field('winners_show_filters', 1, $page_id);
    update_field('winners_show_quotes', 1, $page_id);

    // Populate winners list
    update_field('winners_list', $sample_winners, $page_id);

    return [
      'success' => true,
      'message' => 'Sample winners data added successfully! You can now view the Winners page.',
      'page_id' => $page_id,
    ];
  } else {
    return [
      'success' => false,
      'message' =>
        'ACF Pro plugin is not active. Please ensure ACF Pro is installed and activated.',
    ];
  }
}

/**
 * Handle sample data population action
 */
function nera_handle_populate_sample_winners()
{
  // Check nonce and capabilities
  if (!isset($_GET['nera_populate_winners']) || !isset($_GET['_wpnonce'])) {
    return;
  }

  if (!wp_verify_nonce($_GET['_wpnonce'], 'nera_populate_winners_sample')) {
    wp_die('Security check failed');
  }

  if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to perform this action');
  }

  $result = nera_populate_sample_winners_data();

  // Redirect with result message
  $redirect_url = admin_url('admin.php?page=nera-winners-sample');

  if ($result['success']) {
    $redirect_url = add_query_arg(
      [
        'nera_sample_success' => '1',
        'page_id' => $result['page_id'],
      ],
      admin_url('edit.php?post_type=page'),
    );
  } else {
    $redirect_url = add_query_arg(
      'nera_sample_error',
      urlencode($result['message']),
      admin_url('index.php'),
    );
  }

  wp_redirect($redirect_url);
  exit();
}
add_action('admin_init', 'nera_handle_populate_sample_winners');

/**
 * Display admin notice with button to populate sample winners
 */
function nera_sample_winners_admin_notice()
{
  // Only show to administrators
  if (!current_user_can('manage_options')) {
    return;
  }

  // Check if Winners page exists
  $winners_page = get_posts([
    'post_type' => 'page',
    'meta_key' => '_wp_page_template',
    'meta_value' => 'page-templates/winners-template.php',
    'posts_per_page' => 1,
  ]);

  if (empty($winners_page)) {
    return;
  }

  $page_id = $winners_page[0]->ID;
  $existing_winners = get_field('winners_list', $page_id);

  // Show success message
  if (isset($_GET['nera_sample_success'])) {

    $page_id = isset($_GET['page_id']) ? absint($_GET['page_id']) : 0;
    $view_url = $page_id ? get_permalink($page_id) : '';
    ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong>Success!</strong> Sample winners data has been added to your Winners page.
                <?php if ($view_url): ?>
                    <a href="<?php echo esc_url(
                      $view_url,
                    ); ?>" target="_blank" class="button button-small" style="margin-left: 10px;">View Winners Page</a>
                <?php endif; ?>
            </p>
        </div>
        <?php return;
  }

  // Show error message
  if (isset($_GET['nera_sample_error'])) { ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Error:</strong> <?php echo esc_html(
              urldecode($_GET['nera_sample_error']),
            ); ?></p>
        </div>
        <?php return;}

  // Don't show populate button if winners already exist
  if (!empty($existing_winners) && is_array($existing_winners) && count($existing_winners) > 0) {
    return;
  }

  // Show populate button
  $populate_url = wp_nonce_url(
    add_query_arg('nera_populate_winners', '1', admin_url('admin.php')),
    'nera_populate_winners_sample',
  );
  ?>
    <div class="notice notice-info">
        <p>
            <strong>Winners Page Sample Data</strong><br>
            Your Winners page is empty. Would you like to add sample winner data for testing?
        </p>
        <p>
            <a href="<?php echo esc_url(
              $populate_url,
            ); ?>" class="button button-primary">Add Sample Winners Data</a>
            <a href="<?php echo esc_url(
              get_edit_post_link($page_id),
            ); ?>" class="button" style="margin-left: 10px;">Edit Winners Page</a>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'nera_sample_winners_admin_notice');

// ACE Footer Fields
require_once get_template_directory() . '/inc/acf-footer.php';
