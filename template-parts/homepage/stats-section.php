<?php
/**
 * Stats Section Template Part
 *
 * Stats bar with Trustpilot and key metrics
 * Matches the reference design: Trustpilot on left, Stats on right with dividers
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

// Stats Data
$total_winners = get_field('stat_winners') ?: '150';
$total_value = get_field('stat_value') ?: '2';
$secure_entry = get_field('stat_secure') ?: '100';

// Trustpilot Data
$tp_score = get_field('tp_score') ?: '4.8';
$tp_reviews = get_field('tp_reviews') ?: '1,250';
?>

<section class="stats-section py-5 lg:py-6 bg-surface border-b border-gray-100" id="stats-section" data-aos="fade-up">
	<div class="container mx-auto px-4 lg:px-0">
		<div class="flex flex-col md:flex-row items-center justify-between gap-6 md:gap-8">

			<!-- Left: Trustpilot -->
			<div class="trustpilot-badge flex flex-col items-center md:items-start text-center md:text-left shrink-0">
				<div class="flex items-center gap-2 mb-1">
					<span class="text-xl lg:text-2xl font-bold italic text-text-primary tracking-tight">Trustpilot</span>
					<div class="flex gap-0.5">
						<?php for ($i = 0; $i < 5; $i++): ?>
							<svg class="w-5 h-5 lg:w-6 lg:h-6 text-success fill-current" viewBox="0 0 24 24">
								<path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
							</svg>
						<?php endfor; ?>
					</div>
				</div>
				<p class="text-xs lg:text-sm text-text-secondary">
					Excellent <span class="font-semibold text-text-primary"><?php echo esc_html(
       $tp_score,
     ); ?></span> out of 5 based
					on <span class="font-semibold text-text-primary"><?php echo esc_html($tp_reviews); ?></span> reviews
				</p>
			</div>

			<!-- Right: Stats with Dividers -->
			<div class="stats-grid flex items-stretch justify-center md:justify-end divide-x divide-gray-200">

				<!-- Stat 1: Prizes Won -->
				<div class="stat-item px-6 lg:px-10 text-center">
					<div class="stat-value text-2xl lg:text-3xl font-bold mb-0.5">
						<span class="count-up" data-target="<?php echo esc_attr(
        $total_winners,
      ); ?>" data-suffix="+">0</span><span
							class="stat-suffix">+</span>
					</div>
					<div class="stat-label text-[10px] lg:text-xs font-semibold text-gray-400 uppercase tracking-[0.15em]">
						<?php _e('Prizes Won', 'nera-competitions'); ?>
					</div>
				</div>

				<!-- Stat 2: Total Value -->
				<div class="stat-item px-6 lg:px-10 text-center">
					<div class="stat-value text-2xl lg:text-3xl font-bold mb-0.5">
						<span class="stat-prefix">£</span><span class="count-up" data-target="<?php echo esc_attr(
        $total_value,
      ); ?>"
							data-prefix="£" data-suffix="M+">0</span><span class="stat-suffix">M+</span>
					</div>
					<div class="stat-label text-[10px] lg:text-xs font-semibold text-gray-400 uppercase tracking-[0.15em]">
						<?php _e('Total Value', 'nera-competitions'); ?>
					</div>
				</div>

				<!-- Stat 3: Secure Entry -->
				<div class="stat-item px-6 lg:px-10 text-center">
					<div class="stat-value text-2xl lg:text-3xl font-bold mb-0.5">
						<span class="count-up" data-target="<?php echo esc_attr(
        $secure_entry,
      ); ?>" data-suffix="%">0</span><span
							class="stat-suffix">%</span>
					</div>
					<div class="stat-label text-[10px] lg:text-xs font-semibold text-gray-400 uppercase tracking-[0.15em]">
						<?php _e('Secure Entry', 'nera-competitions'); ?>
					</div>
				</div>

			</div>

		</div>
	</div>
</section>

<script>
	(function () {
		// CountUp Animation
		const countUpElements = document.querySelectorAll('.count-up');

		const animateCountUp = (el) => {
			const target = parseFloat(el.dataset.target);
			const duration = 2000; // 2 seconds
			const startTime = performance.now();
			const startValue = 0;

			const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

			const updateCount = (currentTime) => {
				const elapsed = currentTime - startTime;
				const progress = Math.min(elapsed / duration, 1);
				const easedProgress = easeOutQuart(progress);
				const currentValue = startValue + (target - startValue) * easedProgress;

				// Handle decimals for values like 2 (for £2M+)
				if (target % 1 !== 0) {
					el.textContent = currentValue.toFixed(1);
				} else {
					el.textContent = Math.floor(currentValue);
				}

				if (progress < 1) {
					requestAnimationFrame(updateCount);
				} else {
					el.textContent = target;
				}
			};

			requestAnimationFrame(updateCount);
		};

		// Intersection Observer for triggering animation when in view
		const observerOptions = {
			root: null,
			rootMargin: '0px',
			threshold: 0.3
		};

		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					const countElements = entry.target.querySelectorAll('.count-up');
					countElements.forEach(el => {
						if (!el.classList.contains('counted')) {
							el.classList.add('counted');
							animateCountUp(el);
						}
					});
				}
			});
		}, observerOptions);

		const statsSection = document.getElementById('stats-section');
		if (statsSection) {
			observer.observe(statsSection);
		}
	})();
</script>