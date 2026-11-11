<?php
/**
 * Postal entries (free postal entry logged by an admin).
 *
 * The free postal route: someone posts an entry for a drop, and an admin logs it here. The entry gets an
 * entry number, counts towards the drop's total, is marked "postal" on the public entry list, and the person
 * is emailed.
 *
 *   - Theme Settings -> WooCommerce -> "Postal entries": the switch, and the subject and text of the three
 *     emails. While the switch is off the admin screen and the emails do not exist. Entries already logged keep
 *     their "postal" marker.
 *   - WooCommerce -> Postal entries: pick a drop, find a member (live search) or type an email, enter the
 *     name and postal address, log it. A typed email that belongs to a member is the same as picking them.
 *       - member + drop open and not full: a GBP 0 order is created, the entry number issued, the member
 *         emailed (confirmation).
 *       - email with no member account yet: the entry waits ("pending") and the person is emailed an
 *         invitation to register. When they register (My Account form, checkout or an admin adding the user)
 *         the entry is issued automatically, if the drop is still open and not full.
 *       - drop full or closed (now, or by the time they register): no entry is made, the person is emailed
 *         "not entered".
 *   - One postal entry per person per drop (matched by account and by lowercase email).
 *   - A postal entry counts towards the drop's total (entries left drops) but not towards the member's own
 *     paid cap (the Percentage cap): the cap is raised by the postal entry.
 *
 * Files: postal-entry/core.php (rules, data, privacy), postal-entry/emails.php (emails and settings fields),
 * postal-entry/admin.php (screen and ajax, loaded only while the switch is on),
 * assets/js/admin-postal-entry.js.
 *
 * @package Nera_Competitions
 * @see CONTEXT.md "Postal entry"
 */

if (!defined('ABSPATH')) {
  exit();
}

require_once get_template_directory() . '/inc/postal-entry/core.php';
require_once get_template_directory() . '/inc/postal-entry/emails.php';

if (nera_postal_enabled()) {
  require_once get_template_directory() . '/inc/postal-entry/admin.php';
}
