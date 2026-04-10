<?php
/**
 * Lottery thank-you full-screen overlays (LTY Result Screens).
 *
 * @package Nera_Competitions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'LTY_Result_Screens' ) ) {
	return;
}

define( 'LTY_RS_VERSION', NERA_VERSION );
define( 'LTY_RS_PATH', trailingslashit( NERA_DIR ) . 'lty-result-screens/' );
define( 'LTY_RS_URL', trailingslashit( NERA_URI ) . 'lty-result-screens/' );

require_once LTY_RS_PATH . 'inc/class-lty-result-screens.php';
LTY_Result_Screens::instance();
