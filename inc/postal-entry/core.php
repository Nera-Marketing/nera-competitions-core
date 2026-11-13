<?php
/**
 * Postal entries: the rules and the data (see ../postal-entry.php for the overview).
 *
 * Everything here is plain functions. The admin screen, the emails and the registration hook call them.
 *
 * Data:
 *   - A logged postal entry is a WooCommerce order of GBP 0 with one line (the drop, quantity = the number of
 *     free entries the admin typed, default 1), marked with the order meta `_nera_postal_entry` = yes. The entry
 *     numbers are issued by Lottery for WooCommerce the normal way (automatic numbers), so they count towards
 *     the drop's total. Still one postal entry (one order) per person per drop.
 *   - Every attempt (entered, waiting for registration, not entered, cancelled) is also a row in the option
 *     `nera_postal_entry_log` (autoload off). It holds the entrant's name, email and postal address, so it is
 *     read only by the admin screen, exported and erased through the WordPress privacy tools, and never printed
 *     on the public site. Rows that are finished are dropped after 180 days.
 *
 * @package Nera_Competitions
 * @see CONTEXT.md "Postal entry"
 */

if (!defined('ABSPATH')) {
  exit();
}

/** ACF field name of the site switch (stored as option `options_nera_postal_entry_enabled`). */
const NERA_POSTAL_SWITCH = 'nera_postal_entry_enabled';

/** Order meta marking a postal entry order. */
const NERA_POSTAL_META = '_nera_postal_entry';

/** Option holding the log of postal entry attempts. */
const NERA_POSTAL_LOG = 'nera_postal_entry_log';

/** Days a finished (entered, not entered, cancelled) log row is kept. */
const NERA_POSTAL_KEEP_DAYS = 180;

/** Whether the Postal entries feature is switched on. Reads the raw option so an unsaved page counts as off. */
function nera_postal_enabled(): bool
{
  return (bool) (int) get_option('options_' . NERA_POSTAL_SWITCH, 0);
}

/** @return array<string, array<string, mixed>> Log rows keyed by id. */
function nera_postal_log_all(): array
{
  $rows = get_option(NERA_POSTAL_LOG, []);

  return is_array($rows) ? $rows : [];
}

/**
 * Save the log, dropping finished rows older than NERA_POSTAL_KEEP_DAYS.
 *
 * @param array<string, array<string, mixed>> $rows Rows keyed by id.
 */
function nera_postal_log_save(array $rows): void
{
  $limit = time() - NERA_POSTAL_KEEP_DAYS * DAY_IN_SECONDS;
  foreach ($rows as $id => $row) {
    if ('pending' !== ($row['status'] ?? '') && (int) ($row['updated'] ?? 0) < $limit) {
      unset($rows[$id]);
    }
  }

  if ($rows) {
    update_option(NERA_POSTAL_LOG, $rows, false);
  } else {
    delete_option(NERA_POSTAL_LOG);
  }
}

/**
 * Add a log row.
 *
 * @param array<string, mixed> $row Row fields (status, product_id, email, name, address, note, user_id, order_id, reason).
 * @return array<string, mixed> The saved row, with its id.
 */
function nera_postal_log_add(array $row): array
{
  $row['id'] = substr(md5(uniqid('postal', true)), 0, 12);
  $row['created'] = time();
  $row['updated'] = time();
  $row['created_by'] = get_current_user_id();

  $rows = nera_postal_log_all();
  $rows[$row['id']] = $row;
  nera_postal_log_save($rows);

  return $row;
}

/**
 * Change fields of a log row.
 *
 * @param string               $id      Row id.
 * @param array<string, mixed> $changes Fields to set.
 * @return array<string, mixed>|null The updated row, or null when it does not exist.
 */
function nera_postal_log_update(string $id, array $changes): ?array
{
  $rows = nera_postal_log_all();
  if (!isset($rows[$id])) {
    return null;
  }

  $rows[$id] = array_merge($rows[$id], $changes, ['updated' => time()]);
  nera_postal_log_save($rows);

  return $rows[$id];
}

/**
 * Log rows of one drop, newest first.
 *
 * @param int $product_id Drop (product) ID.
 * @return array<int, array<string, mixed>>
 */
