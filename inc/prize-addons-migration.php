<?php
/**
 * Prize Safety & Add-ons — one-time move to the Catalog (docs/adr/0014).
 *
 * Before the Catalog, every prize held its own Safety items and Add-on options as
 * repeater rows. This copies each of them into the Catalog and makes the prize pick
 * them, without changing what customers see:
 *
 *  - Add-on options keep their ID, so placed orders and the "Purchased" lock still match.
 *    They are not merged, even when two prizes each have a "Storage" for £24: merging
 *    would change an ID and lift the lock for someone who already bought it. The admin
 *    can tidy the Catalog afterwards.
 *  - Safety items have no lock, so identical ones (same icon, title and description) are
 *    shared instead of repeated.
 *  - The prize's own title, description and Full bundle price already live in fields
 *    that kept their names, so they carry over as they are.
 *  - The old rows are left in the database untouched.
 *
 * Runs once (flagged in an option), on the first request after the theme is updated. A
 * prize that already has picks is skipped, so running it again changes nothing.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Option flag: set once the move has completed. */
const NERA_PRIZE_ADDON_MIGRATION_FLAG = 'nera_prize_addons_catalog_version';

/** Option used as a lock so two requests do not run the move together. */
const NERA_PRIZE_ADDON_MIGRATION_LOCK = 'nera_prize_addons_catalog_migrating';

/**
 * Read the rows one prize stored in an old per-prize repeater, straight from its meta.
 *
 * The repeaters are no longer registered, so ACF cannot return their rows.
 *
 * @param int      $product_id Product ID.
 * @param string   $name       Repeater name ('safety_items' or 'addons_items').
 * @param string[] $columns    Sub-field names to read.
 * @return list<array<string,string>>
 */
function nera_prize_addons_legacy_rows(int $product_id, string $name, array $columns): array
{
  $count = (int) get_post_meta($product_id, $name, true);
  $rows = [];
  for ($i = 0; $i < $count; $i++) {
    $row = [];
    foreach ($columns as $column) {
      $row[$column] = (string) get_post_meta($product_id, "{$name}_{$i}_{$column}", true);
    }
    $rows[] = $row;
  }

  return $rows;
}

/**
 * Whether a prize has already picked entries (its pick list is a saved, non-empty array).
 *
 * @param int    $product_id Product ID.
 * @param string $meta_key   'safety_item_ids' or 'addon_option_ids'.
 * @return bool
 */
function nera_prize_addons_has_picks(int $product_id, string $meta_key): bool
{
  $value = get_post_meta($product_id, $meta_key, true);

  return is_array($value) ? !empty($value) : '' !== trim((string) $value);
}

/**
 * Move every prize's own Safety items and Add-on options into the Catalog.
 *
 * @param bool $force Run even if it already ran (used by tests; prizes with picks are still skipped).
 * @return array{ran:bool,prizes:int,safety_added:int,addons_added:int}
 */
