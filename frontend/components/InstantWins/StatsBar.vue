<template>
  <div class="mb-6">
    <!-- Light Premium Card -->
    <div
      class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-6 shadow-[0_10px_30px_-10px_rgba(251,191,36,0.15)]"
    >
      <div class="flex items-center justify-around gap-6">
        <!-- Available Stat Column -->
        <div class="flex-1 text-center">
          <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 border-2 border-emerald-200 mb-3"
          >
            <span class="material-symbols-outlined text-emerald-600 text-2xl">card_giftcard</span>
          </div>
          <p class="text-xs font-bold text-emerald-700 uppercase tracking-widest mb-2">Available</p>
          <p class="text-3xl font-black text-gray-900 [font-variant-numeric:tabular-nums] mb-0">
            {{ availableCount }}
          </p>
        </div>

        <!-- Vertical Divider -->
        <div class="w-px h-20 bg-gray-200"></div>

        <!-- Won Stat Column -->
        <div class="flex-1 text-center">
          <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-50 border-2 border-amber-200 mb-3"
          >
            <span class="material-symbols-outlined text-amber-600 text-2xl">emoji_events</span>
          </div>
          <p class="text-xs font-bold text-amber-700 uppercase tracking-widest mb-2">Won</p>
          <p class="text-3xl font-black text-gray-900 [font-variant-numeric:tabular-nums] mb-0">
            {{ wonCount }}
          </p>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
          <span>Progress</span>
          <span class="font-semibold text-gray-900">{{ Math.round(progressPercentage) }}%</span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-amber-400 via-amber-500 to-yellow-500 rounded-full animate-[instant-wins-progress-fill_1s_cubic-bezier(0.16,1,0.3,1)_0.3s_both]"
            :style="{
              '--progress-width': `${progressPercentage}%`,
              width: 'var(--progress-width)',
            }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Stats Bar component - Premium light card with progress bar (Vue)
 * Single full-width card displaying available and won prize counts
 */
const props = defineProps({
  availableCount: {
    type: Number,
    default: 0,
  },
  wonCount: {
    type: Number,
    default: 0,
  },
  availableLabel: {
    type: String,
    default: '',
  },
  wonLabel: {
    type: String,
    default: '',
  },
});

const totalCount = computed(() => props.availableCount + props.wonCount);
const progressPercentage = computed(() =>
  totalCount.value > 0 ? (props.wonCount / totalCount.value) * 100 : 0
);
</script>
