<?php
/**
 * One-time: Manually set one instant win prize as "won" for demo/testing.
 *
 * Visit as admin: ?nera_set_demo_instant_winner=1&_wpnonce=XXX
 * Safe: runs only for admins, requires valid nonce.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Nonce action for demo winner URL actions */
define('NERA_DEMO_WINNERS_NONCE', 'nera_demo_winners');

/**
 * Verify nonce for demo winner actions. Redirects and exits if invalid.
 *
 * @return void
 */
function nera_demo_winners_verify_nonce()
{
  $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
  if (!wp_verify_nonce($nonce, NERA_DEMO_WINNERS_NONCE)) {
    wp_safe_redirect(
      remove_query_arg([
        'nera_set_demo_instant_winner',
        'nera_generate_demo_winners',
        'nera_duplicate_prize',
        'prize_name',
        '_wpnonce',
      ]),
    );
    exit();
  }
}

/**
 * Set one available instant win prize to "won" with demo winner data.
 * Hooks on init so the lottery plugin is loaded.
 */
function nera_maybe_set_demo_instant_winner()
{
  // Only for admins and only when the secret param is present
  if (
    !isset($_GET['nera_set_demo_instant_winner']) ||
    $_GET['nera_set_demo_instant_winner'] !== '1'
  ) {
    return;
  }
  if (!current_user_can('manage_options')) {
    return;
  }
  nera_demo_winners_verify_nonce();

  // Require lottery plugin helpers
  if (
    !function_exists('lty_get_instant_winner_log_ids') ||
    !function_exists('lty_update_instant_winner_log')
  ) {
    wp_safe_redirect(remove_query_arg('nera_set_demo_instant_winner'));
    exit();
  }

  // Find first lottery product that has instant wins and at least one available prize
  $product_id = null;
  $product = null;
  $log_ids = [];

  $product_ids = wc_get_products([
    'type' => 'lottery',
    'limit' => 50,
    'return' => 'ids',
  ]);

  if (empty($product_ids) || !is_array($product_ids)) {
    nera_demo_instant_winner_redirect('no_lottery_products');
    return;
  }

  foreach ($product_ids as $pid) {
    $p = wc_get_product($pid);
    if (!$p || !is_object($p)) {
      continue;
    }
    if (!method_exists($p, 'is_instant_winner') || !$p->is_instant_winner()) {
      continue;
    }
    if (
      !method_exists($p, 'get_instant_winner_available_prizes_count') ||
      $p->get_instant_winner_available_prizes_count() < 1
    ) {
      continue;
    }
    $list_count = method_exists($p, 'get_current_relist_count')
      ? $p->get_current_relist_count()
      : 0;
    $log_ids = lty_get_instant_winner_log_ids($pid, false, $list_count, 'lty_available');
    if (!empty($log_ids) && is_array($log_ids)) {
      $product_id = $pid;
      $product = $p;
      break;
    }
  }

  if (!$product_id || empty($log_ids)) {
    nera_demo_instant_winner_redirect('no_available_prizes');
    return;
  }

  // Use the first available instant winner log
  $log_id = (int) $log_ids[0];

  // Demo winner data (matches plan: Joseph Rouski, ticket 67907)
  $meta_args = [
    'lty_user_name' => 'Joseph Rouski',
    'lty_user_email' => 'demo@example.com',
    'lty_user_id' => 0,
    'lty_order_id' => 0,
    'lty_ticket_number' => '67907',
  ];
  $post_args = ['post_status' => 'lty_won'];

  lty_update_instant_winner_log($log_id, $meta_args, $post_args);

  nera_demo_instant_winner_redirect('success', $product_id);
}

/**
 * Redirect after demo instant winner setup (removes query param and optionally adds message).
 *
 * @param string $result 'success'|'no_lottery_products'|'no_available_prizes'
 * @param int|null $product_id Product ID for success link
 */
function nera_demo_instant_winner_redirect($result, $product_id = null)
{
  $url = remove_query_arg(['nera_set_demo_instant_winner', '_wpnonce']);
  if ($result === 'success' && $product_id) {
    $product_url = get_permalink($product_id);
    if ($product_url) {
      $url = add_query_arg('nera_demo_winner_set', '1', $product_url);
    }
  } elseif ($result !== 'success') {
    $url = add_query_arg('nera_demo_winner_set', $result, $url);
  }
  wp_safe_redirect($url);
  exit();
}

