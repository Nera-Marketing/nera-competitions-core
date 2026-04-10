<template>
  <div
    class="animate-card-enter bg-white rounded-2xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)] transition-all duration-300 overflow-hidden"
    :style="{ animationDelay: `${index * 100}ms` }"
  >
    <!-- Won Prize - Accordion Header (Clickable) - Horizontal Layout -->
    <template v-if="prize.isWon">
      <button
        @click="handleToggle"
        class="w-full text-left p-5 hover:bg-gray-50 transition-colors duration-200"
      >
        <div class="flex items-center gap-4">
          <!-- Prize Image -->
          <PrizeImage :prize-image="prize.prizeImage" />

          <!-- Content Section -->
          <div class="flex-1 min-w-0">
            <!-- Prize Amount/Title -->
            <h3 class="text-lg font-bold text-text-primary mb-3 line-clamp-2">
              <span v-html="prize.prizeMessage" />
            </h3>

            <!-- Won Badge with Count -->
            <div class="flex items-center gap-2 flex-wrap mb-3">
              <span
                class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded-full text-xs font-semibold"
              >
                <span class="material-symbols-outlined text-sm">emoji_events</span>
                {{ prize.wonCount }} Winner{{ prize.wonCount !== 1 ? 's' : '' }}
              </span>
              <span
                v-if="prize.totalCount > prize.wonCount"
                class="text-xs text-emerald-600 font-medium"
              >
                {{ prize.totalCount - prize.wonCount }} remaining
              </span>
            </div>

            <!-- Per-Prize Progress Bar -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">Won</span>
                <span class="font-semibold text-gray-700"
                  >{{ Math.round(progressPercentage) }}%</span
                >
              </div>
              <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div
                  class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-500"
                  :style="{ width: `${progressPercentage}%` }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Accordion Toggle Icon -->
          <div class="shrink-0">
            <span
              :class="[
                'material-symbols-outlined text-text-secondary transition-transform duration-200',
                isOpen ? 'rotate-180' : '',
              ]"
            >
              expand_more
            </span>
          </div>
        </div>
      </button>

      <!-- Accordion Content (Expandable Winner Details) - Light cards with amber accent -->
      <div v-if="isOpen" class="border-t border-gray-200 bg-gray-50 p-5 animate-fade-in-up">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <div
            v-for="(winner, winnerIndex) in displayedWinners"
            :key="winnerIndex"
            class="relative p-5 bg-gradient-to-b from-amber-50 to-yellow-50 rounded-xl text-center shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 border-2 border-amber-200 overflow-hidden group"
          >
            <!-- Amber glow effect on hover -->
            <div
              class="absolute inset-0 bg-gradient-to-br from-amber-100/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
            ></div>

            <!-- Content -->
            <div class="relative z-10">
              <!-- Trophy Icon -->
              <div
                class="inline-flex items-center justify-center w-10 h-10 mb-3 rounded-full bg-amber-100 border-2 border-amber-300"
              >
                <span class="material-symbols-outlined text-amber-600 text-xl">emoji_events</span>
              </div>

              <!-- Ticket Number -->
              <p class="text-lg font-black text-gray-900 mb-1 tracking-tight">
                {{ winner.ticketNumber || winner.ticket || '—' }}
              </p>

              <!-- Won by Label -->
              <p class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-2">Won by</p>

              <!-- Winner Name -->
              <p class="text-sm font-bold text-gray-700 leading-snug">
                <span v-html="winner.details" />
              </p>
            </div>
          </div>

          <button
            v-if="hasMore"
            @click="handleShowAllWinners"
            class="col-span-2 sm:col-span-3 lg:col-span-4 w-full text-sm font-semibold text-primary hover:text-primary-dark transition-colors py-2 px-3 bg-white rounded-lg border border-primary/20 hover:border-primary/40 hover:bg-primary/5"
          >
            See all winners →
          </button>
        </div>
      </div>
    </template>

    <!-- Available Prize - Horizontal Layout (No Accordion) -->
    <div v-else class="p-5">
      <div class="flex items-center gap-4">
        <!-- Prize Image -->
        <PrizeImage :prize-image="prize.prizeImage" />

        <!-- Content Section -->
        <div class="flex-1 min-w-0">
          <!-- Prize Amount/Title -->
          <h3 class="text-lg font-bold text-text-primary mb-3 line-clamp-2">
            <span v-html="prize.prizeMessage" />
          </h3>

          <!-- Available Badge with Count -->
          <div class="flex items-center gap-2 flex-wrap mb-3">
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-full text-xs font-semibold"
            >
              <span class="material-symbols-outlined text-sm">card_giftcard</span>
              Available
            </span>
            <span class="text-xs text-emerald-600 font-medium">
              {{ prize.totalCount }} remaining
            </span>
          </div>

          <!-- Per-Prize Progress Bar (showing 0% for available) -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs">
              <span class="text-gray-500">Won</span>
              <span class="font-semibold text-gray-700">0%</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-500"
                style="width: 0%"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineComponent, h } from 'vue';

/**
 * Prize Card component - Premium dark design with progress bars (Vue)
 *
 * Features:
 * - Small status badge (bottom-right of image)
 * - Per-prize progress bar
 * - Accordion for won prizes (click to expand/collapse)
 * - Static card for available prizes
 * - Dark winner cards with gold accents
 * - Staggered entrance animation
 */

// PrizeImage component
const PrizeImage = defineComponent({
  name: 'PrizeImage',
  props: {
    prizeImage: String,
  },
  setup(props) {
    return () =>
      h('div', { class: 'relative shrink-0 w-28 h-28' }, [
        props.prizeImage
          ? h('div', {
              class: 'w-full h-full overflow-hidden bg-gray-100 rounded-xl',
              innerHTML: props.prizeImage,
            })
          : h(
              'div',
              {
                class:
                  'w-full h-full overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center',
              },
              [
                h(
                  'span',
                  { class: 'material-symbols-outlined text-gray-300 text-4xl' },
                  'card_giftcard'
                ),
              ]
            ),
        h(
          'div',
          {
            class:
              'absolute bottom-2 right-2 w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 via-yellow-500 to-amber-600 shadow-lg flex items-center justify-center border-2 border-white',
          },
          [h('span', { class: 'material-symbols-outlined text-white text-base' }, 'stars')]
        ),
      ]);
  },
});

const props = defineProps({
  prize: {
    type: Object,
    required: true,
  },
  onShowAllWinners: {
    type: Function,
    default: null,
  },
  index: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['show-all-winners']);

const isOpen = ref(false);

const displayLimit = 4;
const hasMore = computed(() => (props.prize.winners || []).length > displayLimit);
const displayedWinners = computed(() => (props.prize.winners || []).slice(0, displayLimit));

const progressPercentage = computed(() => {
  const { totalCount, wonCount } = props.prize;
  return totalCount > 0 ? (wonCount / totalCount) * 100 : 0;
});

const handleToggle = () => {
  if (props.prize.isWon) {
    isOpen.value = !isOpen.value;
  }
};

const handleShowAllWinners = e => {
  e.stopPropagation();
  if (props.onShowAllWinners) {
    props.onShowAllWinners(props.prize.prizeMessage, props.prize.winners || []);
  }
  emit('show-all-winners', props.prize.prizeMessage, props.prize.winners || []);
};
</script>