function nera_prize_addons_migrate_to_catalog(bool $force = false): array
{
  $summary = ['ran' => false, 'prizes' => 0, 'safety_added' => 0, 'addons_added' => 0];

  if (!function_exists('update_field') || !function_exists('get_field')) {
    return $summary;
  }
  if (!$force && '1' === (string) get_option(NERA_PRIZE_ADDON_MIGRATION_FLAG, '')) {
    return $summary;
  }

  // add_option() fails when the row exists, which makes it an atomic lock. A lock older than
  // ten minutes belongs to a request that died, so it is taken over.
  $locked_at = (int) get_option(NERA_PRIZE_ADDON_MIGRATION_LOCK, 0);
  if ($locked_at && time() - $locked_at < 10 * MINUTE_IN_SECONDS) {
    return $summary;
  }
  delete_option(NERA_PRIZE_ADDON_MIGRATION_LOCK);
  if (!add_option(NERA_PRIZE_ADDON_MIGRATION_LOCK, time(), '', 'no')) {
    return $summary;
  }

  global $wpdb;
  $summary['ran'] = true;

  // Catalog rows as they are now, keyed the way ACF wants them written back.
  $safety_rows = [];
  $safety_dirty = false;
  foreach ((array) get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . 'safety_items', 'option') as $row) {
    if (!is_array($row)) {
      continue;
    }
    $safety_dirty = $safety_dirty || '' === sanitize_key((string) ($row['item_id'] ?? '')); // saved before entries had IDs
    $safety_rows[] = [
      'id' => sanitize_key((string) ($row['item_id'] ?? '')) ?: 'si_' . strtolower(wp_generate_password(10, false, false)),
      'icon' => (string) ($row['icon'] ?? ''),
      'title' => (string) ($row['title'] ?? ''),
      'description' => (string) ($row['description'] ?? ''),
      'icon_custom' => (string) ($row['icon_custom'] ?? ''),
    ];
  }
  $addon_rows = [];
  $addons_dirty = false;
  foreach ((array) get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . 'addons_items', 'option') as $row) {
    if (is_array($row)) {
      $addons_dirty = $addons_dirty || '' === sanitize_key((string) ($row['option_id'] ?? ''));
      $addon_rows[] = [
        'id' => sanitize_key((string) ($row['option_id'] ?? '')) ?: 'ao_' . strtolower(wp_generate_password(10, false, false)),
        'title' => (string) ($row['title'] ?? ''),
        'price' => (string) ($row['price'] ?? ''),
        'description' => (string) ($row['description'] ?? ''),
      ];
    }
  }
  $safety_known = count($safety_rows);
  $addons_known = count($addon_rows);

  $product_ids = array_map(
    'intval',
    $wpdb->get_col(
      "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
        WHERE meta_key IN ('safety_items', 'addons_items') AND meta_value > 0
        ORDER BY post_id"
    )
  );

  foreach ($product_ids as $product_id) {
    $touched = false;

    // ---- Safety: share identical items -------------------------------------
    if ((int) get_post_meta($product_id, 'safety_items', true) > 0 && !nera_prize_addons_has_picks($product_id, 'safety_item_ids')) {
      $picked = [];
      foreach (nera_prize_addons_legacy_rows($product_id, 'safety_items', ['icon', 'title', 'description', 'icon_custom']) as $legacy) {
        if ('' === trim($legacy['title'])) {
          continue;
        }
        $found = null;
        foreach ($safety_rows as $row) {
          if ($row['icon'] === $legacy['icon'] && $row['title'] === $legacy['title'] && $row['description'] === $legacy['description'] && $row['icon_custom'] === $legacy['icon_custom']) {
            $found = $row['id'];
            break;
          }
        }
        if (null === $found) {
          $found = 'si_' . strtolower(wp_generate_password(10, false, false));
          $safety_rows[] = ['id' => $found] + $legacy;
        }
        $picked[$found] = $found;
      }
      if ($picked) {
        update_field(NERA_PRIZE_ADDON_PICK_SAFETY, array_values($picked), $product_id);
        $touched = true;
      }
    }

    // ---- Add-ons: keep every ID, add what the Catalog does not have --------
    if ((int) get_post_meta($product_id, 'addons_items', true) > 0 && !nera_prize_addons_has_picks($product_id, 'addon_option_ids')) {
      $picked = [];
      $known = array_column($addon_rows, 'id');
      foreach (nera_prize_addons_legacy_rows($product_id, 'addons_items', ['option_id', 'title', 'price', 'description']) as $legacy) {
        $id = sanitize_key($legacy['option_id']);
        if ('' === $id || '' === trim($legacy['title']) || (float) $legacy['price'] <= 0) {
          continue;
        }
        if (!in_array($id, $known, true)) {
          $addon_rows[] = ['id' => $id, 'title' => $legacy['title'], 'price' => $legacy['price'], 'description' => $legacy['description']];
          $known[] = $id;
        }
        $picked[$id] = $id;
      }
      if ($picked) {
        update_field(NERA_PRIZE_ADDON_PICK_ADDONS, array_values($picked), $product_id);
        $touched = true;
      }
    }

    if ($touched) {
      $summary['prizes']++;
    }
  }

  // ---- Write the Catalog back (only when something changed) -------------------
  $summary['safety_added'] = count($safety_rows) - $safety_known;
  $summary['addons_added'] = count($addon_rows) - $addons_known;
  $g = NERA_PRIZE_ADDON_KEY_GLOBAL;

  if ($summary['safety_added'] > 0 || $safety_dirty) {
    update_field(
      NERA_PRIZE_ADDON_CATALOG_SAFETY,
      array_map(
        static fn($row) => [
          NERA_PRIZE_ADDON_CATALOG_SAFETY_ID => $row['id'],
          $g . 'safety_item_icon' => $row['icon'],
          $g . 'safety_item_title' => $row['title'],
          $g . 'safety_item_description' => $row['description'],
          $g . 'safety_item_icon_custom' => $row['icon_custom'],
        ],
        $safety_rows
      ),
      'option'
    );
  }
  if ($summary['addons_added'] > 0 || $addons_dirty) {
    update_field(
      NERA_PRIZE_ADDON_CATALOG_ADDONS,
      array_map(
        static fn($row) => [
          NERA_PRIZE_ADDON_CATALOG_ADDONS_ID => $row['id'],
          $g . 'addon_title' => $row['title'],
          $g . 'addon_price' => $row['price'],
          $g . 'addon_description' => $row['description'],
        ],
        $addon_rows
      ),
      'option'
    );
  }

  nera_prize_addons_catalog('safety', true);
  update_option(NERA_PRIZE_ADDON_MIGRATION_FLAG, '1', false);
  delete_option(NERA_PRIZE_ADDON_MIGRATION_LOCK);

  return $summary;
}
// After ACF has registered the field groups (their keys are what update_field() needs).
add_action('acf/init', 'nera_prize_addons_migrate_to_catalog', 30);
