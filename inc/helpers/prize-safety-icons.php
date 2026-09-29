<?php
/**
 * Prize Safety — icon choices for the admin picker.
 *
 * Pure data (no hooks). Loaded before the ACF field group that uses it
 * (inc/acf/single-product/acf-prize-addons.php). Every value is a Material
 * Symbols Outlined ligature name, which is what the prize page renders; the
 * labels are what the admin searches in the dropdown.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Picker value that reveals the free-text icon name field. */
const NERA_PRIZE_SAFETY_ICON_CUSTOM = '__custom';

/** Icon used when nothing (or something unusable) was chosen. */
const NERA_PRIZE_SAFETY_ICON_DEFAULT = 'health_and_safety';

/**
 * Icon choices grouped for the dropdown, as group label => [icon name => label].
 *
 * @return array<string,array<string,string>>
 */
function nera_prize_safety_icon_choices(): array
{
  $choices = [
    'Safety & rescue' => [
      'health_and_safety' => 'Safety shield',
      'support' => 'Lifebuoy / life jacket',
      'medical_services' => 'First aid kit',
      'emergency' => 'Medical emergency',
      'flare' => 'Flare',
      'fire_extinguisher' => 'Fire extinguisher',
      'sos' => 'SOS',
      'sports_motorsports' => 'Helmet',
      'flashlight_on' => 'Torch',
      'visibility' => 'High visibility',
      'shield' => 'Shield',
      'verified_user' => 'Protection',
    ],
    'Communication & navigation' => [
      'settings_input_antenna' => 'VHF radio / antenna',
      'radio' => 'Radio',
      'satellite_alt' => 'Satellite / EPIRB',
      'cell_tower' => 'Signal mast',
      'gps_fixed' => 'GPS',
      'explore' => 'Compass',
      'navigation' => 'Navigation arrow',
      'map' => 'Chart / map',
      'call' => 'Phone',
    ],
    'Water & boats' => [
      'sailing' => 'Sailing boat',
      'directions_boat' => 'Motor boat',
      'anchor' => 'Anchor',
      'rowing' => 'Rowing',
      'kayaking' => 'Kayak',
      'surfing' => 'Surfing',
      'kitesurfing' => 'Kitesurfing',
      'scuba_diving' => 'Diving',
      'pool' => 'Swimming',
      'waves' => 'Waves',
      'water' => 'Water',
    ],
    'Kit & tools' => [
      'checkroom' => 'Clothing',
      'backpack' => 'Bag',
      'inventory_2' => 'Kit box',
      'handyman' => 'Tool kit',
      'build' => 'Spanner',
      'construction' => 'Tools',
      'key' => 'Key / kill-cord',
      'lock' => 'Lock',
      'battery_charging_full' => 'Battery',
      'bolt' => 'Power',
    ],
    'Road & vehicles' => [
      'directions_car' => 'Car',
      'two_wheeler' => 'Motorbike',
      'local_gas_station' => 'Fuel',
      'ev_station' => 'EV charging',
      'tire_repair' => 'Tyre',
      'car_repair' => 'Car service',
    ],
    'Weather' => [
      'wb_sunny' => 'Sun',
      'ac_unit' => 'Cold / frost',
      'thermostat' => 'Temperature',
    ],
    'General' => [
      'check_circle' => 'Tick',
      'star' => 'Star',
      'workspace_premium' => 'Award',
      'card_giftcard' => 'Gift',
      'local_shipping' => 'Delivery',
      'watch' => 'Watch',
      'smartphone' => 'Smartphone',
      'headphones' => 'Headphones',
      'photo_camera' => 'Camera',
    ],
  ];

  /**
   * Filter the Safety icon choices (group label => [Material Symbol name => label]).
   *
   * @param array $choices Grouped choices.
   */
  return (array) apply_filters('nera_prize_safety_icon_choices', $choices);
}

/**
 * Choices in the shape ACF's select field expects, with "Other" last.
 *
 * @return array<string,array<string,string>>
 */
function nera_prize_safety_icon_acf_choices(): array
{
  $choices = nera_prize_safety_icon_choices();
  $choices['Other'] = [NERA_PRIZE_SAFETY_ICON_CUSTOM => 'Other icon (type a Material Symbol name)'];

  return $choices;
}

/**
 * Resolve the icon a Safety row should show.
 *
 * @param string $picked Value chosen in the dropdown.
 * @param string $custom Name typed when "Other" was chosen.
 * @return string Material Symbol name.
 */
function nera_prize_safety_resolve_icon(string $picked, string $custom = ''): string
{
  $name = NERA_PRIZE_SAFETY_ICON_CUSTOM === $picked ? $custom : $picked;
  $name = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($name)));

  return '' !== $name ? $name : NERA_PRIZE_SAFETY_ICON_DEFAULT;
}
