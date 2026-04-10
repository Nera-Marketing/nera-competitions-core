# Instant Wins API - Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         FRONTEND (Browser)                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Single Product Page                                         │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  "View Instant Wins" Button                            │  │  │
│  │  │  @click="$store.instantWins.load(productId)"           │  │  │
│  │  └────────────────────────────────────────────────────────┘  │  │
│  │                           │                                   │  │
│  │                           │ (1) User clicks button           │  │
│  │                           ▼                                   │  │
│  │  ┌────────────────────────────────────────────────────────┐  │  │
│  │  │  Alpine.js Store: instantWins                          │  │  │
│  │  │  - Opens drawer                                        │  │  │
│  │  │  - Shows loading spinner                               │  │  │
│  │  │  - Calls fetch() API                                   │  │  │
│  │  └────────────────────────────────────────────────────────┘  │  │
│  │                           │                                   │  │
│  │                           │ (2) fetch() request              │  │
│  └───────────────────────────┼───────────────────────────────────┘  │
│                              │                                      │
└──────────────────────────────┼──────────────────────────────────────┘
                               │
                               │ GET /wp-json/nera/v1/instant-wins/123
                               │
┌──────────────────────────────┼──────────────────────────────────────┐
│                              ▼                                      │
│                     WORDPRESS REST API                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Nera_Instant_Wins_API::get_instant_wins()                   │  │
│  │                                                              │  │
│  │  (3) Rate Limiting Check ────────────┐                      │  │
│  │      └─ WordPress Transients          │                      │  │
│  │         (30 req/min per IP per product)                      │  │
│  │                                       │                      │  │
│  │  (4) Cache Check ─────────────────────┼──┐                  │  │
│  │      └─ Transient: nera_instant_wins_ │  │                  │  │
│  │         cache_{product_id}            │  │                  │  │
│  │         TTL: 60 seconds               │  │                  │  │
│  │                                       │  │                  │  │
│  │         ┌─────────────────────────────┘  │                  │  │
│  │         │ Cache Hit?                     │                  │  │
│  │         │                                │                  │  │
│  │    ┌────┴─────┐                          │ Cache Miss       │  │
│  │    │   YES    │                          │                  │  │
│  │    └────┬─────┘                          │                  │  │
│  │         │                                │                  │  │
│  │         │ Return cached data (10-20ms)  │                  │  │
│  │         │                                ▼                  │  │
│  │         │                    (5) Product Validation         │  │
│  │         │                        - Exists?                  │  │
│  │         │                        - Is lottery type?         │  │
│  │         │                        - Instant wins enabled?    │  │
│  │         │                                │                  │  │
│  │         │                                ▼                  │  │
│  │         │                    (6) Fetch Data from Plugin     │  │
│  │         │                        └─ lty_get_instant_winner_ │  │
│  │         │                           log_ids($product_id)    │  │
│  │         │                                │                  │  │
│  │         │                                ▼                  │  │
│  │         │                    (7) Process & Group Data       │  │
│  │         │                        - Group by prize (md5)     │  │
│  │         │                        - Extract image URLs       │  │
│  │         │                        - Format winner names      │  │
│  │         │                        - Calculate statistics     │  │
│  │         │                                │                  │  │
│  │         │                                ▼                  │  │
│  │         │                    (8) Cache Result (60s TTL)     │  │
│  │         │                                │                  │  │
│  │         └────────────────────────────────┤                  │  │
│  │                                          ▼                  │  │
│  │  (9) Return JSON Response                                   │  │
│  │      {                                                      │  │
│  │        "success": true,                                     │  │
│  │        "data": {                                            │  │
│  │          "prizes": [...],                                   │  │
│  │          "stats": {...}                                     │  │
│  │        },                                                   │  │
│  │        "cached": false                                      │  │
│  │      }                                                      │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                              │                                      │
└──────────────────────────────┼──────────────────────────────────────┘
                               │