add_action('init', 'nera_maybe_set_demo_instant_winner', 20);

/**
 * Show a one-time success/error notice after demo winner setup redirect.
 */
function nera_demo_instant_winner_notice()
{
  if (!isset($_GET['nera_demo_winner_set']) || !current_user_can('manage_options')) {
    return;
  }
  $code = sanitize_text_field($_GET['nera_demo_winner_set']);
  $message = '';
  $type = 'success';
  if ($code === '1') {
    $message = __(
      'Demo instant win prize set: one available prize is now shown as “Won” (Joseph Rouski, ticket #67907). Open the Instant Win Prizes drawer to view it.',
      'nera-competitions',
    );
  } elseif ($code === 'no_lottery_products') {
    $message = __(
      'No lottery products found. Create a lottery product with instant win rules first.',
      'nera-competitions',
    );
    $type = 'warning';
  } elseif ($code === 'no_available_prizes') {
    $message = __(
      'No available instant win prizes found. All prizes are already won, or the product has no instant win logs.',
      'nera-competitions',
    );
    $type = 'warning';
  }
  if ($message) {
    echo '<div class="notice notice-' .
      esc_attr($type) .
      ' is-dismissible"><p>' .
      esc_html($message) .
      '</p></div>';
  }
}

add_action('admin_notices', 'nera_demo_instant_winner_notice');
add_action('wp_footer', 'nera_demo_instant_winner_notice_frontend', 5);

/**
 * Show success notice on frontend (product page) after redirect.
 */
function nera_demo_instant_winner_notice_frontend()
{
  if (
    !isset($_GET['nera_demo_winner_set']) ||
    $_GET['nera_demo_winner_set'] !== '1' ||
    !current_user_can('manage_options')
  ) {
    return;
  }
  $message = __(
    'Demo instant win prize set. One prize is now shown as “Won” (Joseph Rouski, ticket #67907). Click the Instant Win Prizes button below to view it.',
    'nera-competitions',
  );
  echo '<div class="nera-demo-winner-notice">' . esc_html($message) . '</div>';
  echo '<script>setTimeout(function(){ var e=document.querySelector(".nera-demo-winner-notice"); if(e) e.remove(); }, 6000);</script>';
}

/**
 * Generate multiple demo winners for a specific prize
 * Visit as admin: ?nera_generate_demo_winners=10&prize_name=Omitha
 *
 * @return void
 */
