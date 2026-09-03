<?php
/**
 * Tools → Nera Orders Status Migration.
 *
 * Hand-driven UI for the one-off migration in backfill-complete-lottery-orders.php.
 *
 * WHY IT IS GATED
 * ---------------
 * The whole page — menu entry, renderer and AJAX endpoint alike — exists only
 * when NERA_ORDERS_STATUS_MIGRATION is defined truthy in wp-config.php. The
 * migration runs once per site after the update; a Tools entry that lingers
 * afterwards is a permanent invitation to re-run something nobody remembers the
 * purpose of. Gating on a constant also means the endpoint cannot be reached on
 * a site that never opted in, rather than merely being hidden from the menu.
 *
 * SCAN BEFORE RUN
 * ---------------
 * Scan walks the identical loop with writes disabled and reports what Run would
 * do. Both call nera_lottery_migration_run_batch(), differing by one flag — a
 * preview that could disagree with the real thing would be worse than none.
 * Rolling this out means running it against a different dataset on every site,
 * and the counts have repeatedly turned out different from what the data
 * suggested.
 *
 * Batches are driven from the browser rather than from cron, so progress is
 * visible and the run does not depend on WP-Cron firing. Closing the tab stops
 * it; the work already committed stands and reopening simply resumes.
 *
 * @see docs/adr/0002-backfill-historical-orders-in-tiers.md
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Menu slug, also the AJAX action name suffix. */
const NERA_ORDERS_MIGRATION_SLUG = 'nera-orders-status-migration';

/** Capability required to see and run the migration. */
const NERA_ORDERS_MIGRATION_CAP = 'manage_woocommerce';

/**
 * Whether the migration UI is switched on for this site.
 *
 * @return bool
 */
function nera_orders_migration_enabled(): bool
{
  return defined('NERA_ORDERS_STATUS_MIGRATION') && NERA_ORDERS_STATUS_MIGRATION;
}

add_action('admin_menu', 'nera_orders_migration_register_page');

/**
 * @return void
 */
function nera_orders_migration_register_page(): void
{
  if (!nera_orders_migration_enabled()) {
    return;
  }

  add_management_page(
    __('Nera Orders Status Migration', 'nera-competitions'),
    __('Nera Orders Status Migration', 'nera-competitions'),
    NERA_ORDERS_MIGRATION_CAP,
    NERA_ORDERS_MIGRATION_SLUG,
    'nera_orders_migration_render_page'
  );
}

/**
 * @return void
 */
