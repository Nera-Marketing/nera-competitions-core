<?php
/**
 * The footer for our theme
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
} ?>

</div><!-- #content -->

<?php get_template_part('template-parts/footer'); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

<!-- Toast Notifications Container (AlpineJS) -->
<div x-data class="fixed top-20 right-6 z-[9999] flex flex-col gap-3 max-w-[420px] pointer-events-none">
  <template x-for="toast in $store.toast.items" :key="toast.id">
    <div x-show="toast.isVisible"
      x-transition:enter="transition-all transform ease-[cubic-bezier(0.21,1.02,0.73,1)] duration-300"
      x-transition:enter-start="opacity-0 -translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition-all transform ease-[cubic-bezier(0.21,1.02,0.73,1)] duration-300"
      x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-8"
      class="group bg-white dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 flex gap-4 items-start md:items-center min-h-[64px] max-w-[420px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] pointer-events-auto hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow">

      <!-- Icon -->
      <div class="shrink-0 w-6 h-6 overflow-hidden mt-0.5 md:mt-0 flex items-center justify-center">
        <span :class="{
            'text-green-600 dark:text-green-500': toast.type === 'success',
            'text-red-600 dark:text-red-500': toast.type === 'error',
            'text-orange-500 dark:text-orange-400': toast.type === 'warning',
            'text-blue-500 dark:text-blue-400': toast.type === 'info'
          }" class="material-symbols-outlined text-[20px]"
          x-text="toast.type === 'success' ? 'check_circle' : toast.type === 'error' ? 'error' : toast.type === 'warning' ? 'warning' : 'info'"></span>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <div class="text-zinc-800 dark:text-zinc-100 text-[13px] leading-snug font-medium" x-html="toast.message"></div>
        <!-- <div x-show="toast.action" class="mt-2">
          <button @click="toast.action.callback(); $store.toast.remove(toast.id)"
            class="text-[12px] font-semibold text-zinc-900 dark:text-zinc-50 border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 px-3 py-1.5 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors duration-200"
            x-text="toast.action?.label"></button>
        </div> -->
      </div>

      <!-- Close Button -->
      <button @click="$store.toast.remove(toast.id)"
        class="shrink-0 w-5 h-5 flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer rounded-md transition-all duration-200 opacity-0 group-hover:opacity-100">
        <span class="material-symbols-outlined text-[16px]">close</span>
      </button>
    </div>
  </template>
</div>

</body>

</html>