function nera_generate_multiple_demo_winners()
{
  // Only for admins and only when the param is present
  if (!isset($_GET['nera_generate_demo_winners']) || !current_user_can('manage_options')) {
    return;
  }
  nera_demo_winners_verify_nonce();

  $count = absint($_GET['nera_generate_demo_winners']);
  $prize_name = isset($_GET['prize_name']) ? sanitize_text_field($_GET['prize_name']) : '';

  if ($count < 1 || $count > 50) {
    nera_demo_winners_redirect('invalid_count');
    return;
  }

  // Require lottery plugin helpers
  if (
    !function_exists('lty_get_instant_winner_log_ids') ||
    !function_exists('lty_update_instant_winner_log')
  ) {
    nera_demo_winners_redirect('plugin_not_loaded');
    return;
  }

  // Find lottery products with instant wins
  $product_ids = wc_get_products([
    'type' => 'lottery',
    'limit' => 50,
    'return' => 'ids',
  ]);

  if (empty($product_ids) || !is_array($product_ids)) {
    nera_demo_winners_redirect('no_lottery_products');
    return;
  }

  $available_logs = [];
  $product_id = null;

  // Find all available prizes that match the prize name (if specified)
  foreach ($product_ids as $pid) {
    $p = wc_get_product($pid);
    if (!$p || !is_object($p)) {
      continue;
    }
    if (!method_exists($p, 'is_instant_winner') || !$p->is_instant_winner()) {
      continue;
    }

    $list_count = method_exists($p, 'get_current_relist_count')
      ? $p->get_current_relist_count()
      : 0;
    $log_ids = lty_get_instant_winner_log_ids($pid, false, $list_count, 'lty_available');

    if (!empty($log_ids) && is_array($log_ids)) {
      // If prize name specified, filter by prize message
      if (!empty($prize_name)) {
        foreach ($log_ids as $log_id) {
          $log = lty_get_instant_winner_log($log_id);
          if ($log && is_object($log)) {
            $prize_message = $log->get_prize_message();
            // Case-insensitive search
            if (stripos($prize_message, $prize_name) !== false) {
              $available_logs[] = $log_id;
              $product_id = $pid;
            }
          }
        }
      } else {
        // No filter, add all available prizes
        $available_logs = array_merge($available_logs, $log_ids);
        if (!$product_id) {
          $product_id = $pid;
        }
      }
    }
  }

  if (empty($available_logs)) {
    nera_demo_winners_redirect('no_matching_prizes', null, $prize_name);
    return;
  }

  // Limit to requested count
  $logs_to_update = array_slice($available_logs, 0, $count);
  $actual_count = count($logs_to_update);

  // Demo winner names for variety
  $demo_names = [
    'Tom Baxter',
    'Jake Massey',
    'Darren Wh',
    'Keeley Hill',
    'Tia Rigby',
    'Isha mullings',
    'Josiah Bond',
    'Ashley Sallis',
    'Joe Tilsley',
    'Ben Kennan',
    'S Webb',
    'Emma Thompson',
    'Oliver Smith',
    'Sophie Wilson',
    'James Anderson',
    'Emily Brown',
    'Michael Davis',
    'Charlotte Taylor',
    'Daniel Martinez',
    'Amelia Garcia',
  ];

  // Update each log with demo winner data
  foreach ($logs_to_update as $index => $log_id) {
    $name = $demo_names[$index % count($demo_names)];
    $ticket_number = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);

    $meta_args = [
      'lty_user_name' => $name,
      'lty_user_email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
      'lty_user_id' => 0,
      'lty_order_id' => 0,
      'lty_ticket_number' => $ticket_number,
    ];
    $post_args = ['post_status' => 'lty_won'];

    lty_update_instant_winner_log($log_id, $meta_args, $post_args);
  }

  nera_demo_winners_redirect('success', $product_id, $prize_name, $actual_count);
}
add_action('init', 'nera_generate_multiple_demo_winners', 20);

/**
 * Redirect after multiple demo winners generation
 */
function nera_demo_winners_redirect($result, $product_id = null, $prize_name = '', $count = 0)
{
  $url = remove_query_arg([
    'nera_generate_demo_winners',
    'nera_duplicate_prize',
    'prize_name',
    '_wpnonce',
  ]);

  $args = ['nera_demo_winners_result' => $result];

  if ($result === 'success' && $product_id) {
    $args['winners_count'] = $count;
    if (!empty($prize_name)) {
      $args['prize_name'] = urlencode($prize_name);
    }
    $product_url = get_permalink($product_id);
    if ($product_url) {
      $url = add_query_arg($args, $product_url);
    }
  } else {
    if (!empty($prize_name)) {
      $args['prize_name'] = urlencode($prize_name);
    }
    $url = add_query_arg($args, $url);
  }

  wp_safe_redirect($url);
  exit();
}

/**
 * Show notice after multiple demo winners generation
 */
function nera_multiple_demo_winners_notice()
{
  if (!isset($_GET['nera_demo_winners_result']) || !current_user_can('manage_options')) {
    return;
  }

  $result = sanitize_text_field($_GET['nera_demo_winners_result']);
  $count = isset($_GET['winners_count']) ? absint($_GET['winners_count']) : 0;
  $prize_name = isset($_GET['prize_name'])
    ? sanitize_text_field(urldecode($_GET['prize_name']))
    : '';

  $message = '';
  $type = 'success';

  switch ($result) {
    case 'success':
      $prize_text = !empty($prize_name) ? ' for "' . esc_html($prize_name) . '"' : '';
      $message = sprintf(
        __(
          '%d demo instant win winners generated%s. Open the Instant Win Prizes drawer to see the modal in action!',
          'nera-competitions',
        ),
        $count,
        $prize_text,
      );
      break;
    case 'invalid_count':
      $message = __('Invalid count. Please specify a count between 1 and 50.', 'nera-competitions');
      $type = 'error';
      break;
    case 'plugin_not_loaded':
      $message = __(
        'Lottery plugin functions not available. Make sure the lottery plugin is active.',
        'nera-competitions',
      );
      $type = 'error';
      break;
    case 'no_lottery_products':
      $message = __(
        'No lottery products found. Create a lottery product with instant win rules first.',
        'nera-competitions',
      );
      $type = 'warning';
      break;
    case 'no_matching_prizes':
      $prize_text = !empty($prize_name) ? ' matching "' . esc_html($prize_name) . '"' : '';
      $message = sprintf(
        __(
          'No available instant win prizes found%s. Either all prizes are won, or no prizes match your search.',
          'nera-competitions',
        ),
        $prize_text,
      );
      $type = 'warning';
      break;
  }

  if ($message) {
    // Admin notice
    if (is_admin()) {
      echo '<div class="notice notice-' .
        esc_attr($type) .
        ' is-dismissible"><p>' .
        $message .
        '</p></div>';
    }
  }
}
add_action('admin_notices', 'nera_multiple_demo_winners_notice');

