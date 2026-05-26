<?php
namespace Nera\Components\CountdownTimer;

if (!defined('ABSPATH')) {
    exit;
}

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
