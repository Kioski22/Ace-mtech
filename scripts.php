	<!-- Messenger Floating Button and Popup UI for Users -->
	<link rel="stylesheet" href="admin/messages/messenger-public.css">
	<div id="messenger-fab" title="Messages" style="position:fixed;bottom:20px;right:20px;z-index:2147483647;display:flex;align-items:center;justify-content:center;width:60px;height:60px;border-radius:50%;background:#16a34a;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,0.12);">
		<!-- Messenger Icon SVG -->
		<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
		<span id="fab-badge" data-count="0" style="position:absolute;top:-6px;right:-6px;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:#ef4444;color:#fff;font-size:11px;display:none;align-items:center;justify-content:center;font-weight:700;line-height:20px;box-shadow:0 4px 12px rgba(0,0,0,0.12)"></span>
	</div>
	<!-- Toast container for user notifications (top-right) -->
	<div id="toast-container" style="position:fixed;right:20px;top:20px;z-index:2147483650;display:flex;flex-direction:column;gap:10px;pointer-events:none"></div>
	<div id="messenger-popup">
		<div id="messenger-header">
			<span>Send an Inquiry</span>
			<button id="messenger-close">&times;</button>
		</div>
		<div id="messages-view" style="flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;"></div>
		<form id="message-form" style="width:100%;display:flex;gap:8px;padding:12px 12px 16px 12px;box-sizing:border-box;">
			<input id="message-input" type="text" placeholder="Type your message..." autocomplete="off" style="flex:1;border:1.5px solid #e5e7eb;border-radius:8px;padding:10px 12px;font-size:15px;" />
			<button id="send-message" type="submit" style="background:#22c55e;color:#fff;border:none;border-radius:8px;padding:8px 24px;font-weight:600;font-size:16px;">Send</button>
		</form>
	</div>
	<script type="module" src="admin/messages/messenger.js"></script>
	<script>
		(function () {
			var html = document.documentElement;
			var THEME_KEY = 'acemtech_theme';

			function getPreferredTheme() {
				var savedTheme = localStorage.getItem(THEME_KEY);
				if (savedTheme === 'light' || savedTheme === 'dark') {
					return savedTheme;
				}

				var legacyTheme = localStorage.getItem('theme');
				if (legacyTheme === 'light' || legacyTheme === 'dark') {
					localStorage.setItem(THEME_KEY, legacyTheme);
					return legacyTheme;
				}

				if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
					return 'dark';
				}

				return 'light';
			}

			function applyTheme(theme) {
				html.setAttribute('data-theme', theme);
			}

			applyTheme(getPreferredTheme());

			var themeToggle = document.getElementById('themeToggle');
			if (themeToggle) {
				var sunIcon = themeToggle.querySelector('.sun');
				var moonIcon = themeToggle.querySelector('.moon');

				function updateThemeIcon(theme) {
					if (!sunIcon || !moonIcon) {
						return;
					}

					if (theme === 'dark') {
						sunIcon.style.display = 'none';
						moonIcon.style.display = 'block';
					} else {
						sunIcon.style.display = 'block';
						moonIcon.style.display = 'none';
					}
				}

				updateThemeIcon(html.getAttribute('data-theme') || 'light');

				themeToggle.addEventListener('click', function () {
					var theme = html.getAttribute('data-theme');
					var newTheme = theme === 'light' ? 'dark' : 'light';
					applyTheme(newTheme);
					localStorage.setItem(THEME_KEY, newTheme);
					updateThemeIcon(newTheme);
				});
			}

			// Menu toggle
			var menuBtn = document.getElementById('menuBtn');
			var navlinks = document.getElementById('navlinks');
			var year = document.getElementById('year');
			if (year) {
				year.textContent = String(new Date().getFullYear());
			}

			function setOpen(isOpen) {
				if (!menuBtn || !navlinks) {
					return;
				}

				menuBtn.setAttribute('aria-expanded', String(isOpen));
				navlinks.classList.toggle('open', isOpen);
			}

			if (menuBtn && navlinks) {
				menuBtn.addEventListener('click', function () {
					var isOpen = menuBtn.getAttribute('aria-expanded') === 'true';
					setOpen(!isOpen);
				});

				navlinks.addEventListener('click', function (e) {
					var target = e.target;
					if (target && target.tagName === 'A' && navlinks.classList.contains('open')) {
						setOpen(false);
					}
				});
			}

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') setOpen(false);
			});

			var form = document.getElementById('contactForm');
			if (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					var status = document.getElementById('contactStatus');
					var submitBtn = form.querySelector('button[type="submit"]');
					if (status) status.textContent = '';
					if (submitBtn) submitBtn.disabled = true;

					var formData = new FormData(form);
					fetch(form.action, {
						method: 'POST',
						body: formData,
						headers: {
							'Accept': 'application/json'
						}
					})
						.then(function (res) {
							return res.json().catch(function () {
								return { ok: false, message: 'Unexpected server response.' };
							});
						})
						.then(function (data) {
							if (data && data.ok) {
								if (status) status.textContent = 'Message sent. We will get back to you shortly.';
								form.reset();
							} else {
								if (status) status.textContent = (data && data.message) ? data.message : 'Failed to send. Please try again.';
							}
						})
						.catch(function () {
							if (status) status.textContent = 'Failed to send. Please try again.';
						})
						.finally(function () {
							if (submitBtn) submitBtn.disabled = false;
						});
				});
			}

			var galleryModal = document.getElementById('galleryModal');
			var galleryTriggers = Array.prototype.slice.call(document.querySelectorAll('.galleryTrigger'));
			if (galleryModal && galleryTriggers.length) {
				var modalImage = document.getElementById('galleryModalImage');
				var modalTitle = document.getElementById('galleryModalTitle');
				var modalCaption = document.getElementById('galleryModalCaption');
				var modalCounter = document.getElementById('galleryCounter');
				var closeModalBtn = document.getElementById('galleryModalClose');
				var prevBtn = document.getElementById('galleryPrev');
				var nextBtn = document.getElementById('galleryNext');
				var galleryStage = galleryModal.querySelector('.galleryStage');
				var galleryThumbStrip = document.getElementById('galleryThumbStrip');
				var currentImages = [];
				var currentIndex = 0;
				var touchStartX = 0;
				var touchStartY = 0;
				var touchDeltaX = 0;
				var touchDeltaY = 0;

				if (!modalImage || !modalTitle || !modalCaption || !modalCounter) {
					return;
				}

				function parseTriggerImages(trigger) {
					var payload = trigger.getAttribute('data-images') || '[]';
					try {
						var parsed = JSON.parse(payload);
						return Array.isArray(parsed) ? parsed : [];
					} catch (err) {
						return [];
					}
				}

				function renderGalleryThumbs() {
					if (!galleryThumbStrip) {
						return;
					}

					galleryThumbStrip.innerHTML = '';
					currentImages.forEach(function (image, index) {
						var thumb = document.createElement('button');
						thumb.type = 'button';
						thumb.className = 'galleryThumb';
						thumb.setAttribute('aria-label', 'View image ' + String(index + 1));
						thumb.setAttribute('data-index', String(index));

						var thumbImage = document.createElement('img');
						thumbImage.src = image.src || '';
						thumbImage.alt = image.alt || '';
						thumb.appendChild(thumbImage);

						thumb.addEventListener('click', function () {
							setGalleryItem(index);
						});

						galleryThumbStrip.appendChild(thumb);
					});
				}

				function syncActiveThumb() {
					if (!galleryThumbStrip) {
						return;
					}

					var thumbs = galleryThumbStrip.querySelectorAll('.galleryThumb');
					Array.prototype.forEach.call(thumbs, function (thumb, index) {
						var isActive = index === currentIndex;
						thumb.classList.toggle('is-active', isActive);
						thumb.setAttribute('aria-current', isActive ? 'true' : 'false');
						if (isActive && typeof thumb.scrollIntoView === 'function') {
							thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
						}
					});
				}

				function setGalleryItem(index) {
					if (!currentImages.length) {
						return;
					}

					currentIndex = (index + currentImages.length) % currentImages.length;
					var image = currentImages[currentIndex];
					modalImage.src = image.src || '';
					modalImage.alt = image.alt || '';
					modalCounter.textContent = String(currentIndex + 1) + ' / ' + String(currentImages.length);
					syncActiveThumb();
				}

				function openGalleryModal(trigger) {
					currentImages = parseTriggerImages(trigger);
					if (!currentImages.length) {
						return;
					}

					modalTitle.textContent = trigger.getAttribute('data-title') || '';
					var caption = trigger.getAttribute('data-caption') || '';
					modalCaption.textContent = caption;
					modalCaption.style.display = caption ? 'block' : 'none';
					renderGalleryThumbs();
					setGalleryItem(0);
					galleryModal.hidden = false;
					galleryModal.setAttribute('aria-hidden', 'false');
					document.body.classList.add('gallery-modal-open');
					if (prevBtn) prevBtn.style.display = currentImages.length > 1 ? 'flex' : 'none';
					if (nextBtn) nextBtn.style.display = currentImages.length > 1 ? 'flex' : 'none';
					if (galleryThumbStrip) galleryThumbStrip.style.display = currentImages.length > 1 ? 'flex' : 'none';
				}

				function closeGalleryModal() {
					galleryModal.hidden = true;
					galleryModal.setAttribute('aria-hidden', 'true');
					document.body.classList.remove('gallery-modal-open');
					modalImage.src = '';
					currentImages = [];
				}

				galleryTriggers.forEach(function (trigger) {
					trigger.addEventListener('click', function () {
						openGalleryModal(trigger);
					});
				});

				if (closeModalBtn) {
					closeModalBtn.addEventListener('click', closeGalleryModal);
				}
				if (prevBtn) {
					prevBtn.addEventListener('click', function () {
						setGalleryItem(currentIndex - 1);
					});
				}
				if (nextBtn) {
					nextBtn.addEventListener('click', function () {
						setGalleryItem(currentIndex + 1);
					});
				}

				galleryModal.addEventListener('click', function (e) {
					if (e.target && e.target.hasAttribute('data-gallery-close')) {
						closeGalleryModal();
					}
				});

				if (galleryStage) {
					galleryStage.addEventListener('touchstart', function (e) {
						if (galleryModal.hidden) {
							return;
						}
						if (!e.touches || !e.touches.length) {
							return;
						}
						touchStartX = e.touches[0].clientX;
						touchStartY = e.touches[0].clientY;
						touchDeltaX = 0;
						touchDeltaY = 0;
					}, { passive: true });

					galleryStage.addEventListener('touchmove', function (e) {
						if (galleryModal.hidden) {
							return;
						}
						if (!e.touches || !e.touches.length) {
							return;
						}
						touchDeltaX = e.touches[0].clientX - touchStartX;
						touchDeltaY = e.touches[0].clientY - touchStartY;
					}, { passive: true });

					galleryStage.addEventListener('touchend', function () {
						if (galleryModal.hidden || currentImages.length <= 1) {
							return;
						}
						if (Math.abs(touchDeltaX) < 50 || Math.abs(touchDeltaX) <= Math.abs(touchDeltaY)) {
							return;
						}

						if (touchDeltaX < 0) {
							setGalleryItem(currentIndex + 1);
						} else {
							setGalleryItem(currentIndex - 1);
						}
					});
				}

				document.addEventListener('keydown', function (e) {
					if (galleryModal.hidden) {
						return;
					}

					if (e.key === 'Escape') {
						e.preventDefault();
						closeGalleryModal();
					} else if (e.key === 'ArrowLeft') {
						e.preventDefault();
						setGalleryItem(currentIndex - 1);
					} else if (e.key === 'ArrowRight') {
						e.preventDefault();
						setGalleryItem(currentIndex + 1);
					}
				});
			}
		})();
	</script>
	<!-- Messenger Chat plugin Code -->
<div id="fb-root"></div>
<div id="fb-customer-chat" class="fb-customerchat"></div>
