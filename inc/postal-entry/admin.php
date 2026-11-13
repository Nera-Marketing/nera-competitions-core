<?php
/**
 * Postal entries: the WooCommerce > Postal entries admin screen and its ajax calls.
 *
 * Only registered while the Theme Settings > WooCommerce > Postal entries switch is on, for users who can
 * manage_woocommerce, with a nonce on every call.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Slug of the admin page. */
const NERA_POSTAL_PAGE = 'nera-postal-entries';

/** Ajax actions. */
const NERA_POSTAL_NONCE = 'nera_postal_entry';

/** WooCommerce > Postal entries. */
function nera_postal_admin_menu(): void
{
  if (!nera_postal_enabled()) {
    return;
  }

  add_submenu_page(
    'woocommerce',
    __('Postal entries', 'nera-competitions'),
    __('Postal entries', 'nera-competitions'),
    'manage_woocommerce',
    NERA_POSTAL_PAGE,
    'nera_postal_render_page'
  );
}
add_action('admin_menu', 'nera_postal_admin_menu', 60);

/**
 * Scripts and styles of the screen.
 *
 * @param string $hook_suffix Admin page hook.
 */
function nera_postal_admin_assets(string $hook_suffix): void
{
  if (!nera_postal_enabled() || false === strpos($hook_suffix, NERA_POSTAL_PAGE)) {
    return;
  }

  $file = get_template_directory() . '/assets/js/admin-postal-entry.js';
  if (!file_exists($file)) {
    return;
  }

  wp_enqueue_script('nera-admin-postal-entry', get_template_directory_uri() . '/assets/js/admin-postal-entry.js', ['jquery'], (string) filemtime($file), true);
  wp_localize_script('nera-admin-postal-entry', 'neraPostal', [
    'ajax' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce(NERA_POSTAL_NONCE),
    'i18n' => [
      'working' => __('Checking the drop, issuing the entry, sending the email...', 'nera-competitions'),
      'searching' => __('Searching members...', 'nera-competitions'),
      'none' => __('No member found. You can type the email instead.', 'nera-competitions'),
      'failed' => __('Something went wrong. Reload the page and try again.', 'nera-competitions'),
      'chooseDrop' => __('Choose a drop first.', 'nera-competitions'),
    ],
  ]);

  wp_add_inline_style(
    'wp-admin',
    '.nera-postal{max-width:980px}#nera-postal-rows{overflow-x:auto;max-width:100%}.nera-postal-wide select{max-width:100%}.nera-postal .card{max-width:none;padding:1rem 1.25rem}'
    . '.nera-postal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 20px}@media(max-width:782px){.nera-postal-grid{grid-template-columns:1fr}}'
    . '.nera-postal-grid label{display:block;font-weight:600;margin-bottom:4px}.nera-postal-grid input[type=text],.nera-postal-grid input[type=email],.nera-postal-grid input[type=number],.nera-postal-grid select,.nera-postal-grid textarea{width:100%}'
    . '.nera-postal-wide{grid-column:1/-1}.nera-postal-results{position:relative}'
    . '.nera-postal-results ul{position:absolute;z-index:20;left:0;right:0;margin:0;background:#fff;border:1px solid #c3c4c7;max-height:240px;overflow:auto}'
    . '.nera-postal-results li{margin:0;padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f1}.nera-postal-results li:hover{background:#f0f6fc}'
    . '.nera-postal-chip{display:inline-flex;gap:8px;align-items:center;background:#f0f6fc;border:1px solid #c3c4c7;padding:4px 10px;border-radius:3px}'
    . '.nera-postal-status{margin-top:12px;min-height:1.5em}.nera-postal-status.is-working,.nera-postal-status.is-info{color:#2271b1}.nera-postal-status.is-ok{color:#00a32a;font-weight:600}.nera-postal-status.is-err{color:#d63638;font-weight:600}'
    . '.nera-postal-pill{display:inline-block;padding:1px 8px;border-radius:10px;font-size:12px;background:#f0f0f1}.nera-postal-pill.entered{background:#d7f0dd}.nera-postal-pill.pending{background:#fcf0c2}.nera-postal-pill.not_entered{background:#f9d6d6}'
  );
}
add_action('admin_enqueue_scripts', 'nera_postal_admin_assets');

