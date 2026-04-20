<?php
/**
 * Dynamic Winners Grid
 *
 * Server-renders first page; subsequent pages via AJAX (nera_winners_dynamic_load_more).
 *
 * Alpine state lives in a small factory (see script below). Inline x-data objects with
 * method shorthand break HTML/Alpine parsing and cause ReferenceErrors on child bindings.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$counts = function_exists('nera_winners_dynamic_get_filter_counts')
  ? nera_winners_dynamic_get_filter_counts()
  : ['all' => 0, 'main' => 0, 'instant' => 0];

$dataset = function_exists('nera_winners_dynamic_get_page_dataset')
  ? nera_winners_dynamic_get_page_dataset(1, 'all')
  : ['rows' => [], 'has_more' => false, 'total' => 0, 'per_page' => 12];

$rows = isset($dataset['rows']) && is_array($dataset['rows']) ? $dataset['rows'] : [];

$has_more       = !empty($dataset['has_more']);
$total_active   = isset($dataset['total']) ? (int) $dataset['total'] : 0;
$per_page_ds    = isset($dataset['per_page']) ? (int) $dataset['per_page'] : 12;
$showing_start  = count($rows);
$global_empty   = (int) ($counts['all'] ?? 0) === 0;

$ajax_url = admin_url('admin-ajax.php');
$nonce    = wp_create_nonce('nera_nonce');

$alpine_config = [
  'hasMore'        => $has_more,
  'counts'         => $counts,
  'showingCount'   => (int) $showing_start,
  'totalForActive' => (int) $total_active,
  'perPage'        => (int) $per_page_ds,
  'globalEmpty'    => $global_empty,
  'ajaxUrl'        => $ajax_url,
  'ajaxNonce'      => $nonce,
  'strings'        => [
    'loading'       => __('Loading…', 'nera-competitions'),
    'loadMore'      => __('Load More', 'nera-competitions'),
    'showing'       => __('Showing', 'nera-competitions'),
    'of'            => __('of', 'nera-competitions'),
    'winners'       => __('winners', 'nera-competitions'),
    'hourglassIcon' => 'hourglass_empty',
    'expandIcon'    => 'expand_more',
  ],
];

// Valid JS inside x-data — do not use JSON_HEX_QUOT (\u0022 breaks the object literal).
$json_flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES;
$config_json = wp_json_encode($alpine_config, $json_flags);
$x_data_expr = 'neraWinnersDynamicGrid(' . $config_json . ')';
?>

<script>
(function () {
  window.neraWinnersDynamicGrid = function (config) {
    config = config || {};
    const strings = config.strings || {};

    return {
      activeFilter: 'all',
      page: 1,
      hasMore: !!config.hasMore,
      loading: false,
      counts: config.counts || { all: 0, main: 0, instant: 0 },
      showingCount: typeof config.showingCount === 'number' ? config.showingCount : 0,
      totalForActive: typeof config.totalForActive === 'number' ? config.totalForActive : 0,
      perPage: typeof config.perPage === 'number' ? config.perPage : 12,
      globalEmpty: !!config.globalEmpty,
      ajaxUrl: config.ajaxUrl || '',
      ajaxNonce: config.ajaxNonce || '',
      strings,

      tabClass(f) {
        const base =
          'inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 border focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2';
        return this.activeFilter === f
          ? base + ' bg-primary text-white border-primary shadow-sm'
          : base + ' bg-surface text-text-primary border-gray-200 hover:border-primary/40 hover:shadow-sm';
      },

      badgeClass(f) {
        return this.activeFilter === f
          ? 'inline-flex min-w-[1.5rem] justify-center rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold tabular-nums'
          : 'inline-flex min-w-[1.5rem] justify-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-text-secondary tabular-nums';
      },

      async fetchPage(paged, filter, mode) {
        const body = new URLSearchParams();
        body.append('action', 'nera_winners_dynamic_load_more');
        body.append('nonce', this.ajaxNonce);
        body.append('paged', String(paged));
        body.append('filter', filter);

        const res = await fetch(this.ajaxUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString(),
        });

        const data = await res.json();
        if (!data.success || !data.data) {
          return false;
        }

        const d = data.data;

        if (mode === 'replace') {
          this.$refs.grid.innerHTML = d.html || '';
        } else if (d.html) {
          this.$refs.grid.insertAdjacentHTML('beforeend', d.html);
        }

        this.hasMore = !!d.has_more;
        this.totalForActive =
          typeof d.total === 'number' ? d.total : parseInt(d.total, 10) || 0;
        this.showingCount =
          typeof d.showing === 'number' ? d.showing : parseInt(d.showing, 10) || 0;
        if (d.per_page) {
          this.perPage = parseInt(d.per_page, 10) || this.perPage;
        }
        this.page = paged;

        return true;
      },

      async setFilter(filter) {
        if (this.loading || this.globalEmpty || this.activeFilter === filter) {
          return;
        }

        const prev = this.activeFilter;
        this.loading = true;
        this.activeFilter = filter;

        try {
          const ok = await this.fetchPage(1, filter, 'replace');
          if (!ok) {
            this.activeFilter = prev;
          }
        } catch (e) {
          this.activeFilter = prev;
        } finally {
          this.loading = false;
        }
      },

      async loadMore() {
        if (this.loading || !this.hasMore || this.globalEmpty) {
          return;
        }

        this.loading = true;
        const nextPage = this.page + 1;

        try {
          await this.fetchPage(nextPage, this.activeFilter, 'append');
        } catch (e) {
          /* keep page state */
        } finally {
          this.loading = false;
        }
      },
    };
  };
})();
</script>