function nera_orders_migration_render_page(): void
{
  if (!current_user_can(NERA_ORDERS_MIGRATION_CAP)) {
    wp_die(esc_html__('You do not have permission to run this migration.', 'nera-competitions'));
  }

  $pending = nera_lottery_migration_processing_count();
  $report = (array) get_option(NERA_LOTTERY_MIGRATION_REPORT_OPTION, []);
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('Nera Orders Status Migration', 'nera-competitions'); ?></h1>

    <p style="max-width:44em">
      <?php esc_html_e('Completes competition orders that were fulfilled before automatic completion existed and are still showing as Processing. Orders that are not fully fulfilled are left untouched. Customer emails are not sent — these orders are old.', 'nera-competitions'); ?>
    </p>

    <p style="max-width:44em">
      <strong><?php esc_html_e('Run Scan first.', 'nera-competitions'); ?></strong>
      <?php esc_html_e('It reports exactly what Run would change, without changing anything.', 'nera-competitions'); ?>
    </p>

    <table class="widefat striped" style="max-width:44em;margin-bottom:1em">
      <tbody>
        <tr>
          <th scope="row" style="width:18em"><?php esc_html_e('Orders on Processing now', 'nera-competitions'); ?></th>
          <td><code id="nera-mig-pending"><?php echo esc_html((string) $pending); ?></code></td>
        </tr>
        <?php if (!empty($report['finished_at'])) : ?>
          <tr>
            <th scope="row"><?php esc_html_e('Last run finished', 'nera-competitions'); ?></th>
            <td>
              <code><?php echo esc_html((string) $report['finished_at']); ?></code>
              — <?php echo esc_html(sprintf(
                /* translators: 1: tier 1 count, 2: tier 2 count, 3: untouched count. */
                __('%1$d completed, %2$d completed from recovered records, %3$d left unchanged.', 'nera-competitions'),
                (int) ($report['completed'] ?? 0),
                (int) ($report['forced'] ?? 0),
                (int) ($report['skipped'] ?? 0)
              )); ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <p>
      <button type="button" class="button button-secondary" id="nera-mig-scan">
        <?php esc_html_e('Scan (no changes)', 'nera-competitions'); ?>
      </button>
      <button type="button" class="button button-primary" id="nera-mig-run">
        <?php esc_html_e('Run migration', 'nera-competitions'); ?>
      </button>
      <span id="nera-mig-spinner" class="spinner" style="float:none;margin:0 0 0 .5em"></span>
    </p>

    <div id="nera-mig-progress" style="display:none;max-width:44em">
      <div style="background:#dcdcde;border-radius:3px;height:20px;overflow:hidden">
        <div id="nera-mig-bar" style="background:#2271b1;height:100%;width:0;transition:width .2s"></div>
      </div>
      <p id="nera-mig-status" style="margin:.6em 0 0"></p>
    </div>

    <div id="nera-mig-result" style="display:none;max-width:44em"></div>

    <hr style="margin:2em 0">
    <p style="max-width:44em;color:#646970">
      <?php
      printf(
        /* translators: %s: PHP constant name. */
        esc_html__('This page is only visible because %s is defined in wp-config.php. Remove that line once the migration is finished.', 'nera-competitions'),
        '<code>NERA_ORDERS_STATUS_MIGRATION</code>'
      );
      ?>
    </p>
  </div>

  <script>
  (function () {
    'use strict';

    var ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
    var nonce = <?php echo wp_json_encode(wp_create_nonce('nera_orders_migration')); ?>;
    var totalPending = <?php echo (int) $pending; ?>;

    var els = {
      scan: document.getElementById('nera-mig-scan'),
      run: document.getElementById('nera-mig-run'),
      spinner: document.getElementById('nera-mig-spinner'),
      progress: document.getElementById('nera-mig-progress'),
      bar: document.getElementById('nera-mig-bar'),
      status: document.getElementById('nera-mig-status'),
      result: document.getElementById('nera-mig-result'),
      pending: document.getElementById('nera-mig-pending')
    };

    var busy = false;

    function setBusy(state) {
      busy = state;
      els.scan.disabled = state;
      els.run.disabled = state;
      els.spinner.classList.toggle('is-active', state);
    }

    function notice(type, html) {
      els.result.style.display = '';
      els.result.innerHTML = '<div class="notice notice-' + type + ' inline"><p>' + html + '</p></div>';
    }

    function batch(dry, offset, totals) {
      var body = new URLSearchParams();
      body.append('action', 'nera_orders_migration_batch');
      body.append('nonce', nonce);
      body.append('dry', dry ? '1' : '0');
      body.append('offset', String(offset));

      return fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      }).then(function (r) {
        return r.json();
      }).then(function (json) {
        if (!json || !json.success) {
          throw new Error((json && json.data && json.data.message) || 'Request failed.');
        }

        var d = json.data;
        totals.scanned += d.scanned;
        totals.completed += d.completed;
        totals.forced += d.forced;
        totals.skipped += d.skipped;

        var pct = totalPending > 0 ? Math.min(100, Math.round((totals.scanned / totalPending) * 100)) : 100;
        els.bar.style.width = pct + '%';
        els.status.textContent = (dry ? 'Scanning' : 'Running') + ': ' + totals.scanned + ' inspected, ' +
          (totals.completed + totals.forced) + ' to complete, ' + totals.skipped + ' unchanged.';

        if (d.done) {
          return totals;
        }

        return batch(dry, d.offset, totals);
      });
    }

    function start(dry) {
      if (busy) { return; }
      setBusy(true);
      els.result.style.display = 'none';
      els.progress.style.display = '';
      els.bar.style.width = '0';
      els.status.textContent = dry ? 'Scanning…' : 'Running…';

      batch(dry, 0, { scanned: 0, completed: 0, forced: 0, skipped: 0 })
        .then(function (t) {
          var verb = dry ? 'would be completed' : 'completed';
          notice(
            dry ? 'info' : 'success',
            '<strong>' + (dry ? 'Scan complete.' : 'Migration complete.') + '</strong> ' +
            t.scanned + ' orders inspected. ' +
            '<strong>' + t.completed + '</strong> ' + verb + ' normally, ' +
            '<strong>' + t.forced + '</strong> ' + verb + ' from recovered ticket records, ' +
            '<strong>' + t.skipped + '</strong> left unchanged.' +
            (dry ? ' Nothing was modified.' : ' You can now remove NERA_ORDERS_STATUS_MIGRATION from wp-config.php.')
          );
          if (!dry) {
            totalPending = Math.max(0, totalPending - (t.completed + t.forced));
            els.pending.textContent = String(totalPending);
          }
        })
        .catch(function (e) {
          notice('error', 'Failed: ' + e.message);
        })
        .then(function () {
          setBusy(false);
        });
    }

    els.scan.addEventListener('click', function () { start(true); });
    els.run.addEventListener('click', function () {
      if (window.confirm('This will change order statuses to Completed. Run Scan first if you have not. Continue?')) {
        start(false);
      }
    });
  })();
  </script>
  <?php
}

