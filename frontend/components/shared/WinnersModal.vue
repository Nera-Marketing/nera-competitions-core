<template>
  <Teleport to="body">
    <Transition name="modal" @before-enter="onBeforeEnter" @enter="onEnter" @leave="onLeave">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        aria-labelledby="winners-modal-title"
      >
        <!-- Backdrop with fade-in animation -->
        <div
          :class="[
            'fixed inset-0 bg-black/40 backdrop-blur-sm',
            'transition-opacity duration-300',
            isExiting ? 'opacity-0' : 'opacity-100 animate-[modal-fade-in_0.3s_ease-out]',
          ]"
          @click="modalHandleClose"
          aria-hidden="true"
        ></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
          <div
            ref="modalRef"
            :class="[
              'relative bg-[#FAFAFA] rounded-3xl',
              'shadow-[0_25px_50px_-12px_rgba(0,0,0,0.15)]',
              'max-w-4xl w-full max-h-[85vh] overflow-hidden',
              'transition-all duration-300',
              isExiting
                ? 'opacity-0 scale-95 translate-y-2'
                : 'opacity-100 scale-100 translate-y-0 animate-[modal-scale-in_0.4s_ease-out]',
            ]"
            @click.stop
          >
            <!-- Header - Sticky with elegant typography -->
            <div
              class="sticky top-0 bg-[#FAFAFA] border-b border-gray-100 px-8 py-6 flex items-start gap-6 z-10"
            >
              <div class="flex-1 min-w-0">
                <h2
                  id="winners-modal-title"
                  class="font-['Playfair_Display'] text-3xl sm:text-4xl font-bold text-[#1A1A1A] mb-2 tracking-tight leading-tight"
                >
                  {{ prizeTitle }}
                </h2>
                <p class="font-['DM_Sans'] text-sm text-gray-500">
                  <span class="font-semibold text-[#D4AF37]">{{ winners.length }}</span>
                  <span> {{ winners.length === 1 ? 'Winner' : 'Winners' }}</span>
                  <span v-if="totalPages > 1" class="ml-2 text-gray-400">
                    · Page {{ currentPage }} of {{ totalPages }}
                  </span>
                </p>
              </div>
              <button
                ref="closeButtonRef"
                @click="modalHandleClose"
                class="shrink-0 w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-full transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50"
                aria-label="Close modal"
              >
                <span class="material-symbols-outlined text-gray-600 text-2xl">close</span>
              </button>
            </div>

            <!-- Winners Grid - Scrollable -->
            <div class="px-8 py-6 overflow-y-auto max-h-[calc(85vh-180px)]">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <WinnerCard
                  v-for="(winner, index) in currentWinners"
                  :key="startIndex + index"
                  :winner="winner"
                  :index="index"
                />
              </div>
            </div>

            <!-- Pagination Controls - Only show if multiple pages -->
            <div
              v-if="totalPages > 1"
              class="sticky bottom-0 bg-[#FAFAFA] border-t border-gray-100 px-8 py-5 flex items-center justify-between"
            >
              <!-- Previous Button -->
              <button
                @click="goToPreviousPage"
                :disabled="currentPage === 1"
                class="group flex items-center gap-2 px-4 py-2.5 rounded-xl font-['DM_Sans'] font-semibold text-sm transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent hover:bg-surface hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50"
                aria-label="Previous page"
              >
                <span
                  class="material-symbols-outlined text-xl transition-transform group-hover:-translate-x-0.5"
                >
                  arrow_back
                </span>
                <span class="hidden sm:inline">Previous</span>
              </button>

              <!-- Page Indicators -->
              <div class="flex items-center gap-2">
                <template v-if="totalPages <= 5">
                  <!-- Show dots for 5 or fewer pages -->
                  <button
                    v-for="i in totalPages"
                    :key="i"
                    @click="setCurrentPage(i)"
                    :class="[
                      'w-2.5 h-2.5 rounded-full transition-all duration-200',
                      'focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50',
                      currentPage === i ? 'bg-[#D4AF37] w-8' : 'bg-gray-300 hover:bg-gray-400',
                    ]"
                    :aria-label="`Go to page ${i}`"
                    :aria-current="currentPage === i ? 'page' : undefined"
                  />
                </template>
                <template v-else>
                  <!-- Show numbers for more than 5 pages -->
                  <template v-for="i in totalPages" :key="i">
                    <template v-if="shouldShowPageNumber(i)">
                      <button
                        @click="setCurrentPage(i)"
                        :class="[
                          'min-w-[2.5rem] h-10 rounded-xl font-[\'DM_Sans\'] font-semibold text-sm',
                          'transition-all duration-200',
                          'focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50',
                          currentPage === i
                            ? 'bg-[#D4AF37] text-white shadow-sm'
                            : 'bg-transparent text-gray-600 hover:bg-surface hover:shadow-sm',
                        ]"
                        :aria-label="`Go to page ${i}`"
                        :aria-current="currentPage === i ? 'page' : undefined"
                      >
                        {{ i }}
                      </button>
                    </template>
                    <span v-else-if="shouldShowEllipsis(i)" class="text-gray-400 px-1"> ... </span>
                  </template>
                </template>
              </div>

              <!-- Next Button -->
              <button
                @click="goToNextPage"
                :disabled="currentPage === totalPages"
                class="group flex items-center gap-2 px-4 py-2.5 rounded-xl font-['DM_Sans'] font-semibold text-sm transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent hover:bg-surface hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50"
                aria-label="Next page"
              >
                <span class="hidden sm:inline">Next</span>
                <span
                  class="material-symbols-outlined text-xl transition-transform group-hover:translate-x-0.5"
                >
                  arrow_forward
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import WinnerCard from './WinnerCard.vue';
import { useModal } from '../../composables/useModal';

