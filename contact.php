<?php
require_once __DIR__ . "/includes/repository.php";

$siteLogoPath = get_site_logo_path();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="<?php echo htmlspecialchars($siteLogoPath); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="<?php echo htmlspecialchars($siteLogoPath); ?>" />
	<meta name="theme-color" content="#16a34a" />
	<script>
		(function () {
			var THEME_KEY = 'acemtech_theme';
			var fallbackTheme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
			try {
				var theme = localStorage.getItem(THEME_KEY);
				if (theme !== 'light' && theme !== 'dark') {
					var legacyTheme = localStorage.getItem('theme');
					if (legacyTheme === 'light' || legacyTheme === 'dark') {
						theme = legacyTheme;
						localStorage.setItem(THEME_KEY, legacyTheme);
					} else {
						theme = fallbackTheme;
					}
				}
				document.documentElement.setAttribute('data-theme', theme);
			} catch (error) {
				document.documentElement.setAttribute('data-theme', fallbackTheme);
			}
		})();
	</script>
	<title>Ace‑mtech Group | Contact</title>
	<meta name="description" content="Contact Ace‑mtech Group for construction, engineering, and HVAC services." />
	<link rel="stylesheet" href="styles.css?v=2" />
</head>

<body>
	<a class="skip" href="#content">Skip to content</a>

	<header>
		<div class="container">
			<nav class="nav" aria-label="Primary">
				<a class="brand" href="index.php" aria-label="Ace-mtech Home">
					<img src="<?php echo htmlspecialchars($siteLogoPath); ?>" alt="Ace-mtech logo" class="mark" />
					<span>
						<span class="title">Ace‑Mtech Group Inc.</span>
						<span class="tag">Architectural • Civil • Electrical • Mechanical</span>
					</span>
				</a>

				<button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="navlinks" aria-label="Menu">
					<span aria-hidden="true">☰</span>
				</button>

				<div class="navlinks" id="navlinks">
					<a href="products.php">Products</a>
					<a href="clients.php">Clients</a>
					<div class="navDropdown">
						<button class="dropdownToggle" data-dropdown="company">Company</button>
						<div class="dropdownMenu" id="company">
							<a href="work.php">Our Work</a>
							<a href="ceo.php">Meet Our CEO</a>
						</div>
					</div>
					<a href="partners.php">Partners</a>
					<a href="contact.php">Contact</a>
					<button class="themeToggle" id="themeToggle" type="button" aria-label="Toggle dark mode">
						<svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="12" r="5"/>
							<line x1="12" y1="1" x2="12" y2="3"/>
							<line x1="12" y1="21" x2="12" y2="23"/>
							<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
							<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
							<line x1="1" y1="12" x2="3" y2="12"/>
							<line x1="21" y1="12" x2="23" y2="12"/>
							<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
							<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
						</svg>
						<svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
							<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
						</svg>
					</button>
					<a class="cta" href="index.php#contact">
						<span>Request a Quote</span>
						<span aria-hidden="true">→</span>
					</a>
				</div>
			</nav>
		</div>
	</header>

	<main id="content">
		<div class="container" id="top">
			<section id="contact" class="sectionAlt" aria-label="Contact">
				<div class="sectionHead">
					<div>
						<h2>Contact</h2>
						<p class="sectionDesc">
							Have questions or want to discuss a project? Reach out to Ace‑mtech Group through the contact information below, or send us a message directly using the form. We look forward to connecting with you!
						</p>
					</div>
				</div>

				<div class="contactGrid">
					<div class="contactCard">
						<h3>Reach Ace‑mtech</h3>

						<div style="border-radius:12px; overflow:hidden; margin-bottom:16px; width:100%; height:250px;">
							<iframe
								src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7362.03508234003!2d121.06006287717733!3d14.539064585939927!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c89678cced09%3A0xc5eaa6d95c2bb27d!2sAce%20Mtech%20Group%20Incorporated!5e1!3m2!1sen!2sph!4v1773379299382!5m2!1sen!2sph"
								width="100%"
								height="100%"
								style="border:0; display:block;"
								allowfullscreen=""
								loading="lazy"
								referrerpolicy="no-referrer-when-downgrade"
							></iframe>
						</div>

						<div class="contactRow">
							<div class="icon" aria-hidden="true" style="width:36px;height:36px;border-radius:12px">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5.11 3h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.57 2.61a2 2 0 0 1-.45 2.11L9.09 10.91a16 16 0 0 0 4 4l1.47-1.14a2 2 0 0 1 2.11-.45c.84.25 1.71.45 2.61.57A2 2 0 0 1 22 16.92z" />
								</svg>
							</div>
							<div>
								<b>Phone</b>
								<span><a href="tel:+0000000000">+00 000 0000</a> (replace)</span>
							</div>
						</div>

						<div class="contactRow">
							<div class="icon" aria-hidden="true" style="width:36px;height:36px;border-radius:12px">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M4 4h16v16H4z" />
									<path d="m4 4l8 8l8-8" />
								</svg>
							</div>
							<div>
								<b>Email</b>
								<span><a href="mailto:ace_mtech@yahoo.com">ace_mtech@yahoo.com</a></span>
							</div>
						</div>

						<div class="contactRow">
							<div class="icon" aria-hidden="true" style="width:36px;height:36px;border-radius:12px">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z" />
									<path d="M12 10a2.5 2.5 0 1 0 0 .01" />
								</svg>
							</div>
							<div>
								<b>Location</b>
								<span>BLK 97 LOT 1 Fortune St.<br/>Brgy. Rizal, Taguig City</span>
							</div>
						</div>

						<div class="contactRow">
							<div class="icon" aria-hidden="true" style="width:36px;height:36px;border-radius:12px">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="10" />
									<line x1="2" y1="12" x2="22" y2="12" />
									<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
								</svg>
							</div>
							<div>
								<b>Website</b>
								<span><a href="http://www.ace-mtech.com" target="_blank" rel="noopener">www.ace-mtech.com</a></span>
							</div>
						</div>
					</div>

					<div class="contactCard">
						<h3>Quick message</h3>
						<form id="contactForm" action="send-contact.php" method="post" novalidate>
							<div class="hp-field" aria-hidden="true">
								<label for="website">Website</label>
								<input id="website" name="website" type="text" tabindex="-1" autocomplete="off" />
							</div>
							<div>
								<label for="name">Name</label>
								<input id="name" name="name" type="text" autocomplete="name" placeholder="Your name" required />
							</div>
							<div>
								<label for="company">Company</label>
								<input id="company" name="company" type="text" autocomplete="organization" placeholder="Company / site" />
							</div>
							<div>
								<label for="email">Email</label>
								<input id="email" name="email" type="email" autocomplete="email" placeholder="name@company.com" required />
							</div>
							<div>
								<label for="message">Message</label>
								<textarea id="message" name="message" placeholder="Tell us what you need: scope, timeline, location, constraints…" required></textarea>
							</div>
							<button class="cta" type="submit">
								<span>Request a Quote</span>
								<span aria-hidden="true">→</span>
							</button>
							<div class="formStatus" id="contactStatus" role="status" aria-live="polite"></div>
							<div class="formHelp">
								After clicking, we will show your request status immediately.
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>
	</main>

	<footer>
		<div class="container">
			<div class="foot">
				<div>© <span id="year"></span> Ace‑mtech Group Incorporated. All rights reserved.</div>
				<div style="display:flex; gap:12px; flex-wrap:wrap">
					<a href="work.php">Work</a>
					<a href="products.php">Products</a>
					<a href="clients.php">Clients</a>
					<a href="partners.php">Partners</a>
					<a href="ceo.php">Meet Our CEO</a>
					<a href="contact.php">Contact</a>
				</div>
			</div>
		</div>
	</footer>

<?php include 'scripts.php'; ?>
</body>
</html>