add_action('wp_ajax_nera_orders_migration_batch', 'nera_orders_migration_ajax_batch');

/**
 * Process one batch and report progress.
 *
 * @return void
 */
function nera_orders_migration_ajax_batch(): void
{
  check_ajax_referer('nera_orders_migration', 'nonce');

  if (!nera_orders_migration_enabled()) {
    wp_send_json_error(['message' => __('Migration is not enabled on this site.', 'nera-competitions')], 400);
  }

  if (!current_user_can(NERA_ORDERS_MIGRATION_CAP)) {
    wp_send_json_error(['message' => __('You do not have permission to run this migration.', 'nera-competitions')], 403);
  }

  if (!function_exists('nera_lottery_migration_run_batch')) {
    wp_send_json_error(['message' => __('Migration engine is unavailable.', 'nera-competitions')], 500);
  }

  $dry = isset($_POST['dry']) && '1' === sanitize_text_field(wp_unslash($_POST['dry']));
  $offset = isset($_POST['offset']) ? absint(wp_unslash($_POST['offset'])) : 0;

  $result = nera_lottery_migration_run_batch($offset, $dry);

  // Record the outcome of a real run so the page can report it after a reload.
  // Accumulated on every batch, not just the last: the final batch is the one
  // that found nothing left, so its own counters are all zero.
  if (!$dry) {
    $report = 0 === $offset
      ? ['completed' => 0, 'forced' => 0, 'skipped' => 0]
      : (array) get_option(NERA_LOTTERY_MIGRATION_REPORT_OPTION, []);

    $report['completed'] = (int) ($report['completed'] ?? 0) + $result['completed'];
    $report['forced'] = (int) ($report['forced'] ?? 0) + $result['forced'];
    $report['skipped'] = (int) ($report['skipped'] ?? 0) + $result['skipped'];

    if ($result['done']) {
      $report['finished_at'] = gmdate('c');
    }

    update_option(NERA_LOTTERY_MIGRATION_REPORT_OPTION, $report, false);
  }

  wp_send_json_success($result);
}
