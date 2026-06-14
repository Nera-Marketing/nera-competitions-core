<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$confetti_colors = array( '#f5c842', '#c8940a', '#ff6b6b', '#4ecdc4', '#a78bfa', '#34d399', '#fb923c', '#f472b6' );

$win_heading    = get_field( 'lty_rs_win_heading', 'option' ) ?: __( "You've won!", 'lty-result-screens' );
$win_email_note = get_field( 'lty_rs_win_email_note', 'option' ) ?: __( 'A confirmation email is on its way to you.', 'lty-result-screens' );
$win_button     = get_field( 'lty_rs_win_button', 'option' ) ?: __( 'Claim my prize!', 'lty-result-screens' );
?>
<div class="lty-rs-overlay flex items-center justify-center p-4 bg-[rgba(8,8,18,0.88)] backdrop-blur-md"
     role="dialog" aria-modal="true" aria-labelledby="lty-rs-win-heading">

	<div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-[var(--lty-rs-card-radius)] bg-surface text-center px-9 py-11 shadow-2xl border-t-4 border-[var(--lty-rs-win-accent)] animate-rs-enter">

		<!-- Confetti -->
		<div class="absolute inset-0 pointer-events-none overflow-hidden rounded-[var(--lty-rs-card-radius)] z-0" aria-hidden="true">
			<?php for ( $i = 0; $i < 28; $i++ ) : ?>
				<?php
				$left     = rand( 2, 98 );
				$delay    = rand( 0, 18 ) / 10;
				$duration = rand( 12, 26 ) / 10;
				$size     = rand( 6, 11 );
				$color    = $confetti_colors[ array_rand( $confetti_colors ) ];
				?>
				<span class="lty-rs-confetti-piece" style="left:<?php echo esc_attr( $left ); ?>%;animation-delay:<?php echo esc_attr( $delay ); ?>s;animation-duration:<?php echo esc_attr( $duration ); ?>s;background:<?php echo esc_attr( $color ); ?>;width:<?php echo esc_attr( $size ); ?>px;height:<?php echo esc_attr( $size ); ?>px"></span>
			<?php endfor; ?>
		</div>

		<!-- Content (above confetti) -->
		<div class="relative z-10">

			<button type="button" class="lty-rs-close-x" data-lty-rs-dismiss
				aria-label="<?php esc_attr_e( 'Close', 'lty-result-screens' ); ?>">&times;</button>

			<div class="block text-5xl mb-4 animate-rs-bounce-icon" aria-hidden="true">&#127942;</div>

			<h2 id="lty-rs-win-heading" class="lty-rs-win-heading">
				<?php echo esc_html( $win_heading ); ?>
			</h2>

			<p class="lty-rs-prize-meta" data-lty-rs-meta hidden></p>

			<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5" data-lty-rs-prizes>
			<?php foreach ( $log_ids as $log_id ) : ?>
				<?php $log = lty_get_instant_winner_log( $log_id ); ?>
				<?php if ( ! $log || ! $log->get_id() ) : continue; endif; ?>

				<?php
				$prize_label   = '';
				$wallet_amount = null;
				$prize_type    = $log->get_prize_type();
				if ( 'product' === $prize_type ) {
					$prize_label = $log->get_gift_product_name( false );
				} elseif ( in_array( $prize_type, array( 'wallet', 'woo_wallet' ), true ) ) {
					$wallet_amount = floatval( $log->get_prize_amount() );
				} elseif ( $log->get_prize_message() ) {
					$prize_label = $log->get_prize_message();
				}
				$image_url = $log->get_image_url();
				?>

				<div class="rounded-2xl p-3 bg-[var(--lty-rs-win-bg)] border border-[rgba(200,148,10,0.2)] flex flex-col items-center gap-3 text-center sm:flex-row sm:text-left">

					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>"
							alt="<?php esc_attr_e( 'Prize image', 'lty-result-screens' ); ?>"
							class="lty-rs-prize-img w-16 h-16 object-cover rounded-xl shrink-0 shadow-md" />
					<?php elseif ( null !== $wallet_amount ) : ?>
						<span class="lty-rs-wallet-prize__icon shrink-0" aria-hidden="true">&#128176;</span>
					<?php endif; ?>

					<div class="min-w-0 sm:flex-1">

						<?php if ( null !== $wallet_amount ) : ?>
							<span class="lty-rs-wallet-prize__amount block leading-tight"><?php echo wp_kses_post( wc_price( $wallet_amount ) ); ?></span>
							<span class="lty-rs-wallet-prize__label block"><?php esc_html_e( 'added to your wallet', 'lty-result-screens' ); ?></span>
						<?php elseif ( $prize_label ) : ?>
							<p class="text-sm font-bold text-gray-900 leading-snug m-0">
								<?php echo esc_html( $prize_label ); ?>
							</p>
						<?php endif; ?>

						<?php if ( 'coupon' === $prize_type && $log->get_coupon_code() ) : ?>
							<p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mt-2 mb-1.5">
								<?php esc_html_e( 'Your coupon code:', 'lty-result-screens' ); ?>
							</p>
							<button class="lty-rs-copy-code group relative font-mono text-base font-bold tracking-[0.12em] text-gray-900 border-2 border-dashed border-[var(--lty-rs-win-accent)] rounded-lg px-3 py-1.5 inline-flex items-center gap-2 bg-surface cursor-pointer hover:bg-[var(--lty-rs-win-bg)] transition-colors duration-150"
								data-code="<?php echo esc_attr( $log->get_coupon_code() ); ?>"
								title="<?php esc_attr_e( 'Click to copy', 'lty-result-screens' ); ?>">
								<span class="lty-rs-copy-code__text"><?php echo esc_html( $log->get_coupon_code() ); ?></span>
								<span class="lty-rs-copy-code__icon text-base opacity-50 group-hover:opacity-100 transition-opacity" aria-hidden="true">&#128203;</span>
								<span class="lty-rs-copy-code__confirm hidden absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap">
									<?php esc_html_e( 'Copied!', 'lty-result-screens' ); ?>
								</span>
							</button>
						<?php endif; ?>

					</div>

				</div>

			<?php endforeach; ?>
			</div>

			<div class="lty-rs-pager" data-lty-rs-pager hidden>
				<button type="button" class="lty-rs-pager__btn" data-lty-rs-prev>
					<span aria-hidden="true">&#8592;</span>
					<span><?php esc_html_e( 'Previous', 'lty-result-screens' ); ?></span>
				</button>
				<div class="lty-rs-pager__dots" data-lty-rs-dots></div>
				<button type="button" class="lty-rs-pager__btn" data-lty-rs-next>
					<span><?php esc_html_e( 'Next', 'lty-result-screens' ); ?></span>
					<span aria-hidden="true">&#8594;</span>
				</button>
			</div>

			<p class="text-sm text-gray-400 mt-3.5 mb-6 leading-relaxed">
				<?php echo esc_html( $win_email_note ); ?>
			</p>

			<button class="lty-rs-btn lty-rs-btn-win" data-lty-rs-dismiss>
				<?php echo esc_html( $win_button ); ?>
			</button>

		</div>
	</div>
</div>