function nera_postal_log_for_product(int $product_id): array
{
  $rows = array_filter(
    nera_postal_log_all(),
    static fn($row) => (int) ($row['product_id'] ?? 0) === $product_id
  );
  usort($rows, static fn($a, $b) => (int) ($b['created'] ?? 0) <=> (int) ($a['created'] ?? 0));

  return $rows;
}

/**
 * Normalise an email for matching.
 *
 * @param string $email Raw email.
 */
function nera_postal_email(string $email): string
{
  return strtolower(trim($email));
}

/**
 * Whether the drop can still take an entry, and how many are left.
 *
 * @param WC_Product $product Drop.
 * @return array{open: bool, left: int, reason: string}
 */
function nera_postal_drop_state($product): array
{
  if (!$product instanceof WC_Product || !function_exists('lty_is_lottery_product') || !lty_is_lottery_product($product)) {
    return ['open' => false, 'left' => 0, 'reason' => 'not a drop'];
  }

  $max = (int) $product->get_lty_maximum_tickets();
  $left = $max > 0 ? (int) $product->get_remaining_ticket_count() : PHP_INT_MAX;

  // A drop that sold out is "full" even though Lottery for WooCommerce then closes it.
  if ($left <= 0) {
    return ['open' => false, 'left' => 0, 'reason' => 'full'];
  }

  if (!$product->has_lottery_status('lty_lottery_started') || $product->is_closed()) {
    return ['open' => false, 'left' => $left === PHP_INT_MAX ? 0 : $left, 'reason' => 'closed'];
  }

  return ['open' => true, 'left' => $left, 'reason' => ''];
}

/**
 * The postal order of a person for a drop, if there is one that still counts.
 *
 * @param int    $product_id Drop ID.
 * @param string $email      Lowercase email.
 * @param int    $user_id    User ID or 0.
 * @return int Order ID or 0.
 */
function nera_postal_existing_order(int $product_id, string $email, int $user_id): int
{
  $args = [
    'limit' => -1,
    'return' => 'ids',
    'status' => ['pending', 'processing', 'on-hold', 'completed'],
    'meta_key' => NERA_POSTAL_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
    'meta_value' => 'yes', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
  ];

  foreach ((array) wc_get_orders($args) as $order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
      continue;
    }

    $same_person = ($user_id && (int) $order->get_customer_id() === $user_id)
      || nera_postal_email((string) $order->get_billing_email()) === $email;
    if (!$same_person) {
      continue;
    }

    foreach ($order->get_items() as $item) {
      if ((int) $item->get_product_id() === $product_id) {
        return (int) $order_id;
      }
    }
  }

  return 0;
}

/**
 * The row or order that already gives this person a postal entry (or a waiting one) for the drop.
 *
 * @return string '' when there is none, else a short admin message.
 */
function nera_postal_duplicate_message(int $product_id, string $email, int $user_id): string
{
  foreach (nera_postal_log_for_product($product_id) as $row) {
    $same = nera_postal_email((string) ($row['email'] ?? '')) === $email
      || ($user_id && (int) ($row['user_id'] ?? 0) === $user_id);
    if (!$same) {
      continue;
    }
    if ('pending' === $row['status']) {
      return __('This person already has a postal entry waiting for registration on this drop.', 'nera-competitions');
    }
    if ('entered' === $row['status']) {
      return __('This person already has a postal entry on this drop (one per person per drop).', 'nera-competitions');
    }
  }

  if (nera_postal_existing_order($product_id, $email, $user_id)) {
    return __('This person already has a postal entry on this drop (one per person per drop).', 'nera-competitions');
  }

  return '';
}

/**
 * Ticket (entry) numbers of an order.
 *
 * @param WC_Order $order Order.
 * @return string[]
 */
function nera_postal_order_numbers($order): array
{
  $numbers = [];
  $ids = $order->get_meta('lty_ticket_ids_in_order');
  foreach ((array) $ids as $ticket_id) {
    $ticket = function_exists('lty_get_lottery_ticket') ? lty_get_lottery_ticket($ticket_id) : null;
    if (is_object($ticket) && 'lty_ticket_canceled' !== $ticket->get_status()) {
      $numbers[] = (string) $ticket->get_lottery_ticket_number();
    }
  }

  return $numbers;
}

