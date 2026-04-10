import { VueQueryPlugin, QueryClient } from '@tanstack/vue-query';

/**
 * TanStack Query Client Configuration
 *
 * Provides intelligent request management for the Winners page:
 * - Automatic caching to prevent redundant API calls
 * - Request deduplication (coalesces identical simultaneous requests)
 * - Smart retry logic with exponential backoff for rate limits
 * - Automatic request cancellation when queries become stale
 *
 * @see https://tanstack.com/query/latest/docs/vue/overview
 */
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // Cache data for 5 minutes (matches server-side cache)
      // This prevents refetching when switching between filter tabs
      staleTime: 5 * 60 * 1000,

      // Keep unused data in cache for 10 minutes before garbage collection
      // This allows returning to previously viewed filters without refetching
      gcTime: 10 * 60 * 1000,

      // Retry failed requests with intelligent error handling
      retry: (failureCount, error) => {
        // Don't retry 4xx errors (except 429 rate limits)
        if (error.status >= 400 && error.status < 500 && error.status !== 429) {
          return false;
        }
        // Retry up to 3 times for network errors and 429s
        return failureCount < 3;
      },

      // Exponential backoff delay: 1s, 2s, 4s, 8s (capped at 30s)
      retryDelay: attemptIndex => Math.min(1000 * 2 ** attemptIndex, 30000),

      // Disable refetch on window focus to reduce unnecessary requests
      // The 5-minute cache is sufficient for most use cases
      refetchOnWindowFocus: false,

      // Refetch when network reconnects after being offline
      refetchOnReconnect: true,

      // Pause queries when offline (don't make requests that will fail)
      networkMode: 'online',
    },
  },
});

export { VueQueryPlugin };
