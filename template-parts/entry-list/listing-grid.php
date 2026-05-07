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
?>

<script>
document.addEventListener('alpine:init', () => {
  if (window.__neraEntryListGridRegistered) {
    return;
  }
  window.__neraEntryListGridRegistered = true;

  Alpine.data('neraEntryListGrid', (config) => ({
    restBase: config.restBase || '',
    page: 1,
    hasMore: !!config.hasMore,
    loading: false,
    ajaxUrl: config.ajaxUrl || '',
    ajaxNonce: config.ajaxNonce || '',
    strings: config.strings || {},

    modalOpen: false,
    modalLoading: false,
    modalError: '',
    modalTitle: '',
    modalPayload: null,
    activeProductId: null,
    ticketColumns: [],
    ticketRows: [],
    ticketPagination: { current_page: 1, page_count: 1, has_next: false, has_prev: false },
    ticketSearchDraft: '',
    ticketsLoading: false,

    init() {
      this.$watch('modalOpen', (open) => {
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
          this.$nextTick(() => {
            this.$refs.dialogPanel?.focus?.();
          });
        }
      });
    },

    handleEscape() {
      if (this.modalOpen) {
        this.closeModal();
      }
    },

    async loadMore() {
      if (this.loading || !this.hasMore) {
        return;
      }
      this.loading = true;
      this.page++;
      try {
        const body = new URLSearchParams();
        body.append('action', 'nera_entry_list_load_more');
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
    },

    syncTicketState(ticketLogs) {
      if (!ticketLogs) {
        return;
      }
      this.ticketColumns = ticketLogs.columns || [];
      this.ticketRows = ticketLogs.rows || [];
      this.ticketPagination = ticketLogs.pagination || {
        current_page: 1,
        page_count: 1,
        has_next: false,
        has_prev: false,
      };
      this.ticketSearchDraft = ticketLogs.search || '';
    },

    async openParticipants(detail) {
      const id = detail && detail.id ? parseInt(detail.id, 10) : 0;
      if (!id) {
        return;
      }
      this.activeProductId = id;
      this.modalTitle = detail.title || '';
      this.modalOpen = true;
      this.modalLoading = true;
      this.modalError = '';
      this.modalPayload = null;
      this.ticketColumns = [];
      this.ticketRows = [];
      this.ticketSearchDraft = '';

      try {
        const res = await fetch(`${this.restBase}entry-list/${id}`, {
          headers: { Accept: 'application/json' },
        });
        const body = await res.json();
        if (!res.ok || !body.success) {
          throw new Error(body.message || body.data || 'Request failed');
        }
        this.modalPayload = body.data;
        this.syncTicketState(body.data.ticket_logs);
      } catch (e) {
        this.modalError =
          (e && e.message) || this.strings.loadParticipantsError;
      } finally {
        this.modalLoading = false;
        this.$nextTick(() => {
          this.$refs.dialogPanel?.focus?.();
        });
      }
    },

    closeModal() {
      this.modalOpen = false;
      this.modalLoading = false;
      this.modalError = '';
      this.modalPayload = null;
      this.modalTitle = '';
      this.activeProductId = null;
      this.ticketColumns = [];
      this.ticketRows = [];
      this.ticketSearchDraft = '';
    },

    async fetchTicketsPage(page, search) {
      if (!this.activeProductId) {
        return;
      }
      this.ticketsLoading = true;
      try {
        const u = new URL(`${this.restBase}entry-list/${this.activeProductId}/tickets`);
        u.searchParams.set('page', String(page || 1));
        u.searchParams.set('search', search || '');
        const res = await fetch(u.toString(), { headers: { Accept: 'application/json' } });
        const body = await res.json();
        if (!res.ok || !body.success) {
          throw new Error('Request failed');
        }
        this.syncTicketState(body.data);
      } catch (e) {
        this.modalError = this.strings.loadTicketsError;
      } finally {
        this.ticketsLoading = false;
      }
    },

    ticketPrev() {
      if (!this.ticketPagination.has_prev) {
        return;
      }
      this.fetchTicketsPage(this.ticketPagination.current_page - 1, this.ticketSearchDraft);
    },

    ticketNext() {
      if (!this.ticketPagination.has_next) {
        return;
      }
      this.fetchTicketsPage(this.ticketPagination.current_page + 1, this.ticketSearchDraft);
    },

    ticketSearchSubmit() {
      this.fetchTicketsPage(1, this.ticketSearchDraft);
    },
  }));
});
</script>

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
          <?php esc_html_e('No competitions found', 'nera-competitions'); ?>
        </h3>
        <p class="text-sm text-text-secondary">
          <?php esc_html_e('There are no participant lists available yet. Please check back soon.', 'nera-competitions'); ?>
        </p>
      </div>
    <?php else: ?>
      <div
        x-ref="grid"
        class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 lg:gap-6"
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

    <div
      x-show="modalOpen"
      x-cloak
      class="fixed inset-0 z-200 flex items-end justify-center p-4 sm:items-center"
      role="presentation"
    >
      <div
        class="absolute inset-0 bg-black/50"
        @click="closeModal()"
        aria-hidden="true"
      ></div>

      <div
        class="relative z-10 flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-surface shadow-2xl"
        role="dialog"
        aria-modal="true"
        tabindex="-1"
        x-ref="dialogPanel"
        @click.stop
      >
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
          <div class="min-w-0">
            <h2 id="entry-list-dialog-title" class="text-lg font-bold text-text-primary sm:text-xl" x-text="modalTitle">
            </h2>
            <p class="mt-1 text-xs text-text-secondary" x-show="modalPayload && modalPayload.summary && modalPayload.summary.status_label" x-text="modalPayload && modalPayload.summary ? modalPayload.summary.status_label : ''"></p>
          </div>
          <button
            type="button"
            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-gray-200 p-2 text-text-secondary transition-colors hover:bg-secondary"
            @click="closeModal()"
            aria-label="<?php echo esc_attr(__('Close', 'nera-competitions')); ?>"
          >
            <span class="material-symbols-outlined text-xl">close</span>
          </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
          <template x-if="modalLoading">
            <div class="flex flex-col items-center justify-center py-16 text-text-secondary">
              <span class="material-symbols-outlined mb-3 animate-pulse text-4xl">hourglass_empty</span>
              <p class="text-sm font-semibold"><?php esc_html_e('Loading participants…', 'nera-competitions'); ?></p>
            </div>
          </template>

          <template x-if="!modalLoading && modalError">
            <div class="rounded-xl border border-danger-border bg-danger-bg px-4 py-3 text-sm text-danger">
              <span x-text="modalError"></span>
            </div>
          </template>

          <div x-show="!modalLoading && !modalError && modalPayload" x-cloak class="space-y-8">
            <template x-if="modalPayload && modalPayload.summary && modalPayload.summary.pdf_download_url">
              <div class="flex flex-wrap gap-3">
                <a
                  :href="modalPayload.summary.pdf_download_url"
                  class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-text-primary no-underline transition-colors hover:bg-secondary"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                  <?php esc_html_e('Download PDF', 'lottery-for-woocommerce'); ?>
                </a>
                <a
                  x-show="modalPayload.fallback_url"
                  :href="modalPayload.fallback_url"
                  class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-text-primary no-underline transition-colors hover:bg-secondary"
                >
                  <span class="material-symbols-outlined text-base">open_in_new</span>
                  <?php esc_html_e('Open full page', 'nera-competitions'); ?>
                </a>
              </div>
            </template>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-4" x-show="modalPayload && modalPayload.summary">
              <div class="min-w-0 rounded-xl border border-gray-100 bg-secondary/40 p-3 sm:p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-text-secondary"><?php esc_html_e('Tickets sold', 'nera-competitions'); ?></p>
                <p class="mt-1 text-base font-bold text-text-primary sm:text-lg">
                  <span x-text="modalPayload && modalPayload.summary ? modalPayload.summary.sold : 0"></span>/<span x-text="modalPayload && modalPayload.summary ? modalPayload.summary.max_tickets : 0"></span>
                  <span class="text-xs font-semibold text-primary sm:text-sm">(<span x-text="modalPayload && modalPayload.summary ? modalPayload.summary.progress : 0"></span>%)</span>
                </p>
              </div>
              <div class="min-w-0 rounded-xl border border-gray-100 bg-secondary/40 p-3 sm:p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-text-secondary"><?php esc_html_e('Draw', 'nera-competitions'); ?></p>
                <template x-if="modalPayload && modalPayload.summary && modalPayload.summary.is_active && modalPayload.summary.countdown_timestamp_ms > 0">
                  <div class="mt-1" x-data="countdown(String(modalPayload.summary.countdown_timestamp_ms))">
                    <p class="text-[11px] font-bold uppercase tabular-nums text-text-primary sm:text-xs">
                      <span x-text="days">00</span>d :
                      <span x-text="hours">00</span>h :
                      <span x-text="minutes">00</span>m :
                      <span x-text="seconds">00</span>s
                    </p>
                  </div>
                </template>
                <template x-if="modalPayload && modalPayload.summary && !modalPayload.summary.is_active && modalPayload.summary.draw_date">
                  <p class="mt-1 break-words text-xs font-semibold text-text-primary sm:text-sm" x-text="modalPayload.summary.draw_date"></p>
                </template>
              </div>
            </div>

            <template x-if="modalPayload && modalPayload.winner_logs">
              <div>
                <h3 class="mb-3 text-base font-bold text-text-primary" x-text="modalPayload.winner_logs.heading"></h3>
                <div class="overflow-x-auto rounded-none border border-gray-200">
                  <table class="w-full min-w-[480px] text-sm">
                    <thead>
                      <tr class="bg-primary text-white">
                        <template x-for="col in modalPayload.winner_logs.columns" :key="'w-h-' + col.key">
                          <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" x-text="col.label"></th>
                        </template>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <template x-for="(row, ri) in modalPayload.winner_logs.rows" :key="'w-r-' + ri">
                        <tr class="hover:bg-secondary">
                          <template x-for="cell in row" :key="'w-c-' + ri + '-' + cell.key">
                            <td class="px-4 py-3 text-text-primary" x-text="cell.text"></td>
                          </template>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                </div>
              </div>
            </template>

            <div>
              <h3 class="mb-3 text-base font-bold text-text-primary"><?php esc_html_e('Ticket logs', 'lottery-for-woocommerce'); ?></h3>

              <div class="mb-4 flex flex-col gap-2 sm:flex-row">
                <input
                  type="search"
                  class="flex-1 rounded-lg border border-gray-200 bg-surface px-4 py-2 text-sm text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary/40"
                  x-model="ticketSearchDraft"
                  @keydown.enter.prevent="ticketSearchSubmit()"
                  placeholder="<?php esc_attr_e('Search by ticket number…', 'lottery-for-woocommerce'); ?>"
                />
                <button
                  type="button"
                  class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-dark disabled:opacity-60"
                  @click="ticketSearchSubmit()"
                  :disabled="ticketsLoading"
                >
                  <?php echo esc_html(function_exists('lty_get_ticket_search_button_label') ? lty_get_ticket_search_button_label() : __('Search', 'nera-competitions')); ?>
                </button>
              </div>

              <template x-if="ticketsLoading && ticketRows.length === 0">
                <p class="py-8 text-center text-sm text-text-secondary"><?php esc_html_e('Loading tickets…', 'nera-competitions'); ?></p>
              </template>

              <template x-if="!ticketsLoading && ticketRows.length === 0">
                <div class="rounded-xl border border-gray-200 bg-secondary py-10 text-center text-sm text-text-secondary">
                  <?php esc_html_e('No tickets found.', 'lottery-for-woocommerce'); ?>
                </div>
              </template>

              <div class="overflow-x-auto rounded-none border border-gray-200" x-show="ticketRows.length > 0">
                <table class="w-full min-w-[480px] text-sm">
                  <thead>
                    <tr class="bg-primary text-white">
                      <template x-for="col in ticketColumns" :key="'t-h-' + col.key">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider" x-text="col.label"></th>
                      </template>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <template x-for="(row, ri) in ticketRows" :key="'t-r-' + ri">
                      <tr class="hover:bg-secondary">
                        <template x-for="cell in row" :key="'t-c-' + ri + '-' + cell.key">
                          <td class="px-4 py-3 font-mono text-text-primary" x-text="cell.text"></td>
                        </template>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 flex flex-wrap items-center justify-between gap-3" x-show="ticketPagination.page_count > 1">
                <p class="text-xs text-text-secondary">
                  <?php esc_html_e('Page', 'nera-competitions'); ?>
                  <span x-text="ticketPagination.current_page"></span>
                  /
                  <span x-text="ticketPagination.page_count"></span>
                </p>
                <div class="flex gap-2">
                  <button
                    type="button"
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-text-primary transition-colors hover:bg-secondary disabled:opacity-40"
                    @click="ticketPrev()"
                    :disabled="ticketsLoading || !ticketPagination.has_prev"
                  >
                    <?php esc_html_e('Previous', 'nera-competitions'); ?>
                  </button>
                  <button
                    type="button"
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-text-primary transition-colors hover:bg-secondary disabled:opacity-40"
                    @click="ticketNext()"
                    :disabled="ticketsLoading || !ticketPagination.has_next"
                  >
                    <?php esc_html_e('Next', 'nera-competitions'); ?>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
