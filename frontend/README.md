# Instant Wins React Implementation

## Overview

This document describes the React frontend implementation for lazy-loading instant wins on lottery product pages.

## Architecture

### Lazy Loading Strategy

- React bundle loads **only when instant wins tab is clicked**
- Initial HTML includes a loading skeleton (< 5KB)
- React mounts to `#instant-wins-root` on first tab activation
- Supports both Vite dev server (HMR) and production build

### Code Splitting

- **Main entry**: `frontend/instant-wins-init.jsx` (~11KB gzipped)
- **Vendor chunk**: React + ReactDOM (~45KB gzipped)
- Vendor chunk shared across potential future React components
- Total lazy-loaded: ~56KB gzipped (only when needed)

## File Structure

```
frontend/
├── instant-wins-init.jsx              # Entry point & lazy-load logic
└── components/
    ├── shared/
    │   └── ErrorBoundary.jsx          # React error boundary
    └── InstantWins/
        ├── InstantWinsContainer.jsx   # Main container with data fetching
        ├── PrizeCard.jsx              # Individual prize card
        ├── StatsBar.jsx               # Available/won stats badges
        └── LoadingSkeleton.jsx        # Animated loading state
```

## Components

### 1. `instant-wins-init.jsx`

**Entry point** that:

- Listens for click on `[data-tab="instant-wins"]` button
- Mounts React to `#instant-wins-root` on first click
- Passes product ID from `data-product-id` attribute
- Supports Vite HMR in development

### 2. `InstantWinsContainer.jsx`

**Main container** that:

- Fetches data from `/wp-json/nera/v1/instant-wins/${productId}` on mount
- Manages loading, error, and success states
- Renders StatsBar and prize grid
- Handles "See all winners" modal integration with Alpine.js

**Expected API Response:**

```json
{
  "success": true,
  "data": {
    "stats": {
      "availableCount": 10,
      "wonCount": 5,
      "availableLabel": "Available Prizes",
      "wonLabel": "Won Prizes"
    },
    "prizes": [
      {
        "key": "prize-hash",
        "prizeMessage": "£100 Cash Prize",
        "prizeImage": "<img src='...' />",
        "isWon": true,
        "totalCount": 5,
        "wonCount": 2,
        "winners": [
          {
            "details": "John D. from London",
            "ticketNumber": "12345"
          }
        ]
      }
    ]
  }
}
```

### 3. `PrizeCard.jsx`

**Prize card component** with:

- Gold coin badge overlay on image
- Accordion for won prizes (click to expand/collapse)
- Static card for available prizes
- "See all winners" button for 4+ winners
- Matches existing design from PHP templates

### 4. `StatsBar.jsx`

**Stats badges** showing:

- Available prizes count (green badge)
- Won prizes count (blue badge)
- Material Symbols icons
- Matches design from `instant-winners-logs-layout.php`

### 5. `LoadingSkeleton.jsx`

**Loading skeleton** with:

- 6 animated placeholder cards
- Responsive grid (3-2-1 columns)
- Stats skeleton at top
- Uses Tailwind `animate-pulse`

### 6. `ErrorBoundary.jsx`

**Error handling** that:

- Catches React errors gracefully
- Displays user-friendly error message
- Shows error details in development mode
- Prevents blank screen on errors

## PHP Integration

### Template Changes (`template-parts/single-product/tabs.php`)

**Added Instant Wins Tab:**

```php
<?php if ($has_instant_wins): ?>
  <button class="tab-btn" data-tab="instant-wins">
    Instant Wins
  </button>
<?php endif; ?>
```

**Added React Mount Point:**

```php
<div id="instant-wins-root" data-product-id="<?php echo esc_attr($product_id); ?>">
  <!-- Loading skeleton HTML (visible until React mounts) -->
  <div class="instant-wins-skeleton">
    <!-- 6 skeleton cards -->
  </div>
</div>
```

### Script Enqueue (`functions.php`)

**Function:** `nera_enqueue_instant_wins_react()`

**Conditions:**

- Only on single product pages (`is_product()`)
- Only for lottery products with instant wins enabled
- Checks `lty_is_lottery_product()` and `is_instant_winner()`

**Development Mode:**

- Loads from Vite dev server at `localhost:5173`
- Enables HMR for fast development

**Production Mode:**

- Reads manifest from `dist/.vite/manifest.json`
- Enqueues vendor chunk first (React + ReactDOM)
- Then enqueues instant wins bundle
- Handles dependencies automatically

## Build Configuration

### `vite.config.js`

```javascript
import react from '@vitejs/plugin-react';

plugins: [
  react(),
  tailwindcss()
],

build: {
  rollupOptions: {
    input: {
      main: 'src/main.js',
      'instant-wins': 'frontend/instant-wins-init.jsx',
    },
    output: {
      manualChunks: {
        'react-vendor': ['react', 'react-dom'],
      },
    },
  },
}
```

