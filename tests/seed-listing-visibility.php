<?php
/**
 * Seed test lottery products for the Listing Visibility feature.
 *
 * Creates one published lottery product per scenario (Hide Ended + Hide Sold Out),
 * all in a "LV Test" category, then prints their names, permalinks and the
 * expected visibility when both toggles are ON (the default).
 *
 * Run inside Local's site shell (WP-CLI has DB access):
 *
 *     wp eval-file wp-content/themes/nera-competitions-standard/tests/seed-listing-visibility.php
 *
 * Re-running is safe: existing test products are detected and their links
 * re-printed instead of being duplicated.
 *
 * To remove everything created here afterwards:
 *
 *     wp eval-file wp-content/themes/nera-competitions-standard/tests/seed-listing-visibility.php --wipe
 *     (or set NERA_LV_WIPE=1, see bottom of file)
 *
 * @package Nera_Competitions
 */

// Bootstrap WordPress when the file is run directly (php <file> or via the browser).
// When run through `wp eval-file`, ABSPATH is already defined and this is skipped.
if ( ! defined( 'ABSPATH' ) ) {
	$nera_wp_load = dirname( __FILE__, 5 ) . '/wp-load.php'; // tests -> theme -> themes -> wp-content -> public
	if ( ! is_readable( $nera_wp_load ) ) {
		echo 'Could not locate wp-load.php at ' . $nera_wp_load . ". Run via: wp eval-file " . basename( __FILE__ ) . "\n";
		exit( 1 );
	}
	require $nera_wp_load;
}

// Over the web, restrict to logged-in administrators and emit plain text.
if ( 'cli' !== PHP_SAPI ) {
	if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'manage_options' ) ) {
		echo "Administrator login required (log in to wp-admin first).\n";
		exit( 1 );
	}
	if ( ! headers_sent() ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
	}
}

if ( ! class_exists( 'WC_Product_Lottery' ) ) {
	echo "ERROR: Lottery for WooCommerce is not active. Activate it and retry.\n";
	return;
}

$nera_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$nera_argv = ( isset( $argv ) && is_array( $argv ) ) ? $argv : array();
$WIPE      = in_array( '--wipe', $nera_args, true ) || in_array( '--wipe', $nera_argv, true ) || getenv( 'NERA_LV_WIPE' );

$META_KEY = '_nera_lv_test_key';
$CAT_NAME = 'LV Test';
$tz       = wp_timezone();

/** Local 'today' (site timezone) at a given H:i:s, returned as a DateTime. */
$at_today = static function ( $time, $day_offset = 0 ) use ( $tz ) {
	$d = new DateTime( 'today', $tz );
	if ( $day_offset ) {
		$d->modify( ( $day_offset > 0 ? '+' : '' ) . $day_offset . ' days' );
	}
	list( $h, $m, $s ) = array_pad( explode( ':', $time ), 3, '0' );
	$d->setTime( (int) $h, (int) $m, (int) $s );
	return $d;
};

/** DateTime -> GMT 'Y-m-d H:i:s'. */
$to_gmt = static function ( DateTime $d ) {
	$c = clone $d;
	$c->setTimezone( new DateTimeZone( 'UTC' ) );
	return $c->format( 'Y-m-d H:i:s' );
};
$to_local = static function ( DateTime $d ) {
	return $d->format( 'Y-m-d H:i:s' );
};

/** Find an existing test product by our key. */
$find = static function ( $key ) use ( $META_KEY ) {
	$q = get_posts(
		array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => $META_KEY,
			'meta_value'  => $key,
		)
	);
	return $q ? (int) $q[0] : 0;
};

/** Ensure the "LV Test" product category exists. */
$cat_term = term_exists( $CAT_NAME, 'product_cat' );
if ( ! $cat_term ) {
	$cat_term = wp_insert_term( $CAT_NAME, 'product_cat' );
}
$cat_id = is_array( $cat_term ) ? (int) $cat_term['term_id'] : 0;

