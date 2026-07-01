<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$draw_heading   = get_field( 'lty_rs_draw_heading', 'option' ) ?: __( "You're in the draw!", 'lty-result-screens' );
$draw_subtext   = get_field( 'lty_rs_draw_subtext', 'option' ) ?: __( "Your entry is confirmed \xe2\x80\x94 fingers crossed!", 'lty-result-screens' );
$draw_good_luck = get_field( 'lty_rs_draw_good_luck', 'option' ) ?: __( 'Good luck!', 'lty-result-screens' );
$draw_button    = get_field( 'lty_rs_draw_button', 'option' ) ?: __( 'Got it!', 'lty-result-screens' );

$end_date  = $product->get_lty_end_date();
$draw_date = '';

if ( $end_date ) {
	$timestamp = is_numeric( $end_date )
		? (int) $end_date
		: strtotime( $end_date );

	if ( $timestamp ) {
		$draw_date = date_i18n( get_option( 'date_format' ), $timestamp );
	}
}
?>
<div class="lty-rs-overlay flex items-center justify-center p-4 bg-[rgba(8,8,18,0.88)] backdrop-blur-md"
     role="dialog" aria-modal="true" aria-labelledby="lty-rs-draw-heading">

	<div class="relative w-full max-w-[520px] max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-[var(--lty-rs-card-radius)] bg-[var(--lty-rs-draw-bg)] text-center px-9 py-11 shadow-2xl border-t-4 border-[var(--lty-rs-draw-accent)] animate-rs-enter">

		<div class="block text-5xl mb-4" aria-hidden="true">&#127881;</div>

		<h2 id="lty-rs-draw-heading" class="text-[clamp(1.5rem,4vw,2rem)] font-extrabold tracking-tight text-[var(--lty-rs-draw-accent)] mb-3.5 leading-tight">
			<?php echo esc_html( $draw_heading ); ?>
		</h2>

		<p class="text-base text-[#5a5a6a] leading-relaxed mb-4">
			<?php echo esc_html( $draw_subtext ); ?>
		</p>

		<?php if ( $draw_date ) : ?>
			<p class="inline-flex items-center gap-1.5 bg-[rgba(79,70,229,0.08)] border border-[rgba(79,70,229,0.18)] rounded-full px-3.5 py-1.5 text-sm font-semibold text-[var(--lty-rs-draw-accent)] mb-3.5">
				<span aria-hidden="true">&#128197;</span>
				<?php
				printf(
					/* translators: %s: formatted draw date */
					esc_html__( 'Draw: %s', 'lty-result-screens' ),
					esc_html( $draw_date )
				);
				?>
			</p>
		<?php endif; ?>

		<p class="text-lg font-bold text-[var(--lty-rs-draw-accent)] mb-7">
			<?php echo esc_html( $draw_good_luck ); ?>
			<span aria-hidden="true">&#129310;</span>
		</p>

		<button class="lty-rs-btn lty-rs-btn-draw" data-lty-rs-dismiss>
			<?php echo esc_html( $draw_button ); ?>
		</button>

		<?php if ( ! empty( $spin_links ) ) : ?>
		<div class="lty-rs-spin-banner">
			<div class="lty-rs-spin-banner__eyebrow">
				<span class="lty-rs-spin-wheel" aria-hidden="true"></span>
				<?php echo esc_html( $spin_eyebrow_text ); ?>
			</div>
			<?php foreach ( $spin_links as $row ) : ?>
				<a href="<?php echo esc_url( $row['url'] ); ?>" class="lty-rs-btn lty-rs-btn-spin">
					<span class="lty-rs-spin-wheel" aria-hidden="true"></span>
					<?php esc_html_e( 'Spin the Wheel', 'lty-result-screens' ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
</div>
