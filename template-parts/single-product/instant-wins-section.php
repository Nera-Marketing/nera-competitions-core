<?php
/**
 * Instant Wins Section - Premium Dark Toggle with Collapsible Display
 *
 * Full-width section below product columns with dark gradient toggle button.
 * Shows all instant win prizes in an expandable/collapsible container.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly.
}

$product = $args['product'] ?? null;

if (!$product) {
  return;
}

$product_id = $product->get_id();

// Check if product has instant wins
$has_instant_wins = false;
$available_count = 0;
$won_count = 0;

if (
  function_exists('lty_is_lottery_product') &&
  lty_is_lottery_product($product) &&
  method_exists($product, 'is_instant_winner') &&
  $product->is_instant_winner()
) {
  $has_instant_wins = true;

  // Get prize counts
  if (method_exists($product, 'get_instant_winner_available_prizes_count')) {
    $available_count = absint($product->get_instant_winner_available_prizes_count());
  }
  if (method_exists($product, 'get_instant_winner_won_prizes_count')) {
    $won_count = absint($product->get_instant_winner_won_prizes_count());
  }
}

// Don't show section if no instant wins configured
if (!$has_instant_wins) {
  return;
}

$total_prizes = $available_count + $won_count;
?>

<!-- Instant Wins Section (Full Width Below Columns; width/padding from parent container) -->
<div class="instant-wins-section mt-10 w-full">

  <!-- Toggle Button — Premium Light Card (frontend-design: playful/premium) -->
  <button
    id="instant-wins-toggle-btn"
    onclick="window.toggleInstantWins()"
    class="instant-wins-toggle-shine w-full flex items-center justify-between gap-4 px-6 py-5 rounded-2xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 bg-gradient-to-br from-amber-50 via-yellow-50 to-amber-100/90 ring-1 ring-amber-200/60 shadow-[0_4px_14px_0_rgba(251,191,36,0.15)] active:scale-[0.98] animate-[instant-wins-button-glow_5s_ease-in-out_infinite]"
    aria-expanded="false"
    aria-controls="instant-wins-container"
  >
    <!-- Left: Icon + Text -->
    <div class="flex items-center gap-4">
      <!-- Star icon with glow ring -->
      <div class="relative">
        <div class="absolute inset-0 rounded-full bg-amber-200/50 blur-md"></div>
        <div class="relative w-14 h-14 rounded-full bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 flex items-center justify-center shadow-[0_4px_14px_rgba(245,158,11,0.4)]">
          <span class="material-symbols-outlined text-white text-2xl">stars</span>
        </div>
      </div>

      <div class="text-left">
        <h3 class="text-lg font-bold text-gray-900 mb-1 tracking-tight">
          <?php _e('Instant Win Prizes', 'nera-competitions'); ?>
        </h3>
        <p class="text-sm text-gray-600 mb-0">
          <?php printf(
            _n('%s prize available', '%s prizes available', $total_prizes, 'nera-competitions'),
            '<span class="font-semibold text-amber-600">' . $total_prizes . '</span>',
          ); ?>
        </p>
      </div>
    </div>

    <!-- Right: Badges + Arrow -->
    <div class="flex items-center gap-3">
      <!-- Badge: Available & Won -->
      <div class="hidden sm:flex items-center gap-2">
        <?php if ($available_count > 0): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface/90 border border-amber-200/80 text-emerald-600 rounded-full text-xs font-semibold shadow-sm">
            <span class="relative flex h-2 w-2">
              <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
              <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            <?php echo $available_count; ?> <?php _e('Available', 'nera-competitions'); ?>
          </span>
        <?php endif; ?>

        <?php if ($won_count > 0): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface/90 border border-amber-200/80 text-amber-600 rounded-full text-xs font-semibold shadow-sm">
            <span class="material-symbols-outlined text-sm">emoji_events</span>
            <?php echo $won_count; ?> <?php _e('Won', 'nera-competitions'); ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- Expand/Collapse Arrow -->
      <span class="toggle-arrow material-symbols-outlined text-gray-600">
        expand_more
      </span>
    </div>
  </button>

  <!-- Collapsible Content Container -->
  <div
    id="instant-wins-container"
    class="instant-wins-content hidden overflow-hidden"
    aria-hidden="true"
  >
    <!-- React Mount Point -->
    <div
      id="instant-wins-root"
      data-product-id="<?php echo esc_attr($product_id); ?>"
      class="mt-6"
    >
      <!-- Loading Skeleton (visible until React mounts) -->
      <div class="instant-wins-loading space-y-4">
        <!-- Stats skeleton -->
        <div class="rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 p-6">
          <div class="flex items-center justify-around gap-4">
            <div class="flex-1 space-y-3">
              <div class="h-3 w-20 rounded-lg bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
              <div class="h-8 w-16 rounded-lg bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
            </div>
            <div class="w-px h-12 bg-gray-200"></div>
            <div class="flex-1 space-y-3">
              <div class="h-3 w-20 rounded-lg bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
              <div class="h-8 w-16 rounded-lg bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
            </div>
          </div>
        </div>
        <!-- Card skeletons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <?php for ($i = 0; $i < 2; $i++): ?>
            <div class="rounded-2xl bg-surface border border-gray-100 p-5">
              <div class="flex items-center gap-4">
                <div class="w-28 h-28 shrink-0 rounded-xl bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
                <div class="flex-1 space-y-3">
                  <div class="h-5 w-3/4 rounded-lg bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
                  <div class="h-4 w-1/2 rounded-lg bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
                  <div class="h-1.5 w-full rounded-full bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
                </div>
              </div>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
</div>
