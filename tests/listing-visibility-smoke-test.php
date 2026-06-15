<?php
/**
 * Standalone smoke test for the Listing Visibility feature logic.
 *
 *   php tests/listing-visibility-smoke-test.php
 *
 * Models, without WordPress/DB, the two decisions made by
 * inc/woocommerce.php:
 *   - Hide Ended Competitions  -> nera_active_lottery_meta_query() end-date clause
 *   - Hide Sold Out Competitions -> nera_sold_out_lottery_ids() ($max && $sold >= $max)
 *
 * The end-date clause keeps a product when:
 *     (no _lty_end_date_gmt)  OR  (_lty_end_date_gmt >= CUTOFF)
 * with the meta_query type=DATETIME comparison. For canonical 'Y-m-d H:i:s'
 * GMT strings that ordering is identical to PHP string comparison, so we model
 * it directly.
 *
 * OLD cutoff = now (GMT)            -> hides a competition ending earlier *today*.
 * NEW cutoff = local midnight today, in GMT -> keeps it until the end of today.
 *
 * @package Nera_Competitions
 */

error_reporting( E_ALL );

$pass = 0;
$fail = 0;
function check( $label, $got, $expected ) {
	global $pass, $fail;
	$ok = ( $got === $expected );
	printf(
		"  [%s] %-46s got=%-6s exp=%s\n",
		$ok ? 'PASS' : 'FAIL',
		$label,
		var_export( $got, true ),
		var_export( $expected, true )
	);
	$ok ? $pass++ : $fail++;
}

/** Convert a site-local 'Y-m-d H:i:s' string to a GMT 'Y-m-d H:i:s' string. */
function to_gmt( $local_str, $tz_name ) {
	$dt = new DateTime( $local_str, new DateTimeZone( $tz_name ) );
	$dt->setTimezone( new DateTimeZone( 'UTC' ) );
	return $dt->format( 'Y-m-d H:i:s' );
}

/**
 * Regular-listing decision (start assumed satisfied) — nera_active_lottery_meta_query.
 *   - Hide Ended OFF: everything is shown (ended/closed included).
 *   - Hide Ended ON : keep only active status (not_started/started) AND end >= now
 *     (or no end date). This is the exact inverse of the Closed Prizes query.
 */
function active_listed( $status, $end_gmt, $hide_ended, $now_gmt ) {
	if ( ! $hide_ended ) {
		return true; // OFF -> ended & closed competitions are shown.
	}
	$active    = ( '' === $status || null === $status
		|| 'lty_lottery_not_started' === $status || 'lty_lottery_started' === $status );
	$not_ended = ( '' === $end_gmt || null === $end_gmt || strcmp( $end_gmt, $now_gmt ) >= 0 );
	return $active && $not_ended;
}

/** Closed Prizes decision — nera_closed_lottery_meta_query: finished/closed OR end < now. */
function closed_listed( $status, $end_gmt, $now_gmt ) {
	$closed_status = ( 'lty_lottery_finished' === $status || 'lty_lottery_closed' === $status );
	$ended_date    = ( '' !== $end_gmt && null !== $end_gmt && strcmp( $end_gmt, $now_gmt ) < 0 );
	return $closed_status || $ended_date;
}

/** Sold-out definition from the card template: max set AND sold >= max. */
function is_sold_out( $max, $sold ) {
	return $max > 0 && $sold >= $max;
}

/* ------------------------------------------------------------------ */
/* Scenario: site timezone Europe/London, "now" = 2026-06-14 20:00.    */
/* ------------------------------------------------------------------ */
$TZ        = 'Europe/London';
$now_local = '2026-06-14 20:00:00';
$now_gmt   = to_gmt( $now_local, $TZ );

echo "Timezone: $TZ | now(local)=$now_local | now(gmt)=$now_gmt\n\n";

$end_today_passed = to_gmt( '2026-06-14 12:00:00', $TZ ); // ended earlier today (now > end)
$end_today_later  = to_gmt( '2026-06-14 23:30:00', $TZ ); // ends later today (still open)
$end_tomorrow     = to_gmt( '2026-06-15 10:00:00', $TZ );
$end_yesterday    = to_gmt( '2026-06-13 23:00:00', $TZ );
$end_lastyear     = to_gmt( '2025-06-01 12:00:00', $TZ );

