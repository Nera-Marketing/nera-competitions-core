<?php
/**
 * REST API: Entry list (participants) for modal / lazy loading.
 *
 * GET /wp-json/nera/v1/entry-list/{product_id}
 * GET /wp-json/nera/v1/entry-list/{product_id}/tickets?page=1&search=
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Entry list REST API.
 */
class Nera_Entry_List_API
{
  const NAMESPACE = 'nera/v1';

  /** @var int */
  const RATE_LIMIT_PER_MINUTE = 60;

  public static function init()
  {
    add_action('rest_api_init', [__CLASS__, 'register_routes']);
  }

  public static function register_routes()
  {
    register_rest_route(self::NAMESPACE, '/entry-list/(?P<product_id>\d+)', [
      'methods' => WP_REST_Server::READABLE,
      'callback' => [__CLASS__, 'get_entry_list'],
      'permission_callback' => '__return_true',
      'args' => [
        'product_id' => [
          'required' => true,
          'type' => 'integer',
          'validate_callback' => function ($param) {
            return is_numeric($param) && (int) $param > 0;
          },
          'sanitize_callback' => 'absint',
        ],
      ],
    ]);

    register_rest_route(self::NAMESPACE, '/entry-list/(?P<product_id>\d+)/tickets', [
      'methods' => WP_REST_Server::READABLE,
      'callback' => [__CLASS__, 'get_ticket_logs'],
      'permission_callback' => '__return_true',
      'args' => [
        'product_id' => [
          'required' => true,
          'type' => 'integer',
          'validate_callback' => function ($param) {
            return is_numeric($param) && (int) $param > 0;
          },
          'sanitize_callback' => 'absint',
        ],
        'page' => [
          'required' => false,
          'type' => 'integer',
          'default' => 1,
          'sanitize_callback' => function ($v) {
            return max(1, absint($v));
          },
        ],
        'search' => [
          'required' => false,
          'type' => 'string',
          'default' => '',
          'sanitize_callback' => function ($v) {
            return sanitize_text_field((string) $v);
          },
        ],
      ],
    ]);
  }

  /**
   * @param WP_REST_Request $request Request.
   * @return WP_REST_Response|WP_Error
   */
  public static function get_entry_list($request)
  {
    $product_id = (int) $request->get_param('product_id');
    $rl = self::check_rate_limit($product_id);
    if (is_wp_error($rl)) {
      return $rl;
    }

    $product = self::validate_lottery_product($product_id);
    if (is_wp_error($product)) {
      return $product;
    }

    $data = [
      'summary' => self::build_summary($product),
      'winner_logs' => self::build_winner_logs_payload($product),
      'ticket_logs' => self::build_ticket_logs_payload($product, 1, ''),
      'fallback_url' => function_exists('nera_get_entry_list_url') ? nera_get_entry_list_url($product_id) : '',
    ];

    return rest_ensure_response([
      'success' => true,
      'data' => $data,
    ]);
  }

  /**
   * @param WP_REST_Request $request Request.
   * @return WP_REST_Response|WP_Error
   */
  public static function get_ticket_logs($request)
  {
    $product_id = (int) $request->get_param('product_id');
    $rl = self::check_rate_limit($product_id);
    if (is_wp_error($rl)) {
      return $rl;
    }

    $product = self::validate_lottery_product($product_id);
    if (is_wp_error($product)) {
      return $product;
    }

    $page = max(1, absint($request->get_param('page')));
    $search = sanitize_text_field((string) $request->get_param('search'));

    $payload = self::build_ticket_logs_payload($product, $page, $search);

    return rest_ensure_response([
      'success' => true,
      'data' => $payload,
    ]);
  }

  /**
   * @param int $product_id Product ID.
   * @return true|WP_Error
   */
  private static function check_rate_limit($product_id)
  {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $key = 'nera_entry_list_rl_' . md5($ip . '|' . (string) $product_id);
    $now = time();
    $window = get_transient($key);
    if (!is_array($window)) {
      $window = [];
    }
    $window = array_values(
      array_filter(
        $window,
        static function ($ts) use ($now) {
          return (int) $ts > $now - MINUTE_IN_SECONDS;
        },
      ),
    );
    if (count($window) >= self::RATE_LIMIT_PER_MINUTE) {
      return new WP_Error(
        'rate_limited',
        __('Too many requests. Please try again shortly.', 'nera-competitions'),
        ['status' => 429],
      );
    }
    $window[] = $now;
    set_transient($key, $window, 2 * MINUTE_IN_SECONDS);
    return true;
  }

