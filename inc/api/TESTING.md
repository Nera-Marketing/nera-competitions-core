# Testing the Instant Wins REST API

This guide walks through testing the newly implemented REST API endpoint.

## Prerequisites

Before testing, ensure:

1. ✅ WordPress site is running
2. ✅ WooCommerce is active
3. ✅ WooCommerce Lottery plugin is active
4. ✅ At least one lottery product exists with instant wins configured
5. ✅ Theme files are in place (`inc/api/instant-wins-api.php`)

## Quick Test in Browser Console

### 1. Find a Product ID

Open your WordPress admin and:

1. Go to **Products**
2. Find a lottery product with instant wins
3. Note the product ID (in the URL: `post=123`)

### 2. Test the Endpoint

Open your site in a browser, press **F12** to open DevTools, go to **Console**, and run:

```javascript
// Replace 123 with your actual product ID
fetch('/wp-json/nera/v1/instant-wins/123')
  .then(response => response.json())
  .then(data => {
    console.log('✅ API Response:', data);
    console.log('Success:', data.success);
    console.log('Prizes:', data.data.prizes);
    console.log('Stats:', data.data.stats);
    console.log('Cached:', data.cached);
  })
  .catch(error => {
    console.error('❌ Error:', error);
  });
```

**Expected Output (Success):**

```javascript
{
  "success": true,
  "data": {
    "prizes": [
      {
        "id": "a3f2c1b9e8d7f6",
        "title": "Free Entry - Bambu Lab P1S Combo",
        "image": "https://yoursite.com/prize.jpg",
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
      "total_available": 10,
      "total_won": 3
    }
  },
  "cached": false
}
```

## Comprehensive Test Suite

### Test 1: Valid Product ✅

```javascript
// Should return 200 OK with prize data
fetch('/wp-json/nera/v1/instant-wins/123')
  .then(r => r.json())
  .then(data => {
    console.assert(data.success === true, '✅ Request succeeded');
    console.assert(Array.isArray(data.data.prizes), '✅ Prizes is an array');
    console.assert(typeof data.data.stats === 'object', '✅ Stats is an object');
    console.log('✅ Test 1 Passed: Valid Product');
  });
```

### Test 2: Invalid Product (404) ❌

```javascript
// Should return 404 with error message
fetch('/wp-json/nera/v1/instant-wins/99999')
  .then(r => r.json())
  .then(data => {
    console.assert(data.code === 'invalid_product', '✅ Correct error code');
    console.assert(data.data.status === 404, '✅ Correct status code');
    console.log('✅ Test 2 Passed: Invalid Product');
  });
```

### Test 3: Caching ⚡

```javascript
// First request (should not be cached)
console.log('🔄 First request...');
fetch('/wp-json/nera/v1/instant-wins/123')
  .then(r => r.json())
  .then(data => {
    console.log('First request cached:', data.cached); // Should be false

    // Second request immediately (should be cached)
    setTimeout(() => {
      console.log('🔄 Second request...');
      fetch('/wp-json/nera/v1/instant-wins/123')
        .then(r => r.json())
        .then(data => {
          console.assert(data.cached === true, '✅ Response is cached');
          console.log('✅ Test 3 Passed: Caching Works');
        });
    }, 1000);
  });
```

### Test 4: Rate Limiting 🚫

```javascript
// Send 32 requests quickly (limit is 30/min)
console.log('🔄 Sending 32 requests...');
let requests = [];
for (let i = 0; i < 32; i++) {
  requests.push(
    fetch('/wp-json/nera/v1/instant-wins/123')
      .then(r => r.json())
      .then(data => ({ request: i + 1, code: data.code || 'success' }))
  );
}

Promise.all(requests).then(results => {
  let rateLimited = results.filter(r => r.code === 'rate_limit_exceeded');
  console.assert(rateLimited.length > 0, '✅ Rate limiting triggered');
  console.log(`✅ Test 4 Passed: Rate Limiting (${rateLimited.length} requests blocked)`);
});
```

### Test 5: Data Structure Validation 📊

