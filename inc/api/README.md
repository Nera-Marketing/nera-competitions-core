# Nera Competitions REST API

This directory contains custom REST API endpoints for the Nera Competitions theme.

## Endpoints

### Instant Wins API

**File:** `instant-wins-api.php`

Provides lazy-loading functionality for instant win prizes without full page load.

#### Endpoint

```
GET /wp-json/nera/v1/instant-wins/{product_id}
```

#### Parameters

| Parameter    | Type    | Required | Description                              |
| ------------ | ------- | -------- | ---------------------------------------- |
| `product_id` | integer | Yes      | WooCommerce product ID with instant wins |

#### Response

**Success (200):**

```json
{
  "success": true,
  "data": {
    "prizes": [
      {
        "id": "hash123",
        "title": "Free Entry - Bambu Lab P1S Combo",
        "image": "https://example.com/prize-image.jpg",
        "total_available": 10,
        "won_count": 3,
        "winners": [
          {
            "name": "Stephanie S.",
            "ticket": "12345",
            "date": "2026-02-02"
          }
        ]
      }
    ],
    "stats": {
      "total_available": 50,
      "total_won": 15
    }
  },
  "cached": false
}
```

**Error Responses:**

| Code | Error                   | Description                                          |
| ---- | ----------------------- | ---------------------------------------------------- |
| 400  | `invalid_product_type`  | Product is not a lottery/competition                 |
| 400  | `instant_wins_disabled` | Instant wins not enabled for product                 |
| 404  | `invalid_product`       | Product not found                                    |
| 429  | `rate_limit_exceeded`   | Too many requests (max 30/minute per IP per product) |
| 500  | `plugin_not_available`  | Lottery plugin functions missing                     |
| 500  | `data_fetch_error`      | Error fetching data                                  |

#### Security Features

1. **Rate Limiting**
   - 30 requests per minute per IP address per product
   - Uses WordPress transients for tracking
   - Returns 429 status when exceeded

2. **Response Caching**
   - 60 second TTL (Time To Live)
   - Cached per product using WordPress transients
   - Automatically cleared when prizes are updated

3. **Data Sanitization**
   - Only public winner data exposed (names, ticket numbers, dates)
   - Winner names automatically formatted as "FirstName L." for privacy
   - No email addresses or personal information included
   - All output sanitized using WordPress functions

4. **Validation**
   - Product existence verification
   - Product type check (must be lottery)
   - Instant wins enabled check

#### Usage Examples

**Vanilla JavaScript:**

```javascript
async function loadInstantWins(productId) {
  try {
    const response = await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
    const data = await response.json();

    if (data.success) {
      console.log('Total prizes:', data.data.stats.total_available);
      console.log('Won prizes:', data.data.stats.total_won);

      data.data.prizes.forEach(prize => {
        console.log(`${prize.title}: ${prize.won_count}/${prize.total_available} won`);
      });
    } else {
      console.error('API error:', data.message);
    }
  } catch (error) {
    console.error('Fetch error:', error);
  }
}
```

**jQuery:**

```javascript
$.ajax({
  url: '/wp-json/nera/v1/instant-wins/123',
  method: 'GET',
  success: function (response) {
    if (response.success) {
      // Process prizes data
      response.data.prizes.forEach(function (prize) {
        console.log(prize.title, prize.winners);
      });
    }
  },
  error: function (xhr) {
    console.error('Error:', xhr.status, xhr.responseJSON);
  },
});
```

**Alpine.js (for drawer implementation):**

```javascript
document.addEventListener('alpine:init', () => {
  Alpine.store('instantWins', {
    loading: false,
    prizes: [],
    stats: {},
    error: null,

    async load(productId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
        const data = await response.json();

        if (data.success) {
          this.prizes = data.data.prizes;
          this.stats = data.data.stats;
        } else {
          this.error = data.message || 'Failed to load instant wins';
        }
      } catch (error) {
        this.error = 'Network error loading instant wins';
      } finally {
        this.loading = false;
      }
    },
  });
});
```

#### Cache Management

**Clear cache programmatically:**

```php
// Clear cache for specific product
nera_clear_instant_wins_cache($product_id);
```

**Cache is automatically cleared when:**

