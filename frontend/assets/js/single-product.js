/**
 * Nera Competitions - Single Product Page Interactions
 * Handles quantity selector, Q&A, tabs, FAQ accordion, and price updates.
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  /**
   * Initialize all single product interactions
   */
  function initSingleProduct() {
    initQuantitySelector();
    initQuestionAnswer();
    initProductTabs();
    initFaqAccordion();
    initProgressBarAnimation();
    initAddToCart();
  }

  /**
   * Initialize quantity selector
   */
  function initQuantitySelector() {
    const selector = document.querySelector('[data-quantity-selector]');

    if (!selector) {
      return;
    }

    const input = selector.querySelector('.quantity-input');
    const decreaseBtn = selector.querySelector('[data-action="decrease"]');
    const increaseBtn = selector.querySelector('[data-action="increase"]');
    const quickBtns = selector.querySelectorAll('.quick-select-btn');
    const totalDisplay = selector.querySelector('[data-total]');
    const formInput = document.querySelector('.form-quantity-input');

    const price = parseFloat(selector.dataset.price) || 0;
    const min = parseInt(input?.dataset.min, 10) || 1;
    const max = parseInt(input?.dataset.max, 10) || 100;
    const currencySymbol =
      typeof neraLotteryProduct !== 'undefined' ? neraLotteryProduct.currency : '£';

    /**
     * Update quantity and related displays
     */
    function updateQuantity(newValue) {
      const value = Math.max(min, Math.min(max, parseInt(newValue, 10) || min));

      if (input) {
        input.value = value;
      }

      // Update form input
      if (formInput) {
        formInput.value = value;
      }

      // Update total price
      if (totalDisplay) {
        const total = (price * value).toFixed(2);
        totalDisplay.textContent = currencySymbol + total;
      }

      // Update button states
      if (decreaseBtn) {
        decreaseBtn.disabled = value <= min;
      }
      if (increaseBtn) {
        increaseBtn.disabled = value >= max;
      }

      // Update quick select button states
      quickBtns.forEach(btn => {
        const btnValue = parseInt(btn.dataset.quantity, 10);
        btn.classList.toggle('!border-primary', btnValue === value);
        btn.classList.toggle('!bg-primary/10', btnValue === value);
        btn.classList.toggle('!text-primary', btnValue === value);
      });

      // Dispatch custom event
      document.dispatchEvent(
        new CustomEvent('nera:quantity:change', {
          detail: { quantity: value, total: price * value },
        })
      );
    }

    // Decrease button
    if (decreaseBtn) {
      decreaseBtn.addEventListener('click', function () {
        updateQuantity(parseInt(input.value, 10) - 1);
      });
    }

    // Increase button
    if (increaseBtn) {
      increaseBtn.addEventListener('click', function () {
        updateQuantity(parseInt(input.value, 10) + 1);
      });
    }

    // Quick select buttons
    quickBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        updateQuantity(parseInt(this.dataset.quantity, 10));
      });
    });

    // Direct input
    if (input) {
      input.addEventListener('change', function () {
        updateQuantity(this.value);
      });

      input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          updateQuantity(parseInt(this.value, 10) + 1);
        } else if (e.key === 'ArrowDown') {
          e.preventDefault();
          updateQuantity(parseInt(this.value, 10) - 1);
        }
      });
    }

    // Initialize with current value
    updateQuantity(input?.value || min);
  }

  /**
   * Initialize Q&A answer selection
   */
  function initQuestionAnswer() {
    const qaContainer = document.querySelector('[data-question-answer]');

    if (!qaContainer) {
      return;
    }

    const inputs = qaContainer.querySelectorAll('.answer-input');
    const validationMsg = qaContainer.querySelector('[data-validation-message]');

    inputs.forEach(input => {
      input.addEventListener('change', function () {
        // Hide validation message when an answer is selected
        if (validationMsg) {
          validationMsg.classList.add('hidden');
        }

        // Visual feedback
        const cards = qaContainer.querySelectorAll('.answer-card');
        cards.forEach(card => {
          card.classList.remove('border-primary', 'bg-primary/10');
        });

        const selectedCard = this.nextElementSibling;
        if (selectedCard) {
          selectedCard.classList.add('border-primary', 'bg-primary/10');
        }
      });
    });

    // Form validation
    const form = document.querySelector('[data-add-to-cart-form]');
    if (form) {
      form.addEventListener('submit', function (e) {
        const selectedAnswer = qaContainer.querySelector('.answer-input:checked');

        if (!selectedAnswer && validationMsg) {
          e.preventDefault();
          validationMsg.classList.remove('hidden');
          qaContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    }
  }

  /**
   * Initialize product tabs
   */
  function initProductTabs() {
    const tabsContainer = document.querySelector('[data-product-tabs]');

    if (!tabsContainer) {
      return;
    }

    const tabBtns = tabsContainer.querySelectorAll('.tab-btn');
    const tabPanels = tabsContainer.querySelectorAll('.tab-panel');

    tabBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        const targetTab = this.dataset.tab;

        // Update button states
        tabBtns.forEach(b => {
          const isActive = b.dataset.tab === targetTab;
          b.classList.toggle('border-primary', isActive);
          b.classList.toggle('text-primary', isActive);
          b.classList.toggle('border-transparent', !isActive);
          b.classList.toggle('text-text-secondary', !isActive);
          b.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        // Update panel visibility
        tabPanels.forEach(panel => {
          const isActive = panel.dataset.tabPanel === targetTab;
          panel.classList.toggle('hidden', !isActive);
        });
      });
    });
  }

  /**
   * Initialize FAQ accordion
   */
  function initFaqAccordion() {
    const accordion = document.querySelector('[data-faq-accordion]');

    if (!accordion) {
      return;
    }

    const toggles = accordion.querySelectorAll('.faq-toggle');

    toggles.forEach(toggle => {
      toggle.addEventListener('click', function () {
        const faqItem = this.closest('.faq-item');
        const content = faqItem.querySelector('.faq-content');
        const icon = this.querySelector('.faq-icon');
        const isExpanded = this.getAttribute('aria-expanded') === 'true';

        // Toggle current item
        this.setAttribute('aria-expanded', !isExpanded);
        content.classList.toggle('hidden', isExpanded);
        icon.classList.toggle('rotate-180', !isExpanded);

        // Optional: Close other items (accordion behavior)
        // Uncomment if you want only one item open at a time
        /*
        toggles.forEach((otherToggle) => {
          if (otherToggle !== toggle) {
            const otherItem = otherToggle.closest('.faq-item');
            const otherContent = otherItem.querySelector('.faq-content');
            const otherIcon = otherToggle.querySelector('.faq-icon');
            otherToggle.setAttribute('aria-expanded', 'false');
            otherContent.classList.add('hidden');
            otherIcon.classList.remove('rotate-180');
          }
        });
        */
      });
    });
  }

  /**
   * Initialize progress bar animation
   */
  function initProgressBarAnimation() {
    const progressBars = document.querySelectorAll('[data-progress]');

    if (!progressBars.length) {
      return;
    }

    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const bar = entry.target;
            const progress = bar.dataset.progress;

            // Animate the progress bar
            setTimeout(() => {
              bar.style.width = progress + '%';
            }, 100);

            // Stop observing once animated
            observer.unobserve(bar);
          }
        });
      },
      { threshold: 0.5 }
    );

    progressBars.forEach(bar => {
      observer.observe(bar);
    });
  }

  /**
   * Initialize add to cart functionality
   */
  function initAddToCart() {
    const form = document.querySelector('[data-add-to-cart-form]');

    if (!form) {
      return;
    }

    const submitBtn = form.querySelector('.add-to-cart-btn');
    const btnText = submitBtn?.querySelector('.btn-text');

    form.addEventListener('submit', function (e) {
      // Show loading state
      if (submitBtn) {
        submitBtn.disabled = true;
        if (btnText) {
          btnText.textContent = 'Adding...';
        }
      }

      // The form will submit normally unless there are validation errors
      // If using AJAX, you would prevent default and handle here
    });

    // Reset button state if there's a form error
    window.addEventListener('pageshow', function () {
      if (submitBtn) {
        submitBtn.disabled = false;
        if (btnText) {
          btnText.textContent = 'Add to Cart';
        }
      }
    });
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSingleProduct);
  } else {
    initSingleProduct();
  }

  // Re-initialize for dynamically loaded content
  document.addEventListener('nera:content:loaded', initSingleProduct);

  // Expose functions globally
  window.neraSingleProduct = {
    init: initSingleProduct,
    initQuantitySelector: initQuantitySelector,
    initProductTabs: initProductTabs,
    initFaqAccordion: initFaqAccordion,
  };
})();