<section class="py-12 px-4 sm:px-6 bg-background-light">
  <div
    class="max-w-[1200px] mx-auto"
    x-data="<?php echo esc_attr($x_data_expr); ?>"
  >

    <?php if ($global_empty) : ?>

      <div class="text-center py-20">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
          <span class="material-symbols-outlined text-4xl text-text-secondary">emoji_events</span>
        </div>
        <h3 class="text-xl font-bold text-text-primary mb-2">
          <?php esc_html_e('No winners to show yet', 'nera-competitions'); ?>
        </h3>
        <p class="text-sm text-text-secondary max-w-md mx-auto">
          <?php esc_html_e('Winners appear here once competitions have ended and winners are selected in the giveaway settings.', 'nera-competitions'); ?>
        </p>
      </div>

    <?php else : ?>

      <div
        class="flex flex-wrap gap-2 sm:gap-3 mb-8"
        role="tablist"
        aria-label="<?php esc_attr_e('Filter winners', 'nera-competitions'); ?>"
      >
        <button
          type="button"
          role="tab"
          :aria-selected="activeFilter === 'all'"
          @click="setFilter('all')"
          :disabled="loading"
          :class="tabClass('all')"
        >
          <?php esc_html_e('All Winners', 'nera-competitions'); ?>
          <span :class="badgeClass('all')" x-text="counts.all"></span>
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="activeFilter === 'main'"
          @click="setFilter('main')"
          :disabled="loading"
          :class="tabClass('main')"
        >
          <?php esc_html_e('Live draw', 'nera-competitions'); ?>
          <span :class="badgeClass('main')" x-text="counts.main"></span>
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="activeFilter === 'instant'"
          @click="setFilter('instant')"
          :disabled="loading"
          :class="tabClass('instant')"
        >
          <?php esc_html_e('Instant Win', 'nera-competitions'); ?>
          <span :class="badgeClass('instant')" x-text="counts.instant"></span>
        </button>
      </div>

      <p
        x-show="showingCount === 0 && !loading"
        x-cloak
        class="text-center text-text-secondary py-12 mb-0"
      >
        <?php esc_html_e('No winners in this category yet.', 'nera-competitions'); ?>
      </p>

      <div
        x-ref="grid"
        x-show="showingCount > 0 || loading"
        class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6"
      >
        <?php
        foreach ($rows as $row) {
          get_template_part('template-parts/winners-dynamic/winner-card', null, [
            'row' => $row,
          ]);
        }
        ?>
      </div>

      <p
        class="mt-8 text-center text-sm text-text-secondary"
        x-show="totalForActive > 0"
        x-cloak
      >
        <span x-text="strings.showing"></span>
        <span class="font-semibold text-text-primary tabular-nums" x-text="showingCount"></span>
        <span x-text="strings.of"></span>
        <span class="font-semibold text-text-primary tabular-nums" x-text="totalForActive"></span>
        <span x-text="strings.winners"></span>
      </p>

      <div class="mt-6 text-center" x-show="hasMore" x-cloak>
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

  </div>
</section>
