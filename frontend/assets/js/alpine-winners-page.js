/**
 * AlpineJS Winners Page Component
 */
(function () {
  function initComponent() {
    if (typeof Alpine === 'undefined') {
      console.error('Alpine.js not loaded - winners page component cannot initialize');
      return;
    }

    Alpine.data('winnersPage', config => ({
      items: Array.isArray(config?.items) ? config.items : [],
      perPage: Number.isFinite(config?.perPage) ? config.perPage : 12,
      activeFilter: 'all',
      visibleCount: Number.isFinite(config?.perPage) ? config.perPage : 12,
      buttonWidths: [],
      indicatorStyle: {
        width: '0px',
        transform: 'translateX(0px)',
        boxShadow: '0 4px 12px rgba(19, 19, 236, 0.3)',
      },
      resizeObserver: null,

      init() {
        this.$nextTick(() => {
          this.updateDesktopIndicator();
          this.scrollActiveChipIntoView();
        });

        this.$watch('activeFilter', () => {
          this.$nextTick(() => {
            this.updateDesktopIndicator();
            this.scrollActiveChipIntoView();
          });
        });

        if (this.$refs.desktopTabs && typeof ResizeObserver !== 'undefined') {
          this.resizeObserver = new ResizeObserver(() => {
            this.updateDesktopIndicator();
          });
          this.resizeObserver.observe(this.$refs.desktopTabs);
        }

        if (this.$el && Array.isArray(this.$el._x_cleanups)) {
          this.$el._x_cleanups.push(() => {
            if (this.resizeObserver) {
              this.resizeObserver.disconnect();
              this.resizeObserver = null;
            }
          });
        }
      },

      get filteredIndexes() {
        const out = [];
        this.items.forEach((item, index) => {
          if (this.activeFilter === 'all' || item.category === this.activeFilter) {
            out.push(index);
          }
        });
        return out;
      },

      get filteredCount() {
        return this.filteredIndexes.length;
      },

      get visibleFilteredCount() {
        return Math.min(this.visibleCount, this.filteredCount);
      },

      visible(index) {
        const position = this.filteredIndexes.indexOf(index);
        return position !== -1 && position < this.visibleCount;
      },

      setFilter(value) {
        if (this.activeFilter === value) return;
        this.activeFilter = value;
        this.visibleCount = this.perPage;
      },

      loadMore() {
        if (this.visibleCount < this.filteredCount) {
          this.visibleCount += this.perPage;
        }
      },

      updateDesktopIndicator() {
        if (!this.$refs.desktopTabs) return;

        const buttons = Array.from(this.$refs.desktopTabs.querySelectorAll('[data-filter-item]'));
        if (!buttons.length) return;

        this.buttonWidths = buttons.map(button => button.offsetWidth || 140);

        const activeIndex = buttons.findIndex(
          button => button.dataset.filterItem === this.activeFilter
        );

        const index = activeIndex >= 0 ? activeIndex : 0;
        const activeWidth = this.buttonWidths[index] || 140;
        const gap = 4;
        const padding = 6;

        let left = padding;
        for (let i = 0; i < index; i += 1) {
          left += (this.buttonWidths[i] || 140) + gap;
        }

        this.indicatorStyle = {
          width: `${activeWidth}px`,
          transform: `translateX(${left}px)`,
          boxShadow: '0 4px 12px rgba(19, 19, 236, 0.3)',
        };
      },

      scrollActiveChipIntoView() {
        if (!this.$refs.mobileScroll) return;
        const chip = this.$refs.mobileScroll.querySelector(
          `[data-filter-chip="${this.activeFilter}"]`
        );
        if (chip && typeof chip.scrollIntoView === 'function') {
          chip.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center',
          });
        }
      },
    }));
  }

  document.addEventListener('alpine:init', initComponent);
})();
