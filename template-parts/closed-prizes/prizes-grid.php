<?php
/**
 * Closed Prizes Grid
 *
 * Server-renders first page of closed/finished competitions.
 * Subsequent pages load via AJAX (nera_closed_prizes_load_more).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$query = new WP_Query(
  function_exists('nera_closed_prizes_wp_query_args')
    ? nera_closed_prizes_wp_query_args(1)
    : [],
);

$has_more      = $query->max_num_pages > 1;
$ajax_url      = admin_url('admin-ajax.php');
$ajax_nonce    = wp_create_nonce('nera_nonce');
?>

<section class="py-12 px-4 sm:px-6 bg-background-light">
  <div class="max-w-[1200px] mx-auto"
    x-data="{
      page: 1,
      hasMore: <?php echo $has_more ? 'true' : 'false'; ?>,
      loading: false,
      ajaxUrl: <?php echo wp_json_encode($ajax_url); ?>,
      ajaxNonce: <?php echo wp_json_encode($ajax_nonce); ?>,

      async loadMore() {
        if (this.loading || !this.hasMore) return;
        this.loading = true;
        this.page++;
        try {
          const body = new URLSearchParams();
          body.append('action', 'nera_closed_prizes_load_more');
          body.append('nonce', this.ajaxNonce);
          body.append('paged', String(this.page));
          const res = await fetch(this.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
          });
          const data = await res.json();
          if (data.success && data.data.html) {
            this.$refs.grid.insertAdjacentHTML('beforeend', data.data.html);
          }
          this.hasMore = data.success ? data.data.has_more : false;
        } catch (e) {
          this.page--;
        } finally {
          this.loading = false;
        }
      }
    }"
  >

    <?php if (!$query->have_posts()): ?>

      <!-- Empty State -->
      <div class="text-center py-20">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
          <span class="material-symbols-outlined text-4xl text-text-secondary">emoji_events</span>
        </div>
        <h3 class="text-xl font-bold text-text-primary mb-2">
          <?php esc_html_e('No closed prizes yet', 'nera-competitions'); ?>
        </h3>
        <p class="text-sm text-text-secondary">
          <?php esc_html_e('Check back after our competitions have drawn their winners.', 'nera-competitions'); ?>
        </p>
      </div>

    <?php else: ?>

      <!-- Cards Grid -->
      <div
        x-ref="grid"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <?php
        while ($query->have_posts()) {
          $query->the_post();
          get_template_part('template-parts/closed-prizes/closed-prize-card', null, [
            'product' => wc_get_product(get_the_ID()),
          ]);
        }
        wp_reset_postdata();
        ?>
      </div>

      <!-- Load More -->
      <div class="mt-10 text-center" x-show="hasMore" x-cloak>
        <button
          type="button"
          @click="loadMore()"
          :disabled="loading"
          class="group relative px-8 py-4 bg-surface text-text-primary border border-gray-200 rounded-xl font-bold text-sm shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <span class="flex items-center justify-center gap-2">
            <span class="material-symbols-outlined transition-transform duration-300 group-hover:translate-y-1"
              x-text="loading ? 'hourglass_empty' : 'expand_more'">expand_more</span>
            <span x-text="loading
              ? <?php echo wp_json_encode(__('Loading…', 'nera-competitions')); ?>
              : <?php echo wp_json_encode(__('Load More', 'nera-competitions')); ?>">
              <?php esc_html_e('Load More', 'nera-competitions'); ?>
            </span>
          </span>
        </button>
      </div>

    <?php endif; ?>

  </div>
</section>