  /**
   * @param int $product_id Product ID.
   * @return WC_Product|WP_Error
   */
  private static function validate_lottery_product($product_id)
  {
    if (!function_exists('lty_is_lottery_product')) {
      return new WP_Error(
        'plugin_not_available',
        __('Lottery plugin is not available.', 'nera-competitions'),
        ['status' => 500],
      );
    }

    $product = wc_get_product($product_id);
    if (!$product || !$product->exists()) {
      return new WP_Error('not_found', __('Product not found.', 'nera-competitions'), ['status' => 404]);
    }

    if (!lty_is_lottery_product($product)) {
      return new WP_Error(
        'invalid_type',
        __('Product is not a lottery competition.', 'nera-competitions'),
        ['status' => 400],
      );
    }

    return $product;
  }

  /**
   * @param WC_Product $product Product.
   * @return array<string, mixed>
   */
  private static function build_summary($product)
  {
    $product_id = $product->get_id();
    $lottery_status = get_post_meta($product_id, '_lty_lottery_status', true);
    $labels = function_exists('lty_get_lottery_statuses') ? lty_get_lottery_statuses() : [];
    $status_label = isset($labels[$lottery_status]) && $labels[$lottery_status]
      ? $labels[$lottery_status]
      : '';

    $max = (int) get_post_meta($product_id, '_lty_maximum_tickets', true);
    $sold = method_exists($product, 'get_purchased_ticket_count')
      ? (int) $product->get_purchased_ticket_count()
      : 0;
    $progress = $max > 0 ? min(100, round(($sold / $max) * 100)) : 0;

    $end_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
    $draw = $end_gmt && function_exists('nera_format_draw_date') ? nera_format_draw_date($end_gmt) : '';
    $countdown_ms = $end_gmt ? (int) (strtotime($end_gmt) * 1000) : 0;

    $pdf_url = function_exists('nera_get_entry_list_pdf_download_url')
      ? nera_get_entry_list_pdf_download_url($product_id)
      : '';

    $is_active = in_array(
      (string) $lottery_status,
      ['lty_lottery_not_started', 'lty_lottery_started'],
      true,
    );

    return [
      'product_id' => $product_id,
      'title' => $product->get_name(),
      'status' => (string) $lottery_status,
      'status_label' => $status_label,
      'is_active' => $is_active,
      'sold' => $sold,
      'max_tickets' => $max,
      'progress' => $progress,
      'draw_date' => $draw,
      'countdown_timestamp_ms' => $countdown_ms,
      'pdf_download_url' => $pdf_url,
    ];
  }

  /**
   * @param WC_Product $product Product.
   * @return array<string, mixed>|null
   */
  private static function build_winner_logs_payload($product)
  {
    if (
      'yes' === get_option('lty_settings_hide_entry_list_winners_details', 'no') ||
      !method_exists($product, 'has_lottery_status') ||
      !$product->has_lottery_status('lty_lottery_finished')
    ) {
      return null;
    }

    $columns = function_exists('lty_get_lottery_winner_table_header')
      ? lty_get_lottery_winner_table_header($product)
      : [];
    $winner_ids = method_exists($product, 'get_current_winner_ids') ? $product->get_current_winner_ids() : [];

    if (!function_exists('lty_check_is_array') || !lty_check_is_array($columns) || !lty_check_is_array($winner_ids)) {
      return null;
    }

    $heading = function_exists('lty_get_single_product_lottery_winner_label')
      ? wp_strip_all_tags(lty_get_single_product_lottery_winner_label())
      : __('Winners', 'nera-competitions');

    $column_meta = [];
    foreach ($columns as $col_key => $label) {
      $column_meta[] = [
        'key' => (string) $col_key,
        'label' => (string) $label,
      ];
    }

    $rows = [];
    foreach ($winner_ids as $winner_id) {
      if (!function_exists('lty_get_lottery_winner')) {
        continue;
      }
      $winner_log = lty_get_lottery_winner($winner_id);
      if (!is_object($winner_log)) {
        continue;
      }
      $cells = [];
      foreach ($columns as $col_key => $label) {
        $text = '';
        switch ($col_key) {
          case 'username':
            $text = function_exists('nera_mask_username')
              ? nera_mask_username($winner_log->display_user_name())
              : (string) $winner_log->display_user_name();
            break;
          case 'gift_product':
            $html = function_exists('lty_get_winner_gift_products_title')
              ? lty_get_winner_gift_products_title(array_unique($winner_log->get_gift_products()), $product)
              : '';
            $text = wp_strip_all_tags((string) $html);
            break;
          case 'ticket_number':
            $text = (string) $winner_log->get_lottery_ticket_number();
            break;
          case 'answer':
            $text = (string) $winner_log->get_answer();
            break;
          default:
            $text = '';
            break;
        }
        $cells[] = [
          'key' => (string) $col_key,
          'label' => (string) $label,
          'text' => $text,
        ];
      }
      $rows[] = $cells;
    }

    if ($rows === []) {
      return null;
    }

    return [
      'heading' => $heading,
      'columns' => $column_meta,
      'rows' => $rows,
    ];
  }