1. Instant winner status changes (someone wins a prize)
2. Product instant wins configuration is updated
3. Product is saved in admin

**Manual cache clearing:**

```php
// In theme code
do_action('nera_clear_instant_wins_cache', $product_id);

// Or directly
delete_transient('nera_instant_wins_cache_' . $product_id);
```

#### Performance Considerations

1. **First Load:** ~100-200ms (database queries)
2. **Cached Load:** ~10-20ms (transient retrieval)
3. **Memory Usage:** Minimal (uses transients, not persistent cache)
4. **Database Impact:** Low (60s cache prevents repeated queries)

#### Compatibility

- **WordPress:** 5.0+
- **WooCommerce:** 3.0+
- **PHP:** 7.4+
- **Required Plugins:**
  - WooCommerce Lottery (instant wins functionality)

#### Integration with Frontend

The API is designed to work with the instant wins drawer component:

```javascript
// assets/js/alpine-instant-wins-drawer.js
async openDrawer(productId) {
  this.open = true;
  this.loading = true;

  const response = await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
  const data = await response.json();

  this.prizes = data.data.prizes;
  this.stats = data.data.stats;
  this.loading = false;
}
```

#### Troubleshooting

**404 Not Found:**

- Check WordPress permalinks are enabled
- Flush rewrite rules: Settings > Permalinks > Save
- Verify file exists: `inc/api/instant-wins-api.php`

**Empty prizes array:**

- Verify instant wins are configured for the product
- Check `_instant_wins_enabled` meta is set to 'yes'
- Ensure lottery plugin is active

**Rate limit issues:**

- Default: 30 requests/minute per IP per product
- Adjust by modifying `RATE_LIMIT` constant in class
- Clear rate limit: `delete_transient('nera_instant_wins_rate_' . md5($ip . '_' . $product_id))`

**Cache not updating:**

- Verify hooks are firing: `do_action('lty_instant_winner_log_status_changed')`
- Manually clear: `nera_clear_instant_wins_cache($product_id)`
- Check transient TTL hasn't been modified

#### Future Enhancements

Potential improvements for future versions:

1. **Pagination:** Add `per_page` and `page` parameters for large prize lists
2. **Filtering:** Add `status` parameter to filter by won/available
3. **Sorting:** Add `orderby` parameter for custom sorting
4. **Search:** Add `search` parameter to filter prizes by title
5. **Statistics Endpoint:** Separate endpoint for just stats without full prize data
6. **Webhook Support:** Real-time cache invalidation via webhooks

## Adding New Endpoints

To add a new REST API endpoint:

1. Create a new PHP file in this directory
2. Define a class with static methods
3. Register routes in `register_routes()` method
4. Initialize in the class: `YourClass::init();`
5. Require the file in `functions.php`

Example:

```php
<?php
// inc/api/your-endpoint.php

class Nera_Your_Endpoint_API
{
  const NAMESPACE = 'nera/v1';

  public static function init()
  {
    add_action('rest_api_init', [__CLASS__, 'register_routes']);
  }

  public static function register_routes()
  {
    register_rest_route(self::NAMESPACE, '/your-endpoint', [
      'methods' => WP_REST_Server::READABLE,
      'callback' => [__CLASS__, 'get_data'],
      'permission_callback' => '__return_true',
    ]);
  }

  public static function get_data($request)
  {
    return rest_ensure_response([
      'success' => true,
      'data' => [],
    ]);
  }
}

Nera_Your_Endpoint_API::init();
```

Then in `functions.php`:

```php
// Your custom API endpoint
if (class_exists('WooCommerce')) {
  require_once NERA_DIR . '/inc/api/your-endpoint.php';
}
```

## Best Practices

1. **Always sanitize input** using WordPress functions
2. **Validate permissions** with `permission_callback`
3. **Implement rate limiting** for public endpoints
4. **Cache responses** when data doesn't change frequently
5. **Return proper HTTP status codes** (200, 400, 404, 429, 500)
6. **Use WordPress transients** for caching (not options)
7. **Clear caches automatically** when data changes
8. **Document all endpoints** thoroughly
9. **Test error scenarios** (invalid IDs, missing data, etc.)
10. **Follow WordPress coding standards**