/**
 * Show frontend notice after multiple demo winners generation
 */
function nera_multiple_demo_winners_notice_frontend()
{
  if (
    !isset($_GET['nera_demo_winners_result']) ||
    $_GET['nera_demo_winners_result'] !== 'success' ||
    !current_user_can('manage_options')
  ) {
    return;
  }

  $count = isset($_GET['winners_count']) ? absint($_GET['winners_count']) : 0;
  $prize_name = isset($_GET['prize_name'])
    ? sanitize_text_field(urldecode($_GET['prize_name']))
    : '';

  $prize_text = !empty($prize_name) ? ' for "' . esc_html($prize_name) . '"' : '';
  $message = sprintf(
    __(
      '%d demo winners generated%s! Open a prize card to see the winners list and modal.',
      'nera-competitions',
    ),
    $count,
    $prize_text,
  );

  echo '<div class="nera-demo-winners-notice">' . esc_html($message) . '</div>';
  echo '<script>setTimeout(function(){ var e=document.querySelector(".nera-demo-winners-notice"); if(e) e.remove(); }, 8000);</script>';
}
add_action('wp_footer', 'nera_multiple_demo_winners_notice_frontend', 5);

/**
 * Duplicate existing prizes for testing (creates temporary instant winner logs)
 * Visit as admin: ?nera_duplicate_prize=10&prize_name=Omitha
 *
 * This creates NEW instant winner log entries by duplicating an existing prize,
 * allowing you to test the multiple winners modal without configuring 10+ rules.
 *
 * @return void
 */
