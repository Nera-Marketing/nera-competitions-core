/**
 * Categories Competitions Filter
 *
 * Client-side category filtering with refined animations and micro-interactions
 * Magnetic tab system with kinetic physics-inspired hover states
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  // Configuration
  const CONFIG = {
    selectors: {
      tabs: '[data-category-tab]',
      tabsContainer: '[data-category-tabs]',
      products: '[data-product-category]',
      grid: '[data-categories-grid]',
    },
    classes: {
      activeTab: 'active',
      hiddenProduct: 'hidden',
    },
    animation: {
      staggerDelay: 50, // ms between each card animation
      filterDuration: 400, // ms for filter transition
    },
  };

  /**
   * Initialize category filtering
   */
  function init() {
    const tabsContainer = document.querySelector(CONFIG.selectors.tabsContainer);

    if (!tabsContainer) {
      return; // Section not present on this page
    }

    const tabs = document.querySelectorAll(CONFIG.selectors.tabs);
    const products = document.querySelectorAll(CONFIG.selectors.products);
    const grid = document.querySelector(CONFIG.selectors.grid);

    if (!tabs.length || !products.length || !grid) {
      return;
    }

    // Attach click listeners to tabs
    tabs.forEach(tab => {
      tab.addEventListener('click', handleTabClick);

      // Keyboard accessibility
      tab.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          handleTabClick.call(tab, e);
        }
      });
    });

    // Magnetic hover effect disabled per user request
    // addMagneticEffect(tabs);

    console.log('✅ Categories filter initialized');
  }

  /**
   * Handle tab click event
   */
  function handleTabClick(event) {
    event.preventDefault();

    const clickedTab = event.currentTarget;
    const category = clickedTab.dataset.categoryTab;
    const categoryColor = clickedTab.dataset.categoryColor || '#1313ec';

    // Update active tab state
    updateActiveTab(clickedTab);

    // Filter products
    filterProducts(category, categoryColor);

    // Announce to screen readers
    announceFilterChange(category);
  }

  /**
   * Update active tab state
   */
  function updateActiveTab(activeTab) {
    const tabs = document.querySelectorAll(CONFIG.selectors.tabs);

    tabs.forEach(tab => {
      const isActive = tab === activeTab;
      tab.setAttribute('aria-selected', isActive);

      // Update tabindex for keyboard navigation
      tab.setAttribute('tabindex', isActive ? '0' : '-1');
    });
  }

  /**
   * Filter products by category
   */
  function filterProducts(category, categoryColor) {
    const products = document.querySelectorAll(CONFIG.selectors.products);
    const grid = document.querySelector(CONFIG.selectors.grid);

    // Batch DOM updates for better performance
    requestAnimationFrame(() => {
      let visibleIndex = 0;

      products.forEach(product => {
        const productCategories = product.dataset.productCategory.split(' ');
        const shouldShow = category === 'all' || productCategories.includes(category);

        if (shouldShow) {
          // Show product
          product.classList.remove(CONFIG.classes.hiddenProduct);
          product.style.opacity = '1';
          product.style.pointerEvents = 'auto';
          visibleIndex++;
        } else {
          // Hide product
          product.classList.add(CONFIG.classes.hiddenProduct);
          product.style.opacity = '0';
          product.style.pointerEvents = 'none';
        }
      });

      // Update grid height to prevent layout shift
      updateGridHeight(grid, visibleIndex);
    });
  }

  /**
   * Update grid height to prevent layout shift during filtering
   */
  function updateGridHeight(grid, visibleCount) {
    // Calculate expected height based on visible items
    // This prevents jarring layout shifts when filtering
    if (visibleCount === 0) {
      grid.style.minHeight = '400px'; // Empty state height
    } else {
      grid.style.minHeight = 'auto';
    }
  }

  /**
   * Announce filter change to screen readers
   */
  function announceFilterChange(category) {
    const announcement =
      category === 'all' ? 'Showing all competitions' : `Showing ${category} competitions`;

    // Create or update live region for screen readers
    let liveRegion = document.getElementById('categories-filter-announcement');

    if (!liveRegion) {
      liveRegion = document.createElement('div');
      liveRegion.id = 'categories-filter-announcement';
      liveRegion.setAttribute('role', 'status');
      liveRegion.setAttribute('aria-live', 'polite');
      liveRegion.setAttribute('aria-atomic', 'true');
      liveRegion.style.cssText =
        'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;';
      document.body.appendChild(liveRegion);
    }

    liveRegion.textContent = announcement;
  }

  /**
   * Add magnetic hover effect to tabs (advanced interaction)
   */
  function addMagneticEffect(tabs) {
    tabs.forEach(tab => {
      tab.addEventListener('mouseenter', handleMagneticEnter);
      tab.addEventListener('mousemove', handleMagneticMove);
      tab.addEventListener('mouseleave', handleMagneticLeave);
    });
  }

  /**
   * Magnetic effect - Mouse enter
   */
  function handleMagneticEnter(event) {
    const tab = event.currentTarget;
    tab.style.transition = 'transform 0.1s ease-out';
  }

  /**
   * Magnetic effect - Mouse move (subtle pull effect)
   */
  function handleMagneticMove(event) {
    const tab = event.currentTarget;
    const rect = tab.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    const mouseX = event.clientX;
    const mouseY = event.clientY;

    // Calculate distance from center
    const deltaX = (mouseX - centerX) * 0.15; // Reduced multiplier for subtle effect
    const deltaY = (mouseY - centerY) * 0.15;

    // Apply magnetic pull (subtle)
    tab.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(1.05)`;
  }

  /**
   * Magnetic effect - Mouse leave
   */
  function handleMagneticLeave(event) {
    const tab = event.currentTarget;
    tab.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
    tab.style.transform = '';
  }

  /**
   * Utility: Check if reduced motion is preferred
   */
  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  /**
   * Initialize when DOM is ready
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose public API for external scripts (if needed)
  window.NeraCategories = {
    filter: filterProducts,
    init: init,
  };
})();
