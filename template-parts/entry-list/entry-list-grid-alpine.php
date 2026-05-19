<?php
/**
 * Entry List Grid — Alpine.js component registration
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}
?>

<script>
(function () {
  function registerNeraEntryListGrid() {
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
  }

  if (window.Alpine) {
    registerNeraEntryListGrid();
  } else {
    document.addEventListener('alpine:init', registerNeraEntryListGrid);
  }
})();
</script>
