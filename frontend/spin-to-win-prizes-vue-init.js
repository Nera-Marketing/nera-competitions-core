import { createApp } from 'vue';
import InstantWinsContainer from './components/InstantWins/InstantWinsContainer.vue';

(function () {
  'use strict';

  let vueMounted = false;
  let app = null;

  function initVueApp() {
    if (vueMounted) return;

    const container = document.getElementById('spin-to-win-prizes-root');

    if (!container) {
      console.error('[Spin To Win Prizes Vue] spin-to-win-prizes-root element not found');
      return;
    }

    const productId = container.dataset.productId;
    const endpoint = container.dataset.endpoint;

    if (!productId) {
      console.error('[Spin To Win Prizes Vue] Product ID not found on spin-to-win-prizes-root');
      return;
    }

    try {
      app = createApp(InstantWinsContainer, {
        productId: parseInt(productId, 10),
        endpoint: endpoint || '',
        emptyMessage: 'No spin-to-win prizes configured yet.',
        showStats: false,
        showRemainingBadge: true,
        showWinners: false,
      });

      app.mount(container);
      vueMounted = true;
    } catch (error) {
      console.error('[Spin To Win Prizes Vue] Error mounting Vue app:', error);
    }
  }

  window.toggleSpinToWinPrizes = function () {
    const container = document.getElementById('spin-to-win-prizes-container');
    const button = document.getElementById('spin-to-win-prizes-toggle-btn');

    if (!container || !button) {
      console.error('[Spin To Win Prizes Vue] Elements not found');
      return;
    }

    const arrow = button.querySelector('.toggle-arrow');
    const isHidden = container.classList.contains('hidden');

    if (isHidden) {
      container.classList.remove('hidden');
      container.setAttribute('aria-hidden', 'false');
      button.setAttribute('aria-expanded', 'true');

      if (arrow) {
        arrow.style.transform = 'rotate(180deg)';
      }

      if (!vueMounted) {
        setTimeout(initVueApp, 50);
      }
    } else {
      container.classList.add('hidden');
      container.setAttribute('aria-hidden', 'true');
      button.setAttribute('aria-expanded', 'false');

      if (arrow) {
        arrow.style.transform = 'rotate(0deg)';
      }
    }
  };
})();
