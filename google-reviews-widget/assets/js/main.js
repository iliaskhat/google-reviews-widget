document.addEventListener('DOMContentLoaded', function () {
	const badge = document.getElementById('google-reviews-badge');
	const sidebar = document.getElementById('google-reviews-sidebar');
	const closeButton = document.querySelector('.google-reviews-sidebar__close');

	if (badge && sidebar && closeButton) {
		badge.addEventListener('click', function () {
			sidebar.classList.add('open');

			// Trigger animaties met correcte volgorde
			document.querySelectorAll('.google-review').forEach((el, index) => {
				el.style.animation = 'none'; // reset
				el.offsetHeight; // force reflow
				el.style.animation = `slideIn 0.6s ease ${index * 0.5}s forwards`;
			});
		});

		closeButton.addEventListener('click', function () {
			sidebar.classList.remove('open');
			document.querySelectorAll('.google-review').forEach((el) => {
				el.style.animation = 'none';
			});
		});
		//scroll animation for badge
		window.addEventListener('scroll', function () {
			const scrollTop = window.scrollY;
			const windowHeight = window.innerHeight;
			const documentHeight = document.documentElement.scrollHeight;

			if (scrollTop + windowHeight >= documentHeight - 100) {
				badge.style.transform = 'translateY(100px)';
				badge.style.opacity = '0.3';
			} else {
				badge.style.transform = 'translateY(0)';
				badge.style.opacity = '1';
			}
		});
		//close sidebar when clicking outside of it on mobile
		document.addEventListener('click', function (e) {
			if (
				window.innerWidth < 768 &&
				sidebar.classList.contains('open') &&
				!sidebar.contains(e.target) &&
				!badge.contains(e.target)
			) {
				sidebar.classList.remove('open');
			}
		});

	}

	// Lees meer functionaliteit
	document.querySelectorAll('.read-more').forEach(function (link) {
		link.addEventListener('click', function (e) {
			e.preventDefault();
			const review = this.closest('.google-review__body');
			review.querySelector('.short-text').style.display = 'none';
			review.querySelector('.full-text').style.display = 'inline';
			this.style.display = 'none';
		});
	});
});
