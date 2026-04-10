<?php
/**
 * Instant Winners Logs - Theme Override
 *
 * Wrapper for instant win prizes display (replaces table structure with div).
 * Overrides: lottery-for-woocommerce/templates/single-product/tabs/instant-winners-logs.php
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

defined('ABSPATH') || exit();
// Exit if accessed directly.
?>
<div class="instant-winners-logs-container">
	<?php lty_get_template('single-product/tabs/instant-winners-logs-data.php', [
   'instant_winner_ids' => $post_ids,
   'product' => $product,
   'columns' => $columns,
 ]); ?>

	<?php if ($pagination['page_count'] > 1): ?>
		<!-- Pagination -->
		<div class="instant-wins-pagination mt-8 flex justify-center">
			<div class="pagination-wrapper" data-action_name='lty_instant_winner_logs' data-product_id="<?php echo esc_attr(
     $product->get_id(),
   ); ?>">
				<?php lty_get_template('pagination.php', $pagination); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