┌──────────────────────────────┼──────────────────────────────────────┐
│                              ▼                                      │
│                      WORDPRESS DATABASE                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  wp_posts                                                    │  │
│  │  - Product data                                              │  │
│  │  - Product type: lottery                                     │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  wp_postmeta                                                 │  │
│  │  - _instant_wins_enabled: yes                                │  │
│  │  - _lty_end_date_gmt: timestamp                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  wp_lottery_instant_winners                                  │  │
│  │  - instant_winner_id                                         │  │
│  │  - product_id                                                │  │
│  │  - prize_message                                             │  │
│  │  - status (lty_won / lty_available)                          │  │
│  │  - winner_details (name, date)                               │  │
│  │  - ticket_number                                             │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  wp_options (Transients)                                     │  │
│  │  - _transient_nera_instant_wins_cache_{product_id}           │  │
│  │  - _transient_nera_instant_wins_rate_{hash}                  │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                       CACHE INVALIDATION FLOW                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  When someone wins a prize:                                         │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Lottery Plugin: Prize Won                                   │  │
│  │  └─ do_action('lty_instant_winner_log_status_changed')       │  │
│  │                  │                                            │  │
│  │                  ▼                                            │  │
│  │  Hook: lty_instant_winner_log_status_changed                 │  │
│  │  └─ nera_clear_instant_wins_cache($product_id)               │  │
│  │                  │                                            │  │
│  │                  ▼                                            │  │
│  │  Delete transient: nera_instant_wins_cache_{product_id}      │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  When product is updated:                                           │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  WooCommerce: Product Saved                                  │  │
│  │  └─ do_action('woocommerce_update_product')                  │  │
│  │                  │                                            │  │
│  │                  ▼                                            │  │
│  │  Hook: woocommerce_update_product                            │  │
│  │  └─ nera_clear_instant_wins_cache($product_id)               │  │
│  │                  │                                            │  │
│  │                  ▼                                            │  │
│  │  Delete transient: nera_instant_wins_cache_{product_id}      │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                         DATA FLOW EXAMPLE                           │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Request:  GET /wp-json/nera/v1/instant-wins/123                   │
│                                                                     │
│  Response (200 OK):                                                 │
│  {                                                                  │
│    "success": true,                                                 │
│    "data": {                                                        │
│      "prizes": [                                                    │
│        {                                                            │
│          "id": "a3f2c1b9e8d7f6",                                    │
│          "title": "Free Entry - Bambu Lab P1S Combo",               │
│          "image": "https://site.com/prize-image.jpg",               │
│          "total_available": 10,                                     │
│          "won_count": 3,                                            │
│          "winners": [                                               │
│            {                                                        │
│              "name": "Stephanie S.",                                │
│              "ticket": "12345",                                     │
│              "date": "2026-02-02"                                   │
│            },                                                       │
│            {                                                        │
│              "name": "Donald Y.",                                   │
│              "ticket": "12346",                                     │
│              "date": "2026-02-01"                                   │
│            },                                                       │
│            {                                                        │
│              "name": "Stephen L.",                                  │
│              "ticket": "12347",                                     │
│              "date": "2026-01-31"                                   │
│            }                                                        │
│          ]                                                          │
│        }                                                            │
│      ],                                                             │
│      "stats": {                                                     │
│        "total_available": 10,                                       │
│        "total_won": 3                                               │
│      }                                                              │
│    },                                                               │
│    "cached": false                                                  │
│  }                                                                  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                      SECURITY & PERFORMANCE                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Rate Limiting (Per IP per Product):                                │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Request 1-30:  ✅ Allowed                                    │  │
│  │  Request 31+:   ❌ 429 Rate Limit Exceeded                    │  │
│  │  Reset:         After 60 seconds (MINUTE_IN_SECONDS)          │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  Caching (Per Product):                                             │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  First Request:    100-200ms (Database query)                 │  │
│  │  Cached Requests:  10-20ms (Transient retrieval)              │  │
│  │  Cache Duration:   60 seconds                                 │  │
│  │  Cache Key:        nera_instant_wins_cache_{product_id}       │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  Data Privacy:                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Full Name:        "Stephanie Scott"                          │  │
│  │  API Response:     "Stephanie S."                             │  │
│  │                                                                │  │
│  │  No emails, addresses, or sensitive data exposed              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## File Structure

```
inc/api/
├── instant-wins-api.php      # Main API implementation (583 lines)
├── README.md                  # Complete API documentation (530 lines)
├── IMPLEMENTATION.md          # Implementation summary
└── ARCHITECTURE.md            # This file (architecture diagrams)
```

## Integration Points

1. **Frontend Integration:** Alpine.js store in `assets/js/alpine-instant-wins-drawer.js`
2. **Template Integration:** Drawer template in `template-parts/modals/instant-wins-drawer.php`
3. **Theme Bootstrap:** Loaded in `functions.php` after line 298
4. **Cache Hooks:** Automatic invalidation via WordPress hooks

## Performance Metrics

| Metric                | Value      | Notes                            |
| --------------------- | ---------- | -------------------------------- |
| First Load (Uncached) | 100-200ms  | Full database query + processing |
| Cached Load           | 10-20ms    | Transient retrieval only         |
| Rate Limited Response | ~5ms       | Immediate rejection              |
| Cache TTL             | 60 seconds | Configurable via constant        |
| Rate Limit            | 30 req/min | Per IP per product               |
| Database Queries      | 1          | Per uncached request             |

## Security Layers

```
Request
   │
   ├─ Layer 1: Rate Limiting (30/min per IP)
   │   └─ 429 if exceeded
   │
   ├─ Layer 2: Product Validation
   │   ├─ Product exists?
   │   ├─ Product is lottery type?
   │   └─ Instant wins enabled?
   │
   ├─ Layer 3: Data Sanitization
   │   ├─ Strip HTML tags
   │   ├─ Escape URLs
   │   ├─ Sanitize text fields
   │   └─ Format names (privacy)
   │
   └─ Layer 4: Response Caching
       └─ Reduce database load
```

## Error Flow

```
Request → Validation Failed
              │
              ├─ 400: Invalid product type
              ├─ 400: Instant wins disabled
              ├─ 404: Product not found
              ├─ 429: Rate limit exceeded
              └─ 500: Server error

Each error includes:
- HTTP status code
- Error code (machine-readable)
- Error message (human-readable)
```

## Future Enhancements

1. **Pagination:** Add `per_page` and `page` parameters
2. **Filtering:** Add `status=won|available` parameter
3. **Sorting:** Add `orderby=date|prize|winners` parameter
4. **Real-time Updates:** WebSocket integration for live prize updates
5. **Statistics Endpoint:** Separate lightweight stats-only endpoint
6. **Admin Dashboard:** View API usage and cache hit rates
