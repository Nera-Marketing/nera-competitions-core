import { createApp } from 'vue';
import InstantWinsContainer from './components/InstantWins/InstantWinsContainer.vue';

/**
 * Instant Wins Vue app with inline toggle
 *
 * Initialization:
 * 1. Global toggle function shows/hides the instant wins container
 * 2. Mounts Vue app on first open
 * 3. Passes product ID from data attribute
 */

(function () {
  'use strict';

  let vueMounted = false;
  let app = null;

  /**
   * Initialize Vue app when container is first opened
   */
  function initVueApp() {
    if (vueMounted) return;

    const container = document.getElementById('instant-wins-root');

    if (!container) {
      console.error('[Instant Wins Vue] instant-wins-root element not found');
      return;
    }

    const productId = container.dataset.productId;

    if (!productId) {
      console.error('[Instant Wins Vue] Product ID not found on instant-wins-root');
      return;
    }

    // Mount Vue app
    try {
      app = createApp(InstantWinsContainer, {
        productId: parseInt(productId, 10),
      });

      app.mount(container);
      vueMounted = true;
    } catch (error) {
      console.error('[Instant Wins Vue] Error mounting Vue app:', error);
    }
  }

  /**
   * Global toggle function for instant wins section
   */
  window.toggleInstantWins = function () {
    const container = document.getElementById('instant-wins-container');
    const button = document.getElementById('instant-wins-toggle-btn');

    if (!container || !button) {
      console.error('[Instant Wins Vue] Elements not found');
      return;
    }

    const arrow = button.querySelector('.toggle-arrow');
    const isHidden = container.classList.contains('hidden');

    if (isHidden) {
      // Show container
      container.classList.remove('hidden');
      container.setAttribute('aria-hidden', 'false');
      button.setAttribute('aria-expanded', 'true');

      // Rotate arrow
      if (arrow) {
        arrow.style.transform = 'rotate(180deg)';
      }

      // Mount Vue on first open
      if (!vueMounted) {
        // Small delay to ensure container is visible
        setTimeout(initVueApp, 50);
      }
    } else {
      // Hide container
      container.classList.add('hidden');
      container.setAttribute('aria-hidden', 'true');
      button.setAttribute('aria-expanded', 'false');

      // Rotate arrow back
      if (arrow) {
        arrow.style.transform = 'rotate(0deg)';
      }
    }
  };
})();
