<template>
  <div
    class="animate-card-enter bg-surface rounded-2xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] hover:shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)] transition-all duration-300 overflow-hidden"
    :style="{ animationDelay: `${index * 100}ms` }"
  >
    <!-- Card Header — clickable toggle only when there are winners to reveal -->
    <component
      :is="isInteractive ? 'button' : 'div'"
      @click="isInteractive ? handleToggle() : null"
      :class="[
        'w-full text-left p-5 transition-colors duration-200',
        isInteractive ? 'hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary/30' : '',
      ]"
      :aria-expanded="isInteractive ? isOpen : null"
      :aria-controls="isInteractive ? `prize-group-body-${prize.key}` : null"
    >
      <div class="flex items-center gap-4">
        <!-- Prize Image -->
        <PrizeImage :prize-image="prize.prizeImage" :is-fully-won="isFullyWon" />

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <!-- Title -->
          <h3 class="text-lg font-bold text-text-primary mb-3 line-clamp-2">
            <span v-html="prize.prizeMessage" />
          </h3>

          <!-- Status pills row -->
          <div class="flex items-center gap-2 flex-wrap">
            <!-- Fully won badge -->
            <span
              v-if="isFullyWon"
              class="inline-flex items-center gap-1.5 px-3 py-1 bg-danger-bg border border-danger-border text-danger-text rounded-full text-xs font-semibold"
            >
              <span class="material-symbols-outlined text-sm">emoji_events</span>
              All Won
            </span>

            <!-- Remaining pill — shown when not fully won -->
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-3 py-1 bg-success-bg border border-success-border text-success-text rounded-full text-xs font-semibold"
            >
              <span class="relative flex h-2 w-2 shrink-0">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-success"></span>
              </span>
              {{ remainingCount }} / {{ prize.totalCount }} remaining
            </span>
          </div>
        </div>

        <!-- Expand chevron — only when interactive -->
        <div v-if="isInteractive" class="shrink-0 ml-1">
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
    </component>

    <!-- Expandable body — winner cards, like default mode (no ticket numbers) -->
    <div
      v-if="isInteractive && isOpen"
      :id="`prize-group-body-${prize.key}`"
      class="border-t border-gray-200 bg-gray-50 p-5 animate-fade-in-up"
    >
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
              {{ winner.ticket || '—' }}
            </p>

            <!-- Won by Label -->
            <p class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-2">Won by</p>

            <!-- Winner Name -->
            <p class="text-sm font-bold text-gray-700 leading-snug">{{ winner.name }}</p>
          </div>
        </div>

        <button
          v-if="hasMore"
          @click.stop="handleShowAllWinners"
          class="col-span-2 sm:col-span-3 lg:col-span-4 w-full text-sm font-semibold text-primary hover:text-primary-dark transition-colors py-2 px-3 bg-surface rounded-lg border border-primary/20 hover:border-primary/40 hover:bg-primary/5"
        >
          See all winners →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineComponent, h } from 'vue';

/**
 * PrizeGroupCard — renders a single "group" entry from the instant-wins REST payload
 * when display_mode === "group".
 *
 * Each group reuses PrizeCard's base shape:
 *   { key, prizeMessage, prizeImage, totalCount, wonCount, winners }
 *
 * Individual ticket numbers are intentionally not rendered — groups with
 * winners expand to winner cards (like default mode); groups with no winners
 * are static, non-interactive cards.
 */

// ---- PrizeImage sub-component (mirrors PrizeCard.vue) ----
const PrizeImage = defineComponent({
  name: 'PrizeImage',
  props: {
    prizeImage: String,
    isFullyWon: Boolean,
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
        // Status badge (bottom-right of image) — trophy if won, star if available
        h(
          'div',
          {
            class: props.isFullyWon
              ? 'absolute bottom-2 right-2 w-8 h-8 rounded-full bg-gradient-to-br from-warning-bg via-warning to-warning border-2 border-white shadow-lg flex items-center justify-center'
              : 'absolute bottom-2 right-2 w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 via-yellow-500 to-warning shadow-lg flex items-center justify-center border-2 border-white',
          },
          [
            h(
              'span',
              { class: 'material-symbols-outlined text-white text-base' },
              props.isFullyWon ? 'emoji_events' : 'stars'
            ),
          ]
        ),
      ]);
  },
});

// ---- Props ----
const props = defineProps({
  prize: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    default: 0,
  },
  onShowAllWinners: {
    type: Function,
    default: null,
  },
  showWinners: {
    type: Boolean,
    default: true,
  },
});

// ---- State ----
const isOpen = ref(false);

// ---- Computed ----
const remainingCount = computed(() => {
  const total = props.prize.totalCount ?? 0;
  const won = props.prize.wonCount ?? 0;
  return Math.max(0, total - won);
});

const isFullyWon = computed(() => {
  const total = props.prize.totalCount;
  return total != null && total > 0 && remainingCount.value === 0;
});

const hasWinners = computed(() => (props.prize.winners || []).length > 0);

// Card is an interactive accordion only when there are winners to reveal;
// otherwise it renders as a static card (mirrors default-mode available prizes).
const isInteractive = computed(() => props.showWinners && hasWinners.value);

// ---- Winner paging (mirrors PrizeCard.vue) ----
const displayLimit = 4;
const hasMore = computed(() => (props.prize.winners || []).length > displayLimit);
const displayedWinners = computed(() => (props.prize.winners || []).slice(0, displayLimit));

// ---- Handlers ----
const handleToggle = () => {
  if (isInteractive.value) {
    isOpen.value = !isOpen.value;
  }
};

const handleShowAllWinners = () => {
  if (props.onShowAllWinners) {
    props.onShowAllWinners(props.prize.prizeMessage, props.prize.winners || []);
  }
};
</script>