/**
 * Drops an admin can pick: published drops, with their state.
 *
 * @return array<int, array{id: int, label: string}>
 */
function nera_postal_drop_options(): array
{
  $out = [];
  foreach (wc_get_products(['type' => 'lottery', 'status' => 'publish', 'limit' => -1, 'orderby' => 'title', 'order' => 'ASC']) as $product) {
    $state = nera_postal_drop_state($product);
    $label = $product->get_name();
    $label .= $state['open']
      ? sprintf(' · %s', PHP_INT_MAX === $state['left'] ? __('open', 'nera-competitions') : sprintf(/* translators: %d: entries left */ __('%d entries left', 'nera-competitions'), $state['left']))
      : ' · ' . ('full' === $state['reason'] ? __('full', 'nera-competitions') : __('closed', 'nera-competitions'));
    $out[] = ['id' => (int) $product->get_id(), 'label' => $label];
  }

  return $out;
}

/** Print the admin page. */
function nera_postal_render_page(): void
{
  if (!current_user_can('manage_woocommerce')) {
    return;
  }

  $drops = nera_postal_drop_options();
  ?>
  <div class="wrap nera-postal">
    <h1><?php esc_html_e('Postal entries', 'nera-competitions'); ?></h1>
    <p><?php esc_html_e('Log a free postal entry against a drop. One postal entry per person per drop. The entry gets its number the normal way and counts towards the drop\'s total; it is marked "postal" on the entry list. The entrant must be a registered member: if the email has no account yet, the entry waits and they are emailed an invitation to register.', 'nera-competitions'); ?></p>

    <div class="card">
      <form id="nera-postal-form" autocomplete="off">
        <div class="nera-postal-grid">
          <div class="nera-postal-wide">
            <label for="nera-postal-drop"><?php esc_html_e('Drop', 'nera-competitions'); ?></label>
            <select id="nera-postal-drop" name="product_id">
              <option value=""><?php esc_html_e('Choose a drop...', 'nera-competitions'); ?></option>
              <?php foreach ($drops as $drop) : ?>
                <option value="<?php echo esc_attr((string) $drop['id']); ?>"><?php echo esc_html($drop['label']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="nera-postal-wide">
            <label for="nera-postal-search"><?php esc_html_e('Find a member (name, email or username)', 'nera-competitions'); ?></label>
            <div class="nera-postal-results">
              <input type="text" id="nera-postal-search" placeholder="<?php esc_attr_e('Start typing...', 'nera-competitions'); ?>">
              <ul id="nera-postal-hits" hidden></ul>
            </div>
            <p id="nera-postal-chosen" hidden><span class="nera-postal-chip"><span id="nera-postal-chosen-text"></span> <a href="#" id="nera-postal-clear"><?php esc_html_e('Clear', 'nera-competitions'); ?></a></span></p>
            <input type="hidden" name="user_id" id="nera-postal-user" value="">
          </div>

          <div>
            <label for="nera-postal-email"><?php esc_html_e('Or type an email', 'nera-competitions'); ?></label>
            <input type="email" id="nera-postal-email" name="email" placeholder="name@example.com">
          </div>
          <div>
            <label for="nera-postal-name"><?php esc_html_e('Full name', 'nera-competitions'); ?></label>
            <input type="text" id="nera-postal-name" name="name">
          </div>
          <div class="nera-postal-wide">
            <label for="nera-postal-address"><?php esc_html_e('Postal address (as written on the entry)', 'nera-competitions'); ?></label>
            <input type="text" id="nera-postal-address" name="address_1">
          </div>
          <div>
            <label for="nera-postal-city"><?php esc_html_e('Town / city', 'nera-competitions'); ?></label>
            <input type="text" id="nera-postal-city" name="city">
          </div>
          <div>
            <label for="nera-postal-postcode"><?php esc_html_e('Postcode', 'nera-competitions'); ?></label>
            <input type="text" id="nera-postal-postcode" name="postcode">
          </div>
          <div>
            <label for="nera-postal-quantity"><?php esc_html_e('Number of free entries to generate', 'nera-competitions'); ?></label>
            <input type="number" id="nera-postal-quantity" name="quantity" value="1" min="1" step="1">
          </div>
          <div class="nera-postal-wide">
            <label for="nera-postal-note"><?php esc_html_e('Note or reference (optional, admin only)', 'nera-competitions'); ?></label>
            <input type="text" id="nera-postal-note" name="note">
          </div>
        </div>
        <p><button type="submit" class="button button-primary" id="nera-postal-submit"><?php esc_html_e('Log postal entry', 'nera-competitions'); ?></button></p>
        <div class="nera-postal-status" id="nera-postal-status" role="status" aria-live="polite"></div>
      </form>
    </div>

    <h2><?php esc_html_e('Entries logged for this drop', 'nera-competitions'); ?></h2>
    <div id="nera-postal-rows"><p><?php esc_html_e('Choose a drop to see its postal entries.', 'nera-competitions'); ?></p></div>
  </div>
  <?php
}

/**
 * The table of log rows for a drop.
 *
 * @param int $product_id Drop ID.
 */
function nera_postal_rows_html(int $product_id): string
{
  $rows = nera_postal_log_for_product($product_id);
  if (!$rows) {
    return '<p>' . esc_html__('No postal entries logged for this drop yet.', 'nera-competitions') . '</p>';
  }

  $labels = [
    'entered' => __('Entered', 'nera-competitions'),
    'pending' => __('Waiting for registration', 'nera-competitions'),
    'not_entered' => __('Not entered', 'nera-competitions'),
    'cancelled' => __('Cancelled', 'nera-competitions'),
    'pending_issue' => __('Issuing', 'nera-competitions'),
  ];

  ob_start();
  ?>
  <table class="widefat striped">
    <thead><tr>
      <th><?php esc_html_e('Date', 'nera-competitions'); ?></th>
      <th><?php esc_html_e('Name', 'nera-competitions'); ?></th>
      <th><?php esc_html_e('Email', 'nera-competitions'); ?></th>
      <th><?php esc_html_e('Status', 'nera-competitions'); ?></th>
      <th><?php esc_html_e('Entry number', 'nera-competitions'); ?></th>
      <th><?php esc_html_e('Actions', 'nera-competitions'); ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row) : ?>
      <?php
      $status = (string) ($row['status'] ?? '');
      $order_id = (int) ($row['order_id'] ?? 0);
      $reason = (string) ($row['reason'] ?? '');
      ?>
      <tr>
        <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) ($row['created'] ?? 0))); ?></td>
        <td><?php echo esc_html((string) ($row['name'] ?? '')); ?></td>
        <td><?php echo esc_html((string) ($row['email'] ?? '')); ?></td>
        <td>
          <span class="nera-postal-pill <?php echo esc_attr($status); ?>"><?php echo esc_html($labels[$status] ?? $status); ?></span>
          <?php if ('not_entered' === $status && '' !== $reason) : ?>
            <br><small><?php echo esc_html('full' === $reason ? __('drop was full', 'nera-competitions') : __('drop was closed', 'nera-competitions')); ?></small>
          <?php endif; ?>
        </td>
        <td>
          <?php echo esc_html(implode(', ', (array) ($row['numbers'] ?? []))); ?>
          <?php if ($order_id) : ?>
            <br><a href="<?php echo esc_url(get_edit_post_link($order_id) ?: admin_url('admin.php?page=wc-orders&action=edit&id=' . $order_id)); ?>">#<?php echo esc_html((string) $order_id); ?></a>
          <?php endif; ?>
        </td>
        <td>
          <?php if ('pending' === $status) : ?>
            <button type="button" class="button button-small nera-postal-action" data-do="resend" data-row="<?php echo esc_attr((string) $row['id']); ?>"><?php esc_html_e('Resend invite', 'nera-competitions'); ?></button>
            <button type="button" class="button button-small nera-postal-action" data-do="cancel" data-row="<?php echo esc_attr((string) $row['id']); ?>"><?php esc_html_e('Cancel', 'nera-competitions'); ?></button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php

  return (string) ob_get_clean();
}

