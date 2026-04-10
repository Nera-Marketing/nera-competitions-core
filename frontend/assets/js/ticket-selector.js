/**
 * Nera Competitions - Ticket Selector
 * Enhanced ticket selection functionality
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  /**
   * Initialize ticket selectors
   */
  function initTicketSelectors() {
    // Initialize grid-based ticket selectors
    const ticketGrids = document.querySelectorAll('.ticket-grid');
    ticketGrids.forEach(initTicketGrid);

    // Initialize quick select buttons
    const quickSelectContainers = document.querySelectorAll('.ticket-quick-select');
    quickSelectContainers.forEach(initQuickSelect);

    // Initialize lucky dip buttons
    const luckyDipButtons = document.querySelectorAll('.btn-lucky-dip, .btn--lucky-dip');
    luckyDipButtons.forEach(initLuckyDip);
  }

  /**
   * Initialize a ticket grid
   */
  function initTicketGrid(grid) {
    const tickets = grid.querySelectorAll('.ticket-item');
    const maxTickets = parseInt(grid.dataset.maxTickets, 10) || Infinity;
    let selectedCount = 0;

    tickets.forEach(function (ticket) {
      // Skip sold tickets
      if (ticket.classList.contains('ticket-item--sold')) {
        return;
      }

      ticket.addEventListener('click', function () {
        toggleTicket(ticket, grid, maxTickets);
      });

      // Keyboard accessibility
      ticket.setAttribute('tabindex', '0');
      ticket.setAttribute('role', 'checkbox');
      ticket.setAttribute('aria-checked', 'false');

      ticket.addEventListener('keypress', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggleTicket(ticket, grid, maxTickets);
        }
      });
    });
  }

  /**
   * Toggle ticket selection
   */
  function toggleTicket(ticket, grid, maxTickets) {
    const isSelected = ticket.classList.contains('ticket-item--selected');
    const selectedTickets = grid.querySelectorAll('.ticket-item--selected');
    const currentCount = selectedTickets.length;

    if (isSelected) {
      // Deselect
      ticket.classList.remove('ticket-item--selected');
      ticket.setAttribute('aria-checked', 'false');
      playSound('deselect');
    } else {
      // Check max limit
      if (currentCount >= maxTickets) {
        showNotification(__('Maximum tickets selected', 'nera-competitions'), 'warning');
        shakeElement(ticket);
        return;
      }

      // Select
      ticket.classList.add('ticket-item--selected');
      ticket.setAttribute('aria-checked', 'true');
      playSound('select');
      pulseElement(ticket);
    }

    // Update counter and price
    updateTicketSummary(grid);

    // Dispatch custom event
    const event = new CustomEvent('nera:tickets:changed', {
      detail: {
        grid: grid,
        selectedCount: grid.querySelectorAll('.ticket-item--selected').length,
        selectedTickets: getSelectedTicketNumbers(grid),
      },
      bubbles: true,
    });
    grid.dispatchEvent(event);
  }

  /**
   * Get selected ticket numbers
   */
  function getSelectedTicketNumbers(grid) {
    const selected = grid.querySelectorAll('.ticket-item--selected');
    return Array.from(selected).map(function (ticket) {
      return ticket.dataset.ticket || ticket.textContent.trim();
    });
  }

  /**
   * Update ticket summary (count and price)
   */
  function updateTicketSummary(grid) {
    const selectedCount = grid.querySelectorAll('.ticket-item--selected').length;
    const pricePerTicket = parseFloat(grid.dataset.ticketPrice) || 1;
    const totalPrice = selectedCount * pricePerTicket;

    // Find summary elements
    const countElement = document.querySelector('.selected-ticket-count, [data-ticket-count]');
    const priceElement = document.querySelector('.selected-ticket-price, [data-ticket-price]');

    if (countElement) {
      countElement.textContent = selectedCount;
    }

    if (priceElement) {
      priceElement.textContent = formatCurrency(totalPrice);
    }

    // Update hidden input for form submission
    const hiddenInput = document.querySelector('input[name="selected_tickets"]');
    if (hiddenInput) {
      hiddenInput.value = getSelectedTicketNumbers(grid).join(',');
    }
  }

  /**
   * Initialize quick select buttons
   */
  function initQuickSelect(container) {
    const buttons = container.querySelectorAll('.ticket-quick-select__btn, button[data-tickets]');
    const grid = document.querySelector('.ticket-grid');

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        const ticketCount = parseInt(button.dataset.tickets, 10);
        if (grid && ticketCount) {
          selectRandomTickets(grid, ticketCount);
        }

        // Highlight selected quick select button
        buttons.forEach(function (btn) {
          btn.classList.remove('active');
        });
        button.classList.add('active');
      });
    });
  }

  /**
   * Initialize lucky dip button
   */
  function initLuckyDip(button) {
    const grid = document.querySelector('.ticket-grid');

    button.addEventListener('click', function () {
      if (!grid) return;

      const availableTickets = grid.querySelectorAll('.ticket-item:not(.ticket-item--sold)');
      const randomCount = Math.min(5, availableTickets.length); // Default to 5 tickets

      // Clear previous selections
      clearAllSelections(grid);

      // Add spinning animation
      button.classList.add('animate-spin');

      setTimeout(function () {
        selectRandomTickets(grid, randomCount);
        button.classList.remove('animate-spin');
        playSound('lucky-dip');
      }, 500);
    });
  }

  /**
   * Select random tickets
   */
  function selectRandomTickets(grid, count) {
    // Clear current selections
    clearAllSelections(grid);

    // Get available tickets
    const availableTickets = Array.from(
      grid.querySelectorAll('.ticket-item:not(.ticket-item--sold)')
    );

    if (availableTickets.length === 0) {
      showNotification(__('No tickets available', 'nera-competitions'), 'error');
      return;
    }

    // Shuffle and pick
    const shuffled = shuffleArray(availableTickets);
    const toSelect = shuffled.slice(0, Math.min(count, availableTickets.length));

    // Animate selection one by one
    toSelect.forEach(function (ticket, index) {
      setTimeout(function () {
        ticket.classList.add('ticket-item--selected');
        ticket.setAttribute('aria-checked', 'true');
        pulseElement(ticket);
        playSound('select');
      }, index * 100);
    });

    // Update summary after all selections
    setTimeout(
      function () {
        updateTicketSummary(grid);

        // Dispatch event
        const event = new CustomEvent('nera:tickets:random', {
          detail: {
            grid: grid,
            selectedCount: count,
            selectedTickets: getSelectedTicketNumbers(grid),
          },
          bubbles: true,
        });
        grid.dispatchEvent(event);
      },
      toSelect.length * 100 + 100
    );
  }

  /**
   * Clear all ticket selections
   */
  function clearAllSelections(grid) {
    const selected = grid.querySelectorAll('.ticket-item--selected');
    selected.forEach(function (ticket) {
      ticket.classList.remove('ticket-item--selected');
      ticket.setAttribute('aria-checked', 'false');
    });
    updateTicketSummary(grid);
  }

  /**
   * Shuffle array (Fisher-Yates algorithm)
   */
  function shuffleArray(array) {
    const shuffled = array.slice();
    for (let i = shuffled.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
  }

  /**
   * Format currency
   */
  function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GB', {
      style: 'currency',
      currency: 'GBP',
    }).format(amount);
  }

  /**
   * Show notification using toast system
   */
  /**
   * Show notification using toast system
   */
  function showNotification(message, type) {
    // Use Alpine toast system if available
    if (typeof Alpine !== 'undefined' && Alpine.store('toast')) {
      Alpine.store('toast')[type](message);
    } else if (typeof window.NeraToast !== 'undefined') {
      window.NeraToast.show(message, type);
    } else {
      // Fallback to console
      console.log('[' + type.toUpperCase() + ']', message);
    }
  }

  /**
   * Pulse animation for element
   */
  function pulseElement(element) {
    element.classList.add('animate-pulse');
    setTimeout(function () {
      element.classList.remove('animate-pulse');
    }, 300);
  }

  /**
   * Shake animation for element (error feedback)
   */
  function shakeElement(element) {
    element.style.animation = 'shake 0.5s ease';
    setTimeout(function () {
      element.style.animation = '';
    }, 500);
  }

  /**
   * Play sound effect (optional, can be disabled)
   */
  function playSound(type) {
    // Sounds are disabled by default for better UX
    // Uncomment to enable
    /*
    const sounds = {
      'select': 'click.mp3',
      'deselect': 'pop.mp3',
      'lucky-dip': 'success.mp3'
    };
    
    if (sounds[type]) {
      const audio = new Audio(neraSettings.assetsUrl + '/sounds/' + sounds[type]);
      audio.volume = 0.3;
      audio.play().catch(function() {});
    }
    */
  }

  /**
   * Simple translation function
   */
  function __(text, domain) {
    // If wp.i18n is available, use it
    if (typeof wp !== 'undefined' && wp.i18n && wp.i18n.__) {
      return wp.i18n.__(text, domain);
    }
    return text;
  }

  // Add shake keyframes
  function addShakeStyles() {
    if (document.getElementById('nera-shake-styles')) return;

    const style = document.createElement('style');
    style.id = 'nera-shake-styles';
    style.textContent = `
      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-5px); }
        40% { transform: translateX(5px); }
        60% { transform: translateX(-5px); }
        80% { transform: translateX(5px); }
      }
    `;
    document.head.appendChild(style);
  }

  /**
   * Initialize
   */
  function init() {
    addShakeStyles();
    initTicketSelectors();
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-initialize for dynamically loaded content
  document.addEventListener('nera:content:loaded', initTicketSelectors);

  // Expose for external use
  window.neraTicketSelector = {
    init: init,
    selectRandom: function (count) {
      const grid = document.querySelector('.ticket-grid');
      if (grid) selectRandomTickets(grid, count);
    },
    clearAll: function () {
      const grid = document.querySelector('.ticket-grid');
      if (grid) clearAllSelections(grid);
    },
    getSelected: function () {
      const grid = document.querySelector('.ticket-grid');
      return grid ? getSelectedTicketNumbers(grid) : [];
    },
  };
})();
