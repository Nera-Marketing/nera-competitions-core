<?php
/**
 * Async ticket generation orchestrator.
 *
 * Replaces LTY_Order_Handler's synchronous checkout-time ticket creation
 * with an Action Scheduler job, and runs the same plugin routine in a
 * quiet worker (cache invalidation suspended, term counting deferred).
 *
 * @package Nera_Competitions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Nera_Lty_Async_Tickets' ) ) {
	return;
}

final class Nera_Lty_Async_Tickets {

	const ACTION         = 'nera_lty_generate_tickets';
	const GROUP          = 'lottery-tickets';
	const META_QUEUED    = '_nera_lty_tickets_queued';
	const META_QUEUED_AT = '_nera_lty_tickets_queued_at';
	const META_DONE_AT   = '_nera_lty_tickets_done_at';

	/**
	 * The plugin's sync callback we intercept and replace.
	 * Stored as a constant so the unhook stays in lock-step with the hook.
	 */
	private static function plugin_callback() {
		return array( 'LTY_Order_Handler', 'create_ticket_on_placing_order' );
	}

	public static function init() {
		// Hook earlier (priority 1) than the plugin's callback (priority 10)
		// on the same actions. When our callback fires, it removes the
		// plugin's callback for that action before WP iterates further, so
		// the sync ticket loop never runs in the checkout request.
		// This is more robust than unhooking on plugins_loaded, because it
		// does not depend on when the plugin's own init() runs.
		add_action( 'woocommerce_checkout_update_order_meta',         array( __CLASS__, 'intercept_classic' ),    1, 1 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'intercept_store_api' ), 1, 1 );

		// Worker.
		add_action( self::ACTION, array( __CLASS__, 'run_worker' ), 10, 1 );
	}

	/**
	 * Priority-1 callback on the classic-checkout hook.
	 * Removes the plugin's priority-10 callback, then enqueues the async job.
	 */
	public static function intercept_classic( $order_id ) {
		remove_action( 'woocommerce_checkout_update_order_meta', self::plugin_callback() );
		self::enqueue_for_order( $order_id );
	}

	/**
	 * Priority-1 callback on the Store API checkout hook.
	 * Removes the plugin's priority-10 callback, then enqueues the async job.
	 */
	public static function intercept_store_api( $order ) {
		remove_action( 'woocommerce_store_api_checkout_order_processed', self::plugin_callback() );
		self::enqueue_for_order( $order );
	}

	/**
	 * Enqueue an Action Scheduler job to generate tickets for an order.
	 *
	 * @param int|WC_Order $order_id Order ID or WC_Order instance.
	 */
	public static function enqueue_for_order( $order_id ) {
		if ( is_object( $order_id ) && method_exists( $order_id, 'get_id' ) ) {
			$order_id = $order_id->get_id();
		}
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return;
		}

		// Idempotency: classic and Store API hooks should not both fire for
		// the same order in one request, but guard anyway.
		if ( get_post_meta( $order_id, self::META_QUEUED, true ) ) {
			return;
		}

		// Only queue if the order actually contains a lottery product.
		// Otherwise non-lottery orders would show the "tickets being
		// prepared" banner on their thank-you page until the worker runs.
		if ( ! self::order_has_lottery_product( $order_id ) ) {
			return;
		}

		// Set the queued flag first so a duplicate hook firing in this
		// request can't enqueue twice. Roll back if the enqueue itself fails.
		update_post_meta( $order_id, self::META_QUEUED, 1 );
		update_post_meta( $order_id, self::META_QUEUED_AT, microtime( true ) );

		$action_id = as_enqueue_async_action( self::ACTION, array( $order_id ), self::GROUP );

		if ( ! $action_id ) {
			delete_post_meta( $order_id, self::META_QUEUED );
			delete_post_meta( $order_id, self::META_QUEUED_AT );
			error_log( sprintf(
				'[nera-lty-async-tickets] as_enqueue_async_action returned no action ID for order %d — falling back to inline ticket generation',
				$order_id
			) );

			// Fallback: run the plugin's sync routine right now so the
			// customer doesn't end up with an order and no tickets.
			LTY_Order_Handler::create_ticket_on_placing_order( $order_id );
		}
	}

	/**
	 * Whether the given order contains at least one lottery product.
	 *
	 * @param int $order_id Order ID.
	 * @return bool
	 */
	private static function order_has_lottery_product( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		foreach ( $order->get_items() as $item ) {
			$product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : 0;
			if ( ! $product_id ) {
				continue;
			}
			if ( function_exists( 'lty_is_lottery_product' ) && lty_is_lottery_product( $product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Action Scheduler worker.
	 *
	 * Runs the plugin's original static routine with cache invalidation
	 * suspended and term counting deferred (5–10× faster on large
	 * bundles), then re-enables invalidation and cleans the affected
	 * product caches so the front-end sees fresh ticket counts.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function run_worker( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return;
		}

		$started_at         = microtime( true );
		$prev_defer_terms   = wp_defer_term_counting( true );
		$prev_defer_comments = wp_defer_comment_counting( true );
		wp_suspend_cache_invalidation( true );

		try {
			LTY_Order_Handler::create_ticket_on_placing_order( $order_id );
		} catch ( \Throwable $e ) {
			wp_suspend_cache_invalidation( false );
			wp_defer_comment_counting( $prev_defer_comments );
			wp_defer_term_counting( $prev_defer_terms );

			// Clear our flag and the plugin's flag so a manual AS re-run
			// can complete. NOTE: the plugin has no partial-completion
			// tracking, so a retry after partial success will create
			// duplicate ticket posts. This is a pre-existing plugin
			// limitation; manual cleanup may be required.
			delete_post_meta( $order_id, self::META_QUEUED );
			delete_post_meta( $order_id, 'lty_lottery_ticket_created_once' );

			error_log( sprintf(
				'[nera-lty-async-tickets] worker FAILED for order %d after %.2fs: %s',
				$order_id,
				microtime( true ) - $started_at,
				$e->getMessage()
			) );

			throw $e;
		}

		// Restore WP state BEFORE cleaning post caches — clean_post_cache
		// is a no-op while cache invalidation is suspended.
		wp_suspend_cache_invalidation( false );
		wp_defer_comment_counting( $prev_defer_comments );
		wp_defer_term_counting( $prev_defer_terms );

		$order = wc_get_order( $order_id );
		if ( $order ) {
			foreach ( $order->get_items() as $item ) {
				$product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : 0;
				if ( $product_id ) {
					clean_post_cache( $product_id );
				}
			}
		}

		$elapsed     = microtime( true ) - $started_at;
		$ticket_ids  = $order ? $order->get_meta( 'lty_ticket_ids_in_order' ) : array();
		$ticket_count = is_array( $ticket_ids ) ? count( $ticket_ids ) : 0;
		error_log( sprintf(
			'[nera-lty-async-tickets] order %d: %d tickets in %.2fs (%.1f ms/ticket)',
			$order_id,
			$ticket_count,
			$elapsed,
			$ticket_count > 0 ? ( $elapsed * 1000 / $ticket_count ) : 0
		) );

		update_post_meta( $order_id, self::META_DONE_AT, microtime( true ) );
	}
}
