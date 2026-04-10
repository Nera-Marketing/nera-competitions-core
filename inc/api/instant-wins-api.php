<?php
/**
 * REST API for Instant Wins Lazy Loading
 *
 * Provides a secure, cacheable endpoint for fetching instant win data
 * without requiring full page load. Includes rate limiting and data sanitization.
 *
 * ## Usage
 *
 * Endpoint: GET /wp-json/nera/v1/instant-wins/{product_id}
 *
 * Example:
 * ```javascript
 * fetch('/wp-json/nera/v1/instant-wins/123')
 *   .then(response => response.json())
 *   .then(data => {
 *     console.log(data.data.prizes);
 *     console.log(data.data.stats);
 *   });
 * ```
 *
 * ## Response Format
 *
 * Success (200):
 * ```json
 * {
 *   "success": true,
 *   "data": {
 *     "prizes": [
 *       {
 *         "id": "hash123",
 *         "title": "Prize Name",
 *         "image": "https://example.com/image.jpg",
 *         "total_available": 10,
 *         "won_count": 3,
 *         "winners": [
 *           {"name": "John D.", "ticket": "12345", "date": "2024-01-01"}
 *         ]
 *       }
 *     ],
 *     "stats": {
 *       "total_available": 50,
 *       "total_won": 15
 *     }
 *   },
 *   "cached": false
 * }
 * ```
 *
 * Error Responses:
 * - 400: Invalid product or instant wins not enabled
 * - 404: Product not found
 * - 429: Rate limit exceeded (30 req/min per IP per product)
 * - 500: Server error
 *
 * ## Security Features
 *
 * - Rate limiting: 30 requests/minute per IP per product
 * - Response caching: 60 second TTL (WordPress transients)
 * - Data sanitization: Only public data exposed (names, tickets)
 * - Name privacy: Automatically formats as "FirstName L."
 *
 * ## Cache Management
 *
 * Clear cache programmatically:
 * ```php
 * nera_clear_instant_wins_cache($product_id);
 * ```
 *
 * Cache is automatically cleared on instant win status changes via filter.
 *
 * ## Testing
 *
 * Test the endpoint in browser console:
 * ```javascript
 * // Replace 123 with actual product ID
 * fetch('/wp-json/nera/v1/instant-wins/123')
 *   .then(r => r.json())
 *   .then(data => console.log(data));
 * ```
 *
 * Test rate limiting:
 * ```javascript
 * // Send 31 requests quickly to trigger rate limit
 * for(let i = 0; i < 31; i++) {
 *   fetch('/wp-json/nera/v1/instant-wins/123')
 *     .then(r => r.json())
 *     .then(data => console.log(`Request ${i+1}:`, data));
 * }
 * ```
 *
 * Test cache:
 * ```javascript
 * // First request (not cached)
 * fetch('/wp-json/nera/v1/instant-wins/123')
 *   .then(r => r.json())
 *   .then(data => console.log('Cached:', data.cached)); // false
 *
 * // Second request within 60s (cached)
 * setTimeout(() => {
 *   fetch('/wp-json/nera/v1/instant-wins/123')
 *     .then(r => r.json())
 *     .then(data => console.log('Cached:', data.cached)); // true
 * }, 1000);
 * ```
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Instant Wins REST API Class
 */
class Nera_Instant_Wins_API
{
  /**
   * API namespace
   */
  const NAMESPACE = 'nera/v1';

  /**
   * Rate limit: requests per minute per IP per product
   */
  const RATE_LIMIT = 30;

  /**
   * Cache TTL in seconds
   */
  const CACHE_TTL = 60;

  /**
   * Initialize the API
   */
  public static function init()
  {
    add_action('rest_api_init', [__CLASS__, 'register_routes']);
  }

  /**
   * Register REST API routes
   */
  public static function register_routes()
  {
    register_rest_route(self::NAMESPACE, '/instant-wins/(?P<product_id>\d+)', [
      'methods' => WP_REST_Server::READABLE,
      'callback' => [__CLASS__, 'get_instant_wins'],
      'permission_callback' => '__return_true', // Public endpoint
      'args' => [
        'product_id' => [
          'required' => true,
          'type' => 'integer',
          'validate_callback' => function ($param) {
            return is_numeric($param) && $param > 0;
          },
          'sanitize_callback' => 'absint',
        ],
      ],
    ]);
  }

  /**
   * Get instant wins data for a product
   *
   * @param WP_REST_Request $request Request object.
   * @return WP_REST_Response|WP_Error Response object or error.
   */
  public static function get_instant_wins($request)
  {
    $product_id = $request->get_param('product_id');

    // Rate limiting check
    $rate_limit_check = self::check_rate_limit($product_id);
    if (is_wp_error($rate_limit_check)) {
      return $rate_limit_check;
    }

    // Check cache first
    $cache_key = self::get_cache_key($product_id);
    $cached_data = get_transient($cache_key);

    if (false !== $cached_data) {
      return rest_ensure_response([
        'success' => true,
        'data' => $cached_data,
        'cached' => true,
      ]);
    }

    // Validate product
    $validation_error = self::validate_product($product_id);
    if (is_wp_error($validation_error)) {
      return $validation_error;
    }

    // Fetch and process instant wins data
    $data = self::fetch_instant_wins_data($product_id);

    if (is_wp_error($data)) {
      return $data;
    }

    // Cache the result
    set_transient($cache_key, $data, self::CACHE_TTL);

    // Return response
    return rest_ensure_response([
      'success' => true,
      'data' => $data,
      'cached' => false,
    ]);
  }