function nera_duplicate_prize_for_testing()
{
  // Only for admins and only when the param is present
  if (!isset($_GET['nera_duplicate_prize']) || !current_user_can('manage_options')) {
    return;
  }
  nera_demo_winners_verify_nonce();

  $count = absint($_GET['nera_duplicate_prize']);
  $prize_name = isset($_GET['prize_name']) ? sanitize_text_field($_GET['prize_name']) : '';

  if ($count < 1 || $count > 50) {
    nera_demo_winners_redirect('invalid_count');
    return;
  }

  // Require lottery plugin helpers
  if (
    !function_exists('lty_get_instant_winner_log_ids') ||
    !function_exists('lty_get_instant_winner_log')
  ) {
    nera_demo_winners_redirect('plugin_not_loaded');
    return;
  }

  // Find lottery products with instant wins
  $product_ids = wc_get_products([
    'type' => 'lottery',
    'limit' => 50,
    'return' => 'ids',
  ]);

  if (empty($product_ids) || !is_array($product_ids)) {
    nera_demo_winners_redirect('no_lottery_products');
    return;
  }

  $source_log = null;
  $product_id = null;

  // Find a prize that matches the prize name (available OR won - doesn't matter)
  foreach ($product_ids as $pid) {
    $p = wc_get_product($pid);
    if (!$p || !is_object($p)) {
      continue;
    }
    if (!method_exists($p, 'is_instant_winner') || !$p->is_instant_winner()) {
      continue;
    }

    $list_count = method_exists($p, 'get_current_relist_count')
      ? $p->get_current_relist_count()
      : 0;

    // Get ALL logs (both available and won)
    $available_logs = lty_get_instant_winner_log_ids($pid, false, $list_count, 'lty_available');
    $won_logs = lty_get_instant_winner_log_ids($pid, false, $list_count, 'lty_won');
    $all_logs = array_merge((array) $available_logs, (array) $won_logs);

    if (!empty($all_logs) && is_array($all_logs)) {
      foreach ($all_logs as $log_id) {
        $log = lty_get_instant_winner_log($log_id);
        if ($log && is_object($log)) {
          $prize_message = $log->get_prize_message();

          // Case-insensitive search
          if (empty($prize_name) || stripos($prize_message, $prize_name) !== false) {
            $source_log = $log;
            $product_id = $pid;
            break 2; // Found one, break out of both loops
          }
        }
      }
    }
  }

  if (!$source_log) {
    nera_demo_winners_redirect('no_matching_prizes', null, $prize_name);
    return;
  }

  // Demo winner names for variety
  $demo_names = [
    'Tom Baxter',
    'Jake Massey',
    'Darren Wh',
    'Keeley Hill',
    'Tia Rigby',
    'Isha mullings',
    'Josiah Bond',
    'Ashley Sallis',
    'Joe Tilsley',
    'Ben Kennan',
    'S Webb',
    'Emma Thompson',
    'Oliver Smith',
    'Sophie Wilson',
    'James Anderson',
    'Emily Brown',
    'Michael Davis',
    'Charlotte Taylor',
    'Daniel Martinez',
    'Amelia Garcia',
  ];

  // Get the correct post type from the source log
  $correct_post_type = get_post_type($source_log->get_id());

  // Create duplicate instant winner logs
  $created_count = 0;
  for ($i = 0; $i < $count; $i++) {
    $name = $demo_names[$i % count($demo_names)];
    $ticket_number = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);

    // Create a new instant winner log post using the correct post type
    $new_log_args = [
      'post_type' => $correct_post_type,
      'post_status' => 'lty_won',
      'post_title' => 'Instant Winner #' . time() . '-' . $i,
      'post_parent' => $product_id,
    ];

    $new_log_id = wp_insert_post($new_log_args, true); // true returns WP_Error on failure

    if ($new_log_id && !is_wp_error($new_log_id)) {
      // Copy ALL meta from source log to ensure plugin recognizes it
      $source_meta = get_post_meta($source_log->get_id());

      // Set our custom meta
      update_post_meta($new_log_id, 'lty_lottery_id', $product_id);
      update_post_meta(
        $new_log_id,
        'lty_instant_winner_prize_message',
        $source_log->get_prize_message(),
      );
      update_post_meta($new_log_id, 'lty_user_name', $name);
      update_post_meta(
        $new_log_id,
        'lty_user_email',
        strtolower(str_replace(' ', '.', $name)) . '@example.com',
      );
      update_post_meta($new_log_id, 'lty_user_id', 0);
      update_post_meta($new_log_id, 'lty_order_id', 0);
      update_post_meta($new_log_id, 'lty_ticket_number', $ticket_number);

      // Copy other important meta from source (like rule_id, prize_image_id, etc.)
      foreach ($source_meta as $meta_key => $meta_values) {
        // Skip meta we're setting custom values for
        if (
          in_array($meta_key, [
            'lty_user_name',
            'lty_user_email',
            'lty_user_id',
            'lty_order_id',
            'lty_ticket_number',
          ])
        ) {
          continue;
        }
        // Copy the meta
        foreach ($meta_values as $meta_value) {
          add_post_meta($new_log_id, $meta_key, maybe_unserialize($meta_value));
        }
      }

      $created_count++;
    }
  }

  // Clear WordPress object cache to force refresh
  wp_cache_flush();

  // Clear product transients
  delete_transient('lty_instant_winner_log_ids_' . $product_id);
  delete_transient('lty_instant_winner_logs_' . $product_id);

  nera_demo_winners_redirect('success', $product_id, $prize_name, $created_count);
}
add_action('init', 'nera_duplicate_prize_for_testing', 20);

/**
 * Add Demo Winners to Tools menu for secure URL generation.
 */
function nera_demo_winners_admin_menu()
{
  add_management_page(
    __('Demo Winners Generator', 'nera-competitions'),
    __('Demo Winners', 'nera-competitions'),
    'manage_options',
    'nera-demo-winners',
    'nera_demo_winners_admin_page',
    30,
  );
}
add_action('admin_menu', 'nera_demo_winners_admin_menu');

