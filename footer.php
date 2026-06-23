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
<div x-data class="ncs-toast-region fixed top-20 right-6 z-[9999] flex flex-col gap-3 max-w-[420px] pointer-events-none">
  <template x-for="toast in $store.toast.items" :key="toast.id">
    <div x-show="toast.isVisible"
      x-transition:enter="transition-all transform ease-[cubic-bezier(0.21,1.02,0.73,1)] duration-300"
      x-transition:enter-start="opacity-0 -translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition-all transform ease-[cubic-bezier(0.21,1.02,0.73,1)] duration-300"
      x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-8"
      :class="'ncs-toast--' + toast.type"
      class="ncs-toast group bg-surface dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 flex gap-4 items-start md:items-center min-h-[64px] max-w-[420px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] pointer-events-auto hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-shadow">

      <!-- Icon -->
      <div class="ncs-toast__icon shrink-0 w-6 h-6 overflow-hidden mt-0.5 md:mt-0 flex items-center justify-center">
        <span :class="{
            'text-success dark:text-success': toast.type === 'success',
            'text-danger dark:text-danger': toast.type === 'error',
            'text-warning dark:text-warning': toast.type === 'warning',
            'text-primary dark:text-primary': toast.type === 'info'
          }" class="material-symbols-outlined text-[20px]"
          x-text="toast.type === 'success' ? 'check_circle' : toast.type === 'error' ? 'error' : toast.type === 'warning' ? 'warning' : 'info'"></span>
      </div>

      <!-- Content -->
      <div class="ncs-toast__content flex-1 min-w-0">
        <div class="ncs-toast__message text-gray-800 dark:text-gray-100 text-[13px] leading-snug font-medium" x-html="toast.message"></div>
        <!-- <div x-show="toast.action" class="mt-2">
          <button @click="toast.action.callback(); $store.toast.remove(toast.id)"
            class="text-[12px] font-semibold text-gray-900 dark:text-gray-50 border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200"
            x-text="toast.action?.label"></button>
        </div> -->
      </div>

      <!-- Close Button -->
      <button @click="$store.toast.remove(toast.id)"
        class="ncs-toast__close shrink-0 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-900 dark:text-gray-500 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer rounded-md transition-all duration-200 opacity-0 group-hover:opacity-100">
        <span class="material-symbols-outlined text-[16px]">close</span>
      </button>
    </div>
  </template>
</div>

<!-- Confirm / Alert Dialog (AlpineJS) — promise-based replacement for window.confirm/alert -->
<div
  x-data
  x-show="$store.dialog.open"
  x-cloak
  @keydown.escape.window="$store.dialog.open && $store.dialog.cancel()"
  :class="{ 'ncs-dialog--danger': $store.dialog.variant === 'danger', 'ncs-dialog--alert': $store.dialog.mode === 'alert' }"
  class="ncs-dialog fixed inset-0 z-[10000] flex items-center justify-center p-4"
  role="dialog"
  aria-modal="true"
  aria-labelledby="nera-dialog-title"
  style="display: none;"
>
  <!-- Backdrop -->
  <div
    x-show="$store.dialog.open"
    x-transition:enter="transition-opacity ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click="$store.dialog.cancel()"
    class="ncs-dialog__backdrop absolute inset-0 bg-black/50"
  ></div>

  <!-- Panel -->
  <div
    x-show="$store.dialog.open"
    x-transition:enter="transition-all transform ease-[cubic-bezier(0.21,1.02,0.73,1)] duration-300"
    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition-all transform ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    x-effect="if ($store.dialog.open) $nextTick(() => $refs.dialogConfirm && $refs.dialogConfirm.focus())"
    @click.stop
    class="ncs-dialog__panel relative z-[1] max-h-[min(100vh-2rem,90dvh)] max-w-md w-full overflow-hidden rounded-2xl border border-gray-200 bg-surface shadow-2xl"
  >
    <div class="flex max-h-[min(100vh-2rem,90dvh)] min-h-0 flex-col overflow-y-auto">
      <div class="p-6 border-b border-gray-100">
        <h4 id="nera-dialog-title" class="ncs-dialog__title text-lg font-bold text-gray-900 flex items-center gap-2">
          <span
            class="ncs-dialog__icon material-symbols-outlined"
            :class="$store.dialog.variant === 'danger' ? 'text-danger' : 'text-primary'"
            x-text="$store.dialog.variant === 'danger' ? 'help' : 'info'"
          ></span>
          <span x-text="$store.dialog.title"></span>
        </h4>
        <div class="ncs-dialog__message text-sm text-gray-600 mt-2" x-html="$store.dialog.message"></div>
      </div>
      <div class="ncs-dialog__actions flex flex-col-reverse sm:flex-row sm:justify-end gap-2 p-4 bg-gray-50/80 rounded-b-2xl shrink-0">
        <button
          type="button"
          x-show="$store.dialog.mode === 'confirm'"
          @click="$store.dialog.cancel()"
          class="ncs-dialog__cancel inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold border-2 border-gray-200 text-gray-700 bg-surface hover:border-gray-300 transition-colors w-full sm:w-auto"
          x-text="$store.dialog.cancelText"
        ></button>
        <button
          type="button"
          x-ref="dialogConfirm"
          @click="$store.dialog.accept()"
          class="ncs-dialog__confirm inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-white border shadow-sm transition-colors w-full sm:w-auto focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
          :class="$store.dialog.variant === 'danger'
            ? 'bg-danger hover:bg-danger-text border-danger-text focus-visible:ring-danger'
            : 'bg-primary hover:bg-primary-dark border-primary-dark focus-visible:ring-primary'"
          x-text="$store.dialog.confirmText"
        ></button>
      </div>
    </div>
  </div>
</div>

</body>

</html>