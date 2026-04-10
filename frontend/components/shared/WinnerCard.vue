<template>
  <div
    :class="[
      'relative bg-white rounded-2xl shadow-[0_2px_8px_-2px_rgba(0,0,0,0.08)]',
      'hover:shadow-[0_8px_16px_-4px_rgba(0,0,0,0.12)]',
      'transition-all duration-300 p-6 group hover:scale-[1.02]',
      'animate-[winner-card-enter_0.4s_ease-out_forwards] opacity-0',
    ]"
    :style="{
      animationDelay: `${index * 50}ms`,
      animationFillMode: 'forwards',
    }"
  >
    <!-- Subtle hover glow effect -->
    <div
      class="absolute inset-0 rounded-2xl bg-gradient-to-br from-[#D4AF37]/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
    ></div>

    <!-- Content -->
    <div class="relative z-10 flex items-start gap-4">
      <!-- Position Number Circle -->
      <div
        class="shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-[#FAFAFA] to-[#F5F5F5] border border-gray-100 shadow-sm"
      >
        <span class="font-['DM_Sans'] text-lg font-bold text-[#1A1A1A] tracking-tight">
          {{ index + 1 }}
        </span>
      </div>

      <!-- Winner Details -->
      <div class="flex-1 min-w-0 space-y-3">
        <!-- Winner Name/Details -->
        <p
          class="font-['Playfair_Display'] text-lg font-semibold text-[#1A1A1A] leading-snug tracking-tight"
        >
          {{ winner.details }}
        </p>

        <!-- Ticket Number -->
        <div class="flex items-center gap-2">
          <span class="text-xs font-['DM_Sans'] font-medium text-gray-500 uppercase tracking-wider">
            Ticket
          </span>
          <span class="font-['DM_Sans'] text-sm font-bold text-[#D4AF37] font-mono tracking-wide">
            #{{ winner.ticket_number }}
          </span>
        </div>
      </div>

      <!-- Trophy Icon - Subtle accent -->
      <div class="shrink-0 opacity-40 group-hover:opacity-100 transition-opacity duration-300">
        <div
          class="w-10 h-10 rounded-full bg-gradient-to-br from-[#D4AF37]/10 to-[#D4AF37]/5 flex items-center justify-center"
        >
          <span class="material-symbols-outlined text-[#D4AF37] text-xl"> emoji_events </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * WinnerCard Component - Minimal & Elegant Design (Vue)
 *
 * Individual winner display card with:
 * - Refined typography using Playfair Display and DM Sans
 * - Staggered entrance animations
 * - Elegant hover states with subtle scale and shadow
 * - Champagne gold accents for luxury feel
 *
 * @param {Object} winner - Winner data object
 * @param {string} winner.details - Winner name and date
 * @param {string} winner.ticket_number - Ticket number
 * @param {number} index - Card index for staggered animation
 */
defineProps({
  winner: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    default: 0,
  },
});
</script>