### `package.json`

```json
{
  "dependencies": {
    "react": "^18.3.1",
    "react-dom": "^18.3.1"
  },
  "devDependencies": {
    "@vitejs/plugin-react": "^4.3.4"
  }
}
```

## Development Workflow

### Start Dev Server

```bash
npm run dev
```

- Vite dev server runs at `http://localhost:5173`
- Enable dev mode: `define('NERA_DEV_MODE', true);` in `wp-config.php`
- HMR enabled for instant component updates

### Build for Production

```bash
npm run build
```

- Outputs to `dist/` folder
- Creates optimized bundles with code splitting
- Generates manifest for WordPress integration

## Performance Metrics

### Initial Page Load (without instant wins tab click)

- **+0 KB** - No React loaded
- Only shows loading skeleton HTML

### After Tab Click (first time)

- **~56 KB gzipped** total
  - react-vendor chunk: ~45 KB
  - instant-wins bundle: ~11 KB
- Lazy loads only when needed

### Subsequent Tab Clicks

- **No additional load** - React already mounted
- Instant tab switching

## Styling

### TailwindCSS Classes

All components use theme's existing Tailwind utilities:

- `bg-green-50`, `border-green-200` - Available prizes
- `bg-blue-50`, `border-blue-200` - Won prizes
- `rounded-2xl`, `shadow-card` - Card styling
- `animate-pulse` - Loading skeletons
- `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` - Responsive grid

### Design Consistency

Matches existing PHP templates:

- Same color scheme and spacing
- Same Material Symbols icons
- Same gold coin badge design
- Same accordion animation style

## Testing Checklist

### Functionality

- [ ] Instant wins tab appears for lottery products with instant wins
- [ ] Tab is hidden for regular products
- [ ] Clicking tab loads React app
- [ ] Loading skeleton shows while fetching data
- [ ] Prizes render correctly after data loads
- [ ] Available prizes show green badges
- [ ] Won prizes show blue badges with winner count
- [ ] Clicking won prize cards toggles accordion
- [ ] "See all winners" button opens modal (Alpine.js integration)
- [ ] Error state displays if API fails
- [ ] "Try Again" button refetches data

### Performance

- [ ] React not loaded on initial page load
- [ ] React loads only when instant wins tab clicked
- [ ] Subsequent tab clicks are instant (no reload)
- [ ] Dev server HMR works for component updates
- [ ] Production build creates separate vendor chunk

### Responsive Design

- [ ] Desktop: 3 columns
- [ ] Tablet: 2 columns
- [ ] Mobile: 1 column
- [ ] Skeleton matches grid layout
- [ ] Cards stack properly on all screen sizes

### Browser Compatibility

- [ ] Chrome/Edge
- [ ] Firefox
- [ ] Safari
- [ ] Mobile browsers

## Troubleshooting

### React not loading

1. Check browser console for errors
2. Verify Vite dev server is running (`npm run dev`)
3. Check `NERA_DEV_MODE` is true in `wp-config.php`
4. Verify product has instant wins enabled

### Tab not appearing

1. Check product is lottery type
2. Verify `is_instant_winner()` returns true
3. Check console for PHP errors

### API errors

1. Verify REST endpoint exists: `/wp-json/nera/v1/instant-wins/{product_id}`
2. Check REST API is enabled
3. Verify product ID is valid

### Styling issues

1. Clear browser cache
2. Rebuild Tailwind: `npm run build`
3. Check Tailwind utilities are available
4. Inspect element classes in browser DevTools

## Future Enhancements

### Potential Improvements

- Add pagination for large prize lists
- Implement prize filtering (available/won)
- Add search functionality
- Enable real-time updates via WebSocket
- Add animations for prize wins
- Implement virtual scrolling for very long lists

### Code Splitting Opportunities

- Lazy load PrizeCard only when needed
- Split accordion logic into separate chunk
- Defer winner modal integration

## Notes

### Alpine.js Integration

The "See all winners" button integrates with existing Alpine.js modal:

```javascript
if (window.Alpine && window.Alpine.store('winnersModal')) {
  window.Alpine.store('winnersModal').open(prizeTitle, '', winners);
}
```

### Error Handling

- ErrorBoundary catches React errors
- Container component handles API errors
- User-friendly error messages
- Retry functionality for failed requests

### Accessibility

- Semantic HTML structure
- Keyboard navigation support (native button/focus)
- ARIA labels for screen readers (inherited from Tailwind classes)
- Focus states on interactive elements

## Support

For issues or questions:

1. Check browser console for errors
2. Review this documentation
3. Verify WordPress and plugin versions
4. Check Vite build output for errors
