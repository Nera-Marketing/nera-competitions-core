<?php
/**
 * Purchase Card — Orchestrator
 * Renders the correct outer wrapper and delegates inner content to partials.
 *
 * section = 'full'   (default) — one unified card (layout Default)
 * section = 'header'           — title/countdown/tickets only, above gallery (details_first)
 * section = 'body'             — price/form/badges only, below gallery (details_first)
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$product        = $args['product'] ?? null;
$countdown      = $args['countdown'] ?? [];
$sold_tickets   = $args['sold_tickets'] ?? 0;
$max_tickets    = $args['max_tickets'] ?? 0;
$remaining      = $args['remaining'] ?? 0;
$progress       = $args['progress'] ?? 0;
$is_low_stock   = $args['is_low_stock'] ?? false;
$price          = $args['price'] ?? 0;
$lottery_data   = $args['lottery_data'] ?? [];
$has_qa         = $args['has_qa'] ?? false;
$questions      = $args['questions'] ?? [];
$qa_can_display = $args['qa_can_display'] ?? false;
$cart_answer_id = $args['cart_answer_id'] ?? '';
$is_expired     = $args['is_expired'] ?? false;

// section: 'full' (default) | 'header' (title/countdown/tickets only) | 'body' (price/qty/form only)
$section = $args['section'] ?? 'full';

$unified_mobile = !empty($args['unified_mobile']);
$seam_pb = $unified_mobile ? 'ncs-unified-mobile-seam-bottom' : '';
$seam_pt = $unified_mobile ? 'ncs-unified-mobile-seam-top pt-6' : 'pt-6';
$unified_header_class = $unified_mobile ? ' ncs-unified-mobile-seg ncs-unified-mobile-seg--header' : '';
$unified_body_class   = $unified_mobile ? ' ncs-unified-mobile-seg ncs-unified-mobile-seg--body' : '';

if (!$product) {
  return;
}

$product_id       = $product->get_id();
$is_sold_out      = function_exists('nera_lottery_product_is_sold_out')
  ? nera_lottery_product_is_sold_out($product, $lottery_data)
  : false;
$is_manual_ticket = method_exists($product, 'is_manual_ticket') ? $product->is_manual_ticket() : false;

$inner_args = [
  'product'         => $product,
  'countdown'       => $countdown,
  'sold_tickets'    => $sold_tickets,
  'max_tickets'     => $max_tickets,
  'remaining'       => $remaining,
  'progress'        => $progress,
  'is_low_stock'    => $is_low_stock,
  'price'           => $price,
  'lottery_data'    => $lottery_data,
  'has_qa'          => $has_qa,
  'questions'       => $questions,
  'qa_can_display'  => $qa_can_display,
  'cart_answer_id'  => $cart_answer_id,
  'is_expired'      => $is_expired,
  'is_sold_out'     => $is_sold_out,
  'is_manual_ticket' => $is_manual_ticket,
  'seam_pb'         => $seam_pb,
  'seam_pt'         => $seam_pt,
];
?>

<?php if ($section === 'header'): ?>

<div class="order-first lg:order-none bg-surface rounded-2xl max-lg:rounded-b-none lg:rounded-t-2xl lg:rounded-b-none max-lg:border-b-0 lg:border-b-0 max-lg:shadow-none lg:shadow-none shadow-xl border border-gray-100 overflow-hidden<?php echo esc_attr($unified_header_class); ?>">
  <?php get_template_part('template-parts/single-product/purchase-card-header-inner', null, $inner_args); ?>
</div>

<?php elseif ($section === 'body'): ?>

<div class="order-2 lg:order-none bg-surface rounded-2xl max-lg:rounded-t-none lg:rounded-t-none lg:rounded-b-2xl max-lg:border-t-0 shadow-xl border border-gray-100 border-t-0 overflow-hidden<?php echo esc_attr($unified_body_class); ?>">
  <?php get_template_part('template-parts/single-product/purchase-card-body-inner', null, $inner_args); ?>
</div>

<?php else: ?>

<div class="bg-surface rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
  <?php get_template_part('template-parts/single-product/purchase-card-header-inner', null, $inner_args); ?>
  <?php get_template_part('template-parts/single-product/purchase-card-body-inner', null, $inner_args); ?>
</div>

<?php endif; ?>