  /**
   * Validate product exists and has instant wins enabled
   *
   * @param int $product_id Product ID.
   * @return true|WP_Error True if valid, WP_Error otherwise.
   */
  private static function validate_product($product_id)
  {
    // Check if product exists
    $product = wc_get_product($product_id);

    if (!$product || !$product->exists()) {
      return new WP_Error('invalid_product', __('Product not found.', 'nera-competitions'), [
        'status' => 404,
      ]);
    }

    // Check if product is a lottery type
    if ($product->get_type() !== 'lottery') {
      return new WP_Error(
        'invalid_product_type',
        __('Product is not a lottery/competition.', 'nera-competitions'),
        ['status' => 400],
      );
    }

    // Check if instant wins are enabled using the lottery plugin's method
    $has_instant_wins = false;
    if (method_exists($product, 'is_instant_winner')) {
      $has_instant_wins = $product->is_instant_winner();
    }

    if (!$has_instant_wins) {
      return new WP_Error(
        'instant_wins_disabled',
        __('Instant wins are not enabled for this product.', 'nera-competitions'),
        ['status' => 400],
      );
    }

    return true;
  }

  /**
   * Fetch instant wins data from the lottery plugin
   *
   * @param int $product_id Product ID.
   * @return array|WP_Error Array of formatted data or WP_Error.
   */
  private static function fetch_instant_wins_data($product_id)
  {
    // Check if lottery plugin function exists
    if (!function_exists('lty_get_instant_winner_log_ids')) {
      return new WP_Error(
        'plugin_not_available',
        __('Lottery plugin functions not available.', 'nera-competitions'),
        ['status' => 500],
      );
    }

    try {
      // Get instant winner log IDs (same as shortcode)
      $instant_winner_ids = lty_get_instant_winner_log_ids($product_id);

      if (empty($instant_winner_ids)) {
        // No instant wins configured - return empty data
        return [
          'prizes' => [],
          'stats' => [
            'total_available' => 0,
            'total_won' => 0,
          ],
        ];
      }

      // Group prizes by prize message (same logic as template)
      $prizes_grouped = [];

      foreach ($instant_winner_ids as $instant_winner_id) {
        $instant_winner = lty_get_instant_winner_log($instant_winner_id);

        if (!is_object($instant_winner)) {
          continue;
        }

        $prize_message = $instant_winner->get_prize_message();
        $key = md5($prize_message); // Group by prize title hash

        if (!isset($prizes_grouped[$key])) {
          $prizes_grouped[$key] = [
            'id' => $key,
            'title' => wp_strip_all_tags($prize_message),
            'image' => self::extract_image_url($instant_winner->get_image()),
            'total_available' => 0,
            'won_count' => 0,
            'winners' => [],
          ];
        }

        $prizes_grouped[$key]['total_available']++;

        // Check if this instant win was won
        if ($instant_winner->has_status('lty_won')) {
          $prizes_grouped[$key]['won_count']++;

          // Get winner details
          $winner_details = self::format_winner_details($instant_winner);

          if ($winner_details) {
            $prizes_grouped[$key]['winners'][] = $winner_details;
          }
        }
      }

      // Calculate overall statistics
      $stats = [
        'total_available' => 0,
        'total_won' => 0,
      ];

      foreach ($prizes_grouped as &$prize) {
        $stats['total_available'] += $prize['total_available'];
        $stats['total_won'] += $prize['won_count'];
      }

      // Convert associative array to indexed array for JSON
      $prizes_array = array_values($prizes_grouped);

      return [
        'prizes' => $prizes_array,
        'stats' => $stats,
      ];
    } catch (Exception $e) {
      return new WP_Error(
        'data_fetch_error',
        __('Error fetching instant wins data.', 'nera-competitions'),
        ['status' => 500],
      );
    }
  }

  /**
   * Extract image URL from HTML image tag
   *
   * @param string $html HTML string containing image tag.
   * @return string|null Image URL or null if not found.
   */
  private static function extract_image_url($html)
  {
    if (empty($html)) {
      return null;
    }

    // Extract src attribute from img tag
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
      return esc_url($matches[1]);
    }

