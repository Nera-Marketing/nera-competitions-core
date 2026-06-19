<?php
/**
 * Purchase Card — Header Inner partial
 * Renders: ProductTitle + CountdownTimer + TicketsProgress
 * Used by both section=header (details_first layout) and section=full (default layout).
 *
 * @package Nera_Competitions
 *
 * Expected $args keys (forwarded from purchase-card.php):
 *   product, sold_tickets, max_tickets, remaining, progress, is_low_stock,
 *   countdown, is_expired, is_sold_out, seam_pb
 */

if (!defined('ABSPATH')) {
  exit();
}

$product        = $args['product'] ?? null;
$sold_tickets   = $args['sold_tickets'] ?? 0;
$max_tickets    = $args['max_tickets'] ?? 0;
$remaining      = $args['remaining'] ?? 0;
$progress       = $args['progress'] ?? 0;
$is_low_stock   = $args['is_low_stock'] ?? false;
$countdown      = $args['countdown'] ?? [];
$is_expired     = $args['is_expired'] ?? false;
$is_sold_out    = $args['is_sold_out'] ?? false;
$seam_pb        = $args['seam_pb'] ?? '';

if (!$product) {
  return;
}
?>

<?php if ($is_sold_out): ?>

  <div class="p-6 pb-4">
    <?php if (function_exists('nera_render_component')) { nera_render_component('ProductTitle', ['name' => $product->get_name(), 'is_sold_out' => true]); } ?>
  </div>

  <div class="px-6 pb-6 <?php echo esc_attr($seam_pb); ?>">
    <?php if (function_exists('nera_render_component')) { nera_render_component('TicketsProgress', [
      'sold'         => $sold_tickets,
      'max'          => $max_tickets,
      'progress'     => $progress,
      'remaining'    => $remaining,
      'is_low_stock' => $is_low_stock,
    ]); } ?>
  </div>

<?php else: ?>

  <div class="p-6 pb-4">
    <?php if (function_exists('nera_render_component')) { nera_render_component('ProductTitle', ['name' => $product->get_name(), 'is_sold_out' => false]); } ?>
  </div>

  <div class="px-6 pb-6">
    <?php
    $countdown_date_for_js = '';
    if (method_exists($product, 'get_countdown_timer_enddate')) {
      $countdown_date_for_js = $product->get_countdown_timer_enddate();
    }
    ?>
    <?php if (function_exists('nera_render_component')) { nera_render_component('CountdownTimer', [
      'countdown_date' => $countdown_date_for_js,
      'countdown'      => $countdown,
      'is_expired'     => $is_expired,
    ]); } ?>
  </div>

  <div class="px-6 pb-6 <?php echo esc_attr($seam_pb); ?>">
    <?php if (function_exists('nera_render_component')) { nera_render_component('TicketsProgress', [
      'sold'         => $sold_tickets,
      'max'          => $max_tickets,
      'progress'     => $progress,
      'remaining'    => $remaining,
      'is_low_stock' => $is_low_stock,
    ]); } ?>
  </div>

<?php endif; ?>
