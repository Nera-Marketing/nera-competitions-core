<?php
/**
 * Spin-to-Win Prizes Section - Collapsible prize listing for spin wheel products.
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit();
}

$product = $args['product'] ?? null;

if (!$product) {
  return;
}

$product_id = $product->get_id();

// Gate: only show on products in the spin-to-win category
if (!has_term('spin-to-win', 'product_cat', $product_id)) {
  return;
}

// Endpoint URL for the Vue app to fetch prize data
$prizes_endpoint = rest_url('nera-stw/v1/product/' . $product_id . '/prizes');
?>

<!-- Spin-to-Win Prizes Section (Full Width Below Columns) -->
<div class="spin-to-win-prizes-section w-full">

  <!-- Toggle Button -->
  <button
    id="spin-to-win-prizes-toggle-btn"
    onclick="window.toggleSpinToWinPrizes()"
    class="instant-wins-toggle-shine w-full flex items-center justify-between gap-4 px-6 py-5 rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 bg-gradient-to-br from-primary/10 via-primary/10 to-primary/5 ring-1 ring-primary/30 shadow-[0_4px_14px_0_rgba(0,0,0,0.08)] active:scale-[0.98]"
    aria-expanded="false"
    aria-controls="spin-to-win-prizes-container"
  >
    <!-- Left: Icon + Text -->
    <div class="flex items-center gap-4">
      <div class="relative">
        <div class="absolute inset-0 rounded-full bg-primary/20 blur-md"></div>
        <div class="relative w-14 h-14 rounded-full bg-gradient-to-br from-primary via-primary to-primary-dark flex items-center justify-center shadow-[0_4px_14px_rgba(0,0,0,0.2)]">
          <span class="material-symbols-outlined text-on-primary text-2xl">casino</span>
        </div>
      </div>

      <div class="text-left">
        <h3 class="text-lg font-bold text-text-primary mb-1 tracking-tight">
          <?php _e('Spin To Win Prizes', 'nera-competitions'); ?>
        </h3>
        <p class="text-sm text-text-secondary mb-0">
          <?php _e('View all prizes available on the spin wheel', 'nera-competitions'); ?>
        </p>
      </div>
    </div>

    <!-- Right: Arrow -->
    <div class="flex items-center gap-3">
      <span class="toggle-arrow material-symbols-outlined text-text-secondary">
        expand_more
      </span>
    </div>
  </button>

  <!-- Collapsible Content Container -->
  <div
    id="spin-to-win-prizes-container"
    class="hidden overflow-hidden"
    aria-hidden="true"
  >
    <!-- Vue Mount Point -->
    <div
      id="spin-to-win-prizes-root"
      data-product-id="<?php echo esc_attr($product_id); ?>"
      data-endpoint="<?php echo esc_url($prizes_endpoint); ?>"
    >
      <!-- Loading Skeleton (visible until Vue mounts) -->
      <div class="instant-wins-loading space-y-4 py-4">
        <!-- Aggregate remaining badge skeleton -->
        <div class="flex justify-end mb-4">
          <div class="h-8 w-48 rounded-full bg-[linear-gradient(90deg,#f3f4f6_25%,#e5e7eb_50%,#f3f4f6_75%)] [background-size:200%_100%] animate-[instant-wins-skeleton-shimmer_1.5s_ease-in-out_infinite]"></div>
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
