<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading    = get_field( 'lty_rs_no_win_heading', 'option' ) ?: __( 'Thanks for entering!', 'lty-result-screens' );
$message    = get_field( 'lty_rs_no_win_message', 'option' ) ?: __( "Not this time \xe2\x80\x94 but every entry brings you closer. There are always more competitions to enter!", 'lty-result-screens' );
$btn_text   = get_field( 'lty_rs_no_win_button', 'option' ) ?: __( 'Browse more competitions', 'lty-result-screens' );
$browse_url = get_field( 'lty_rs_browse_url', 'option' ) ?: get_permalink( wc_get_page_id( 'shop' ) );
?>
<div class="lty-rs-overlay flex items-center justify-center p-4 bg-[rgba(8,8,18,0.88)] backdrop-blur-md"
     role="dialog" aria-modal="true" aria-labelledby="lty-rs-no-win-heading">

	<div class="relative w-full max-w-[520px] max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-[var(--lty-rs-card-radius)] bg-[var(--lty-rs-no-win-bg)] text-center px-9 py-11 shadow-2xl border-t-4 border-[var(--lty-rs-no-win-accent)] animate-rs-enter">

		<!-- X close button -->
		<button class="lty-rs-close-x"
			data-lty-rs-dismiss
			aria-label="<?php esc_attr_e( 'Close', 'lty-result-screens' ); ?>">
			&#10005;
		</button>

		<div class="block text-5xl mb-4" aria-hidden="true">&#127808;</div>

		<h2 id="lty-rs-no-win-heading" class="text-[clamp(1.5rem,4vw,2rem)] font-extrabold tracking-tight text-gray-800 mb-3.5 leading-tight">
			<?php echo esc_html( $heading ); ?>
		</h2>

		<p class="text-[1.0625rem] text-[#5a5a6a] leading-relaxed mb-7">
			<?php echo esc_html( $message ); ?>
		</p>

		<a href="<?php echo esc_url( $browse_url ); ?>" class="lty-rs-btn lty-rs-btn-no-win" data-lty-rs-dismiss>
			<?php echo esc_html( $btn_text ); ?>
		</a>

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
