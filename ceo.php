<?php
require_once __DIR__ . "/includes/repository.php";

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
	<title>Ace‑mtech Group | Meet Our CEO</title>
	<meta name="description" content="Meet our CEO and explore his life achievements, leadership milestones, and vision for Ace-mtech Group." />
	<link rel="stylesheet" href="styles.css?v=2" />
	<style>
		.ceoHero{display:grid;grid-template-columns:1.05fr .95fr;gap:26px;align-items:stretch}
		.ceoPortraitWrap{border:1.5px solid var(--stroke);border-radius:20px;padding:14px;background:var(--panel);box-shadow:var(--shadow-card)}
		.ceoPortrait{width:100%;height:100%;min-height:420px;object-fit:cover;border-radius:14px;display:block}
		.ceoBadge{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1px solid var(--stroke);border-radius:999px;background:var(--bg-soft);color:var(--brand);font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
		.achievementGrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
		.achievementCard{padding:18px;border:1.5px solid var(--stroke);border-radius:14px;background:var(--panel);box-shadow:var(--shadow-sm)}
		.achievementYear{font-family:var(--mono);font-size:12px;color:var(--brand);font-weight:700;letter-spacing:.4px}
		.achievementCard h3{margin:10px 0 8px;font-size:18px;line-height:1.35;color:var(--text-heading)}
		.achievementCard p{margin:0;color:var(--muted);line-height:1.65;font-size:14px}
		.ceoQuote{margin-top:18px;border-left:4px solid var(--brand);padding:14px 0 14px 16px;color:var(--muted);font-style:italic;line-height:1.75}
		@media (max-width:960px){.ceoHero{grid-template-columns:1fr}.achievementGrid{grid-template-columns:1fr}.ceoPortrait{min-height:340px}}
	</style>
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

				<button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="navlinks">
					<span aria-hidden="true">☰</span>
					<span>Menu</span>
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
			<section class="sectionAlt" aria-label="Meet our CEO">
				<div class="sectionHead">
					<div>
						<span class="ceoBadge">Leadership Spotlight</span>
						<h2 style="margin-top:12px">Meet Our CEO</h2>
						<p class="sectionDesc">A visionary builder and engineering leader dedicated to innovation, excellence, and nation-building through sustainable infrastructure.</p>
					</div>
				</div>

				<div class="ceoHero">
					<div class="ceoPortraitWrap">
						<img class="ceoPortrait" src="source_img/ceo-placeholder.svg" alt="Portrait of Ace-mtech Chief Executive Officer" />
					</div>
					<div>
						<div class="card" style="height:100%">
							<h3 style="margin-top:0">Engr. Alexander C. Estrella</h3>
							<p style="margin:0;color:var(--muted)">Founder & Chief Executive Officer, Ace‑mtech Group Incorporated</p>
							<p class="ceoQuote">"Our mission is simple: deliver infrastructure that improves everyday life while mentoring the next generation of Filipino engineers and builders."</p>
							<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:8px">
								<div class="stat"><b>20+ Years</b><span>Industry Leadership</span></div>
								<div class="stat"><b>500+</b><span>Projects Delivered</span></div>
								<div class="stat"><b>120+</b><span>Team Members Mentored</span></div>
								<div class="stat"><b>Nationwide</b><span>Project Coverage</span></div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section aria-label="Life achievements">
				<div class="sectionHead">
					<div>
						<h2>Life Achievements</h2>
						<p class="sectionDesc">A timeline of key milestones that shaped his leadership journey.</p>
					</div>
				</div>
				<div class="achievementGrid">
					<article class="achievementCard">
						<div class="achievementYear">2003</div>
						<h3>Licensed Engineer & Early Career</h3>
						<p>Earned professional engineering credentials and led multidisciplinary construction support teams on institutional and commercial facilities.</p>
					</article>
					<article class="achievementCard">
						<div class="achievementYear">2010</div>
						<h3>Founded Ace‑mtech Group</h3>
						<p>Established Ace‑mtech with a clear vision: integrated architecture, civil, electrical, and mechanical engineering under one trusted brand.</p>
					</article>
					<article class="achievementCard">
						<div class="achievementYear">2016</div>
						<h3>Major Infrastructure Milestone</h3>
						<p>Successfully delivered large-scale industrial and commercial systems, including HVAC, fire protection, and automation packages.</p>
					</article>
					<article class="achievementCard">
						<div class="achievementYear">2024</div>
						<h3>Leadership & Community Impact</h3>
						<p>Expanded operations while championing technical mentorship programs, safety-first culture, and local workforce development initiatives.</p>
					</article>
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
