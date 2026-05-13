/**
 * k6 stress test for the lottery-ticket checkout flow.
 *
 *   Verifies the async-ticket-generation fix in
 *   ../class-nera-lty-async-tickets.php by driving real Store API
 *   checkouts at concurrency on a local site.
 *
 * Run (sanity, 1 VU / 10s):
 *
 *   k6 run --vus 1 --duration 10s \
 *     -e USERNAME=adm1n -e PASSWORD='your-password' \
 *     lottery-stress.js
 *
 * Run (full ramp, defaults — 30 VUs over ~2m20s):
 *
 *   k6 run -e USERNAME=adm1n -e PASSWORD='your-password' lottery-stress.js
 *
 * NEVER hard-code credentials in this file — pass them via -e at runtime.
 *
 * A/B procedure:
 *   1. Comment out `require_once .../lty-async-tickets/loader.php` in
 *      the theme's functions.php → run k6 → record p(95) of name:checkout.
 *   2. Re-enable that require_once → run k6 → record p(95).
 *   3. The async run's p(95) should be much lower at the same VU count.
 *
 * Wallet contention caveat: with a single shared admin user, all VUs
 * debit the same TeraWallet row, which serializes. Some of the latency
 * you measure is wallet contention, not ticket creation. See the
 * README in this folder for details.
 */

import http from 'k6/http';
import { check, sleep, fail } from 'k6';

const BASE           = __ENV.BASE           || 'http://competitions-core.local';
const PRODUCT_ID     = parseInt(__ENV.PRODUCT_ID || '783', 10);
const TICKETS        = parseInt(__ENV.TICKETS    || '5',   10);
const USERNAME       = __ENV.USERNAME       || 'adm1n';
const PASSWORD       = __ENV.PASSWORD       || 'rnXXpMGD%SKm#$6ltbEqQKbX';
const PAYMENT_METHOD = __ENV.PAYMENT_METHOD || 'wallet';
// This site uses a "hide login" plugin — the real login URL is /nmlogin/
// not /wp-login.php. Override with -e LOGIN_PATH=/your-path if needed.
const LOGIN_PATH     = __ENV.LOGIN_PATH     || '/nmlogin/';

if (!USERNAME || !PASSWORD) {
	fail('USERNAME and PASSWORD env vars are required. Run k6 with -e USERNAME=... -e PASSWORD=...');
}

export const options = {
	scenarios: {
		ramp: {
			executor: 'ramping-vus',
			startVUs: 0,
			stages: [
				{ target: 5,  duration: '20s' },  // warm up
				{ target: 30, duration: '40s' },  // ramp
				{ target: 30, duration: '60s' },  // sustain peak
				{ target: 0,  duration: '20s' },  // cool down
			],
			gracefulRampDown: '10s',
		},
	},
	insecureSkipTLSVerify: true,
	thresholds: {
		'http_req_duration{name:checkout}':    ['p(95)<3000'],
		'http_req_duration{name:add-to-cart}': ['p(95)<1000'],
		'http_req_failed':                     ['rate<0.05'],
	},
};

export function setup() {
	const ping = http.get(`${BASE}/`, { tags: { name: 'ping' } });
	if (ping.status === 0 || ping.status >= 500) {
		fail(`Cannot reach ${BASE} (status ${ping.status}). Is the site running?`);
	}
	// Run ID is propagated to every VU so emails are globally unique across
	// runs. Stops "An account is already registered" collisions when WC's
	// auto-create-account-at-checkout setting is on.
	return { runId: Date.now().toString(36) };
}

// Per-VU state — module scope is per-VU in k6, persists across iterations.
let loggedIn   = false;
let storeNonce = '';

export default function (data) {
	if (!loggedIn) {
		login();
		loggedIn   = true;
		storeNonce = fetchStoreNonce();
	}

	// 1) Add lottery product to cart via Store API.
	const addRes = postStore(
		`${BASE}/wp-json/wc/store/v1/cart/add-item`,
		JSON.stringify({ id: PRODUCT_ID, quantity: TICKETS }),
		'add-to-cart'
	);

	const cartOk = check(addRes, {
		'cart 2xx': (r) => r.status >= 200 && r.status < 300,
	});
	if (!cartOk) {
		// Useful for debugging during sanity runs; cheap noise once at scale.
		console.warn(`[VU${__VU}/${__ITER}] add-to-cart failed: ${addRes.status} ${truncate(addRes.body, 200)}`);
		return;
	}

	const cartToken = addRes.headers['Cart-Token'] || addRes.headers['cart-token'] || '';

	// 2) Submit checkout via Store API.
	const checkoutRes = postStore(
		`${BASE}/wp-json/wc/store/v1/checkout`,
		JSON.stringify({
			billing_address: {
				first_name: 'Load',
				last_name:  `Test${__VU}`,
				address_1:  '1 Test Street',
				city:       'Testville',
				postcode:   'SW1A 1AA',
				country:    'GB',
				state:      '',
				email:      `vu${__VU}_iter${__ITER}_${data.runId}@loadtest.local`,
				phone:      '+447000000000',
			},
			shipping_address: {
				first_name: 'Load',
				last_name:  `Test${__VU}`,
				address_1:  '1 Test Street',
				city:       'Testville',
				postcode:   'SW1A 1AA',
				country:    'GB',
				state:      '',
			},
			payment_method: PAYMENT_METHOD,
		}),
		'checkout',
		{ 'Cart-Token': cartToken }
	);

	const checkoutOk = check(checkoutRes, {
		'checkout 2xx': (r) => r.status >= 200 && r.status < 300,
	});
	if (!checkoutOk) {
		console.warn(`[VU${__VU}/${__ITER}] checkout failed: ${checkoutRes.status} ${truncate(checkoutRes.body, 300)}`);
	}

	sleep(1);
}

