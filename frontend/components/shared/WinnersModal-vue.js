import { createApp } from 'vue';
import WinnersModal from './WinnersModal.vue';

/**
 * Standalone Vue entry point for WinnersModal
 *
 * This can be used independently or as a bridge between React and Vue components.
 *
 * Usage from React or vanilla JS:
 *
 * ```js
 * import { mountWinnersModal } from './WinnersModal-vue'
 *
 * const unmount = mountWinnersModal({
 *   isOpen: true,
 *   onClose: () => console.log('closed'),
 *   prizeTitle: 'iPhone 15 Pro Max',
 *   winners: [
 *     { details: 'John Doe – 2024-01-15', ticket_number: '12345' },
 *     { details: 'Jane Smith – 2024-01-16', ticket_number: '67890' }
 *   ]
 * })
 *
 * // Later, to unmount:
 * unmount()
 * ```
 */

/**
 * Mount WinnersModal Vue component programmatically
 *
 * @param {Object} props - Component props
 * @param {boolean} props.isOpen - Modal visibility state
 * @param {function} props.onClose - Close handler
 * @param {string} props.prizeTitle - Prize title/message
 * @param {Array} props.winners - Array of winner objects {details, ticket_number}
 * @param {string|HTMLElement} target - Mount point selector or element (defaults to new div in body)
 * @returns {function} Unmount function
 */
export function mountWinnersModal(props = {}, target = null) {
  // Create mount point if not provided
  let mountPoint = target;
  let shouldCleanupMountPoint = false;

  if (!mountPoint) {
    mountPoint = document.createElement('div');
    mountPoint.id = 'winners-modal-vue-root';
    document.body.appendChild(mountPoint);
    shouldCleanupMountPoint = true;
  } else if (typeof mountPoint === 'string') {
    mountPoint = document.querySelector(mountPoint);
  }

  if (!mountPoint) {
    console.error('WinnersModal: Mount point not found');
    return () => {};
  }

  // Create Vue app
  const app = createApp(WinnersModal, props);
  app.mount(mountPoint);

  // Return unmount function
  return () => {
    app.unmount();
    if (shouldCleanupMountPoint && mountPoint.parentNode) {
      mountPoint.parentNode.removeChild(mountPoint);
    }
  };
}

/**
 * Create a reactive WinnersModal instance
 * Returns methods to control the modal imperatively
 *
 * @returns {Object} Modal controller with open, close, and update methods
 */
export function createWinnersModalController() {
  let unmountFn = null;
  let currentApp = null;
  let currentProps = {
    isOpen: false,
    onClose: () => {},
    prizeTitle: '',
    winners: [],
  };

  return {
    /**
     * Open the modal with given props
     */
    open({ prizeTitle, winners, onClose }) {
      if (currentApp) {
        this.close();
      }

      currentProps = {
        isOpen: true,
        onClose: () => {
          this.close();
          if (onClose) onClose();
        },
        prizeTitle,
        winners,
      };

      unmountFn = mountWinnersModal(currentProps);
      currentApp = true;
    },

    /**
     * Close the modal
     */
    close() {
      if (unmountFn) {
        currentProps.isOpen = false;
        // Give animation time to complete before unmounting
        setTimeout(() => {
          if (unmountFn) {
            unmountFn();
            unmountFn = null;
            currentApp = null;
          }
        }, 300);
      }
    },

    /**
     * Update modal props
     */
    update(newProps) {
      if (currentApp) {
        Object.assign(currentProps, newProps);
      }
    },
  };
}

// Auto-initialize if there's a mount point on page load
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}

function init() {
  const mountPoint = document.getElementById('winners-modal-vue-root');

  if (mountPoint) {
    // Get initial props from data attributes
    const propsData = mountPoint.dataset.props;
    let props = {
      isOpen: false,
      onClose: () => {},
      prizeTitle: '',
      winners: [],
    };

    if (propsData) {
      try {
        props = { ...props, ...JSON.parse(propsData) };
      } catch (e) {
        console.error('WinnersModal: Failed to parse props data', e);
      }
    }

    mountWinnersModal(props, mountPoint);
  }
}

// Export for direct use in Vue apps
export default WinnersModal;