/**
 * Issue the entry: a GBP 0 order, the entry number from Lottery for WooCommerce, order completed.
 *
 * While this runs the shop's own order and ticket emails are held back (nera_postal_email_enabled_filter);
 * the entrant gets the Postal entry confirmation email instead.
 *
 * @param WC_Product           $product Drop.
 * @param string               $email   Lowercase email.
 * @param WP_User|null         $user    Account to attach the entry to.
 * @param array<string, mixed> $details Name and address (name, address_1, city, postcode, note).
 * @param string               $row_id  Log row id (stored on the order).
 * @return array{order_id: int, numbers: string[]}|WP_Error
 */
function nera_postal_issue($product, string $email, $user, array $details, string $row_id = '')
{
  $GLOBALS['nera_postal_issuing'] = true;

  try {
    $order = wc_create_order([
      'status' => 'pending',
      'customer_id' => $user instanceof WP_User ? (int) $user->ID : 0,
      'created_via' => 'nera_postal_entry',
    ]);
    if (is_wp_error($order)) {
      return $order;
    }

    $name = trim((string) ($details['name'] ?? ''));
    $parts = preg_split('/\s+/', $name, 2);
    $quantity = max(1, (int) ($details['quantity'] ?? 1));
    $order->add_product($product, $quantity, ['subtotal' => 0, 'total' => 0]);
    $order->set_billing_first_name((string) ($parts[0] ?? ''));
    $order->set_billing_last_name((string) ($parts[1] ?? ''));
    $order->set_billing_email($email);
    $order->set_billing_address_1((string) ($details['address_1'] ?? ''));
    $order->set_billing_city((string) ($details['city'] ?? ''));
    $order->set_billing_postcode((string) ($details['postcode'] ?? ''));
    $order->set_billing_country('GB');
    $order->set_payment_method_title(__('Free postal entry', 'nera-competitions'));
    $order->update_meta_data(NERA_POSTAL_META, 'yes');
    $order->update_meta_data('_nera_postal_entry_row', $row_id);
    if (!empty($details['note'])) {
      $order->update_meta_data('_nera_postal_entry_note', (string) $details['note']);
    }
    // Keeps the async tickets plugin's own "tickets confirmed" email away; ours replaces it.
    $order->update_meta_data('_nera_lty_ticket_email_sent', '1');
    $order->calculate_totals(false);
    $order->save();

    // Entry number the normal way. The last argument lets a guest order through.
    LTY_Order_Handler::create_ticket_for_order_item($order, false, false, true, true);
    $order = wc_get_order($order->get_id());

    $numbers = nera_postal_order_numbers($order);
    if (count($numbers) !== $quantity) {
      $order->delete(true);

      return new WP_Error(
        'nera_postal_no_entry',
        sprintf(
          /* translators: %d: number of entries asked for */
          _n(
            'No entry number could be issued. Check that the drop uses automatic entry numbers and has no skill question.',
            'The %d entry numbers could not all be issued. Check that the drop has enough entries left, uses automatic entry numbers and has no skill question.',
            $quantity,
            'nera-competitions'
          ),
          $quantity
        )
      );
    }

    // Confirm the entry (the same step payment would trigger), then complete the order.
    LTY_Order_Handler::update_lottery_ticket_in_order($order->get_id(), $order);
    $order = wc_get_order($order->get_id());
    $order->add_order_note(
      sprintf(
        /* translators: %s: admin display name */
        __('Free postal entry logged by %s.', 'nera-competitions'),
        wp_get_current_user()->display_name ?: 'the system'
      )
    );
    $order->update_status('completed');

    return ['order_id' => (int) $order->get_id(), 'numbers' => nera_postal_order_numbers(wc_get_order($order->get_id()))];
  } finally {
    unset($GLOBALS['nera_postal_issuing']);
  }
}

/**
 * Log a postal entry: issue it now, hold it for registration, or record that it was not entered.
 *
 * @param array<string, mixed> $input product_id, user_id, email, name, address_1, city, postcode, note.
 * @return array{status: string, message: string, row?: array<string, mixed>}|WP_Error
 */
