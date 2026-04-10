/**
 * AlpineJS Countdown Component
 *
 * Replaces legacy countdown.js
 */
(function () {
  function initComponent() {
    if (typeof Alpine === 'undefined') {
      console.error('Alpine.js not loaded - countdown component cannot initialize');
      return;
    }

    Alpine.data('countdown', timestamp => ({
      endTime: 0,
      days: '00',
      hours: '00',
      minutes: '00',
      seconds: '00',
      isExpired: false,
      intervalId: null,

      init() {
        // Parse timestamp - handle both milliseconds number or date string
        if (/^\d+$/.test(timestamp)) {
          this.endTime = parseInt(timestamp, 10);
        } else {
          this.endTime = new Date(timestamp).getTime();
        }

        this.update();

        // Update every second
        this.intervalId = setInterval(() => {
          this.update();
        }, 1000);
      },

      update() {
        const now = Date.now();
        const diff = this.endTime - now;

        if (diff <= 0) {
          this.isExpired = true;
          this.days = '00';
          this.hours = '00';
          this.minutes = '00';
          this.seconds = '00';

          clearInterval(this.intervalId);

          // Dispatch event for parent components
          this.$el.dispatchEvent(new CustomEvent('nera:countdown:end', { bubbles: true }));
          return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        this.days = String(days).padStart(2, '0');
        this.hours = String(hours).padStart(2, '0');
        this.minutes = String(minutes).padStart(2, '0');
        this.seconds = String(seconds).padStart(2, '0');
      },

      destroy() {
        if (this.intervalId) {
          clearInterval(this.intervalId);
        }
      },
    }));
  }

  // Initialize when Alpine is ready
  // CRITICAL FIX: Attach alpine:init listener IMMEDIATELY, not after DOMContentLoaded
  // Alpine's init event can fire before DOMContentLoaded in some scenarios
  document.addEventListener('alpine:init', initComponent);
})();
