<?php
/**
 * Async lottery-ticket generation — loader.
 *
 * Replaces the synchronous LTY_Order_Handler checkout callback with an
 * Action Scheduler job so the user's checkout request returns in
 * near-constant time regardless of ticket-bundle size. See README.md.
 *
 * @package Nera_Competitions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'LTY' )
	|| ! class_exists( 'WooCommerce' )
	|| ! function_exists( 'as_enqueue_async_action' )
	|| ! class_exists( 'LTY_Order_Handler' )
) {
	return;
}

require_once trailingslashit( __DIR__ ) . 'class-nera-lty-async-tickets.php';
require_once trailingslashit( __DIR__ ) . 'order-received-polling.php';

Nera_Lty_Async_Tickets::init();
Nera_Lty_Async_Tickets_Order_Received::init();