/**
 * WinnersModal Component - Minimal & Elegant Design (Vue)
 *
 * A sophisticated modal for displaying prize winners with:
 * - Pagination (10 winners per page)
 * - Keyboard navigation (Escape to close, Arrow keys for pagination)
 * - Focus trap for accessibility
 * - Orchestrated entrance animations
 * - Body scroll lock when open
 *
 * @param {boolean} isOpen - Modal visibility state
 * @param {function} onClose - Close handler
 * @param {string} prizeTitle - Prize title/message
 * @param {Array} winners - Array of winner objects {details, ticket_number}
 */
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  onClose: {
    type: Function,
    required: true,
  },
  prizeTitle: {
    type: String,
    required: true,
  },
  winners: {
    type: Array,
    default: () => [],
  },
});

// Use modal composable for common modal functionality
const {
  isExiting,
  modalRef,
  closeButtonRef,
  handleClose: modalHandleClose,
  lockBodyScroll,
  unlockBodyScroll,
  focusCloseButton,
  setupModal,
  cleanupModal,
} = useModal({
  onClose: () => {
    currentPage.value = 1; // Reset page on close
    props.onClose();
  },
  exitDuration: 250,
});

const currentPage = ref(1);

const winnersPerPage = 10;
const totalPages = computed(() => Math.ceil(props.winners.length / winnersPerPage));

// Calculate current page winners
const startIndex = computed(() => (currentPage.value - 1) * winnersPerPage);
const endIndex = computed(() => startIndex.value + winnersPerPage);
const currentWinners = computed(() => props.winners.slice(startIndex.value, endIndex.value));

// Pagination methods
const setCurrentPage = page => {
  currentPage.value = page;
};

const goToPreviousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const goToNextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

// Pagination display logic for many pages
const shouldShowPageNumber = pageNum => {
  const isCurrentPage = currentPage.value === pageNum;
  const isNearCurrent = Math.abs(currentPage.value - pageNum) <= 1;
  const isFirstOrLast = pageNum === 1 || pageNum === totalPages.value;

  return isCurrentPage || isNearCurrent || isFirstOrLast;
};

const shouldShowEllipsis = pageNum => {
  return pageNum === 2 || pageNum === totalPages.value - 1;
};

// Enhanced keyboard navigation with arrow keys for pagination
const handleKeyDown = e => {
  if (!props.isOpen) return;

  if (e.key === 'Escape') {
    modalHandleClose();
  } else if (e.key === 'ArrowLeft' && currentPage.value > 1) {
    goToPreviousPage();
  } else if (e.key === 'ArrowRight' && currentPage.value < totalPages.value) {
    goToNextPage();
  }
};

// Watch isOpen to manage body scroll and focus
watch(
  () => props.isOpen,
  async newValue => {
    if (newValue) {
      lockBodyScroll();
      focusCloseButton();
    } else {
      unlockBodyScroll();
    }
  }
);

// Lifecycle hooks
let focusTrapHandler = null;

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
  focusTrapHandler = setupModal();
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  cleanupModal(focusTrapHandler);
});

// Transition hooks
const onBeforeEnter = el => {
  // Animation setup is handled by CSS classes
};

const onEnter = el => {
  // Animation is handled by CSS classes
};

const onLeave = el => {
  // Animation is handled by CSS classes
};
</script>

<style scoped>
/* Transition classes for Vue Transition component */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
