<?php
namespace Nera\Components\CountdownTimer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   countdown_date: string, // required, default '' — ISO/GMT date string; passed to data-time attr for JS
 *   days: string,           // required, default '00' — zero-padded days remaining
 *   hours: string,          // required, default '00' — zero-padded hours remaining
 *   minutes: string,        // required, default '00' — zero-padded minutes remaining
 *   seconds: string,        // required, default '00' — zero-padded seconds remaining
 *   is_expired: bool,       // required, default false — switches to expired (static 00s) variant
 * }
 */
function get_data(array $args = []): array
{
    $countdown = $args['countdown'] ?? [];

    $pad = function ($value): string {
        return str_pad((string) $value, 2, '0', STR_PAD_LEFT);
    };

    return [
        'countdown_date' => (string) ($args['countdown_date'] ?? ''),
        'days'           => $pad($countdown['days'] ?? 0),
        'hours'          => $pad($countdown['hours'] ?? 0),
        'minutes'        => $pad($countdown['minutes'] ?? 0),
        'seconds'        => $pad($countdown['seconds'] ?? 0),
        'is_expired'     => (bool) ($args['is_expired'] ?? false),
    ];
}
