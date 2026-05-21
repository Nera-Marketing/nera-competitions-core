<?php
/**
 * Single product: Spin-to-Win prizes block below the hero grid (public).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Echo the Spin-to-Win prizes section below the hero grid.
 *
 * @param WC_Product|null $product Single product in context.
 */
function nera_competitions_render_spin_to_win_prizes_section($product)
{
  if (!$product instanceof WC_Product) {
    return;
  }

  ob_start();
  get_template_part('template-parts/single-product/spin-to-win-prizes-section', null, [
    'product' => $product,
  ]);
  $inner = ob_get_clean();

  if ('' === trim((string) $inner)) {
    return;
  }
  ?>
    <!-- Spin-to-Win prizes: own section below hero grid -->
    <section aria-label="<?php esc_attr_e('Spin To Win prizes', 'nera-competitions'); ?>">
      <div class="w-full min-w-0 px-4 lg:px-0">
        <?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted internal template output. ?>
      </div>
    </section>
  <?php
}