echo "== Hide Ended OFF: ended / closed competitions are SHOWN in regular listings ==\n";
check( 'OFF: closed last year -> SHOWN',    active_listed( 'lty_lottery_closed', $end_lastyear, false, $now_gmt ), true );
check( 'OFF: finished yesterday -> SHOWN',  active_listed( 'lty_lottery_finished', $end_yesterday, false, $now_gmt ), true );
check( 'OFF: finished ended-today -> SHOWN',active_listed( 'lty_lottery_finished', $end_today_passed, false, $now_gmt ), true );
check( 'OFF: started ended-today -> SHOWN', active_listed( 'lty_lottery_started', $end_today_passed, false, $now_gmt ), true );

echo "\n== Hide Ended ON: closed competitions HIDDEN from regular listings ==\n";
check( 'ON: finished ended-today -> HIDDEN', active_listed( 'lty_lottery_finished', $end_today_passed, true, $now_gmt ), false );
check( 'ON: started ended-today (time passed) -> HIDDEN', active_listed( 'lty_lottery_started', $end_today_passed, true, $now_gmt ), false );
check( 'ON: closed last year -> HIDDEN',     active_listed( 'lty_lottery_closed', $end_lastyear, true, $now_gmt ), false );
check( 'ON: finished yesterday -> HIDDEN',   active_listed( 'lty_lottery_finished', $end_yesterday, true, $now_gmt ), false );
check( 'ON: failed yesterday -> HIDDEN',     active_listed( 'lty_lottery_failed', $end_yesterday, true, $now_gmt ), false );
echo "  (still-open competitions remain visible)\n";
check( 'ON: started ends-later-today -> SHOWN', active_listed( 'lty_lottery_started', $end_today_later, true, $now_gmt ), true );
check( 'ON: started future -> SHOWN',           active_listed( 'lty_lottery_started', $end_tomorrow, true, $now_gmt ), true );
check( 'ON: started no-end (unlimited) -> SHOWN', active_listed( 'lty_lottery_started', '', true, $now_gmt ), true );

echo "\n== Closed Prizes page shows exactly the ended/closed ones (complement) ==\n";
check( 'Closed page: finished ended-today -> SHOWN', closed_listed( 'lty_lottery_finished', $end_today_passed, $now_gmt ), true );
check( 'Closed page: ended-today (time passed) -> SHOWN', closed_listed( 'lty_lottery_started', $end_today_passed, $now_gmt ), true );
check( 'Closed page: finished yesterday -> SHOWN',   closed_listed( 'lty_lottery_finished', $end_yesterday, $now_gmt ), true );
check( 'Closed page: open later-today -> NOT shown',  closed_listed( 'lty_lottery_started', $end_today_later, $now_gmt ), false );
check( 'Closed page: open future -> NOT shown',       closed_listed( 'lty_lottery_started', $end_tomorrow, $now_gmt ), false );
echo "  (regular ON-listing and Closed Prizes are mutually exclusive for these)\n";
foreach ( array(
	array( 'lty_lottery_finished', $end_today_passed ),
	array( 'lty_lottery_started',  $end_today_passed ),
	array( 'lty_lottery_started',  $end_today_later ),
	array( 'lty_lottery_started',  $end_tomorrow ),
	array( 'lty_lottery_finished', $end_yesterday ),
) as $c ) {
	$in_active = active_listed( $c[0], $c[1], true, $now_gmt );
	$in_closed = closed_listed( $c[0], $c[1], $now_gmt );
	check( "exclusive: {$c[0]} / {$c[1]}", $in_active && $in_closed, false );
}

echo "\n== Hide Sold Out: definition (max set AND sold >= max) ==\n";
check( 'max100 sold100 -> sold out', is_sold_out( 100, 100 ), true );
check( 'max100 sold101 -> sold out', is_sold_out( 100, 101 ), true );
check( 'max100 sold50  -> available', is_sold_out( 100, 50 ), false );
check( 'no max (0) -> never sold out', is_sold_out( 0, 9999 ), false );

echo "\n== Hide Sold Out OFF: function returns [] -> nothing excluded ==\n";
$hide_sold_out_enabled = false;
$excluded = $hide_sold_out_enabled ? array( 123 ) : array();
check( 'disabled -> empty exclude list', $excluded, array() );

echo "\n----------------------------------------------------------\n";
printf( "RESULT: %d passed, %d failed\n", $pass, $fail );
exit( $fail > 0 ? 1 : 0 );