/**
 * Admin page rendering demo winner URLs with nonces.
 */
function nera_demo_winners_admin_page()
{
  if (!current_user_can('manage_options')) {
    return;
  }
  $nonce = wp_create_nonce(NERA_DEMO_WINNERS_NONCE);
  $base = home_url('/');
  ?>
    <div class="wrap">
        <h1><?php esc_html_e('Demo Winners Generator', 'nera-competitions'); ?></h1>
        <p><?php esc_html_e(
          'Use these links to generate demo instant win winners for testing. Each link includes a security nonce and will only work when you are logged in as an administrator.',
          'nera-competitions',
        ); ?></p>

        <h2><?php esc_html_e(
          'Option 1: Duplicate Existing Prize (Recommended)',
          'nera-competitions',
        ); ?></h2>
        <p><?php esc_html_e(
          'Creates new winner log entries by duplicating an existing prize. Works even if all prizes are already won.',
          'nera-competitions',
        ); ?></p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Action', 'nera-competitions'); ?></th>
                    <th><?php esc_html_e('URL', 'nera-competitions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php esc_html_e(
                      'Duplicate 10 prizes (e.g. Omitha)',
                      'nera-competitions',
                    ); ?></td>
                    <td><code><a href="<?php echo esc_url(
                      add_query_arg(
                        [
                          'nera_duplicate_prize' => 10,
                          'prize_name' => 'Omitha',
                          '_wpnonce' => $nonce,
                        ],
                        $base,
                      ),
                    ); ?>"><?php echo esc_html(
  $base,
); ?>?nera_duplicate_prize=10&prize_name=Omitha&_wpnonce=<?php echo esc_html(
  $nonce,
); ?></a></code></td>
                </tr>
                <tr>
                    <td><?php esc_html_e(
                      'Duplicate 5 prizes (any name)',
                      'nera-competitions',
                    ); ?></td>
                    <td><code><a href="<?php echo esc_url(
                      add_query_arg(['nera_duplicate_prize' => 5, '_wpnonce' => $nonce], $base),
                    ); ?>"><?php echo esc_html(
  $base,
); ?>?nera_duplicate_prize=5&_wpnonce=<?php echo esc_html($nonce); ?></a></code></td>
                </tr>
            </tbody>
        </table>

        <h2><?php esc_html_e('Option 2: Mark Available Prizes as Won', 'nera-competitions'); ?></h2>
        <p><?php esc_html_e(
          'Marks existing available (not yet won) prizes with demo winner data. Requires enough available prizes.',
          'nera-competitions',
        ); ?></p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Action', 'nera-competitions'); ?></th>
                    <th><?php esc_html_e('URL', 'nera-competitions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php esc_html_e(
                      'Mark 10 available prizes as won (Omitha)',
                      'nera-competitions',
                    ); ?></td>
                    <td><code><a href="<?php echo esc_url(
                      add_query_arg(
                        [
                          'nera_generate_demo_winners' => 10,
                          'prize_name' => 'Omitha',
                          '_wpnonce' => $nonce,
                        ],
                        $base,
                      ),
                    ); ?>"><?php echo esc_html(
  $base,
); ?>?nera_generate_demo_winners=10&prize_name=Omitha&_wpnonce=<?php echo esc_html(
  $nonce,
); ?></a></code></td>
                </tr>
            </tbody>
        </table>

        <h2><?php esc_html_e('Option 3: Set One Demo Winner', 'nera-competitions'); ?></h2>
        <p><?php esc_html_e(
          'Marks a single available prize as won (Joseph Rouski, ticket #67907).',
          'nera-competitions',
        ); ?></p>
        <p><code><a href="<?php echo esc_url(
          add_query_arg(['nera_set_demo_instant_winner' => '1', '_wpnonce' => $nonce], $base),
        ); ?>"><?php echo esc_html(
  $base,
); ?>?nera_set_demo_instant_winner=1&_wpnonce=<?php echo esc_html($nonce); ?></a></code></p>

        <p class="description"><?php esc_html_e(
          'Nonces expire after 12–24 hours. If a link stops working, return to this page to get a fresh link.',
          'nera-competitions',
        ); ?></p>
    </div>
    <?php
}
