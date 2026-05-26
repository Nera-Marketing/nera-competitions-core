# Winners Tab Loading Indicator — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show skeleton placeholder cards while the winners grid reloads on tab switch, giving users clear visual feedback.

**Architecture:** Add an `isFiltering` boolean to the existing Alpine.js factory in `winners-grid.php`. When `true` (tab switch only, not load-more), hide the real grid and show 6 skeleton placeholder cards in the same grid layout. Skeleton uses Tailwind's built-in `animate-pulse` on `bg-gray-200` elements — no CSS file changes required.

**Tech Stack:** Alpine.js (already in-page), Tailwind v4 (animate-pulse + bg-gray-200 + existing tokens), PHP loop for DRY skeleton cards.

---

## File Map

| File | Change |
|---|---|
| `template-parts/winners-dynamic/winners-grid.php` | Add `isFiltering` to Alpine data + `setFilter()`, update grid `x-show`, add skeleton markup |

---

### Task 1: Add `isFiltering` flag to Alpine factory

**Files:**
- Modify: `template-parts/winners-dynamic/winners-grid.php` (JS `<script>` block, lines 91–203)

- [ ] **Step 1: Add `isFiltering` property to Alpine data object**

  In the `return { ... }` block (after `loading: false,` on line 95), add:

  ```js
  loading: false,
  isFiltering: false,
  ```

- [ ] **Step 2: Set and clear `isFiltering` inside `setFilter()`**

  Current `setFilter()` (lines 166–185):
  ```js
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
  ```

  Replace with:
  ```js
  async setFilter(filter) {
    if (this.loading || this.globalEmpty || this.activeFilter === filter) {
      return;
    }

    const prev = this.activeFilter;
    this.loading = true;
    this.isFiltering = true;
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
      this.isFiltering = false;
    }
  },
  ```

---

### Task 2: Add skeleton markup and update grid visibility

**Files:**
- Modify: `template-parts/winners-dynamic/winners-grid.php` (HTML section, around lines 287–301)

- [ ] **Step 1: Update the real grid's `x-show` to hide during filter loading**

  Current grid div (line 287–290):
  ```html
  <div
    x-ref="grid"
    x-show="showingCount > 0 || loading"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 <?php echo $stack_layout ? 'gap-2.5 sm:gap-4 lg:gap-6' : 'gap-3 sm:gap-6'; ?>"
  >
  ```

  Replace with:
  ```html
  <div
    x-ref="grid"
    x-show="(showingCount > 0 || loading) && !isFiltering"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 <?php echo $stack_layout ? 'gap-2.5 sm:gap-4 lg:gap-6' : 'gap-3 sm:gap-6'; ?>"
  >
  ```

- [ ] **Step 2: Add skeleton grid immediately before the real grid div**

  Insert the following block directly before the real grid `<div x-ref="grid" ...>`:

  ```html
  <div
    x-show="isFiltering"
    x-cloak
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 <?php echo $stack_layout ? 'gap-2.5 sm:gap-4 lg:gap-6' : 'gap-3 sm:gap-6'; ?>"
    aria-hidden="true"
    aria-label="<?php esc_attr_e('Loading winners…', 'nera-competitions'); ?>"
  >
    <?php for ($i = 0; $i < 6; $i++) : ?>
    <?php if ($stack_layout) : ?>
    <div class="bg-surface rounded-[1.2rem] overflow-hidden border border-gray-100 flex flex-col">
      <div class="aspect-5/3 bg-gray-200 animate-pulse"></div>
      <div class="p-4 sm:p-6 flex flex-col gap-3">
        <div class="h-5 bg-gray-200 animate-pulse rounded-lg w-3/4"></div>
        <div class="h-3 bg-gray-200 animate-pulse rounded w-1/2"></div>
        <div class="flex gap-2">
          <div class="h-3 bg-gray-200 animate-pulse rounded w-1/4"></div>
          <div class="h-3 bg-gray-200 animate-pulse rounded w-1/3"></div>
        </div>
        <div class="h-3 bg-gray-200 animate-pulse rounded w-2/3 mt-1 pt-2 border-t border-gray-100"></div>
        <div class="mt-auto h-10 bg-gray-200 animate-pulse rounded-xl w-full"></div>
      </div>
    </div>
    <?php else : ?>
    <div class="bg-surface rounded-2xl sm:rounded-3xl overflow-hidden border border-gray-100 flex flex-col sm:flex-row sm:items-stretch">
      <div class="w-full min-h-[140px] sm:w-40 sm:min-h-[160px] bg-gray-200 animate-pulse shrink-0"></div>
      <div class="p-4 sm:p-5 flex flex-col flex-1 gap-3 justify-center">
        <div class="h-5 bg-gray-200 animate-pulse rounded-lg w-3/4"></div>
        <div class="h-3 bg-gray-200 animate-pulse rounded w-1/2"></div>
        <div class="h-3 bg-gray-200 animate-pulse rounded w-2/3"></div>
        <div class="h-10 bg-gray-200 animate-pulse rounded-xl w-full mt-2"></div>
      </div>
    </div>
    <?php endif; ?>
    <?php endfor; ?>
  </div>
  ```

---

### Task 3: Verify

- [ ] **Step 1: Open the winners entry list page**

  Navigate to `http://competitions-core.local/winners-entry-list/`

- [ ] **Step 2: Click a tab — confirm skeleton appears**

  Click "Live draw" or "Instant Win". Confirm:
  - Skeleton cards appear immediately (pulsing gray placeholders matching card shape)
  - Real cards are hidden while loading
  - After load completes, skeleton disappears and real cards show

- [ ] **Step 3: Click "Load More" — confirm NO skeleton**

  Click "Load More". Confirm:
  - Skeleton does NOT appear
  - Only the existing button spinner/text feedback fires (lines 325–327 of the original file)
  - New cards append below existing ones as before

- [ ] **Step 4: Rapid tab switching — confirm no race condition**

  Click tabs quickly. Confirm:
  - Tabs are disabled during loading (`:disabled="loading"` already in place)
  - No duplicate skeletons or stuck loading state

- [ ] **Step 5: Build assets**

  ```bash
  cd /Users/minhle/Local\ Sites/competitions-core/app/public/wp-content/themes/nera-competitions-standard/frontend
  yarn build
  ```

  Expected: build completes with no lint errors (no forbidden palette utilities added).