function login() {
	// WordPress login form POST. k6's per-VU cookieJar captures the
	// wordpress_logged_in_* and wordpress_sec_* cookies automatically,
	// and reuses them on every subsequent request from this VU.
	const res = http.post(
		`${BASE}${LOGIN_PATH}`,
		{
			log:           USERNAME,
			pwd:           PASSWORD,
			'wp-submit':   'Log In',
			redirect_to:   BASE,
			testcookie:    '1',
		},
		{
			redirects: 0, // 302 on success, 200 (form re-render) on failure
			tags:      { name: 'login' },
		}
	);

	const jar     = http.cookieJar();
	const cookies = jar.cookiesForURL(BASE);
	const authed  = Object.keys(cookies).some((name) => name.indexOf('wordpress_logged_in_') === 0);

	// Trust the cookie, not the status code. The hide-login plugin can
	// return 200 (with the cookie set) instead of the standard 302.
	if (!authed) {
		fail(`[VU${__VU}] login failed: status=${res.status}, no auth cookie set. Check USERNAME/PASSWORD or LOGIN_PATH.`);
	}
}

// POST to a WC Store API endpoint with the current nonce, capture the
// rotated nonce from the response, and retry once on 403-invalid-nonce
// (which happens after a successful checkout destroys the cart session).
function postStore(url, body, tagName, extraHeaders) {
	const buildOpts = (overrideHeaders) => ({
		headers: Object.assign(
			{
				'Content-Type': 'application/json',
				'Nonce':        storeNonce,
			},
			extraHeaders || {},
			overrideHeaders || {}
		),
		tags: { name: tagName },
	});

	let res = http.post(url, body, buildOpts());
	updateNonceFromResponse(res);

	if (res.status === 403 && isNonceError(res.body)) {
		// Bootstrap a fresh nonce against /cart and retry once.
		storeNonce = fetchStoreNonce();
		res = http.post(url, body, buildOpts({ 'Nonce': storeNonce }));
		updateNonceFromResponse(res);
	}

	return res;
}

function isNonceError(body) {
	if (typeof body !== 'string') {
		return false;
	}
	return body.indexOf('woocommerce_rest_invalid_nonce') !== -1
		|| body.indexOf('woocommerce_rest_missing_nonce') !== -1;
}

// WC Store API rotates the Nonce on every response. Capture the fresh
// value off each response so the next request stays valid.
function updateNonceFromResponse(res) {
	const next = (res && res.headers)
		? (res.headers['Nonce'] || res.headers['nonce'] || res.headers['X-Wc-Store-Api-Nonce'] || res.headers['x-wc-store-api-nonce'] || '')
		: '';
	if (next) {
		storeNonce = next;
	}
}

function fetchStoreNonce() {
	// The WC Store API returns a fresh `Nonce` header in every response
	// for logged-in users. Hitting GET /cart once after login is the
	// cheapest way to obtain one.
	const res = http.get(`${BASE}/wp-json/wc/store/v1/cart`, {
		tags: { name: 'store-nonce' },
	});

	// Headers are case-insensitive in HTTP but k6 exposes them with the
	// case the server sent. Check the common spellings.
	const nonce = res.headers['Nonce']
		|| res.headers['nonce']
		|| res.headers['X-Wc-Store-Api-Nonce']
		|| res.headers['x-wc-store-api-nonce']
		|| '';

	if (!nonce) {
		fail(`[VU${__VU}] could not obtain a Store API nonce (status ${res.status}). Headers: ${JSON.stringify(res.headers)}`);
	}

	return nonce;
}

function truncate(s, n) {
	if (typeof s !== 'string') {
		return '';
	}
	return s.length <= n ? s : s.slice(0, n) + '…';
}
