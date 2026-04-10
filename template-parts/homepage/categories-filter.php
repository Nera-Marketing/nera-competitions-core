<?php
/**
 * Advanced Filter Section Template Part
 *
 * Standalone section: multi-select category dropdown, price filter,
 * sort dropdown, and a competition cards grid. Category selection is
 * reflected in the URL (?product_cat=slug1,slug2); when present, the
 * initial grid is filtered server-side. Price and sort remain client-side.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

// Get all active product categories
$categories = get_terms([
  'taxonomy' => 'product_cat',
  'hide_empty' => true,
  'exclude' => get_option('default_product_cat'),
]);

$category_colors = nera_advanced_filter_category_colors();

// Prepare category data for the Alpine combobox
$cat_names = [];
$cat_options = [];
if (!empty($categories) && !is_wp_error($categories)) {
  foreach ($categories as $cat) {
    $cat_names[$cat->slug] = $cat->name;
    $cat_options[] = ['slug' => $cat->slug, 'name' => $cat->name];
  }
}


// URL ?product_cat=slug1,slug2 — validated slugs only (OR semantics via tax IN).
$url_category_slugs = [];
if (isset($_GET['product_cat'])) {
  $url_category_slugs = nera_advanced_filter_whitelist_category_slugs(wp_unslash($_GET['product_cat']));
}

$filter_competitions_args = nera_advanced_filter_competitions_wp_query_args($url_category_slugs, 1);

$competitions = new WP_Query($filter_competitions_args);
$nera_adv_grid_max_pages = (int) $competitions->max_num_pages;
$nera_adv_posts_per_page = function_exists('nera_advanced_filter_get_posts_per_page')
  ? nera_advanced_filter_get_posts_per_page()
  : 9;
?>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('advancedFilterSection', () => ({
      selectedCategories: <?php echo wp_json_encode($url_category_slugs); ?>,
      priceRange: '',
      sortBy: 'ending-soon',
      categoryDropdownOpen: false,
      categorySearchTerm: '',
      categoryNames: <?php echo wp_json_encode($cat_names); ?>,
      categoryOptions: <?php echo wp_json_encode($cat_options); ?>,
      categoryColors: <?php echo wp_json_encode($category_colors); ?>,
      ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
      ajaxNonce: <?php echo wp_json_encode(wp_create_nonce('nera_nonce')); ?>,
      gridFoundPosts: <?php echo (int) $competitions->found_posts; ?>,
      gridPage: 1,
      gridMaxPages: <?php echo (int) $nera_adv_grid_max_pages; ?>,
      gridPerPage: <?php echo (int) $nera_adv_posts_per_page; ?>,
      loadMoreLoading: false,
      gridLoading: false,
      _categoryDebounceTimer: null,
      _categoryFetchAbort: null,
      _categoryFetchSeq: 0,

      init() {
        this.$watch('sortBy', () => this.sortGrid());
        window.addEventListener('popstate', () => this.applyUrlToCategories());
        this.syncUrl();
        this.$nextTick(() => {
          this.$watch(
            'selectedCategories',
            () => {
              this.syncUrl();
              this.scheduleCategoryGridFetch();
            },
            { deep: true },
          );
        });
      },

      syncUrl() {
        const url = new URL(window.location.href);
        const slugs = this.selectedCategories.filter(Boolean);
        if (slugs.length === 0) {
          url.searchParams.delete('product_cat');
        } else {
          url.searchParams.set('product_cat', slugs.join(','));
        }
        history.replaceState({}, '', url.toString());
      },

      scheduleCategoryGridFetch() {
        clearTimeout(this._categoryDebounceTimer);
        this._categoryDebounceTimer = setTimeout(() => this.fetchCategoryGrid(), 300);
      },

      async fetchCategoryGrid() {
        const grid = document.getElementById('advanced-filter-grid');
        if (!grid) return;
        const fetchId = ++this._categoryFetchSeq;
        if (this._categoryFetchAbort) {
          this._categoryFetchAbort.abort();
        }
        this._categoryFetchAbort = new AbortController();
        const signal = this._categoryFetchAbort.signal;
        this.gridLoading = true;
        const body = new URLSearchParams();
        body.append('action', 'nera_advanced_filter_competitions');
        body.append('nonce', this.ajaxNonce);
        body.append('product_cat', this.selectedCategories.join(','));
        body.append('paged', '1');
        body.append('append', '0');
        try {
          const res = await fetch(this.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
            signal,
          });
          const json = await res.json();
          if (!json.success || !json.data) {
            throw new Error(json.data && json.data.message ? json.data.message : 'Request failed');
          }
          grid.innerHTML = json.data.html;
          this.gridFoundPosts = typeof json.data.found_posts === 'number' ? json.data.found_posts : parseInt(json.data.found_posts, 10) || 0;
          this.gridPage = 1;
          this.gridMaxPages = typeof json.data.max_num_pages === 'number'
            ? json.data.max_num_pages
            : parseInt(json.data.max_num_pages, 10) || 1;
          if (window.Alpine && typeof Alpine.initTree === 'function') {
            Alpine.initTree(grid);
          }
          this.$nextTick(() => this.sortGrid());
        } catch (e) {
          if (e.name === 'AbortError') return;
          console.error(e);
        } finally {
          if (fetchId === this._categoryFetchSeq) {
            this.gridLoading = false;
          }
        }
      },

      async loadMore() {
        if (this.loadMoreLoading || this.gridPage >= this.gridMaxPages || this.gridLoading) return;
        const sentinel = document.getElementById('advanced-filter-grid-append-sentinel');
        const grid = document.getElementById('advanced-filter-grid');
        if (!grid || !sentinel) return;
        this.loadMoreLoading = true;
        const nextPage = this.gridPage + 1;
        const body = new URLSearchParams();
        body.append('action', 'nera_advanced_filter_competitions');
        body.append('nonce', this.ajaxNonce);
        body.append('product_cat', this.selectedCategories.join(','));
        body.append('paged', String(nextPage));
        body.append('append', '1');
        try {
          const res = await fetch(this.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString(),
          });
          const json = await res.json();
          if (!json.success || !json.data) {
            throw new Error(json.data && json.data.message ? json.data.message : 'Request failed');
          }
          const html = (json.data.html || '').trim();
          if (!html) {
            this.gridMaxPages = this.gridPage;
            return;
          }
          const tpl = document.createElement('template');
          tpl.innerHTML = html;
          const toInsert = [...tpl.content.children];
          toInsert.forEach(node => {
            grid.insertBefore(node, sentinel);
          });
          if (json.data.found_posts !== undefined) {
            this.gridFoundPosts =
              typeof json.data.found_posts === 'number'
                ? json.data.found_posts
                : parseInt(json.data.found_posts, 10) || 0;
          }
          if (json.data.max_num_pages !== undefined) {
            this.gridMaxPages = parseInt(json.data.max_num_pages, 10) || this.gridMaxPages;
          }
          this.gridPage = nextPage;
          if (window.Alpine && typeof Alpine.initTree === 'function') {
            toInsert.forEach(el => Alpine.initTree(el));
          }
          this.$nextTick(() => this.sortGrid());
        } catch (e) {
          console.error(e);
        } finally {
          this.loadMoreLoading = false;
        }
      },

      applyUrlToCategories() {
        const params = new URLSearchParams(window.location.search);
        const raw = params.get('product_cat');
        const slugByLower = new Map(
          this.categoryOptions.map(o => [o.slug.toLowerCase(), o.slug]),
        );
        const next = [];
        if (raw) {
          raw.split(',').forEach(part => {
            const key = String(part).trim().toLowerCase();
            if (key === '') return;
            const slug = slugByLower.get(key);
            if (slug && !next.includes(slug)) next.push(slug);
          });
        }
        clearTimeout(this._categoryDebounceTimer);
        this.selectedCategories = next;
        clearTimeout(this._categoryDebounceTimer);
        this.$nextTick(() => {
          this.fetchCategoryGrid();
        });
      },

      filteredCategories() {
        if (this.categorySearchTerm === '') return this.categoryOptions;
        let term = this.categorySearchTerm.toLowerCase();
        return this.categoryOptions.filter(o => o.name.toLowerCase().includes(term));
      },

      toggleCategory(slug) {
        let idx = this.selectedCategories.indexOf(slug);
        idx > -1 ? this.selectedCategories.splice(idx, 1) : this.selectedCategories.push(slug);
      },

      categoryMatch(categoriesJson) {
        if (this.selectedCategories.length === 0) return true;
        return this.selectedCategories.some(c => JSON.parse(categoriesJson).includes(c));
      },

      priceMatch(priceStr) {
        if (this.priceRange === '') return true;
        let p = parseFloat(priceStr);
        if (this.priceRange === '0-5') return p < 5;
        if (this.priceRange === '5-10') return p >= 5 && p < 10;
        if (this.priceRange === '10-25') return p >= 10 && p < 25;
        if (this.priceRange === '25+') return p >= 25;
        return true;
      },

      hasActiveFilters() {
        return this.selectedCategories.length > 0 || this.priceRange !== '' || this.sortBy !== 'ending-soon';
      },

      hasMatchingCards() {
        return [...document.querySelectorAll('#advanced-filter-grid [data-price]')].some(c =>
          this.categoryMatch(c.dataset.categories) && this.priceMatch(c.dataset.price)
        );
      },

      selectAllCategories() {
        if (this.selectedCategories.length === this.categoryOptions.length) {
          this.selectedCategories = [];
        } else {
          this.selectedCategories = this.categoryOptions.map(o => o.slug);
        }
      },

      clearFilters() {
        this.selectedCategories = [];
        this.priceRange = '';
        this.sortBy = 'ending-soon';
        this.categorySearchTerm = '';
        this.$nextTick(() => this.sortGrid());
      },

      sortGrid() {
        let grid = document.getElementById('advanced-filter-grid');
        if (!grid) return;
        const sentinel = document.getElementById('advanced-filter-grid-append-sentinel');
        let cards = Array.from(grid.querySelectorAll('[data-price]'));
        cards.sort((a, b) => {
          switch (this.sortBy) {
            case 'price-low': return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price-high': return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'newest': return Number(b.dataset.postedDate) - Number(a.dataset.postedDate);
            case 'popularity': return Number(b.dataset.popularity) - Number(a.dataset.popularity);
            default: return Number(a.dataset.endDate) - Number(b.dataset.endDate);
          }
        });
        cards.forEach(c => {
          if (sentinel) {
            grid.insertBefore(c, sentinel);
          } else {
            grid.appendChild(c);
          }
        });
      }
    }));
  });
</script>

<section class="py-12 bg-gray-50" id="advanced-filter-competitions" x-data="advancedFilterSection">

  <div class="max-w-[1400px] mx-auto px-4 lg:px-10">

    <!-- Section Header -->
    <div class="mb-10 text-center" data-aos="fade-up" data-aos-duration="600">

    </div>

    <!-- Filter Bar -->
    <div class="relative z-10" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-3 mb-6
                  bg-surface border border-gray-100 rounded-2xl
                  shadow-[0_2px_20px_-4px_rgba(0,0,0,0.07)]
                  p-3 sm:p-4
                  transition-shadow duration-300
                  hover:shadow-[0_8px_24px_-6px_rgba(0,0,0,0.08)]">

        <!-- Filter Icon Label -->
        <div class="hidden sm:flex items-center gap-2 pl-1 pr-3 border-r border-gray-200 mr-1 shrink-0">
          <svg class="w-4 h-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
          <span class="text-[0.6rem] font-bold uppercase tracking-[0.18em] text-text-secondary"><?php _e('Filters', 'nera-competitions'); ?></span>
          <!-- Active filter count badge -->
          <span x-show="hasActiveFilters()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-50"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-50"
                x-text="selectedCategories.length + (priceRange !== '' ? 1 : 0) + (sortBy !== 'ending-soon' ? 1 : 0)"
                class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary text-white text-[0.6rem] font-bold leading-none">
          </span>
        </div>

        <!-- Category Combobox (multi-select) -->
        <div class="relative" @click.outside="categoryDropdownOpen = false">

          <!-- Trigger -->
          <div @click="categoryDropdownOpen = !categoryDropdownOpen"
            :class="categoryDropdownOpen ? 'border-primary ring-2 ring-primary/20' : 'border-gray-200 hover:border-gray-300'"
            class="relative min-h-[44px] min-w-[200px] flex flex-wrap items-center gap-1.5 px-3.5 py-2 pr-9
                   bg-surface border rounded-xl cursor-pointer
                   transition-all duration-300
                   hover:-translate-y-px hover:shadow-[0_2px_8px_rgba(0,0,0,0.06)]">

            <!-- Selected Chips -->
            <template x-for="slug in selectedCategories.slice(0, 3)" :key="slug">
              <span x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 scale-75"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-75"
                class="inline-flex items-center gap-1.5 px-2.5 py-[3px] rounded-full text-white
                       shadow-[0_1px_3px_rgba(0,0,0,.2)] border border-white/20"
                :style="{ backgroundColor: categoryColors[slug] || '#1313ec' }">
                <span class="w-1.5 h-1.5 rounded-full bg-surface/50 shrink-0"></span>
                <span x-text="categoryNames[slug]" class="text-[0.62rem] font-semibold uppercase tracking-[0.08em]"></span>
                <button type="button" @click.stop="toggleCategory(slug)"
                  class="opacity-50 hover:opacity-100 transition-opacity ml-0.5 -mr-0.5">
                  <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"
                    stroke-linecap="round">
                    <path d="M18 6L6 18M6 6l12 12" />
                  </svg>
                </button>
              </span>
            </template>

            <!-- +N more badge -->
            <span x-show="selectedCategories.length > 3"
              class="inline-flex items-center px-2.5 py-[3px] rounded-full text-xs font-medium bg-gray-100 text-text-primary">
              +<span x-text="selectedCategories.length - 3"></span> more
            </span>

            <!-- Search Input -->
            <input type="text" x-model="categorySearchTerm" @click.stop="categoryDropdownOpen = true"
              @keydown.escape="categoryDropdownOpen = false"
              :placeholder="selectedCategories.length === 0 ? '<?php echo esc_js(
                __('Select categories…', 'nera-competitions'),
              ); ?>' : '<?php echo esc_js(__('Search…', 'nera-competitions')); ?>'"
              class="flex-1 min-w-[60px] bg-transparent border-none outline-none text-sm font-medium text-text-primary placeholder-gray-400 cursor-text">

            <!-- Chevron -->
            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
              <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': categoryDropdownOpen }"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
              </svg>
            </span>
          </div>

          <!-- Dropdown -->
          <div x-show="categoryDropdownOpen" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute z-50 top-full mt-2 w-full min-w-[240px] bg-surface border border-gray-200 rounded-xl shadow-lg overflow-hidden">
            <ul class="max-h-52 overflow-y-auto py-1.5" role="listbox">
              <template x-for="option in filteredCategories()" :key="option.slug">
                <li @click="toggleCategory(option.slug)"
                  :class="selectedCategories.includes(option.slug) ? 'bg-primary/5 border-l-2 border-l-primary' : 'hover:bg-gray-50 border-l-2 border-l-transparent'"
                  class="flex items-center gap-2.5 px-3 py-2.5 text-sm cursor-pointer transition-all duration-150" role="option"
                  :aria-selected="selectedCategories.includes(option.slug)">
               
                  <!-- Checkbox indicator -->
                  <span class="flex items-center justify-center w-4 h-4 rounded border transition-all duration-200"
                    :class="selectedCategories.includes(option.slug) ? 'bg-primary border-primary scale-110' : 'border-gray-300 bg-surface'">
                    <template x-if="selectedCategories.includes(option.slug)">
                      <svg class="w-2.5 h-2.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                      </svg>
                    </template>
                  </span>
                  <!-- Option label -->
                  <span
                    :class="selectedCategories.includes(option.slug) ? 'text-primary font-semibold uppercase tracking-[0.06em] text-xs' : 'text-text-primary text-sm'"
                    x-text="option.name"></span>
                </li>
              </template>
              <!-- No search results -->
              <template x-if="filteredCategories().length === 0">
                <li class="px-3 py-2.5 text-sm text-text-secondary text-center">
                  <?php _e('No matching categories', 'nera-competitions'); ?>
                </li>
              </template>
            </ul>
          </div>
        </div>

        <!-- Price Dropdown -->
        <div class="relative group transition-all duration-300 hover:-translate-y-px hover:shadow-[0_2px_8px_rgba(0,0,0,0.06)] rounded-xl">
          <!-- Prefix icon -->
          <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-primary group-focus-within:text-primary transition-colors duration-200">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.5 9a3 3 0 0 1 5 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
          <select x-model="priceRange"
            class="appearance-none bg-surface border border-gray-200 rounded-xl
                   h-[44px] pl-9 pr-10 text-sm font-medium text-text-primary
                   cursor-pointer
                   hover:border-primary
                   focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none
                   transition-colors duration-300 w-full md:w-auto">
            <option value=""><?php _e('All Prices', 'nera-competitions'); ?></option>
            <option value="0-5"><?php _e('Under £5', 'nera-competitions'); ?></option>
            <option value="5-10"><?php _e('£5 – £10', 'nera-competitions'); ?></option>
            <option value="10-25"><?php _e('£10 – £25', 'nera-competitions'); ?></option>
            <option value="25+"><?php _e('£25+', 'nera-competitions'); ?></option>
          </select>
          <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </span>
        </div>

        <!-- Sort Dropdown -->
        <div class="relative group transition-all duration-300 hover:-translate-y-px hover:shadow-[0_2px_8px_rgba(0,0,0,0.06)] rounded-xl">
          <!-- Prefix icon -->
          <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-primary group-focus-within:text-primary transition-colors duration-200">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M3 6h18M7 12h10M11 18h2"/>
            </svg>
          </span>
          <select x-model="sortBy"
            class="appearance-none bg-surface border border-gray-200 rounded-xl
                   h-[44px] pl-9 pr-10 text-sm font-medium text-text-primary
                   cursor-pointer
                   hover:border-primary
                   focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none
                   transition-colors duration-300 w-full md:w-auto">
            <option value="ending-soon"><?php _e('Ending Soon', 'nera-competitions'); ?></option>
            <option value="newest"><?php _e('Newest First', 'nera-competitions'); ?></option>
            <option value="price-low"><?php _e('Price: Low to High', 'nera-competitions'); ?></option>
            <option value="price-high"><?php _e('Price: High to Low', 'nera-competitions'); ?></option>
            <option value="popularity"><?php _e('Most Popular', 'nera-competitions'); ?></option>
          </select>
          <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </span>
        </div>

        <!-- Clear Filters Button -->
        <button type="button" x-show="hasActiveFilters()" @click="clearFilters()"
          class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-text-secondary hover:text-primary hover:bg-gray-50 rounded-lg border border-transparent hover:border-gray-200 transition-all duration-200">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
          <?php _e('Clear', 'nera-competitions'); ?>
        </button>

      </div>

      <!-- Active Filters Tag Bar -->
      <div x-show="hasActiveFilters()"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 -translate-y-2"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 -translate-y-2"
           class="flex flex-wrap items-center gap-2 mb-6 px-1">

        <span class="text-[0.6rem] font-bold uppercase tracking-[0.18em] text-text-secondary mr-1">
          <?php _e('Active:', 'nera-competitions'); ?>
        </span>

        <!-- Category filter pills -->
        <template x-for="slug in selectedCategories" :key="'af-' + slug">
          <button type="button" @click="toggleCategory(slug)"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 scale-90"
                  x-transition:enter-end="opacity-100 scale-100"
                  x-transition:leave="transition ease-in duration-150"
                  x-transition:leave-start="opacity-100 scale-100"
                  x-transition:leave-end="opacity-0 scale-90"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                         text-[0.62rem] font-semibold uppercase tracking-[0.1em]
                         text-white border border-white/20
                         shadow-[0_1px_4px_rgba(0,0,0,0.15)]
                         hover:opacity-80 hover:-translate-y-px
                         transition-all duration-150"
                  :style="{ backgroundColor: categoryColors[slug] || '#1313ec' }">
            <span x-text="categoryNames[slug]"></span>
            <svg class="w-2.5 h-2.5 opacity-70" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="3.5" stroke-linecap="round">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </template>

        <!-- Price filter pill -->
        <template x-if="priceRange !== ''">
          <button type="button" @click="priceRange = ''"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 scale-90"
                  x-transition:enter-end="opacity-100 scale-100"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                         text-[0.62rem] font-semibold uppercase tracking-[0.1em]
                         bg-primary/10 text-primary border border-primary/20
                         hover:bg-primary/15 hover:-translate-y-px
                         transition-all duration-150">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.5 9a3 3 0 0 1 5 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span x-text="priceRange === '0-5' ? 'Under £5' : priceRange === '5-10' ? '£5–£10' : priceRange === '10-25' ? '£10–£25' : '£25+'"></span>
            <svg class="w-2.5 h-2.5 opacity-70" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="3.5" stroke-linecap="round">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </template>

        <!-- Sort pill (only when not default) -->
        <template x-if="sortBy !== 'ending-soon'">
          <button type="button" @click="sortBy = 'ending-soon'; $nextTick(() => sortGrid())"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 scale-90"
                  x-transition:enter-end="opacity-100 scale-100"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full
                         text-[0.62rem] font-semibold uppercase tracking-[0.1em]
                         bg-primary/10 text-primary border border-primary/20
                         hover:bg-primary/15 hover:-translate-y-px
                         transition-all duration-150">
            <span x-text="sortBy === 'newest' ? 'Newest' : sortBy === 'price-low' ? 'Price ↑' : sortBy === 'price-high' ? 'Price ↓' : 'Popular'"></span>
            <svg class="w-2.5 h-2.5 opacity-70" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="3.5" stroke-linecap="round">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </template>

        <!-- Clear All -->
        <button type="button" @click="clearFilters()"
                class="ml-auto text-[0.6rem] font-semibold uppercase tracking-[0.12em]
                       text-text-secondary hover:text-primary underline underline-offset-2
                       transition-colors duration-150">
          <?php _e('Clear All', 'nera-competitions'); ?>
        </button>

      </div>
    </div>

    <!-- Results Count Bar -->
    <div class="flex items-center justify-between mb-5 px-1" data-aos="fade-up" data-aos-duration="400" data-aos-delay="200">
      <p class="text-xs text-text-secondary font-medium">
        <?php _e('Showing', 'nera-competitions'); ?>
        <strong class="text-primary font-semibold" x-text="[...document.querySelectorAll('#advanced-filter-grid [data-price]')].filter(c => categoryMatch(c.dataset.categories) && priceMatch(c.dataset.price)).length"></strong>
        <?php _e('of', 'nera-competitions'); ?>
        <strong class="text-primary font-semibold" x-text="gridFoundPosts"><?php echo (int) $competitions->found_posts; ?></strong>
        <?php _e('competitions', 'nera-competitions'); ?>
      </p>
    </div>

    <!-- Competitions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 transition-opacity duration-200"
         id="advanced-filter-grid"
         :class="{ 'opacity-50 pointer-events-none': gridLoading }"
         data-aos="fade-up" data-aos-duration="600" data-aos-delay="150">

      <?php
      echo nera_advanced_filter_render_grid_html($competitions);
      wp_reset_postdata();
      ?>
    </div>

    <div class="flex justify-center mt-8 px-1" x-show="gridPage < gridMaxPages && !gridLoading" x-cloak>
      <button
        type="button"
        @click="loadMore()"
        :disabled="loadMoreLoading"
        class="inline-flex items-center gap-2 px-8 py-3 text-sm font-semibold uppercase tracking-[0.12em] rounded-xl
               bg-primary text-white border border-primary
               hover:opacity-90 disabled:opacity-50 disabled:pointer-events-none
               transition-all duration-200">
        <span x-show="!loadMoreLoading"><?php esc_html_e('Load more', 'nera-competitions'); ?></span>
        <span x-show="loadMoreLoading" x-cloak><?php esc_html_e('Loading…', 'nera-competitions'); ?></span>
      </button>
    </div>

  </div>
</section>
