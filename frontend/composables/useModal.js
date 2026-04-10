import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';

/**
 * useModal composable - Reusable modal logic
 *
 * Provides common modal functionality:
 * - Body scroll lock management
 * - Keyboard navigation (Escape to close)
 * - Focus management
 * - Exit animations
 *
 * @param {Object} options - Configuration options
 * @param {Function} options.onClose - Close handler callback
 * @param {Number} options.exitDuration - Exit animation duration in ms (default: 250)
 * @returns {Object} Modal state and methods
 */
export function useModal(options = {}) {
  const { onClose, exitDuration = 250 } = options;

  const isExiting = ref(false);
  const modalRef = ref(null);
  const closeButtonRef = ref(null);

  /**
   * Handle modal close with exit animation
   */
  const handleClose = () => {
    if (!onClose) return;

    isExiting.value = true;
    setTimeout(() => {
      isExiting.value = false;
      onClose();
    }, exitDuration);
  };

  /**
   * Keyboard event handler for Escape key
   */
  const handleKeyDown = e => {
    if (e.key === 'Escape') {
      handleClose();
    }
  };

  /**
   * Body scroll lock - to be called when modal opens/closes
   */
  const lockBodyScroll = () => {
    document.body.style.overflow = 'hidden';
  };

  const unlockBodyScroll = () => {
    document.body.style.overflow = '';
  };

  /**
   * Focus management
   */
  const focusCloseButton = async () => {
    await nextTick();
    setTimeout(() => closeButtonRef.value?.focus(), 100);
  };

  /**
   * Focus trap handler for Tab key
   */
  const createFocusTrap = () => {
    if (!modalRef.value) return null;

    const handleTab = e => {
      if (e.key !== 'Tab') return;

      const focusableElements = modalRef.value.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          e.preventDefault();
          lastElement?.focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          e.preventDefault();
          firstElement?.focus();
        }
      }
    };

    return handleTab;
  };

  /**
   * Setup and cleanup
   */
  const setupModal = () => {
    window.addEventListener('keydown', handleKeyDown);
    const focusTrapHandler = createFocusTrap();
    if (focusTrapHandler && modalRef.value) {
      modalRef.value.addEventListener('keydown', focusTrapHandler);
    }
    return focusTrapHandler;
  };

  const cleanupModal = focusTrapHandler => {
    window.removeEventListener('keydown', handleKeyDown);
    if (focusTrapHandler && modalRef.value) {
      modalRef.value.removeEventListener('keydown', focusTrapHandler);
    }
    unlockBodyScroll();
  };

  return {
    // Refs
    isExiting,
    modalRef,
    closeButtonRef,

    // Methods
    handleClose,
    lockBodyScroll,
    unlockBodyScroll,
    focusCloseButton,
    setupModal,
    cleanupModal,
  };
}