  /**
   * Normalize ticket log column order (matches theme ticket-logs-layout.php).
   *
   * @param array<string, string> $columns Columns.
   * @return array<string, string>
   */
  private static function normalize_ticket_columns($columns)
  {
    if (!is_array($columns)) {
      return [];
    }
    $preferred_order = ['ticket_number', 'user_name', 'date', 'answer'];
    $columns_ordered = [];
    foreach ($preferred_order as $key) {
      if (isset($columns[$key])) {
        $columns_ordered[$key] = $columns[$key];
      }
    }
    foreach ($columns as $key => $label) {
      if (!isset($columns_ordered[$key])) {
        $columns_ordered[$key] = $label;
      }
    }
    return $columns_ordered;
  }

  /**
   * @param int    $ticket_id Ticket post ID.
   * @param array  $columns   key => label.
   * @return array<int, array{key:string,label:string,text:string}>
   */
  private static function build_ticket_row_cells($ticket_id, $columns)
  {
    if (!function_exists('lty_get_lottery_ticket')) {
      return [];
    }
    $ticket = lty_get_lottery_ticket($ticket_id);
    $cells = [];
    foreach ($columns as $key => $label) {
      $text = '';
      switch ($key) {
        case 'ticket_number':
          $text = (string) $ticket->get_lottery_ticket_number();
          break;
        case 'user_name':
          $text = function_exists('nera_mask_username')
            ? nera_mask_username($ticket->display_user_name_by())
            : (string) $ticket->display_user_name_by();
          break;
        case 'date':
          $text = (string) $ticket->get_formatted_created_date();
          break;
        case 'answer':
          $text = (string) $ticket->get_answer();
          break;
        default:
          $text = '';
          break;
      }
      $cells[] = [
        'key' => (string) $key,
        'label' => (string) $label,
        'text' => $text,
      ];
    }
    return $cells;
  }

  /**
   * @param WC_Product $product Product.
   * @param int        $page    Page.
   * @param string     $search  Search.
   * @return array<string, mixed>
   */
  private static function build_ticket_logs_payload($product, $page, $search)
  {
    if (!function_exists('lty_prepare_lottery_entry_list_ticket_log_arguments')) {
      return [
        'columns' => [],
        'rows' => [],
        'pagination' => [
          'current_page' => 1,
          'page_count' => 1,
          'has_next' => false,
          'has_prev' => false,
        ],
        'search' => $search,
      ];
    }

    $args = lty_prepare_lottery_entry_list_ticket_log_arguments($product, max(1, (int) $page), (string) $search);
    $columns = isset($args['columns']) && is_array($args['columns']) ? self::normalize_ticket_columns($args['columns']) : [];
    $ticket_ids = isset($args['ticket_ids']) && is_array($args['ticket_ids']) ? $args['ticket_ids'] : [];
    $pagination = isset($args['pagination']) && is_array($args['pagination']) ? $args['pagination'] : [];

    $current = isset($pagination['current_page']) ? (int) $pagination['current_page'] : max(1, (int) $page);
    $page_count = isset($pagination['page_count']) ? max(1, (int) $pagination['page_count']) : 1;

    $column_meta = [];
    foreach ($columns as $k => $lab) {
      $column_meta[] = ['key' => (string) $k, 'label' => (string) $lab];
    }

    $rows = [];
    foreach ($ticket_ids as $ticket_id) {
      $rows[] = self::build_ticket_row_cells((int) $ticket_id, $columns);
    }

    return [
      'columns' => $column_meta,
      'rows' => $rows,
      'pagination' => [
        'current_page' => $current,
        'page_count' => $page_count,
        'has_next' => $current < $page_count,
        'has_prev' => $current > 1,
      ],
      'search' => (string) $search,
    ];
  }
}

Nera_Entry_List_API::init();
