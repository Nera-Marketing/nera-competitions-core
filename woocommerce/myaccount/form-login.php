<?php
/**
 * Login / Register Form
 *
 * Tabbed login and registration form for the My Account page.
 * Overrides the WooCommerce default myaccount/form-login.php template.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if (!defined('ABSPATH')) {
  exit();
}

$active_tab = isset($_GET['action']) && $_GET['action'] === 'register' ? 'register' : 'login';

?>

<!-- Form Container -->
<div class="max-w-2xl mx-auto px-4 pt-8 pb-8 lg:pt-10 lg:pb-10">

		<?php do_action('woocommerce_before_customer_login_form'); ?>

		<!-- Card -->
		<div class="bg-surface rounded-2xl shadow-md overflow-hidden">

			<!-- Tab Bar -->
			<div class="flex border-b border-gray-200">
				<button
					type="button"
					id="tab-login"
					data-tab="login"
					class="flex-1 py-4 text-sm font-semibold transition-all <?php echo $active_tab === 'login'
       ? 'bg-primary text-white'
       : 'bg-gray-50 text-text-secondary hover:bg-gray-100'; ?>"
					aria-selected="<?php echo $active_tab === 'login' ? 'true' : 'false'; ?>"
					role="tab">
					<?php esc_html_e('Login', 'woocommerce'); ?>
				</button>
				<button
					type="button"
					id="tab-register"
					data-tab="register"
					class="flex-1 py-4 text-sm font-semibold transition-all <?php echo $active_tab === 'register'
       ? 'bg-primary text-white'
       : 'bg-gray-50 text-text-secondary hover:bg-gray-100'; ?>"
					aria-selected="<?php echo $active_tab === 'register' ? 'true' : 'false'; ?>"
					role="tab">
					<?php esc_html_e('Register', 'woocommerce'); ?>
				</button>
			</div>

			<!-- ============================================================
			     LOGIN PANEL
			     ============================================================ -->
			<div id="panel-login" class="p-6 lg:p-8 <?php echo $active_tab === 'register' ? 'hidden' : ''; ?>">

				<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

					<?php do_action('woocommerce_login_form_start'); ?>

					<?php if (!empty($_GET['redirect_to'])): ?>
						<input type="hidden" name="redirect" value="<?php echo esc_url(wp_sanitize_redirect(wp_unslash($_GET['redirect_to']))); ?>" />
					<?php endif; ?>

					<!-- Username / Email -->
					<div class="mb-5">
						<label for="username" class="block text-sm font-semibold text-text-primary mb-2">
							<?php esc_html_e('Username or email address', 'woocommerce'); ?>&nbsp;
							<span class="text-danger" aria-hidden="true">*</span>
							<span class="sr-only"><?php esc_html_e('Required', 'woocommerce'); ?></span>
						</label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-text-primary placeholder:text-gray-400"
							name="username"
							id="username"
							autocomplete="username"
							value="<?php echo !empty($_POST['username']) && is_string($_POST['username'])
         ? esc_attr(wp_unslash($_POST['username']))
         : ''; ?>"
							placeholder="<?php esc_attr_e('Enter your username or email', 'woocommerce'); ?>"
							required
							aria-required="true" /><?php
// @codingStandardsIgnoreLine
?>
					</div>

					<!-- Password -->
					<div class="mb-5">
						<label for="password" class="block text-sm font-semibold text-text-primary mb-2">
							<?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;
							<span class="text-danger" aria-hidden="true">*</span>
							<span class="sr-only"><?php esc_html_e('Required', 'woocommerce'); ?></span>
						</label>
						<input
							class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-text-primary placeholder:text-gray-400"
							type="password"
							name="password"
							id="password"
							autocomplete="current-password"
							placeholder="<?php esc_attr_e('Enter your password', 'woocommerce'); ?>"
							required
							aria-required="true" />
					</div>

					<?php do_action('woocommerce_login_form'); ?>

					<!-- Remember Me & Submit -->
					<div class="space-y-4 mb-5">
						<label class="flex items-center cursor-pointer group">
							<input
								class="w-4 h-4 text-primary bg-surface border-2 border-gray-300 rounded focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer"
								name="rememberme"
								type="checkbox"
								id="rememberme"
								value="forever" />
							<span class="ml-2 text-sm text-text-secondary group-hover:text-text-primary transition-colors">
								<?php esc_html_e('Remember me', 'woocommerce'); ?>
							</span>
						</label>

						<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>

						<button
							type="submit"
							class="w-full inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-primary to-primary text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-sm hover:shadow-md"
							name="login"
							value="<?php esc_attr_e('Log in', 'woocommerce'); ?>">
							<span class="material-symbols-outlined text-xl">login</span>
							<?php esc_html_e('Log in', 'woocommerce'); ?>
						</button>
					</div>

					<!-- Lost Password -->
					<div class="text-center">
						<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"
							class="text-sm text-primary hover:text-primary/80 font-semibold transition-colors inline-flex items-center gap-1">
							<span class="material-symbols-outlined text-base">lock_reset</span>
							<?php esc_html_e('Lost your password?', 'woocommerce'); ?>
						</a>
					</div>

					<?php do_action('woocommerce_login_form_end'); ?>

				</form>

			</div><!-- /panel-login -->

			<!-- ============================================================
			     REGISTER PANEL
			     ============================================================ -->
			<div id="panel-register" class="p-6 lg:p-8 <?php echo $active_tab === 'login' ? 'hidden' : ''; ?>">

				<form
					id="nera-register-form"
					method="post"
					class="woocommerce-form woocommerce-form-register register !border-none !p-0"
					novalidate
					<?php do_action('woocommerce_register_form_tag'); ?>>

					<?php do_action('woocommerce_register_form_start'); ?>

					<?php if (!empty($_GET['redirect_to'])): ?>
						<input type="hidden" name="redirect" value="<?php echo esc_url(wp_sanitize_redirect(wp_unslash($_GET['redirect_to']))); ?>" />
					<?php endif; ?>

					<!-- Full Name -->
					<div class="mb-5">
						<label for="reg_full_name" class="block text-sm font-semibold text-text-primary mb-2">
							<?php esc_html_e('Full Name', 'woocommerce'); ?>&nbsp;
							<span class="text-danger" aria-hidden="true">*</span>
							<span class="sr-only"><?php esc_html_e('Required', 'woocommerce'); ?></span>
						</label>
						<input
							type="text"
							class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-text-primary placeholder:text-gray-400"
							name="billing_first_name"
							id="reg_full_name"
							autocomplete="name"
							value="<?php echo !empty($_POST['billing_first_name'])
         ? esc_attr(wp_unslash($_POST['billing_first_name']))
         : ''; ?>"
							placeholder="<?php esc_attr_e('Enter your full name', 'woocommerce'); ?>"
							required
							aria-required="true" />
					</div>

					<!-- Email -->
					<div class="mb-5">
						<label for="reg_email" class="block text-sm font-semibold text-text-primary mb-2">
							<?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;
							<span class="text-danger" aria-hidden="true">*</span>
							<span class="sr-only"><?php esc_html_e('Required', 'woocommerce'); ?></span>
						</label>
						<input
							type="email"
							class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-text-primary placeholder:text-gray-400"
							name="email"
							id="reg_email"
							autocomplete="email"
							value="<?php echo !empty($_POST['email']) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>"
							placeholder="<?php esc_attr_e('Enter your email address', 'woocommerce'); ?>"
							required
							aria-required="true" /><?php
// @codingStandardsIgnoreLine
?>
					</div>

					<!-- Password -->
					<div class="mb-2">
						<label for="reg_password" class="block text-sm font-semibold text-text-primary mb-2">
							<?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;
							<span class="text-danger" aria-hidden="true">*</span>
							<span class="sr-only"><?php esc_html_e('Required', 'woocommerce'); ?></span>
						</label>
						<div class="relative">
							<input
								type="password"
								class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-text-primary placeholder:text-gray-400"
								name="password"
								id="reg_password"
								autocomplete="new-password"
								placeholder="<?php esc_attr_e('Create a password', 'woocommerce'); ?>"
								required
								aria-required="true" />
							<button
								type="button"
								class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-text-primary transition-colors"
								aria-label="<?php esc_attr_e('Toggle password visibility', 'woocommerce'); ?>"
								data-toggle-password="reg_password">
								<!-- <span class="material-symbols-outlined text-xl" data-eye-icon="reg_password">visibility</span> -->
							</button>
						</div>
					</div>

					<!-- Password Strength Bar -->
					<div class="mb-5">
						<div class="flex gap-1 mb-1" id="strength-bars">
							<div class="h-1 flex-1 rounded-full bg-gray-200 transition-all" data-bar="1"></div>
							<div class="h-1 flex-1 rounded-full bg-gray-200 transition-all" data-bar="2"></div>
							<div class="h-1 flex-1 rounded-full bg-gray-200 transition-all" data-bar="3"></div>
							<div class="h-1 flex-1 rounded-full bg-gray-200 transition-all" data-bar="4"></div>
						</div>
						<p class="text-xs text-text-secondary" id="strength-label">
							<?php esc_html_e('Password strength: Enter a password', 'woocommerce'); ?>
						</p>
					</div>

					<!-- Confirm Password -->
					<div class="mb-5">
						<label for="reg_password2" class="block text-sm font-semibold text-text-primary mb-2">
							<?php esc_html_e('Confirm Password', 'woocommerce'); ?>&nbsp;
							<span class="text-danger" aria-hidden="true">*</span>
							<span class="sr-only"><?php esc_html_e('Required', 'woocommerce'); ?></span>
						</label>
						<div class="relative">
							<input
								type="password"
								class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-text-primary placeholder:text-gray-400"
								name="password2"
								id="reg_password2"
								autocomplete="new-password"
								placeholder="<?php esc_attr_e('Confirm your password', 'woocommerce'); ?>"
								required
								aria-required="true" />
							<button
								type="button"
								class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-text-primary transition-colors"
								aria-label="<?php esc_attr_e('Toggle confirm password visibility', 'woocommerce'); ?>"
								data-toggle-password="reg_password2">
								<!-- <span class="material-symbols-outlined text-xl" data-eye-icon="reg_password2">visibility</span> -->
							</button>
						</div>
						<p class="text-xs mt-1 hidden" id="password-match-msg"></p>
					</div>

					<!-- Checkboxes -->
					<div class="space-y-3 mb-6">
						<!-- Terms & Conditions -->
						<label class="flex items-start gap-3 cursor-pointer group">
							<input
								type="checkbox"
								name="terms_conditions"
								id="reg_terms"
								class="mt-0.5 w-4 h-4 text-primary bg-surface border-2 border-gray-300 rounded focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer flex-shrink-0"
								required />
							<span class="text-sm text-text-secondary group-hover:text-text-primary transition-colors leading-snug">
								<?php
        $terms_page_id = wc_get_page_id('terms');
        $terms_url = $terms_page_id > 0 ? get_permalink($terms_page_id) : '#';
        printf(
          /* translators: %s: terms and conditions page link */
          esc_html__('I agree to the %s', 'woocommerce'),
          '<a href="' .
            esc_url($terms_url) .
            '" target="_blank" class="text-primary hover:underline font-semibold">' .
            esc_html__('Terms &amp; Conditions', 'woocommerce') .
            '</a>',
        );
        ?>
							</span>
						</label>

						<!-- Age Confirmation -->
						<label class="flex items-start gap-3 cursor-pointer group">
							<input
								type="checkbox"
								name="age_confirm"
								id="reg_age"
								class="mt-0.5 w-4 h-4 text-primary bg-surface border-2 border-gray-300 rounded focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer flex-shrink-0"
								required />
							<span class="text-sm text-text-secondary group-hover:text-text-primary transition-colors leading-snug">
								<?php esc_html_e('I am over the age of 18', 'woocommerce'); ?>
							</span>
						</label>
					</div>

					<?php do_action('woocommerce_register_form'); ?>

					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>

					<!-- Submit -->
					<button
						type="submit"
						class="w-full inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-xl hover:opacity-90 transition-all shadow-sm hover:shadow-md tracking-wide uppercase"
						name="register"
						value="<?php esc_attr_e('Register', 'woocommerce'); ?>">
						<span class="material-symbols-outlined text-xl">person_add</span>
						<?php esc_html_e('Register', 'woocommerce'); ?>
					</button>

					<?php do_action('woocommerce_register_form_end'); ?>

				</form>

			</div><!-- /panel-register -->

		</div><!-- /card -->

	</div><!-- /container -->

