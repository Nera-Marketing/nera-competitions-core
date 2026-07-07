<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stw_heading      = get_field( 'lty_rs_stw_heading', 'option' ) ?: __( 'Your spins are ready!', 'lty-result-screens' );
$stw_subtext      = get_field( 'lty_rs_stw_subtext', 'option' );
$stw_spin_button  = get_field( 'lty_rs_stw_spin_button', 'option' ) ?: __( 'Spin the Wheel', 'lty-result-screens' );
$stw_dismiss      = get_field( 'lty_rs_stw_dismiss_button', 'option' ) ?: __( 'Got it!', 'lty-result-screens' );
?>
<div class="lty-rs-overlay flex items-center justify-center p-4 bg-[rgba(8,8,18,0.88)] backdrop-blur-md"
     role="dialog" aria-modal="true" aria-labelledby="lty-rs-stw-heading">

	<div class="relative w-full max-w-[520px] max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-[var(--lty-rs-card-radius)] bg-[var(--lty-rs-stw-bg)] text-center px-9 py-11 shadow-2xl border-t-4 border-[var(--lty-rs-stw-accent)] animate-rs-enter">

		<!-- X close button -->
		<button class="lty-rs-close-x"
			data-lty-rs-dismiss
			aria-label="<?php esc_attr_e( 'Close', 'lty-result-screens' ); ?>">
			&#10005;
		</button>

		<div class="block text-5xl mb-4" aria-hidden="true">&#127920;</div>

		<h2 id="lty-rs-stw-heading" class="text-[clamp(1.5rem,4vw,2rem)] font-extrabold tracking-tight text-[var(--lty-rs-stw-accent)] mb-3.5 leading-tight">
			<?php echo esc_html( $stw_heading ); ?>
		</h2>

		<?php if ( ! empty( $stw_subtext ) ) : ?>
			<p class="text-base text-[#5a5a6a] leading-relaxed mb-4">
				<?php echo esc_html( $stw_subtext ); ?>
			</p>
		<?php endif; ?>

		<div class="lty-rs-spin-banner__eyebrow justify-center mb-6">
			<span class="lty-rs-spin-wheel" aria-hidden="true"></span>
			<?php echo esc_html( $spin_eyebrow_text ); ?>
		</div>

		<?php foreach ( $spin_links as $row ) : ?>
			<a href="<?php echo esc_url( $row['url'] ); ?>" class="lty-rs-btn lty-rs-btn-spin mb-3">
				<span class="lty-rs-spin-wheel" aria-hidden="true"></span>
				<?php echo esc_html( $stw_spin_button ); ?>
				<?php if ( count( $spin_links ) > 1 ) : ?>
					<span class="font-normal opacity-80 text-sm">&mdash; <?php echo esc_html( $row['label'] ); ?></span>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>

		<button class="lty-rs-btn lty-rs-btn-stw-dismiss" data-lty-rs-dismiss>
			<?php echo esc_html( $stw_dismiss ); ?>
		</button>

	</div>
</div>
