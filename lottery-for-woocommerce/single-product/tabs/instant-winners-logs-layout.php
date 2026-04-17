<?php
/**
 * Instant Winners Logs Layout - Theme Override
 *
 * Modern card-based layout with styled summary badges.
 * Overrides: lottery-for-woocommerce/templates/single-product/tabs/instant-winners-logs-layout.php
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit(); // Exit if accessed directly.
} ?>

<div class="instant-wins-wrapper">
	<!-- Summary Stats -->
	<div class="instant-wins-stats mb-6 flex flex-wrap gap-4">
		<!-- Available Prizes Badge -->
		<div class="stat-badge flex items-center gap-3 bg-success-bg border border-success-border rounded-xl px-5 py-3">
			<div class="w-10 h-10 rounded-full bg-success-bg flex items-center justify-center">
				<span class="material-symbols-outlined text-success text-xl">card_giftcard</span>
			</div>
			<div>
				<p class="text-xs font-semibold text-success uppercase tracking-wide mb-0.5">
					<?php echo esc_html(lty_get_instant_winner_available_prices_count_label()); ?>
				</p>
				<p class="text-2xl font-bold text-success-text mb-0">
					<?php echo absint($product->get_instant_winner_available_prizes_count()); ?>
				</p>
			</div>
		</div>

		<!-- Won Prizes Badge -->
		<div class="stat-badge flex items-center gap-3 bg-info-bg border border-info-border rounded-xl px-5 py-3">
			<div class="w-10 h-10 rounded-full bg-info-bg flex items-center justify-center">
				<span class="material-symbols-outlined text-primary text-xl">emoji_events</span>
			</div>
			<div>
				<p class="text-xs font-semibold text-primary uppercase tracking-wide mb-0.5">
					<?php echo esc_html(lty_get_instant_winner_won_prices_count_label()); ?>
				</p>
				<p class="text-2xl font-bold text-info-text mb-0">
					<?php echo absint($product->get_instant_winner_won_prizes_count()); ?>
				</p>
			</div>
		</div>
	</div>

	<!-- Prizes Grid -->
	<?php if (lty_check_is_array($post_ids)):
   lty_get_template('single-product/tabs/instant-winners-logs.php', [
     'post_ids' => $post_ids,
     'product' => $product,
     'columns' => $columns,
     'pagination' => $pagination,
   ]);
 else:
    ?>
		<div class="no-prizes-message bg-gray-50 border border-gray-200 rounded-2xl p-8 text-center">
			<span class="material-symbols-outlined text-gray-400 text-5xl mb-3">inbox</span>
			<p class="text-text-secondary mb-0">
				<?php _e('No instant win prizes configured yet.', 'nera-competitions'); ?>
			</p>
		</div>
	<?php
 endif; ?>
</div>