function nera_postal_submit(array $input)
{
  $product = wc_get_product((int) ($input['product_id'] ?? 0));
  if (!$product || !function_exists('lty_is_lottery_product') || !lty_is_lottery_product($product)) {
    return new WP_Error('nera_postal_drop', __('Choose a drop.', 'nera-competitions'));
  }

  $user = null;
  if (!empty($input['user_id'])) {
    $user = get_userdata((int) $input['user_id']) ?: null;
  }

  $email = nera_postal_email($user ? (string) $user->user_email : (string) ($input['email'] ?? ''));
  if (!is_email($email)) {
    return new WP_Error('nera_postal_email', __('Pick a member or type a valid email address.', 'nera-competitions'));
  }

  // A typed email that belongs to a member counts as picking that member.
  if (!$user) {
    $user = get_user_by('email', $email) ?: null;
  }

  $name = trim((string) ($input['name'] ?? ''));
  if ('' === $name && $user) {
    $name = trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name;
  }
  if ('' === $name) {
    return new WP_Error('nera_postal_name', __('Enter the entrant\'s name.', 'nera-competitions'));
  }

  $user_id = $user ? (int) $user->ID : 0;
  $duplicate = nera_postal_duplicate_message($product->get_id(), $email, $user_id);
  if ('' !== $duplicate) {
    return new WP_Error('nera_postal_duplicate', $duplicate);
  }

  $details = [
    'name' => $name,
    'address_1' => trim((string) ($input['address_1'] ?? '')),
    'city' => trim((string) ($input['city'] ?? '')),
    'postcode' => trim((string) ($input['postcode'] ?? '')),
    'note' => trim((string) ($input['note'] ?? '')),
    'quantity' => max(1, (int) ($input['quantity'] ?? 1)),
  ];
  $base = [
    'product_id' => $product->get_id(),
    'product_name' => $product->get_name(),
    'email' => $email,
    'user_id' => $user_id,
  ] + $details;

  $state = nera_postal_drop_state($product);
  if (!$state['open']) {
    return nera_postal_record_not_entered($base, $state['reason']);
  }
  if ($details['quantity'] > $state['left']) {
    return new WP_Error(
      'nera_postal_quantity',
      sprintf(
        /* translators: %d: entries left on the drop */
        _n('Only %d entry is left on this drop. Lower the number of free entries.', 'Only %d entries are left on this drop. Lower the number of free entries.', $state['left'], 'nera-competitions'),
        $state['left']
      )
    );
  }

  if (!$user) {
    $row = nera_postal_log_add($base + ['status' => 'pending']);
    nera_postal_send('invite', $row);

    return [
      'status' => 'pending',
      'message' => __('No member account has this email yet. The entry is waiting: an email asking them to register has been sent, and the entry is issued as soon as they do.', 'nera-competitions'),
      'row' => $row,
    ];
  }

  return nera_postal_enter($base, $product, $user);
}

/**
 * Issue the entry for a prepared log row base and record it.
 *
 * @param array<string, mixed> $base    Row fields.
 * @param WC_Product           $product Drop.
 * @param WP_User              $user    Member.
 * @param string               $row_id  Existing pending row to complete ('' to add a new row).
 * @return array{status: string, message: string, row?: array<string, mixed>}|WP_Error
 */
function nera_postal_enter(array $base, $product, $user, string $row_id = '')
{
  $row_pre = $row_id ? ['id' => $row_id] : nera_postal_log_add($base + ['status' => 'pending_issue']);
  $result = nera_postal_issue($product, (string) $base['email'], $user, $base, (string) $row_pre['id']);

  if (is_wp_error($result)) {
    if (!$row_id) {
      $rows = nera_postal_log_all();
      unset($rows[$row_pre['id']]);
      nera_postal_log_save($rows);
    }

    return $result;
  }

  $row = nera_postal_log_update((string) $row_pre['id'], [
    'status' => 'entered',
    'order_id' => $result['order_id'],
    'numbers' => $result['numbers'],
    'user_id' => (int) $user->ID,
  ]);
  nera_postal_send('confirmation', (array) $row);

  return [
    'status' => 'entered',
    'message' => sprintf(
      /* translators: %s: entry number(s) */
      _n('Entered. Entry number %s. A confirmation email has been sent.', 'Entered. Entry numbers %s. A confirmation email has been sent.', count($result['numbers']), 'nera-competitions'),
      implode(', ', $result['numbers'])
    ),
    'row' => (array) $row,
  ];
}

