<?php
/**
 * Single product: Instant Win prizes block below the hero grid (public).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Echo the Instant Win prizes section below the hero grid.
 *
 * Plugins (e.g. Nera Instant Win Threshold) may replace the full markup via
 * {@see 'nera_competitions_instant_win_prizes_section_html'}; when the filter
 * returns null, the theme renders its template part as before.
 *
 * @param WC_Product|null $product Single product in context.
 */
function nera_competitions_render_instant_win_prizes_section($product)
{
  if (!$product instanceof WC_Product) {
    return;
  }

  /**
   * Full HTML for the instant-win prizes section (comment + section + wrapper).
   * Return null to use the theme default (template part).
   *
   * @param string|null   $html    Default null.
   * @param WC_Product    $product Current product.
   */
  $html = apply_filters('nera_competitions_instant_win_prizes_section_html', null, $product);
  if (null !== $html) {
    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filter returns trusted markup.
    return;
  }

  ?>
    <!-- Instant Win prizes: own section below hero grid (avoids WC flex/float fighting inner flex-col) -->
    <section class="pb-8 lg:pb-10" aria-label="<?php esc_attr_e('Instant win prizes', 'nera-competitions'); ?>">
      <div class="max-w-7xl mx-auto w-full min-w-0 px-4 lg:px-0">
        <?php get_template_part('template-parts/single-product/instant-wins-section', null, [
          'product' => $product,
        ]); ?>
      </div>
    </section>
  <?php
}
