/**
 * Nera Competitions - Main JavaScript Entry
 *
 * This is the entry point for Vite bundling.
 * It imports the main CSS file and all JavaScript modules.
 */

// Import TailwindCSS (processed by Vite)
import './main.css';

// Import Product Gallery styles
import './product-gallery.css';

// NOTE: Alpine.js stores are NOT imported here
// They must be loaded separately BEFORE Alpine.js in functions.php:
// - alpine-toast.js (toast + postDialog stores)
// - alpine-winners-modal.js (winnersModal store)
// - alpine-instant-wins-drawer.js (instantWinsDrawer store)
// - alpine-countdown.js (countdown component/directive)

// Import theme JavaScript modules
import '@assets/js/animations.js';
import '@assets/js/homepage.js';
import '@assets/js/stats-counter.js';
// import '@assets/js/categories-filter.js'; // Replaced by AlpineJS implementation
import '@assets/js/ticket-selector.js';
import '@assets/js/scroll-to-top.js';
import '@assets/js/product-listing.js';
import '@assets/js/cart.js';
import '@assets/js/cart-sound.js';

// Single product page modules
// Product gallery: assets/js/single-product-gallery.js + alpine-product-gallery.js (enqueued in functions.php)
import '@assets/js/quantity-control.js';
import '@assets/js/single-product.js';

// Checkout page - loaded separately in functions.php before Alpine.js
// import '../assets/js/checkout.js';

// Flynt JS islands infrastructure
import FlyntComponent from '@assets/js/flynt-component.js';
window.customElements.define('flynt-component', FlyntComponent);

console.log('Nera Competitions theme loaded');