/**
 * Record that the drop could not take the entry and email the entrant.
 *
 * @param array<string, mixed> $base   Row fields.
 * @param string               $reason 'full' or 'closed'.
 * @param string               $row_id Existing pending row to mark ('' to add a new row).
 * @return array{status: string, message: string, row?: array<string, mixed>}
 */
function nera_postal_record_not_entered(array $base, string $reason, string $row_id = ''): array
{
  if ($row_id) {
    $row = (array) nera_postal_log_update($row_id, ['status' => 'not_entered', 'reason' => $reason]);
  } else {
    $row = nera_postal_log_add($base + ['status' => 'not_entered', 'reason' => $reason]);
  }
  nera_postal_send('not_entered', $row);

  return [
    'status' => 'not_entered',
    'message' => 'full' === $reason
      ? __('The drop is full, so the entry was not made. The entrant has been emailed.', 'nera-competitions')
      : __('The drop is closed, so the entry was not made. The entrant has been emailed.', 'nera-competitions'),
    'row' => $row,
  ];
}

/**
 * A member registered: issue (or refuse) every postal entry waiting for their email.
 *
 * @param int $user_id New user ID.
 */
function nera_postal_fulfil_pending($user_id): void
{
  static $done = [];

  $user_id = (int) $user_id;
  if (!nera_postal_enabled() || isset($done[$user_id])) {
    return;
  }

  $user = get_userdata($user_id);
  if (!$user) {
    return;
  }
  $done[$user_id] = true;

  $email = nera_postal_email((string) $user->user_email);
  foreach (nera_postal_log_all() as $row) {
    if ('pending' !== ($row['status'] ?? '') || nera_postal_email((string) ($row['email'] ?? '')) !== $email) {
      continue;
    }

    $product = wc_get_product((int) $row['product_id']);
    $state = nera_postal_drop_state($product);
    if (!$state['open'] || max(1, (int) ($row['quantity'] ?? 1)) > $state['left']) {
      nera_postal_record_not_entered($row, $state['open'] ? 'full' : $state['reason'], (string) $row['id']);
      continue;
    }

    if (nera_postal_existing_order((int) $row['product_id'], $email, $user_id)) {
      nera_postal_log_update((string) $row['id'], ['status' => 'cancelled', 'reason' => 'duplicate']);
      continue;
    }

    nera_postal_enter($row, $product, $user, (string) $row['id']);
  }
}
// The account form and checkout create a customer (details such as the date of birth are saved first at the default priority).
add_action('woocommerce_created_customer', 'nera_postal_fulfil_pending', 60);
// A member added by an admin in wp-admin.
add_action('user_register', static function ($user_id) {
  if (is_admin()) {
    nera_postal_fulfil_pending($user_id);
  }
}, 99);

/**
 * A postal entry does not use up the member's own paid cap: give the cap back the entry LFW counts for the member.
 *
 * @param mixed      $value   Cap.
 * @param WC_Product $product Drop.
 * @return mixed
 */
function nera_postal_user_cap_filter($value, $product)
{
  static $cache = [];

  if (!nera_postal_enabled() || !is_user_logged_in() || !$product instanceof WC_Product || !is_numeric($value)) {
    return $value;
  }

  $key = get_current_user_id() . ':' . $product->get_id();
  if (!isset($cache[$key])) {
    $orders = wc_get_orders([
      'limit' => 1,
      'return' => 'ids',
      'customer_id' => get_current_user_id(),
      'status' => ['processing', 'completed'],
      'meta_key' => NERA_POSTAL_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
      'meta_value' => 'yes', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
    ]);
    $cache[$key] = 0;
    foreach ((array) $orders as $order_id) {
      $order = wc_get_order($order_id);
      foreach ($order ? $order->get_items() : [] as $item) {
        if ((int) $item->get_product_id() === $product->get_id()) {
          $cache[$key] = count(nera_postal_order_numbers($order));
        }
      }
    }
  }

  return (int) $value + $cache[$key];
}
add_filter('woocommerce_product_get_lty_user_maximum_tickets', 'nera_postal_user_cap_filter', 20, 2);