    return null;
  }

  /**
   * Format winner details for API response
   *
   * @param object $instant_winner Instant winner object.
   * @return array|null Formatted winner data or null.
   */
  private static function format_winner_details($instant_winner)
  {
    // Get winner details HTML (contains name/date)
    $details_html = $instant_winner->get_instant_winner_details();

    if (empty($details_html)) {
      return null;
    }

    // Extract name and date from HTML
    // Expected format: "Name – Date" or similar
    $details_text = wp_strip_all_tags($details_html);

    // Parse the details
    $name = '';
    $date = '';

    // Try to split by common separators (–, -, |)
    if (preg_match('/^(.+?)\s*[–\-|]\s*(.+)$/', $details_text, $matches)) {
      $name = trim($matches[1]);
      $date = trim($matches[2]);
    } else {
      $name = $details_text;
    }

    // Sanitize name (only public display name, never email/address)
    $name = self::sanitize_winner_name($name);

    // Get ticket number
    $ticket_number = $instant_winner->get_formatted_ticket_number();

    return [
      'name' => $name,
      'ticket' => $ticket_number ? sanitize_text_field($ticket_number) : '',
      'date' => sanitize_text_field($date),
    ];
  }

  /**
   * Sanitize winner name for public display
   * Ensures only first name + last initial is shown
   *
   * @param string $name Full name.
   * @return string Sanitized name.
   */
  private static function sanitize_winner_name($name)
  {
    $name = sanitize_text_field($name);

    // If already formatted as "First L." return as-is
    if (preg_match('/^[A-Za-z]+\s+[A-Z]\.$/', $name)) {
      return $name;
    }

    // Split into parts
    $parts = explode(' ', $name);

    if (count($parts) >= 2) {
      $first_name = $parts[0];
      $last_initial = strtoupper(substr($parts[count($parts) - 1], 0, 1));
      return $first_name . ' ' . $last_initial . '.';
    }

    return $name;
  }

  /**
   * Check rate limit for IP and product
   *
   * @param int $product_id Product ID.
   * @return true|WP_Error True if allowed, WP_Error if rate limit exceeded.
   */
  private static function check_rate_limit($product_id)
  {
    $ip = self::get_client_ip();
    $rate_key = self::get_rate_limit_key($ip, $product_id);

    $request_count = get_transient($rate_key);

    if (false === $request_count) {
      // First request in this window
      set_transient($rate_key, 1, MINUTE_IN_SECONDS);
      return true;
    }

    if ($request_count >= self::RATE_LIMIT) {
      return new WP_Error(
        'rate_limit_exceeded',
        sprintf(
          __('Rate limit exceeded. Maximum %d requests per minute.', 'nera-competitions'),
          self::RATE_LIMIT,
        ),
        ['status' => 429],
      );
    }

    // Increment counter
    set_transient($rate_key, $request_count + 1, MINUTE_IN_SECONDS);

    return true;
  }

  /**
   * Get client IP address
   *
   * @return string Client IP address.
   */
  private static function get_client_ip()
  {
    $ip_keys = [
      'HTTP_CLIENT_IP',
      'HTTP_X_FORWARDED_FOR',
      'HTTP_X_FORWARDED',
      'HTTP_X_CLUSTER_CLIENT_IP',
      'HTTP_FORWARDED_FOR',
      'HTTP_FORWARDED',
      'REMOTE_ADDR',
    ];

    foreach ($ip_keys as $key) {
      if (array_key_exists($key, $_SERVER) === true) {
        foreach (explode(',', $_SERVER[$key]) as $ip) {
          $ip = trim($ip);

          if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
            return $ip;
          }
        }
      }
    }

    return '0.0.0.0';
  }

  /**
   * Get rate limit transient key
   *
   * @param string $ip Client IP.
   * @param int    $product_id Product ID.
   * @return string Transient key.
   */
  private static function get_rate_limit_key($ip, $product_id)
  {
    return 'nera_instant_wins_rate_' . md5($ip . '_' . $product_id);
  }

  /**
   * Get cache transient key
   *
   * @param int $product_id Product ID.
   * @return string Transient key.
   */
  private static function get_cache_key($product_id)
  {
    return 'nera_instant_wins_cache_' . $product_id;
  }

  /**
   * Clear cache for a specific product
   * Called when instant wins are updated
   *
   * @param int $product_id Product ID.
   */
  public static function clear_cache($product_id)
  {
    delete_transient(self::get_cache_key($product_id));
  }
}

// Initialize the API
Nera_Instant_Wins_API::init();

/**
 * Clear instant wins cache when prize status is updated
 * Hook into lottery plugin's instant win status changes
 */
add_action(
  'lty_instant_winner_log_status_changed',
  function ($instant_winner_id, $new_status, $old_status) {
    if ($instant_winner_id) {
      $instant_winner = lty_get_instant_winner_log($instant_winner_id);
      if ($instant_winner && method_exists($instant_winner, 'get_product_id')) {
        $product_id = $instant_winner->get_product_id();
        if ($product_id) {
          nera_clear_instant_wins_cache($product_id);
        }
      }
    }
  },
  10,
  3,
);

/**
 * Clear instant wins cache when product instant wins are updated
 */
add_action(
  'woocommerce_update_product',
  function ($product_id) {
    $product = wc_get_product($product_id);
    if ($product && $product->get_type() === 'lottery') {
      nera_clear_instant_wins_cache($product_id);
    }
  },
  10,
  1,
);

/**
 * Helper function to clear instant wins cache
 * Can be called from other parts of the theme
 *
 * @param int $product_id Product ID.
 */
function nera_clear_instant_wins_cache($product_id)
{
  Nera_Instant_Wins_API::clear_cache($product_id);
}