/** Check capability and nonce for an ajax call; stop with JSON on failure. */
function nera_postal_ajax_guard(): void
{
  if (!nera_postal_enabled() || !current_user_can('manage_woocommerce') || !check_ajax_referer(NERA_POSTAL_NONCE, 'nonce', false)) {
    wp_send_json_error(['message' => __('Not allowed.', 'nera-competitions')], 403);
  }
}

/** Ajax: search members. */
function nera_postal_ajax_user_search(): void
{
  nera_postal_ajax_guard();

  $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';
  if (strlen($term) < 2) {
    wp_send_json_success(['users' => []]);
  }

  $query = new WP_User_Query([
    'search' => '*' . $term . '*',
    'search_columns' => ['user_login', 'user_email', 'display_name', 'user_nicename'],
    'number' => 10,
    'orderby' => 'display_name',
    'fields' => 'all',
  ]);
  $by_name = new WP_User_Query([
    'meta_query' => [
      'relation' => 'OR',
      ['key' => 'first_name', 'value' => $term, 'compare' => 'LIKE'],
      ['key' => 'last_name', 'value' => $term, 'compare' => 'LIKE'],
    ],
    'number' => 10,
    'fields' => 'all',
  ]);

  $users = [];
  foreach (array_merge((array) $query->get_results(), (array) $by_name->get_results()) as $user) {
    if (isset($users[$user->ID])) {
      continue;
    }
    $users[$user->ID] = [
      'id' => (int) $user->ID,
      'name' => trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name,
      'email' => $user->user_email,
      'login' => $user->user_login,
      'address_1' => (string) get_user_meta($user->ID, 'billing_address_1', true),
      'city' => (string) get_user_meta($user->ID, 'billing_city', true),
      'postcode' => (string) get_user_meta($user->ID, 'billing_postcode', true),
    ];
  }

  wp_send_json_success(['users' => array_slice(array_values($users), 0, 10)]);
}
add_action('wp_ajax_nera_postal_user_search', 'nera_postal_ajax_user_search');

