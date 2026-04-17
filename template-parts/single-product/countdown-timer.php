<?php
/**
 * Countdown Timer Template Part
 *
 * Large format countdown timer for competition end date.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;
$countdown = isset($args['countdown']) ? $args['countdown'] : [];

if (!$product) {
  return;
}

$product_id = $product->get_id();
$end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);

if (!$end_date_gmt) {
  return;
}

$end_timestamp = strtotime($end_date_gmt) * 1000; // Convert to milliseconds for JS
$is_urgent = isset($countdown['urgent']) && $countdown['urgent'];
$is_expired = isset($countdown['expired']) && $countdown['expired'];

$urgent_class = $is_urgent ? 'countdown--urgent' : '';
?>

<div class="countdown-section">
  <div class="flex items-center justify-between mb-3">
    <span class="text-sm font-semibold text-text-secondary uppercase tracking-wide">
      <?php _e('Competition Ends In', 'nera-competitions'); ?>
    </span>
    <?php if ($is_urgent && !$is_expired): ?>
      <span class="inline-flex items-center gap-1 text-danger text-xs font-bold animate-pulse">
        <span class="material-symbols-outlined text-sm">schedule</span>
        <?php _e('Ending Soon!', 'nera-competitions'); ?>
      </span>
    <?php endif; ?>
  </div>

  <div class="countdown-timer grid grid-cols-4 gap-2 lg:gap-3 <?php echo esc_attr(
    $urgent_class,
  ); ?>"
    x-data="countdown('<?php echo esc_attr($end_timestamp); ?>')">
    <!-- Days -->
    <div class="countdown-item text-center">
      <div
        class="countdown-value-wrapper bg-gray-100 rounded-xl p-3 lg:p-4 <?php echo $is_urgent
          ? 'bg-danger-bg'
          : ''; ?>">
        <span
          class="countdown-value block text-2xl lg:text-3xl font-bold text-text-primary <?php echo $is_urgent
            ? 'text-danger'
            : ''; ?>"
          x-text="days">
          00
        </span>
      </div>
      <span class="countdown-label block mt-1 text-xs font-medium text-text-secondary uppercase">
        <?php _e('Days', 'nera-competitions'); ?>
      </span>
    </div>

    <!-- Hours -->
    <div class="countdown-item text-center">
      <div
        class="countdown-value-wrapper bg-gray-100 rounded-xl p-3 lg:p-4 <?php echo $is_urgent
          ? 'bg-danger-bg'
          : ''; ?>">
        <span
          class="countdown-value block text-2xl lg:text-3xl font-bold text-text-primary <?php echo $is_urgent
            ? 'text-danger'
            : ''; ?>"
          x-text="hours">
          00
        </span>
      </div>
      <span class="countdown-label block mt-1 text-xs font-medium text-text-secondary uppercase">
        <?php _e('Hours', 'nera-competitions'); ?>
      </span>
    </div>

    <!-- Minutes -->
    <div class="countdown-item text-center">
      <div
        class="countdown-value-wrapper bg-gray-100 rounded-xl p-3 lg:p-4 <?php echo $is_urgent
          ? 'bg-danger-bg'
          : ''; ?>">
        <span
          class="countdown-value block text-2xl lg:text-3xl font-bold text-text-primary <?php echo $is_urgent
            ? 'text-danger'
            : ''; ?>"
          x-text="minutes">
          00
        </span>
      </div>
      <span class="countdown-label block mt-1 text-xs font-medium text-text-secondary uppercase">
        <?php _e('Mins', 'nera-competitions'); ?>
      </span>
    </div>

    <!-- Seconds -->
    <div class="countdown-item text-center">
      <div
        class="countdown-value-wrapper bg-gray-100 rounded-xl p-3 lg:p-4 <?php echo $is_urgent
          ? 'bg-danger-bg'
          : ''; ?>">
        <span
          class="countdown-value block text-2xl lg:text-3xl font-bold text-text-primary <?php echo $is_urgent
            ? 'text-danger'
            : ''; ?>"
          x-text="seconds">
          00
        </span>
      </div>
      <span class="countdown-label block mt-1 text-xs font-medium text-text-secondary uppercase">
        <?php _e('Secs', 'nera-competitions'); ?>
      </span>
    </div>
  </div>
</div>