<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * The order has an instant win that is still pending payment. Until $max_tries reloads have
 * passed, the overlay carries a retry URL and the script reloads the page, which renders the
 * won screen once the payment is confirmed. After that it stops and points to the order.
 */
$at_limit     = $try >= $max_tries;
$received_url = $order->get_checkout_order_received_url();
$retry_url    = '';

if ( $at_limit ) {
	$heading  = get_field( 'lty_rs_pending_limit_heading', 'option' ) ?: __( 'Still confirming your result', 'lty-result-screens' );
	$message  = get_field( 'lty_rs_pending_limit_message', 'option' ) ?: __( 'Your payment is taking longer than usual to confirm. Check My Account shortly to see your result.', 'lty-result-screens' );
	$btn_text = get_field( 'lty_rs_pending_limit_button', 'option' ) ?: __( 'Go to My Account', 'lty-result-screens' );
	$is_owner = is_user_logged_in() && get_current_user_id() === (int) $order->get_user_id();
	$btn_url  = $is_owner ? $order->get_view_order_url() : $received_url;
} else {
	$heading   = get_field( 'lty_rs_pending_heading', 'option' ) ?: __( "Confirming your result\xe2\x80\xa6", 'lty-result-screens' );
	$message   = get_field( 'lty_rs_pending_message', 'option' ) ?: __( "We're confirming your payment. Your result will appear here in a moment.", 'lty-result-screens' );
	$retry_url = add_query_arg( 'lty_rs_wait', $try + 1, $received_url );
}
?>
<div class="lty-rs-overlay flex items-center justify-center p-4 bg-[rgba(8,8,18,0.88)] backdrop-blur-md"
     role="dialog" aria-modal="true" aria-labelledby="lty-rs-pending-heading"
     <?php if ( $retry_url ) : ?>data-lty-rs-retry-url="<?php echo esc_url( $retry_url ); ?>" data-lty-rs-retry-ms="3000"<?php endif; ?>>

	<div class="lty-rs-pending-card relative w-full max-w-[520px] max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-[var(--lty-rs-card-radius)] bg-[var(--lty-rs-no-win-bg)] text-center px-9 py-11 shadow-2xl border-t-4 animate-rs-enter">

		<!-- X close button -->
		<button class="lty-rs-close-x"
			data-lty-rs-dismiss
			aria-label="<?php esc_attr_e( 'Close', 'lty-result-screens' ); ?>">
			&#10005;
		</button>

		<div class="block text-5xl mb-4" aria-hidden="true">&#9203;</div>

		<h2 id="lty-rs-pending-heading" class="text-[clamp(1.5rem,4vw,2rem)] font-extrabold tracking-tight text-gray-800 mb-3.5 leading-tight">
			<?php echo esc_html( $heading ); ?>
		</h2>

		<p class="text-[1.0625rem] text-[#5a5a6a] leading-relaxed mb-7">
			<?php echo esc_html( $message ); ?>
		</p>

		<?php if ( $at_limit ) : ?>
		<a href="<?php echo esc_url( $btn_url ); ?>" class="lty-rs-btn lty-rs-btn-pending">
			<?php echo esc_html( $btn_text ); ?>
		</a>
		<?php endif; ?>

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