/** Ajax: log a postal entry. */
function nera_postal_ajax_log(): void
{
  nera_postal_ajax_guard();

  $input = [];
  foreach (['product_id', 'user_id', 'quantity'] as $key) {
    $input[$key] = isset($_POST[$key]) ? absint(wp_unslash($_POST[$key])) : 0;
  }
  $input['quantity'] = max(1, (int) $input['quantity']);
  foreach (['email', 'name', 'address_1', 'city', 'postcode', 'note'] as $key) {
    $input[$key] = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
  }

  $result = nera_postal_submit($input);
  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message(), 'rows' => nera_postal_rows_html((int) $input['product_id'])]);
  }

  wp_send_json_success([
    'status' => $result['status'],
    'message' => $result['message'],
    'rows' => nera_postal_rows_html((int) $input['product_id']),
  ]);
}
add_action('wp_ajax_nera_postal_log', 'nera_postal_ajax_log');

/** Ajax: the rows of a drop. */
function nera_postal_ajax_rows(): void
{
  nera_postal_ajax_guard();

  $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
  wp_send_json_success(['rows' => $product_id ? nera_postal_rows_html($product_id) : '']);
}
add_action('wp_ajax_nera_postal_rows', 'nera_postal_ajax_rows');

/** Ajax: cancel a waiting entry or resend its invite. */
function nera_postal_ajax_row_action(): void
{
  nera_postal_ajax_guard();

  $id = isset($_POST['row']) ? sanitize_key(wp_unslash($_POST['row'])) : '';
  $do = isset($_POST['do']) ? sanitize_key(wp_unslash($_POST['do'])) : '';
  $rows = nera_postal_log_all();
  $row = $rows[$id] ?? null;
  if (!$row || 'pending' !== ($row['status'] ?? '')) {
    wp_send_json_error(['message' => __('That entry is no longer waiting.', 'nera-competitions')]);
  }

  if ('cancel' === $do) {
    nera_postal_log_update($id, ['status' => 'cancelled', 'reason' => 'admin']);
    $message = __('Cancelled. No entry will be issued.', 'nera-competitions');
  } elseif ('resend' === $do) {
    nera_postal_send('invite', $row);
    $message = __('Invitation sent again.', 'nera-competitions');
  } else {
    wp_send_json_error(['message' => __('Unknown action.', 'nera-competitions')]);
  }

  wp_send_json_success(['message' => $message, 'rows' => nera_postal_rows_html((int) $row['product_id'])]);
}
add_action('wp_ajax_nera_postal_row_action', 'nera_postal_ajax_row_action');
