<?php
/**
 * Edit account form
 *
 * @package Nera Competitions Standard
 */

defined('ABSPATH') || exit();

do_action('woocommerce_before_edit_account_form');
?>

<div class="nera-edit-account">
  
  <!-- Page Header -->
  <div class="mb-8">
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>"
       class="lg:hidden inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary transition-colors mb-4">
      <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
      <?php esc_html_e('Back to Dashboard', 'nera-competitions-standard'); ?>
    </a>

    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
      <span class="material-symbols-outlined text-primary text-3xl sm:text-4xl">person</span>
      <?php esc_html_e('Account details', 'woocommerce'); ?>
    </h2>
    <p class="text-gray-600">
      <?php esc_html_e(
        'Update your account information and password',
        'nera-competitions-standard',
      ); ?>
    </p>
  </div>

  <form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action(
    'woocommerce_edit_account_form_tag',
  ); ?>>

    <?php do_action('woocommerce_edit_account_form_start'); ?>

    <!-- Personal Information Card -->
    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
      <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
        <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-white text-xl">badge</span>
        </div>
        <h3 class="text-xl font-bold text-gray-900">Personal Information</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <p class="woocommerce-form-row woocommerce-form-row--first form-row ">
          <label for="account_first_name" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('First name', 'woocommerce'); ?>&nbsp;
            <span class="required text-danger">*</span>
          </label>
          <input type="text" 
                 class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="account_first_name" 
                 id="account_first_name" 
                 autocomplete="given-name" 
                 value="<?php echo esc_attr($user->first_name); ?>" />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--last form-row">
          <label for="account_last_name" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('Last name', 'woocommerce'); ?>&nbsp;
            <span class="required text-danger">*</span>
          </label>
          <input type="text" 
                 class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="account_last_name" 
                 id="account_last_name" 
                 autocomplete="family-name" 
                 value="<?php echo esc_attr($user->last_name); ?>" />
        </p>
      </div>

      <div class="mt-6">
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="account_display_name" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('Display name', 'woocommerce'); ?>&nbsp;
            <span class="required text-danger">*</span>
          </label>
          <input type="text" 
                 class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="account_display_name" 
                 id="account_display_name" 
                 value="<?php echo esc_attr($user->display_name); ?>" />
          <span class="text-sm text-gray-500 mt-1 block">
            <em><?php esc_html_e(
              'This will be how your name will be displayed in the account section and in reviews',
              'woocommerce',
            ); ?></em>
          </span>
        </p>
      </div>

      <div class="mt-6">
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="account_email" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;
            <span class="required text-danger">*</span>
          </label>
          <input type="email" 
                 class="woocommerce-Input woocommerce-Input--email input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="account_email" 
                 id="account_email" 
                 autocomplete="email" 
                 value="<?php echo esc_attr($user->user_email); ?>" />
        </p>
      </div>
    </div>

    <!-- Password Change Card -->
    <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
      <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
        <div class="w-10 h-10 bg-gradient-to-br from-warning to-warning rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-white text-xl">lock</span>
        </div>
        <h3 class="text-xl font-bold text-gray-900">Password change</h3>
      </div>

      <p class="text-sm text-gray-600 mb-6">
        <?php esc_html_e(
          'Leave blank to keep your current password',
          'nera-competitions-standard',
        ); ?>
      </p>

      <div class="space-y-6">
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="password_current" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('Current password (leave blank to leave unchanged)', 'woocommerce'); ?>
          </label>
          <input type="password" 
                 class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="password_current" 
                 id="password_current" 
                 autocomplete="off" />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="password_1" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('New password (leave blank to leave unchanged)', 'woocommerce'); ?>
          </label>
          <input type="password" 
                 class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="password_1" 
                 id="password_1" 
                 autocomplete="off" />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="password_2" class="block text-sm font-semibold text-gray-700 mb-2">
            <?php esc_html_e('Confirm new password', 'woocommerce'); ?>
          </label>
          <input type="password" 
                 class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all" 
                 name="password_2" 
                 id="password_2" 
                 autocomplete="off" />
        </p>
      </div>
    </div>

    <?php do_action('woocommerce_edit_account_form'); ?>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3">
          <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
          <button type="submit" 
                  class="woocommerce-Button button !inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-primary to-primary text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-sm hover:shadow-md w-full sm:w-auto" 
                  name="save_account_details" 
                  value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>">
            <span class="material-symbols-outlined text-xl">save</span>
            <?php esc_html_e('Save changes', 'woocommerce'); ?>
          </button>
          <input type="hidden" name="action" value="save_account_details" />
      
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" 
         class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-surface border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-primary hover:text-primary transition-all w-full sm:w-auto">
        <span class="material-symbols-outlined text-xl">cancel</span>
        <?php esc_html_e('Cancel', 'nera-competitions-standard'); ?>
      </a>
    </div>

    <?php do_action('woocommerce_edit_account_form_end'); ?>
  </form>

  <!-- Deactivate account (separate POST; does not submit with Save changes) -->
  <div class="mt-10 pt-8 border-t border-danger-bg">
    <div class="bg-danger-bg/80 rounded-2xl border border-danger-border/80 shadow-sm p-6">
      <div class="flex items-start gap-3 mb-4">
        <div class="w-10 h-10 shrink-0 bg-gradient-to-br from-danger to-danger-text rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-white text-xl">warning</span>
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-lg font-bold text-gray-900">
            <?php esc_html_e('Deactivate account', 'nera-competitions-standard'); ?>
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            <?php esc_html_e(
              'Permanently delete your account and personal data associated with it. This cannot be undone.',
              'nera-competitions-standard',
            ); ?>
          </p>
        </div>
      </div>

      <form
        id="nera-deactivate-account-form"
        class="nera-deactivate-account-form"
        method="post"
        action="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>"
      >
        <?php wp_nonce_field('nera_deactivate_account', 'nera-deactivate-account-nonce'); ?>
        <input type="hidden" name="action" value="nera_deactivate_account" />
        <input type="hidden" name="nera_deactivate_user_id" value="<?php echo esc_attr(get_current_user_id()); ?>" />

        <button
          type="button"
          id="nera-deactivate-account-open"
          class="inline-flex items-center justify-center gap-2 px-6 py-3.5 w-full sm:w-auto rounded-xl font-semibold text-white bg-gradient-to-r from-danger to-danger-text hover:from-danger-text hover:to-danger-text border border-danger-text/30 shadow-sm hover:shadow-md transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-danger focus-visible:ring-offset-2"
        >
          <span class="material-symbols-outlined text-xl">person_off</span>
          <?php esc_html_e('Deactivate Account', 'nera-competitions-standard'); ?>
        </button>
      </form>

      <script>
        (function () {
          var openBtn = document.getElementById('nera-deactivate-account-open');
          var form = document.getElementById('nera-deactivate-account-form');
          if (!openBtn || !form) return;

          openBtn.addEventListener('click', function () {
            // Shared custom HTML dialog store (native <dialog>/confirm replaced for WebView reliability)
            if (window.Alpine && Alpine.store('dialog')) {
              Alpine.store('dialog')
                .confirm({
                  title: '<?php echo esc_js(__('Delete your account?', 'nera-competitions-standard')); ?>',
                  message: '<?php echo esc_js(
                    __(
                      'This will permanently remove your account. If you are sure, click Yes.',
                      'nera-competitions-standard',
                    ),
                  ); ?>',
                  confirmText: '<?php echo esc_js(__('Yes', 'nera-competitions-standard')); ?>',
                  cancelText: '<?php echo esc_js(__('Cancel', 'nera-competitions-standard')); ?>',
                  variant: 'danger',
                })
                .then(function (confirmed) {
                  if (confirmed) form.submit();
                });
            } else if (window.confirm('<?php echo esc_js(__('Delete your account?', 'nera-competitions-standard')); ?>')) {
              form.submit();
            }
          });
        })();
      </script>
    </div>
  </div>

</div>

<?php do_action('woocommerce_after_edit_account_form'); ?>
