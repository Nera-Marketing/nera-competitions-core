<?php
/**
 * Prize Safety inside the Lucky Dip dialogs.
 *
 * The free "included with this prize" list, shown in the same places as the Lucky Dip
 * add-ons (lucky-dip-addons.php) and above them. Display only, so it is a compact row of
 * chips rather than the prize page's full list: it must not push the Lucky Dip controls
 * off a small dialog.
 *
 * Renders nothing unless the Add-ons Bundles switch is on and the prize has Safety items.
 *
 * Expected $args keys: product (WC_Product).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$nera_ld_product = $args['product'] ?? null;
if (
  !$nera_ld_product ||
  !function_exists('nera_prize_addons_site_enabled') ||
  !nera_prize_addons_site_enabled() ||
  !function_exists('nera_prize_safety_config')
) {
  return;
}

$nera_ld_safety = nera_prize_safety_config((int) $nera_ld_product->get_id());
if (empty($nera_ld_safety['enabled']) || empty($nera_ld_safety['items'])) {
  return;
}
?>
<section class="nera-ld-safety" aria-label="<?php echo esc_attr($nera_ld_safety['title']); ?>">
  <p class="nera-ld-safety__title">
    <span class="material-symbols-outlined" aria-hidden="true">health_and_safety</span>
    <?php echo esc_html($nera_ld_safety['title']); ?>
  </p>
  <ul class="nera-ld-safety__list" role="list">
    <?php foreach ($nera_ld_safety['items'] as $nera_ld_item): ?>
      <li class="nera-ld-safety__item">
        <span class="material-symbols-outlined" aria-hidden="true"><?php echo esc_html($nera_ld_item['icon']); ?></span>
        <?php echo esc_html($nera_ld_item['title']); ?>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
