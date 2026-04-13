<?php
/**
 * Pagination for blog post listings (previous / next page of posts).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}
?>

<nav class="mt-12 flex justify-center gap-4" aria-label="<?php esc_attr_e('Posts navigation', 'nera-competitions'); ?>">
    <?php
    $prev_link = get_previous_posts_link(__('&larr; Newer Posts', 'nera-competitions'));
    $next_link = get_next_posts_link(__('Older Posts &rarr;', 'nera-competitions'));

    if ($prev_link) {
      echo '<span class="px-6 py-3 bg-surface rounded-lg shadow-sm hover:shadow-md transition-shadow font-semibold text-text-primary">' .
        $prev_link .
        '</span>';
    }
    if ($next_link) {
      echo '<span class="px-6 py-3 bg-surface rounded-lg shadow-sm hover:shadow-md transition-shadow font-semibold text-text-primary">' .
        $next_link .
        '</span>';
    }
    ?>
</nav>