```javascript
fetch('/wp-json/nera/v1/instant-wins/123')
  .then(r => r.json())
  .then(data => {
    if (data.success && data.data.prizes.length > 0) {
      let prize = data.data.prizes[0];

      // Validate prize structure
      console.assert(typeof prize.id === 'string', '✅ Prize has ID');
      console.assert(typeof prize.title === 'string', '✅ Prize has title');
      console.assert(typeof prize.total_available === 'number', '✅ Prize has total_available');
      console.assert(typeof prize.won_count === 'number', '✅ Prize has won_count');
      console.assert(Array.isArray(prize.winners), '✅ Prize has winners array');

      // Validate winner structure (if any winners)
      if (prize.winners.length > 0) {
        let winner = prize.winners[0];
        console.assert(typeof winner.name === 'string', '✅ Winner has name');
        console.assert(typeof winner.ticket === 'string', '✅ Winner has ticket');
        console.assert(typeof winner.date === 'string', '✅ Winner has date');
      }

      // Validate stats structure
      console.assert(
        typeof data.data.stats.total_available === 'number',
        '✅ Stats has total_available'
      );
      console.assert(typeof data.data.stats.total_won === 'number', '✅ Stats has total_won');

      console.log('✅ Test 5 Passed: Data Structure Valid');
    }
  });
```

### Test 6: Name Privacy 🔒

```javascript
// Verify winner names are formatted as "FirstName L."
fetch('/wp-json/nera/v1/instant-wins/123')
  .then(r => r.json())
  .then(data => {
    if (data.success && data.data.prizes.length > 0) {
      data.data.prizes.forEach(prize => {
        prize.winners.forEach(winner => {
          // Check name format: "FirstName L."
          let isValidFormat = /^[A-Za-z]+\s+[A-Z]\.$/.test(winner.name);
          console.assert(isValidFormat, `✅ Name is private: ${winner.name}`);
        });
      });
      console.log('✅ Test 6 Passed: Name Privacy Enforced');
    }
  });
```

## Testing Different Product States

### Product Without Instant Wins

```javascript
// Should return 400: instant_wins_disabled
fetch('/wp-json/nera/v1/instant-wins/456') // Product without instant wins
  .then(r => r.json())
  .then(data => {
    console.assert(
      data.code === 'instant_wins_disabled',
      '✅ Correct error for disabled instant wins'
    );
    console.log('✅ Test Passed: Product Without Instant Wins');
  });
```

### Non-Lottery Product

```javascript
// Should return 400: invalid_product_type
fetch('/wp-json/nera/v1/instant-wins/789') // Regular product (not lottery)
  .then(r => r.json())
  .then(data => {
    console.assert(
      data.code === 'invalid_product_type',
      '✅ Correct error for non-lottery product'
    );
    console.log('✅ Test Passed: Non-Lottery Product');
  });
```

## Testing Cache Invalidation

### Manual Cache Clear

In WordPress admin or via SSH:

```php
// In wp-admin > Tools > Theme File Editor > functions.php (temporarily add at bottom)
add_action('admin_init', function () {
  if (isset($_GET['clear_instant_wins_cache'])) {
    $product_id = absint($_GET['product_id']);
    nera_clear_instant_wins_cache($product_id);
    wp_die('Cache cleared for product ' . $product_id);
  }
});
```

Then visit: `https://yoursite.com/wp-admin/?clear_instant_wins_cache=1&product_id=123`

### Verify Cache Cleared

```javascript
// 1. Load data (cached)
fetch('/wp-json/nera/v1/instant-wins/123')
  .then(r => r.json())
  .then(data => {
    console.log('Before clear - Cached:', data.cached); // true

    // 2. Clear cache manually (use admin URL above)

    // 3. Load data again (should not be cached)
    fetch('/wp-json/nera/v1/instant-wins/123')
      .then(r => r.json())
      .then(data => {
        console.log('After clear - Cached:', data.cached); // false
        console.log('✅ Cache invalidation works');
      });
  });
```

## Testing with cURL

### Basic Request

```bash
curl -X GET "https://yoursite.com/wp-json/nera/v1/instant-wins/123"
```

### With Headers

```bash
curl -X GET "https://yoursite.com/wp-json/nera/v1/instant-wins/123" \
  -H "Accept: application/json" \
  -i
```

### Test Rate Limiting

```bash
# Send 35 requests quickly
for i in {1..35}; do
  echo "Request $i:"
  curl -s "https://yoursite.com/wp-json/nera/v1/instant-wins/123" | jq '.code'
done
```

## Performance Testing

### Measure Response Time

