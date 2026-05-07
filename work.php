<?php
require_once __DIR__ . "/includes/repository.php";

$siteLogoPath = get_site_logo_path();
$services = get_service_products();
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
	<title>Ace‑mtech Group | Work</title>
	<meta name="description" content="Explore Ace‑mtech Group services across architectural, civil, electrical, and mechanical engineering." />
	<link rel="stylesheet" href="styles.css?v=2" />
	<style>
		#work .grid{
			display:grid;
			grid-template-columns:repeat(auto-fit,minmax(250px,320px));
			gap:24px;
			justify-content:center;
			align-items:stretch;
		}
		#work .card{
			width:100%;
			max-width:320px;
			min-height:380px;
			overflow:hidden;
			display:flex;
			flex-direction:column;
		}
		#work .card.wide{
			grid-column:auto;
			max-width:320px;
		}
		#work .productMedia,
		#work .card .productMedia{
			width:100%;
			height:200px;
			max-height:200px;
			overflow:hidden;
			border-radius:10px;
			margin-bottom:12px;
		}
		#work .productMedia img,
		#work .card .productMedia img{
			width:100%;
			height:200px;
			max-height:200px;
			display:block;
			object-fit:cover;
			object-position:center;
			max-width:100%;
		}
		#work .card img{max-width:100%;}
		@media (max-width:640px){
			#work .grid{
				grid-template-columns:minmax(0,1fr);
			}
			#work .card,
			#work .card.wide{
				max-width:100%;
				min-height:unset;
			}
		}
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
			<section id="work" aria-label="Work">
				<div class="sectionHead">
					<div>
						<h2>Our Services</h2>
						<p class="sectionDesc">
							Comprehensive construction and engineering services covering architectural, civil, electrical, and mechanical works for all project types.
						</p>
					</div>
				</div>

				<div class="grid">
					<?php if ($services): ?>
						<?php foreach ($services as $service): ?>
							<article class="card">
								<?php if (!empty($service["image_path"])): ?>
									<div class="productMedia">
										<img src="<?php echo htmlspecialchars($service["image_path"]); ?>" alt="<?php echo htmlspecialchars($service["name"]); ?>" loading="lazy" />
									</div>
								<?php endif; ?>
								<h3><?php echo htmlspecialchars($service["name"]); ?></h3>
								<?php if (!empty($service["description"])): ?>
									<p><?php echo htmlspecialchars($service["description"]); ?></p>
								<?php else: ?>
									<p>Comprehensive construction and engineering support tailored to your project requirements.</p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					<?php else: ?>
						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
									<polyline points="9 22 9 12 15 12 15 22" />
								</svg>
							</div>
							<h3>Architectural & Civil Works</h3>
							<p>Construction, renovation, residential, commercial building, office fit-out, industrial building, power house, and substation projects.</p>
						</article>

						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
								</svg>
							</div>
							<h3>Electrical Works</h3>
							<p>Electrical construction, building automation (fire alarm, CCTV, CATV), switchgear maintenance, power generator installation, and lightning protection.</p>
						</article>

						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
									<polyline points="14 2 14 8 20 8" />
									<path d="M12 18v-6" />
									<path d="M9 15l3 3 3-3" />
								</svg>
							</div>
							<h3>Mechanical Works</h3>
							<p>HVAC systems, refrigeration, air conditioning, fire protection, sprinkler systems, process piping, chilled water, refrigerant piping, and ducting works.</p>
						</article>

						<article class="card wide">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M12 2v10" />
									<path d="M18.4 6.6a9 9 0 1 1-12.8 0" />
									<polyline points="8 10 12 6 16 10" />
								</svg>
							</div>
							<h3>HVAC & Refrigeration Systems</h3>
							<p>
								Complete heating, ventilation, and air conditioning solutions. Refrigeration and air conditioning systems for commercial and industrial applications.
								Authorized dealer and installer for Daikin, Samsung, LG, and Carrier equipment.
							</p>
						</article>

						<article class="card wide">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="2" y="7" width="20" height="15" rx="2" ry="2" />
									<polyline points="17 2 12 7 7 2" />
								</svg>
							</div>
							<h3>Building Automation & Technical Works</h3>
							<p>
								Fire alarm and detection systems, CCTV, CATV/IP/BGM, telephone systems, infrared and thermographic inspection.
								Complete facilities and maintenance works, preventive and corrective maintenance for all building systems.
							</p>
						</article>
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
					<a href="ceo.php">Meet Our CEO</a>
					<a href="contact.php">Contact</a>
				</div>
			</div>
		</div>
	</footer>

<?php include 'scripts.php'; ?>
</body>
</html>