<?php do_action('woocommerce_after_customer_login_form'); ?>

<script>
(function () {
  'use strict';

  // ── Tab switching ──────────────────────────────────────────────────────────
  var tabLogin    = document.getElementById('tab-login');
  var tabRegister = document.getElementById('tab-register');
  var panelLogin    = document.getElementById('panel-login');
  var panelRegister = document.getElementById('panel-register');

  function activateTab(tab) {
    var isRegister = tab === 'register';

    // Toggle panels
    panelLogin.classList.toggle('hidden', isRegister);
    panelRegister.classList.toggle('hidden', !isRegister);

    // Active tab styles
    var activeClasses   = ['bg-primary', 'text-white'];
    var inactiveClasses = ['bg-gray-50', 'text-text-secondary', 'hover:bg-gray-100'];

    [tabLogin, tabRegister].forEach(function (btn) {
      var isActive = btn.dataset.tab === tab;
      activeClasses.forEach(function (c)   { btn.classList.toggle(c, isActive); });
      inactiveClasses.forEach(function (c) { btn.classList.toggle(c, !isActive); });
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    // Update URL without reload
    var url = new URL(window.location.href);
    if (isRegister) {
      url.searchParams.set('action', 'register');
    } else {
      url.searchParams.delete('action');
    }
    history.pushState(null, '', url.toString());

    // Update heading and description
    var heading = document.querySelector('h1');
    var desc    = heading ? heading.nextElementSibling : null;
    if (heading) {
      heading.textContent = isRegister
        ? '<?php echo esc_js(__('Create Account', 'woocommerce')); ?>'
        : '<?php echo esc_js(__('Welcome Back', 'woocommerce')); ?>';
    }
    if (desc) {
      desc.textContent = isRegister
        ? '<?php echo esc_js(
          __('Join us and start entering competitions today', 'woocommerce'),
        ); ?>'
        : '<?php echo esc_js(__('Log in to your account to continue', 'woocommerce')); ?>';
    }
  }

  tabLogin.addEventListener('click', function ()    { activateTab('login'); });
  tabRegister.addEventListener('click', function () { activateTab('register'); });

  // ── Password show / hide ───────────────────────────────────────────────────
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = btn.dataset.togglePassword;
      var input    = document.getElementById(targetId);
      var icon     = btn.querySelector('[data-eye-icon]');
      if (!input) return;
      var isHidden = input.type === 'password';
      input.type   = isHidden ? 'text' : 'password';
      if (icon) icon.textContent = isHidden ? 'visibility_off' : 'visibility';
    });
  });

  // ── Password strength meter ────────────────────────────────────────────────
  var regPassword  = document.getElementById('reg_password');
  var strengthBars = document.querySelectorAll('[data-bar]');
  var strengthLabel = document.getElementById('strength-label');

  var strengthConfig = [
    { label: '<?php echo esc_js(
      __('Password strength: Too short', 'woocommerce'),
    ); ?>',   color: 'bg-danger',    bars: 1 },
    { label: '<?php echo esc_js(
      __('Password strength: Weak', 'woocommerce'),
    ); ?>',        color: 'bg-warning', bars: 1 },
    { label: '<?php echo esc_js(
      __('Password strength: Fair', 'woocommerce'),
    ); ?>',        color: 'bg-warning', bars: 2 },
    { label: '<?php echo esc_js(
      __('Password strength: Good', 'woocommerce'),
    ); ?>',        color: 'bg-primary',   bars: 3 },
    { label: '<?php echo esc_js(
      __('Password strength: Strong', 'woocommerce'),
    ); ?>',      color: 'bg-success',  bars: 4 },
  ];

  function getStrengthScore(pw) {
    if (pw.length === 0) return -1;
    if (pw.length < 8)   return 0;
    var score = 1;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return score; // 1–4
  }

  function updateStrength(pw) {
    var score = getStrengthScore(pw);

    // Reset all bars
    strengthBars.forEach(function (bar) {
      bar.className = 'h-1 flex-1 rounded-full bg-gray-200 transition-all';
    });

    if (score === -1) {
      strengthLabel.textContent = '<?php echo esc_js(
        __('Password strength: Enter a password', 'woocommerce'),
      ); ?>';
      return;
    }

    var cfg = strengthConfig[score];
    strengthLabel.textContent = cfg.label;
    for (var i = 0; i < cfg.bars; i++) {
      strengthBars[i].className = 'h-1 flex-1 rounded-full transition-all ' + cfg.color;
    }
  }

  if (regPassword) {
    regPassword.addEventListener('input', function () {
      updateStrength(this.value);
      checkPasswordMatch();
    });
  }

  // ── Confirm password match ─────────────────────────────────────────────────
  var regPassword2 = document.getElementById('reg_password2');
  var matchMsg     = document.getElementById('password-match-msg');

  function checkPasswordMatch() {
    if (!regPassword2 || !regPassword2.value) {
      matchMsg.classList.add('hidden');
      return;
    }
    var match = regPassword.value === regPassword2.value;
    matchMsg.classList.remove('hidden', 'text-success', 'text-danger');
    matchMsg.classList.add(match ? 'text-success' : 'text-danger');
    matchMsg.textContent = match
      ? '<?php echo esc_js(__('Passwords match', 'woocommerce')); ?>'
      : '<?php echo esc_js(__('Passwords do not match', 'woocommerce')); ?>';
  }

  if (regPassword2) {
    regPassword2.addEventListener('input', checkPasswordMatch);
  }

  // ── Form submit guard ──────────────────────────────────────────────────────
  var registerForm = document.getElementById('nera-register-form');
  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      var fullName = document.getElementById('reg_full_name');
      var email    = document.getElementById('reg_email');
      var terms    = document.getElementById('reg_terms');
      var age      = document.getElementById('reg_age');
      var errors   = [];

      if (fullName && !fullName.value.trim()) {
        errors.push('<?php echo esc_js(__('Please enter your full name.', 'woocommerce')); ?>');
      }
      if (email && !email.value.trim()) {
        errors.push('<?php echo esc_js(__('Please enter your email address.', 'woocommerce')); ?>');
      }
      if (regPassword && !regPassword.value) {
        errors.push('<?php echo esc_js(__('Please enter a password.', 'woocommerce')); ?>');
      }
      if (regPassword && regPassword2 && regPassword.value !== regPassword2.value) {
        errors.push('<?php echo esc_js(__('Passwords do not match.', 'woocommerce')); ?>');
      }
      if (terms && !terms.checked) {
        errors.push('<?php echo esc_js(
          __('You must agree to the Terms & Conditions.', 'woocommerce'),
        ); ?>');
      }
      if (age && !age.checked) {
        errors.push('<?php echo esc_js(
          __('You must confirm you are over 18.', 'woocommerce'),
        ); ?>');
      }

      if (errors.length > 0) {
        e.preventDefault();
        // Custom HTML alert dialog (native alert() is unreliable in mobile WebViews).
        if (window.Alpine && Alpine.store('dialog')) {
          Alpine.store('dialog').alert({
            title: '<?php echo esc_js(__('Please check the form', 'woocommerce')); ?>',
            message:
              '<ul class="list-disc pl-5 space-y-1 text-left">' +
              errors.map(function (m) { return '<li>' + m + '</li>'; }).join('') +
              '</ul>',
          });
        } else {
          alert(errors.join('\n'));
        }
      }
    });
  }
}());
</script>
