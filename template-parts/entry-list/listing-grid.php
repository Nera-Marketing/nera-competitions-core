<?php
/**
 * Entry List Grid
 *
 * Server-renders page 1 and appends subsequent pages via AJAX.
 * Opens participant details in a modal via REST API.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$query = new WP_Query(
  function_exists('nera_entry_list_wp_query_args')
    ? nera_entry_list_wp_query_args(1)
    : [],
);

$has_more   = $query->max_num_pages > 1;
$ajax_url   = admin_url('admin-ajax.php');
$ajax_nonce = wp_create_nonce('nera_nonce');
$rest_base  = esc_url_raw(trailingslashit(rest_url('nera/v1')));

$page_id = get_queried_object_id() ?: get_the_ID();
$empty_heading = function_exists('get_field') ? get_field('entry_list_empty_heading', $page_id) : '';
$empty_description = function_exists('get_field') ? get_field('entry_list_empty_description', $page_id) : '';
$empty_heading = is_string($empty_heading) ? trim($empty_heading) : '';
$empty_description = is_string($empty_description) ? trim($empty_description) : '';
if ($empty_heading === '') {
  $empty_heading = __('No competitions found', 'nera-competitions');
}
if ($empty_description === '') {
  $empty_description = __('There are no participant lists available yet. Please check back soon.', 'nera-competitions');
}

$entry_list_alpine_config = [
  'restBase'   => $rest_base,
  'hasMore'    => $has_more,
  'ajaxUrl'    => $ajax_url,
  'ajaxNonce'  => $ajax_nonce,
  'strings'    => [
    'loading'                 => __('Loading…', 'nera-competitions'),
    'loadMore'                => __('Load More', 'nera-competitions'),
    'hourglassIcon'           => 'hourglass_empty',
    'expandIcon'              => 'expand_more',
    'loadParticipantsError'   => __('Could not load participants.', 'nera-competitions'),
    'loadTicketsError'        => __('Could not load ticket list.', 'nera-competitions'),
  ],
];

// Valid JS inside x-data — do not use JSON_HEX_QUOT (\u0022 breaks the object literal).
$json_flags  = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES;
$config_json = wp_json_encode($entry_list_alpine_config, $json_flags);
$x_data_expr = 'neraEntryListGrid(' . $config_json . ')';

get_template_part('template-parts/entry-list/entry-list-grid-alpine');
?>

<section class="py-8 sm:py-12 px-3 sm:px-6 bg-surface">
  <div
    class="max-w-[1200px] mx-auto"
    x-data="<?php echo esc_attr($x_data_expr); ?>"
    @nera-open-entry-list="openParticipants($event.detail)"
    @keydown.escape.window="handleEscape()"
  >
    <?php if (!$query->have_posts()): ?>
      <div class="text-center py-20">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
          <span class="material-symbols-outlined text-4xl text-text-secondary">emoji_events</span>
        </div>
        <h3 class="text-xl font-bold text-text-primary mb-2">
          <?php echo esc_html($empty_heading); ?>
        </h3>
        <p class="text-sm text-text-secondary">
          <?php echo esc_html($empty_description); ?>
        </p>
      </div>
    <?php else: ?>
      <div
        x-ref="grid"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 lg:gap-6"
      >
        <?php
        while ($query->have_posts()) {
          $query->the_post();
          get_template_part('template-parts/entry-list/entry-list-card', null, [
            'product' => wc_get_product(get_the_ID()),
          ]);
        }
        wp_reset_postdata();
        ?>
      </div>

      <div class="mt-10 text-center" x-show="hasMore" x-cloak>
        <button
          type="button"
          @click="loadMore()"
          :disabled="loading"
          class="group relative px-8 py-4 bg-surface text-text-primary border border-gray-200 rounded-xl font-bold text-sm shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <span class="flex items-center justify-center gap-2">
            <span
              class="material-symbols-outlined transition-transform duration-300 group-hover:translate-y-1"
              x-text="loading ? strings.hourglassIcon : strings.expandIcon"
            >expand_more</span>
            <span x-text="loading ? strings.loading : strings.loadMore">
              <?php esc_html_e('Load More', 'nera-competitions'); ?>
            </span>
          </span>
        </button>
      </div>
    <?php endif; ?>

    <?php get_template_part('template-parts/entry-list/participants-modal'); ?>
  </div>
</section>
