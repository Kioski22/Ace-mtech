<?php
require_once __DIR__ . "/includes/repository.php";

$siteLogoPath = get_site_logo_path();
$ceoSettings = get_ceo_settings();
$ceoPortraitPath = get_ceo_portrait_path();
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

		/* ── Card animations ── */
		@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
		@keyframes fadeScale{from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)}}

		.animReady .ceoBadge{opacity:0;animation:fadeUp .5s ease forwards}
		.animReady .sectionHead h2{opacity:0;animation:fadeUp .5s ease .1s forwards}
		.animReady .sectionHead .sectionDesc{opacity:0;animation:fadeUp .5s ease .18s forwards}
		.animReady .ceoPortraitWrap{opacity:0;animation:fadeScale .6s ease .2s forwards}
		.animReady .ceoHero > div:last-child > .card{opacity:0;animation:fadeUp .6s ease .35s forwards}
		.animReady .stat{opacity:0;animation:fadeUp .4s ease forwards}
		.animReady .stat:nth-child(1){animation-delay:.45s}
		.animReady .stat:nth-child(2){animation-delay:.55s}
		.animReady .stat:nth-child(3){animation-delay:.65s}
		.animReady .stat:nth-child(4){animation-delay:.75s}

		.achievementCard{opacity:0;transform:translateY(30px);transition:opacity .5s ease,transform .5s ease}
		.achievementCard.visible{opacity:1;transform:translateY(0)}
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
			<section class="sectionAlt" aria-label="Meet our CEO">
				<div class="sectionHead">
					<div>
						<span class="ceoBadge"><?php echo htmlspecialchars((string)$ceoSettings["ceo_badge"]); ?></span>
						<h2 style="margin-top:12px"><?php echo htmlspecialchars((string)$ceoSettings["ceo_heading"]); ?></h2>
						<p class="sectionDesc"><?php echo htmlspecialchars((string)$ceoSettings["ceo_description"]); ?></p>
					</div>
				</div>

				<div class="ceoHero">
					<div class="ceoPortraitWrap">
						<img class="ceoPortrait" src="<?php echo htmlspecialchars($ceoPortraitPath); ?>" alt="Portrait of <?php echo htmlspecialchars((string)$ceoSettings["ceo_name"]); ?>" />
					</div>
					<div>
						<div class="card" style="height:100%">
							<h3 style="margin-top:0"><?php echo htmlspecialchars((string)$ceoSettings["ceo_name"]); ?></h3>
							<p style="margin:0;color:var(--muted)"><?php echo htmlspecialchars((string)$ceoSettings["ceo_role"]); ?></p>
							<p class="ceoQuote">"<?php echo htmlspecialchars((string)$ceoSettings["ceo_quote"]); ?>"</p>
							<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:8px">
								<?php foreach (($ceoSettings["stats"] ?? []) as $stat): ?>
									<div class="stat">
										<b><?php echo htmlspecialchars((string)($stat["value"] ?? "")); ?></b>
										<span><?php echo htmlspecialchars((string)($stat["label"] ?? "")); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section aria-label="Life achievements">
				<div class="sectionHead">
					<div>
						<h2><?php echo htmlspecialchars((string)$ceoSettings["ceo_achievements_heading"]); ?></h2>
						<p class="sectionDesc"><?php echo htmlspecialchars((string)$ceoSettings["ceo_achievements_description"]); ?></p>
					</div>
				</div>
				<div class="achievementGrid">
					<?php foreach (($ceoSettings["achievements"] ?? []) as $achievement): ?>
						<article class="achievementCard">
							<?php if (trim((string)($achievement["year"] ?? "")) !== ""): ?>
								<div class="achievementYear"><?php echo htmlspecialchars((string)$achievement["year"]); ?></div>
							<?php endif; ?>
							<h3><?php echo htmlspecialchars((string)($achievement["title"] ?? "")); ?></h3>
							<p><?php echo htmlspecialchars((string)($achievement["description"] ?? "")); ?></p>
						</article>
					<?php endforeach; ?>
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
<script>
(function(){
	document.getElementById('content').classList.add('animReady');

	var cards=document.querySelectorAll('.achievementCard');
	if(!cards.length)return;
	var io=new IntersectionObserver(function(entries){
		entries.forEach(function(e){
			if(e.isIntersecting){
				var i=[].indexOf.call(cards,e.target);
				e.target.style.transitionDelay=(i*0.1)+'s';
				e.target.classList.add('visible');
				io.unobserve(e.target);
			}
		});
	},{threshold:0.15});
	cards.forEach(function(c){io.observe(c);});
})();
</script>
</body>
</html>
