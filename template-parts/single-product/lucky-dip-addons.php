<?php
/**
 * Prize add-ons inside the Lucky Dip dialogs.
 *
 * A compact copy of the prize page's Add-ons block, for the places a Lucky Dip runs:
 * the Lucky Dip box (before the click, "add directly" method) and the Lucky Dip popups
 * (between the generated tickets and the quantity / gate note). It carries no Alpine:
 * the popups are injected by Lottery for WooCommerce after Alpine has started, and
 * Alpine does not pick up markup added later. assets/js/lucky-dip-addons.js owns the
 * behaviour and keeps every copy on the page in step, and adds the chosen options to
 * the Lucky Dip requests (see nera_prize_addons_sync_from_lucky_dip()).
 *
 * Renders nothing unless the Add-ons Bundles switch is on and the prize has add-ons.
 *
 * Expected $args keys: product (WC_Product).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$nera_ld_product = $args['product'] ?? null;
if (!$nera_ld_product || !function_exists('nera_prize_addons_site_enabled') || !nera_prize_addons_site_enabled()) {
  return;
}

if (!function_exists('Nera\Components\PrizeAddOns\get_data')) {
  require_once get_template_directory() . '/Components/blocks/PrizeAddOns/index.php';
}
$nera_ld = \Nera\Components\PrizeAddOns\get_data(['product' => $nera_ld_product]);

if (empty($nera_ld['enabled']) || empty($nera_ld['options'])) {
  return;
}
?>
<section class="nera-ld-addons"
  data-nera-ld-addons
  data-product-id="<?php echo esc_attr($nera_ld['product_id']); ?>"
  data-config="<?php echo esc_attr($nera_ld['config_json']); ?>">
  <div class="nera-ld-addons__head">
    <span class="nera-ld-addons__eyebrow"><?php echo esc_html($nera_ld['i18n']['eyebrow']); ?></span>
    <span class="nera-ld-addons__head-end">
      <span class="nera-ld-addons__total" data-nera-ld-total aria-live="polite"><?php echo esc_html($nera_ld['initial']['total_text']); ?></span>
      <button type="button" class="nera-ld-addons__toggle" data-nera-ld-toggle aria-expanded="true"
        aria-label="<?php echo esc_attr(nera_prize_addons_label('hide')); ?>"
        data-label-hide="<?php echo esc_attr(nera_prize_addons_label('hide')); ?>"
        data-label-show="<?php echo esc_attr(nera_prize_addons_label('show')); ?>">
        <span class="material-symbols-outlined" aria-hidden="true">expand_less</span>
      </button>
    </span>
  </div>

  <?php // Shown in place of the list while collapsed ("2 of 5 selected"); filled in by lucky-dip-addons.js. ?>
  <p class="nera-ld-addons__summary" data-nera-ld-summary hidden></p>

  <div class="nera-ld-addons__body" data-nera-ld-body>
  <ul class="nera-ld-addons__list" role="list">
    <?php foreach ($nera_ld['options'] as $nera_ld_option): ?>
      <li class="nera-ld-addons__item<?php echo $nera_ld_option['purchased'] ? ' nera-ld-addons__item--purchased' : ''; ?>">
        <?php if ($nera_ld_option['purchased']): ?>
          <span class="nera-ld-addons__name"><?php echo esc_html($nera_ld_option['title']); ?></span>
          <span class="nera-ld-addons__purchased">
            <span class="material-symbols-outlined" aria-hidden="true">check</span>
            <?php echo esc_html($nera_ld['i18n']['purchased']); ?>
          </span>
        <?php else: ?>
          <label class="nera-ld-addons__label">
            <input type="checkbox" name="nera_ld_addon_ids[]" class="nera-ld-addons__check"
              value="<?php echo esc_attr($nera_ld_option['id']); ?>"
              <?php checked($nera_ld_option['selected']); ?>>
            <span class="nera-ld-addons__name"><?php echo esc_html($nera_ld_option['title']); ?></span>
            <?php if (!empty($nera_ld['terms_enabled'])): ?>
              <span class="nera-ld-addons__peryear"><?php echo wp_kses_post($nera_ld_option['price_html']); ?> <?php echo esc_html($nera_ld['i18n']['per_year'] ?? ''); ?></span>
              <?php
              // A plain number input, not Alpine (see this file's own docblock): lucky-dip-addons.js
              // reads/writes it directly via the data-nera-ld-years attribute.
              ?>
              <input type="number" class="nera-ld-addons__years-input"
                data-nera-ld-years="<?php echo esc_attr($nera_ld_option['id']); ?>"
                min="1" max="<?php echo esc_attr($nera_ld['max_term']); ?>" step="1"
                value="<?php echo esc_attr($nera_ld_option['years']); ?>"
                <?php disabled(empty($nera_ld_option['selected'])); ?>
                aria-label="<?php echo esc_attr(($nera_ld['i18n']['years'] ?? 'Years') . ' — ' . $nera_ld_option['title']); ?>">
            <?php endif; ?>
            <span class="nera-ld-addons__price" data-nera-ld-amount="<?php echo esc_attr($nera_ld_option['id']); ?>"><?php echo wp_kses_post($nera_ld_option['price_html']); ?></span>
          </label>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if (!empty($nera_ld['bundle']['available'])): ?>
    <button type="button" class="nera-ld-addons__bundle" data-nera-ld-select-all
      data-label-select="<?php echo esc_attr($nera_ld['i18n']['select_all']); ?>"
      data-label-clear="<?php echo esc_attr(nera_prize_addons_label('clear')); ?>">
      <span class="nera-ld-addons__bundle-text"><?php echo wp_kses_post($nera_ld['bundle']['offer_html']); ?></span>
    </button>
  <?php endif; ?>

  <?php if ('' !== $nera_ld['notice']): ?>
    <p class="nera-ld-addons__notice"><?php echo esc_html($nera_ld['notice']); ?></p>
  <?php endif; ?>
  </div>

  <p class="nera-ld-addons__note"><?php echo esc_html($nera_ld['i18n']['non_refundable']); ?></p>
</section>
