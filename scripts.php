	<!-- Messenger Floating Button and Popup UI for Users -->
	<link rel="stylesheet" href="admin/messages/messenger.css">
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
			// Dark mode toggle
			var themeToggle = document.getElementById('themeToggle');
			var html = document.documentElement;
			var sunIcon = themeToggle.querySelector('.sun');
			var moonIcon = themeToggle.querySelector('.moon');
			
			// Check for saved theme preference or default to 'light'
			var currentTheme = localStorage.getItem('theme') || 'light';
			html.setAttribute('data-theme', currentTheme);
			updateThemeIcon(currentTheme);
			
			function updateThemeIcon(theme) {
				if (theme === 'dark') {
					sunIcon.style.display = 'none';
					moonIcon.style.display = 'block';
				} else {
					sunIcon.style.display = 'block';
					moonIcon.style.display = 'none';
				}
			}
			
			themeToggle.addEventListener('click', function() {
				var theme = html.getAttribute('data-theme');
				var newTheme = theme === 'light' ? 'dark' : 'light';
				html.setAttribute('data-theme', newTheme);
				localStorage.setItem('theme', newTheme);
				updateThemeIcon(newTheme);
			});

			// Menu toggle
			var menuBtn = document.getElementById('menuBtn');
			var navlinks = document.getElementById('navlinks');
			var year = document.getElementById('year');
			year.textContent = String(new Date().getFullYear());

			function setOpen(isOpen) {
				menuBtn.setAttribute('aria-expanded', String(isOpen));
				navlinks.classList.toggle('open', isOpen);
			}

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

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') setOpen(false);
			});

			var form = document.getElementById('contactForm');
			if (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					var name = document.getElementById('name').value.trim();
					var company = document.getElementById('company').value.trim();
					var email = document.getElementById('email').value.trim();
					var message = document.getElementById('message').value.trim();

					var subject = 'Project inquiry' + (company ? ' — ' + company : '');
					var body =
						'Name: ' + name + '\n' +
						'Company: ' + (company || '-') + '\n' +
						'Email: ' + email + '\n\n' +
						message + '\n';

					var to = 'ace_mtech@yahoo.com';
					var url =
						'mailto:' + encodeURIComponent(to) +
						'?subject=' + encodeURIComponent(subject) +
						'&body=' + encodeURIComponent(body);

					window.location.href = url;
				});
			}
		})();
	</script>
	<!-- Messenger Chat plugin Code -->
<div id="fb-root"></div>
<div id="fb-customer-chat" class="fb-customerchat"></div>