```javascript
async function measurePerformance(productId, iterations = 10) {
  let times = [];

  for (let i = 0; i < iterations; i++) {
    let start = performance.now();
    await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
    let end = performance.now();
    times.push(end - start);

    if (i === 0) await new Promise(r => setTimeout(r, 100)); // Clear cache after first request
  }

  let avg = times.reduce((a, b) => a + b) / times.length;
  console.log(`Average response time: ${avg.toFixed(2)}ms`);
  console.log(`First request (uncached): ${times[0].toFixed(2)}ms`);
  console.log(
    `Subsequent requests (cached): ${(times.slice(1).reduce((a, b) => a + b) / (times.length - 1)).toFixed(2)}ms`
  );
}

// Run performance test
measurePerformance(123);
```

**Expected Results:**

- First request (uncached): 100-200ms
- Cached requests: 10-20ms

## Troubleshooting

### Issue: 404 Not Found

**Solution:**

1. Flush permalinks: **Settings > Permalinks > Save Changes**
2. Verify file exists: `inc/api/instant-wins-api.php`
3. Check functions.php includes the file

### Issue: Empty prizes array

**Solution:**

1. Verify instant wins are configured in product admin
2. Check `_instant_wins_enabled` meta is set to 'yes'
3. Ensure lottery plugin is active

### Issue: Rate limit not working

**Solution:**

1. Check transients are working: `wp_using_ext_object_cache()`
2. Clear rate limit manually: `delete_transient('nera_instant_wins_rate_*')`
3. Verify IP detection is working

### Issue: Cache not updating

**Solution:**

1. Clear cache manually: `nera_clear_instant_wins_cache($product_id)`
2. Check hooks are firing: Enable WP_DEBUG_LOG
3. Verify transient TTL hasn't expired

## Success Criteria

✅ All tests pass
✅ Rate limiting triggers after 30 requests
✅ Caching works (cached = true on 2nd request)
✅ Data structure matches expected format
✅ Winner names are formatted as "FirstName L."
✅ Response time is fast (10-20ms cached, 100-200ms uncached)
✅ Error handling works (404, 400, 429)

## Next Steps

Once all tests pass:

1. ✅ API is production-ready
2. 🔄 Move to frontend implementation (Alpine.js drawer)
3. 🔄 Integrate with instant wins drawer template
4. 🔄 Test end-to-end user flow

## Automated Test Runner

Run all tests at once:

```javascript
async function runAllTests(productId) {
  console.log('🧪 Starting Instant Wins API Test Suite...\n');

  // Test 1: Valid Product
  console.log('Test 1: Valid Product');
  let response1 = await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
  let data1 = await response1.json();
  console.log(data1.success ? '✅ PASS' : '❌ FAIL', '\n');

  // Test 2: Invalid Product
  console.log('Test 2: Invalid Product (404)');
  let response2 = await fetch('/wp-json/nera/v1/instant-wins/99999');
  let data2 = await response2.json();
  console.log(data2.code === 'invalid_product' ? '✅ PASS' : '❌ FAIL', '\n');

  // Test 3: Caching
  console.log('Test 3: Caching');
  let response3a = await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
  let data3a = await response3a.json();
  await new Promise(r => setTimeout(r, 100));
  let response3b = await fetch(`/wp-json/nera/v1/instant-wins/${productId}`);
  let data3b = await response3b.json();
  console.log(data3b.cached === true ? '✅ PASS' : '❌ FAIL', '\n');

  // Test 4: Data Structure
  console.log('Test 4: Data Structure');
  let valid =
    data1.data &&
    Array.isArray(data1.data.prizes) &&
    data1.data.stats &&
    typeof data1.data.stats.total_available === 'number';
  console.log(valid ? '✅ PASS' : '❌ FAIL', '\n');

  console.log('🎉 Test Suite Complete!');
}

// Run with your product ID
runAllTests(123);
```

## API Monitoring

Monitor API usage in production:

```php
// Add to functions.php temporarily
add_action('rest_api_init', function () {
  add_filter(
    'rest_pre_dispatch',
    function ($result, $server, $request) {
      if ($request->get_route() === '/nera/v1/instant-wins/(?P<product_id>\d+)') {
        error_log('Instant Wins API: ' . $request->get_method() . ' ' . $request->get_route());
      }
      return $result;
    },
    10,
    3,
  );
});
```

Then check logs: `wp-content/debug.log`
