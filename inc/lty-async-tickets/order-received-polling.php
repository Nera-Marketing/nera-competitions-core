<?php
/**
 * Order-received page UX for async ticket generation.
 *
 * Renders a "tickets being prepared" banner on the thank-you page until
 * the worker finishes, plus a tiny REST endpoint the page polls to
 * detect completion.
 *
 * @package Nera_Competitions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Nera_Lty_Async_Tickets_Order_Received' ) ) {
	return;
}

final class Nera_Lty_Async_Tickets_Order_Received {

	const REST_NAMESPACE = 'nera/v1';

	public static function init() {
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'render_banner' ), 5, 1 );
		add_action( 'rest_api_init',        array( __CLASS__, 'register_rest_route' ) );
	}

	/**
	 * Render the "tickets being prepared" banner on the thank-you page.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function render_banner( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return;
		}

		$queued = get_post_meta( $order_id, Nera_Lty_Async_Tickets::META_QUEUED, true );
		$done   = get_post_meta( $order_id, Nera_Lty_Async_Tickets::META_DONE_AT, true );
		if ( ! $queued || $done ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Include the order key in the polling URL so guest-checkout
		// requests authenticate without relying on Referer (which
		// Referrer-Policy can strip).
		$status_url = add_query_arg(
			array( 'key' => $order->get_order_key() ),
			rest_url( self::REST_NAMESPACE . '/order/' . $order_id . '/tickets-status' )
		);
		$status_url = esc_url_raw( $status_url );
		$nonce      = wp_create_nonce( 'wp_rest' );
		?>
		<div id="nera-lty-tickets-pending"
			class="nera-lty-tickets-pending bg-surface border border-gray-200 rounded-lg p-4 my-5"
			role="status"
			aria-live="polite">
			<strong class="text-text-primary">
				<?php esc_html_e( 'Your tickets are being prepared…', 'nera-competitions' ); ?>
			</strong>
			<p class="mt-1 text-sm text-text-secondary">
				<?php esc_html_e( 'This page will refresh automatically as soon as they are ready.', 'nera-competitions' ); ?>
			</p>
		</div>
		<script>
		(function () {
			var url   = <?php echo wp_json_encode( $status_url ); ?>;
			var nonce = <?php echo wp_json_encode( $nonce ); ?>;
			var tries = 0;
			var max   = 120; // ~6 minutes at 3s interval.

			function poll() {
				tries++;
				fetch(url, { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
					.then(function (r) { return r.ok ? r.json() : null; })
					.then(function (data) {
						if (data && data.status === 'done') {
							window.location.reload();
							return;
						}
						if (tries < max) {
							setTimeout(poll, 3000);
						}
					})
					.catch(function () {
						if (tries < max) {
							setTimeout(poll, 5000);
						}
					});
			}
			setTimeout(poll, 3000);
		})();
		</script>
		<?php
	}

	public static function register_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/order/(?P<id>\d+)/tickets-status',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_status' ),
				'permission_callback' => array( __CLASS__, 'rest_permission' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function ( $v ) {
							return is_numeric( $v ) && (int) $v > 0;
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Permission callback.
	 *
	 * Allows: shop managers, the order's logged-in customer, or a request
	 * whose Referer carries the order's WC `?key=` (covers guest checkout
	 * polling from the thank-you page).
	 */
	public static function rest_permission( $request ) {
		$order_id = absint( $request['id'] );
		if ( ! $order_id ) {
			return false;
		}

		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$user_id = get_current_user_id();
		if ( $user_id && (int) $order->get_user_id() === $user_id ) {
			return true;
		}

		$order_key = (string) $order->get_order_key();
		if ( '' === $order_key ) {
			return false;
		}

		// Direct ?key= on the polling request, if the caller chose to send it.
		if ( ! empty( $_GET['key'] ) ) {
			$key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
			if ( hash_equals( $order_key, $key ) ) {
				return true;
			}
		}

		// Guest checkout: the fetch runs from the WC thank-you page, which
		// has ?key= in its URL. Validate against the Referer.
		$referer = wp_get_referer();
		if ( $referer ) {
			$qs = wp_parse_url( $referer, PHP_URL_QUERY );
			if ( $qs ) {
				parse_str( $qs, $parts );
				if ( ! empty( $parts['key'] ) && hash_equals( $order_key, (string) $parts['key'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	public static function rest_status( $request ) {
		$order_id = absint( $request['id'] );
		$done     = (float) get_post_meta( $order_id, Nera_Lty_Async_Tickets::META_DONE_AT, true );
		$queued   = (bool) get_post_meta( $order_id, Nera_Lty_Async_Tickets::META_QUEUED, true );

		$ticket_ids = get_post_meta( $order_id, 'lty_ticket_ids_in_order', true );
		$count      = is_array( $ticket_ids ) ? count( $ticket_ids ) : 0;

		return rest_ensure_response( array(
			'status'       => $done ? 'done' : ( $queued ? 'queued' : 'unknown' ),
			'ticket_count' => $count,
		) );
	}
}