/* ---------------- wipe mode ---------------- */
if ( $WIPE ) {
	$ids = get_posts(
		array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => $META_KEY,
		)
	);
	foreach ( $ids as $id ) {
		// Delete child tickets too.
		$tickets = get_posts(
			array(
				'post_type'   => 'lottery_ticket',
				'post_status' => 'any',
				'post_parent' => $id,
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		foreach ( $tickets as $t ) {
			wp_delete_post( $t, true );
		}
		wp_delete_post( $id, true );
		echo "Deleted product #$id and its tickets.\n";
	}
	delete_transient( 'nera_sold_out_lottery_ids' );
	echo "Wipe complete.\n";
	return;
}

/**
 * Create one lottery test product.
 *
 * @param array $s key,title,price,max,tickets,status,start(DateTime|null),end(DateTime|null)
 * @return int product ID
 */
$make = static function ( array $s ) use ( $find, $META_KEY, $cat_id, $to_gmt, $to_local ) {
	$existing = $find( $s['key'] );
	if ( $existing ) {
		return $existing;
	}

	$product = new WC_Product_Lottery();
	$product->set_name( $s['title'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( (string) $s['price'] );
	$product->set_price( (string) $s['price'] );
	$product->set_short_description( 'Listing Visibility test product.' );
	$product_id = $product->save();
	if ( ! $product_id ) {
		return 0;
	}

	$meta = array(
		$META_KEY                      => $s['key'],
		'_lty_lottery_schedule_type'   => '1',
		'_lty_minimum_tickets'         => '1',
		'_lty_maximum_tickets'         => (string) $s['max'],
		'_lty_ticket_price_type'       => '1',
		'_lty_regular_price'           => (string) $s['price'],
		'_lty_preset_tickets'          => '1',
		'_lty_user_minimum_tickets'    => '1',
		'_lty_user_maximum_tickets'    => (string) min( 100, $s['max'] ),
		'_lty_winners_count'           => '1',
		'_lty_ticket_start_number'     => '1',
		'_lty_ticket_length'           => '5',
		'_manage_stock'                => 'yes',
		'_stock'                       => (string) $s['max'],
		'_stock_status'                => 'instock',
		'_lty_lottery_status'          => $s['status'],
	);

	// Start date (optional). Active scenarios start in the past so they are "live".
	if ( ! empty( $s['start'] ) ) {
		$meta['_lty_start_date']     = $to_local( $s['start'] );
		$meta['_lty_start_date_gmt'] = $to_gmt( $s['start'] );
	}
	// End date (optional). Omitted entirely for the "no end date" scenario.
	if ( ! empty( $s['end'] ) ) {
		$meta['_lty_end_date']     = $to_local( $s['end'] );
		$meta['_lty_end_date_gmt'] = $to_gmt( $s['end'] );
	}

	foreach ( $meta as $k => $v ) {
		update_post_meta( $product_id, $k, $v );
	}

	if ( $cat_id ) {
		wp_set_object_terms( $product_id, array( $cat_id ), 'product_cat' );
	}

	// Purchased tickets (for the sold-out scenario the count must reach max).
	if ( ! empty( $s['tickets'] ) && function_exists( 'lty_create_new_lottery_ticket' ) ) {
		for ( $i = 1; $i <= (int) $s['tickets']; $i++ ) {
			lty_create_new_lottery_ticket(
				array(
					'lty_user_id'       => 0,
					'lty_product_id'    => $product_id,
					'lty_amount'        => (string) $s['price'],
					'lty_user_name'     => 'LV Tester ' . $i,
					'lty_user_email'    => 'lv+' . $i . '@example.com',
					'lty_currency'      => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'GBP',
					'lty_order_id'      => 0,
					'lty_ticket_number' => str_pad( (string) $i, 5, '0', STR_PAD_LEFT ),
					'lty_ip_address'    => '127.0.0.1',
				),
				array(
					'post_parent' => $product_id,
					'post_status' => 'lty_ticket_buyer',
				)
			);
		}
	}

	clean_post_cache( $product_id );
	return (int) $product_id;
};

/* ---------------- scenarios ---------------- */
$start_past = ( clone $at_today( '00:00', -10 ) ); // 10 days ago, midnight local.

$specs = array(
	array(
		'key'    => 'ends_earlier_today',
		'title'  => 'LV Test — Ends earlier today (00:01)',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_started',
		'start'  => $start_past,
		'end'    => $at_today( '00:01' ),
		'expect' => 'end time passed -> ON: HIDDEN (only on Closed Prizes); OFF: SHOWN',
	),
	array(
		'key'    => 'ends_today_autoclosed',
		'title'  => 'LV Test — Ends today, auto-closed (status finished)',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_finished', // simulates the cron closing it once end time passed
		'start'  => $start_past,
		'end'    => $at_today( '00:05' ),
		'expect' => 'finished/closed -> ON: HIDDEN (only on Closed Prizes); OFF: SHOWN',
	),
	array(
		'key'    => 'ends_later_today',
		'title'  => 'LV Test — Ends later today (23:30)',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_started',
		'start'  => $start_past,
		'end'    => $at_today( '23:30' ),
		'expect' => 'SHOWN while open (ends later today); after its end time -> Closed Prizes',
	),
	array(
		'key'    => 'ended_yesterday',
		'title'  => 'LV Test — Ended yesterday',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_started', // started, so only the END-DATE rule can hide it
		'start'  => $start_past,
		'end'    => $at_today( '12:00', -1 ),
		'expect' => 'HIDDEN when Hide Ended ON (shown when OFF)',
	),
	array(
		'key'    => 'closed_last_week',
		'title'  => 'LV Test — Closed last week (finished)',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_finished', // a genuinely closed/ended competition
		'start'  => $start_past,
		'end'    => $at_today( '12:00', -7 ),
		'expect' => 'HIDDEN when Hide Ended ON; SHOWN when Hide Ended OFF',
	),
	array(
		'key'    => 'ends_next_week',
		'title'  => 'LV Test — Ends next week',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_started',
		'start'  => $start_past,
		'end'    => $at_today( '18:00', 7 ),
		'expect' => 'SHOWN',
	),
	array(
		'key'    => 'no_end_date',
		'title'  => 'LV Test — No end date (unlimited)',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_started',
		'start'  => $start_past,
		'end'    => null,
		'expect' => 'SHOWN always (no end date)',
	),
	array(
		'key'    => 'sold_out',
		'title'  => 'LV Test — Sold out (2/2 tickets)',
		'price'  => 5,
		'max'    => 2,
		'tickets' => 2, // sold == max -> sold out
		'status' => 'lty_lottery_started',
		'start'  => $start_past,
		'end'    => $at_today( '18:00', 30 ),
		'expect' => 'HIDDEN when Hide Sold Out ON (shown when OFF)',
	),
	array(
		'key'    => 'available',
		'title'  => 'LV Test — Available (1/100 tickets)',
		'price'  => 5,
		'max'    => 100,
		'tickets' => 1,
		'status' => 'lty_lottery_started',
		'start'  => $start_past,
		'end'    => $at_today( '18:00', 30 ),
		'expect' => 'SHOWN',
	),
);

echo "Seeding Listing Visibility test products...\n";
echo str_repeat( '-', 92 ) . "\n";

$results = array();
foreach ( $specs as $s ) {
	$id = $make( $s );
	$results[] = array( $s, $id );
}

// Recompute sold-out cache so the sold-out product is recognised immediately.
delete_transient( 'nera_sold_out_lottery_ids' );

/* ---------------- report ---------------- */
$hide_ended   = function_exists( 'get_field' ) ? get_field( 'hide_ended_competitions', 'option' ) : '(n/a)';
$hide_soldout = function_exists( 'get_field' ) ? get_field( 'hide_sold_out_competitions', 'option' ) : '(n/a)';
$norm = static function ( $v ) {
	return ( '0' === $v || 0 === $v || false === $v ) ? 'OFF' : 'ON';
};

foreach ( $results as $r ) {
	list( $s, $id ) = $r;
	if ( ! $id ) {
		echo "FAILED: {$s['title']}\n";
		continue;
	}
	printf( "%-38s  #%-5d  %s\n", $s['title'], $id, get_permalink( $id ) );
	printf( "%-38s  expect: %s\n\n", '', $s['expect'] );
}

echo str_repeat( '-', 92 ) . "\n";
echo 'Current toggles  ->  Hide Ended: ' . $norm( $hide_ended ) . '   |   Hide Sold Out: ' . $norm( $hide_soldout ) . "\n";
echo 'Home:             ' . home_url( '/' ) . "\n";
if ( function_exists( 'wc_get_page_permalink' ) ) {
	echo 'All competitions: ' . wc_get_page_permalink( 'shop' ) . "\n";
}
echo "Category 'LV Test': " . ( $cat_id ? get_term_link( $cat_id, 'product_cat' ) : '(none)' ) . "\n";
echo "\nTip: toggle the options in Theme Settings -> Listing Visibility and reload to compare.\n";
echo "Done.\n";
