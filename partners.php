<?php
require_once __DIR__ . "/includes/repository.php";

$partners = get_partners();
$siteLogoPath = get_site_logo_path();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
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
	<title>Ace‑mtech Group | Partners</title>
	<meta name="description" content="Technology and supply partners of Ace‑mtech Group." />
	<link rel="stylesheet" href="styles.css" />
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

				<button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="navlinks">
					<span aria-hidden="true">☰</span>
					<span>Menu</span>
				</button>

				<div class="navlinks" id="navlinks">
					<a href="work.php">Work</a>
					<a href="products.php">Products</a>
					<a href="clients.php">Clients</a>
					<a href="partners.php">Partners</a>
					<a href="contact.php">Contact</a>
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
			<section id="partners" class="sectionAlt" aria-label="Partners">
				<div class="sectionHead">
					<div>
						<h2>Partners</h2>
						<p class="sectionDesc">
							Technology and supply partners we work with. Swap these placeholders with real partner brands.
						</p>
					</div>
				</div>

				<div class="logos" aria-label="Partner list">
					<?php if (!$partners): ?>
						<div class="logo">Partner 1</div>
						<div class="logo">Partner 2</div>
						<div class="logo">Partner 3</div>
						<div class="logo">Partner 4</div>
						<div class="logo">Partner 5</div>
						<div class="logo">Partner 6</div>
					<?php else: ?>
						<?php foreach ($partners as $partner): ?>
							<div class="logo">
								<?php if (!empty($partner["image_path"])): ?>
									<img src="<?php echo htmlspecialchars($partner["image_path"]); ?>" alt="<?php echo htmlspecialchars($partner["name"]); ?> logo" loading="lazy" />
								<?php endif; ?>
								<span><?php echo htmlspecialchars($partner["name"]); ?></span>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
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
					<a href="contact.php">Contact</a>
				</div>
			</div>
		</div>
	</footer>

<?php include 'scripts.php'; ?>
</body>
</html>