/**
 * Mark a postal entry on the published entry list (a child theme can add more to the name through the same filter).
 *
 * @param string $text   Public name.
 * @param object $ticket Ticket.
 */
function nera_postal_public_name($text, $ticket): string
{
  $text = (string) $text;
  if (false !== stripos($text, '(postal)') || !is_object($ticket) || !method_exists($ticket, 'get_order')) {
    return $text;
  }

  $order = $ticket->get_order();
  if (is_object($order) && method_exists($order, 'get_meta') && 'yes' === $order->get_meta(NERA_POSTAL_META)) {
    $text .= ' (' . __('postal', 'nera-competitions') . ')';
  }

  return $text;
}
add_filter('nera_entry_list_public_name', 'nera_postal_public_name', 20, 2);

/**
 * Hold back the shop's own emails for a postal order: the customer order emails, the new order email and the
 * Lottery for WooCommerce ticket confirmation. The postal confirmation email replaces them.
 *
 * @param bool  $enabled Whether the email is enabled.
 * @param mixed $object  The order, when the email has one.
 */
function nera_postal_email_enabled_filter($enabled, $object = null)
{
  if (!empty($GLOBALS['nera_postal_issuing'])) {
    return false;
  }

  if ($object instanceof WC_Order && 'yes' === $object->get_meta(NERA_POSTAL_META)) {
    return false;
  }

  return $enabled;
}

foreach (
  [
    'customer_processing_order',
    'customer_completed_order',
    'customer_on_hold_order',
    'customer_invoice',
    'new_order',
    'customer_lottery_ticket_confirmation',
    'admin_lottery_ticket_confirmation',
    'admin_lottery_ticket_confirmation_order',
  ] as $nera_postal_email_id
) {
  add_filter('woocommerce_email_enabled_' . $nera_postal_email_id, 'nera_postal_email_enabled_filter', 10, 2);
}
unset($nera_postal_email_id);

/**
 * Privacy: add a person's postal log rows to the WordPress personal data export.
 *
 * @param array<string, mixed> $exporters Exporters.
 * @return array<string, mixed>
 */
add_filter('wp_privacy_personal_data_exporters', static function ($exporters) {
  $exporters['nera-postal-entry'] = [
    'exporter_friendly_name' => __('Postal entries', 'nera-competitions'),
    'callback' => static function ($email) {
      $items = [];
      foreach (nera_postal_log_all() as $row) {
        if (nera_postal_email((string) ($row['email'] ?? '')) !== nera_postal_email((string) $email)) {
          continue;
        }
        $items[] = [
          'group_id' => 'nera-postal-entry',
          'group_label' => __('Postal entries', 'nera-competitions'),
          'item_id' => 'postal-' . $row['id'],
          'data' => [
            ['name' => __('Drop', 'nera-competitions'), 'value' => (string) ($row['product_name'] ?? '')],
            ['name' => __('Name', 'nera-competitions'), 'value' => (string) ($row['name'] ?? '')],
            ['name' => __('Address', 'nera-competitions'), 'value' => trim(($row['address_1'] ?? '') . ', ' . ($row['city'] ?? '') . ' ' . ($row['postcode'] ?? ''), ', ')],
            ['name' => __('Status', 'nera-competitions'), 'value' => (string) ($row['status'] ?? '')],
          ],
        ];
      }

      return ['data' => $items, 'done' => true];
    },
  ];

  return $exporters;
});

/** Privacy: erase a person's postal log rows. */
add_filter('wp_privacy_personal_data_erasers', static function ($erasers) {
  $erasers['nera-postal-entry'] = [
    'eraser_friendly_name' => __('Postal entries', 'nera-competitions'),
    'callback' => static function ($email) {
      $rows = nera_postal_log_all();
      $removed = false;
      foreach ($rows as $id => $row) {
        if (nera_postal_email((string) ($row['email'] ?? '')) === nera_postal_email((string) $email)) {
          unset($rows[$id]);
          $removed = true;
        }
      }
      if ($removed) {
        nera_postal_log_save($rows);
      }

      return ['items_removed' => $removed, 'items_retained' => false, 'messages' => [], 'done' => true];
    },
  ];

  return $erasers;
});
