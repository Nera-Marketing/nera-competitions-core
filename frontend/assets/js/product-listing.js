/**
 * Nera Competitions - Product Listing Scripts
 * Handles AJAX filtering, load more, and add to cart functionality
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  // Cache DOM elements
  let filterSection = null;
  let productGrid = null;
  let loadMoreBtn = null;
  let loadMoreSpinner = null;
  let loadMoreSection = null;
  let productCountEl = null;
  let loadingOverlay = null;
  let clearFiltersBtn = null;

  // State
  let currentFilters = {
    category: '',
    price: '',
    status: '',
    sort: 'ending-soon',
  };
  let currentPage = 1;
  let isLoading = false;

  /**
   * Initialize product listing functionality
   */
  function init() {
    // Cache DOM elements
    filterSection = document.querySelector('[data-product-filters]');
    productGrid = document.querySelector('[data-product-grid]');
    loadMoreBtn = document.querySelector('[data-load-more]');
    loadMoreSpinner = document.querySelector('[data-load-more-spinner]');
    loadMoreSection = document.querySelector('[data-load-more-section]');
    productCountEl = document.querySelector('[data-product-count]');
    loadingOverlay = document.querySelector('[data-loading-overlay]');
    clearFiltersBtn = document.querySelector('[data-clear-filters]');

    if (!productGrid) return;

    // Initialize filter listeners
    initFilters();

    // Initialize load more
    initLoadMore();

    // Initialize add to cart
    initAddToCart();
  }

  /**
   * Initialize filter dropdowns
   */
  function initFilters() {
    if (!filterSection) return;

    const filterSelects = filterSection.querySelectorAll('[data-filter]');

    filterSelects.forEach(function (select) {
      select.addEventListener('change', function () {
        const filterType = this.dataset.filter;
        const value = this.value;

        // Update state
        currentFilters[filterType] = value;
        currentPage = 1;

        // Update clear button visibility
        updateClearButtonVisibility();

        // Fetch filtered products
        fetchProducts(true);
      });
    });

    // Clear filters button
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', function () {
        clearFilters();
      });
    }
  }

  /**
   * Clear all filters
   */
  function clearFilters() {
    currentFilters = {
      category: '',
      price: '',
      status: '',
      sort: 'ending-soon',
    };
    currentPage = 1;

    // Reset select elements
    if (filterSection) {
      const filterSelects = filterSection.querySelectorAll('[data-filter]');
      filterSelects.forEach(function (select) {
        select.value = currentFilters[select.dataset.filter] || '';
      });
    }

    // Hide clear button
    updateClearButtonVisibility();

    // Fetch products
    fetchProducts(true);
  }

  /**
   * Update clear button visibility
   */
  function updateClearButtonVisibility() {
    if (!clearFiltersBtn) return;

    const hasActiveFilters =
      currentFilters.category ||
      currentFilters.price ||
      currentFilters.status ||
      currentFilters.sort !== 'ending-soon';

    if (hasActiveFilters) {
      clearFiltersBtn.classList.remove('hidden');
      clearFiltersBtn.classList.add('inline-flex');
    } else {
      clearFiltersBtn.classList.add('hidden');
      clearFiltersBtn.classList.remove('inline-flex');
    }
  }

  /**
   * Initialize load more button
   */
  function initLoadMore() {
    if (!loadMoreBtn) return;

    loadMoreBtn.addEventListener('click', function () {
      if (isLoading) return;

      currentPage++;
      fetchProducts(false);
    });
  }

  /**
   * Fetch products via AJAX
   * @param {boolean} replace - Whether to replace existing products or append
   */
  function fetchProducts(replace) {
    if (isLoading) return;

    isLoading = true;

    // Show loading state
    if (replace && loadingOverlay) {
      loadingOverlay.classList.remove('hidden');
      loadingOverlay.classList.add('flex');
    } else if (loadMoreBtn && loadMoreSpinner) {
      loadMoreBtn.classList.add('hidden');
      loadMoreSpinner.classList.remove('hidden');
      loadMoreSpinner.classList.add('flex');
    }

    // Build request data
    const data = new FormData();
    data.append('action', 'nera_filter_products');
    data.append('nonce', window.neraSettings ? window.neraSettings.nonce : '');
    data.append('category', currentFilters.category);
    data.append('price', currentFilters.price);
    data.append('status', currentFilters.status);
    data.append('sort', currentFilters.sort);
    data.append('page', currentPage);
    data.append('per_page', productGrid ? productGrid.dataset.perPage : 12);

    // Make AJAX request
    const ajaxUrl = window.neraSettings ? window.neraSettings.ajaxUrl : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (response) {
        if (response.success) {
          updateProductGrid(response.data, replace);
        } else {
          console.error('Error loading products:', response.data);
        }
      })
      .catch(function (error) {
        console.error('AJAX error:', error);
      })
      .finally(function () {
        isLoading = false;

        // Hide loading state
        if (loadingOverlay) {
          loadingOverlay.classList.add('hidden');
          loadingOverlay.classList.remove('flex');
        }
        if (loadMoreBtn && loadMoreSpinner) {
          loadMoreSpinner.classList.add('hidden');
          loadMoreSpinner.classList.remove('flex');
          loadMoreBtn.classList.remove('hidden');
        }
      });
  }

  /**
   * Update product grid with new products
   * @param {object} data - Response data with HTML and pagination info
   * @param {boolean} replace - Whether to replace existing products
   */
  function updateProductGrid(data, replace) {
    if (!productGrid) return;

    if (replace) {
      // Replace grid content with animation
      productGrid.style.opacity = '0';
      productGrid.style.transform = 'translateY(10px)';

      setTimeout(function () {
        productGrid.innerHTML = data.html;
        productGrid.dataset.page = currentPage;
        productGrid.dataset.total = data.total;

        // Trigger reflow
        productGrid.offsetHeight;

        productGrid.style.opacity = '1';
        productGrid.style.transform = 'translateY(0)';

        // Re-initialize countdowns and add to cart
        initAddToCart();

        // Dispatch event for other scripts
        document.dispatchEvent(new CustomEvent('nera:content:loaded'));
      }, 200);
    } else {
      // Append new products
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = data.html;

      const newCards = tempDiv.querySelectorAll('article');
      newCards.forEach(function (card, index) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        productGrid.appendChild(card);

        // Animate in with stagger
        setTimeout(function () {
          card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, index * 100);
      });

      productGrid.dataset.page = currentPage;

      // Re-initialize countdowns and add to cart
      initAddToCart();

      // Dispatch event for other scripts
      document.dispatchEvent(new CustomEvent('nera:content:loaded'));
    }

    // Update product count
    updateProductCount(data.showing, data.total);

    // Update load more button
    updateLoadMoreButton(data.has_more, data.next_page);
  }

  /**
   * Update product count text
   */
  function updateProductCount(showing, total) {
    if (!productCountEl) return;

    productCountEl.textContent = productCountEl.textContent
      .replace(/\d+(?=\s*of)/, showing)
      .replace(/of\s*\d+/, 'of ' + total);
  }

  /**
   * Update load more button state
   */
  function updateLoadMoreButton(hasMore, nextPage) {
    if (!loadMoreSection) return;

    if (hasMore) {
      loadMoreSection.classList.remove('hidden');
      if (loadMoreBtn) {
        loadMoreBtn.dataset.nextPage = nextPage;
      }
    } else {
      loadMoreSection.classList.add('hidden');
    }
  }

  /**
   * Initialize add to cart buttons
   */
  function initAddToCart() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(function (button) {
      // Remove existing listener to prevent duplicates
      button.removeEventListener('click', handleAddToCart);
      button.addEventListener('click', handleAddToCart);
    });
  }

  /**
   * Handle add to cart click
   */
  function handleAddToCart(e) {
    e.preventDefault();

    const button = e.currentTarget;
    const productId = button.dataset.productId;
    const quantity = button.dataset.quantity || 1;

    if (!productId || button.classList.contains('loading')) return;

    // Show loading state
    const originalText = button.innerHTML;
    button.classList.add('loading');
    button.innerHTML =
      '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>';
    button.disabled = true;

    // Build request data
    const data = new FormData();
    data.append('action', 'woocommerce_ajax_add_to_cart');
    data.append('product_id', productId);
    data.append('quantity', quantity);

    // Make AJAX request
    const ajaxUrl = window.neraSettings ? window.neraSettings.ajaxUrl : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (response) {
        if (response.error) {
          // Show error state
          button.innerHTML =
            '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

          setTimeout(function () {
            button.innerHTML = originalText;
            button.classList.remove('loading');
            button.disabled = false;
          }, 1500);
        } else {
          // Show success state
          button.innerHTML =
            '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Added!';
          button.classList.add('bg-green-500');
          button.classList.remove('bg-primary');

          // Update cart fragments
          if (response.fragments) {
            updateCartFragments(response.fragments);
          }

          // Dispatch event
          document.dispatchEvent(
            new CustomEvent('nera:cart:updated', {
              detail: { productId: productId },
            })
          );

          setTimeout(function () {
            button.innerHTML = originalText;
            button.classList.remove('loading', 'bg-green-500');
            button.classList.add('bg-primary');
            button.disabled = false;
          }, 2000);
        }
      })
      .catch(function (error) {
        console.error('Add to cart error:', error);
        button.innerHTML = originalText;
        button.classList.remove('loading');
        button.disabled = false;
      });
  }

  /**
   * Update cart fragments (mini cart, count, etc.)
   */
  function updateCartFragments(fragments) {
    if (!fragments) return;

    Object.keys(fragments).forEach(function (selector) {
      const elements = document.querySelectorAll(selector);
      elements.forEach(function (element) {
        element.outerHTML = fragments[selector];
      });
    });
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-initialize for dynamically loaded content
  document.addEventListener('nera:content:loaded', function () {
    initAddToCart();
  });

  // Expose public API
  window.neraProductListing = {
    init: init,
    fetchProducts: fetchProducts,
    clearFilters: clearFilters,
  };
})();
