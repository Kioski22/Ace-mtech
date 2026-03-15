<?php
require_once __DIR__ . "/includes/repository.php";

$products = get_products();
$galleryPosts = get_product_gallery_posts();
$socialLinks = get_social_links();
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
	<title>Ace‑mtech Group | Products</title>
	<meta name="description" content="Authorized HVAC and appliance brands with professional installation and support." />
	<link rel="stylesheet" href="styles.css?v=2" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
			<section id="products" class="sectionAlt" aria-label="Products">
				<div class="sectionHead">
					<div>
						<h2>Authorized Dealer & Installer</h2>
						<p class="sectionDesc">
							We are authorized dealers and certified installers for leading HVAC and appliance brands, ensuring quality products and professional installation.
						</p>
					</div>
				</div>

				<div class="grid">
					<?php if ($products): ?>
						<?php foreach ($products as $product): ?>
							<article class="card productCard">
								<?php if (!empty($product["image_path"])): ?>
									<div class="productMedia">
										<img src="<?php echo htmlspecialchars($product["image_path"]); ?>" alt="<?php echo htmlspecialchars($product["name"]); ?>" loading="lazy" />
									</div>
								<?php endif; ?>
								<h3><?php echo htmlspecialchars($product["name"]); ?></h3>
								<?php if (!empty($product["description"])): ?>
									<p><?php echo htmlspecialchars($product["description"]); ?></p>
								<?php else: ?>
									<p>Authorized dealer and certified installer. Contact us for pricing and availability.</p>
								<?php endif; ?>
							</article>
						<?php endforeach; ?>
					<?php else: ?>
						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="3" y="11" width="18" height="10" rx="2" />
									<path d="M7 11V7a5 5 0 0 1 10 0v4" />
								</svg>
							</div>
							<h3>Daikin</h3>
							<p>Authorized dealer for Daikin air conditioning systems. VRV, split-type, and multi-split units for residential and commercial applications.</p>
						</article>

						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="2" y="7" width="20" height="14" rx="2" />
									<path d="M16 3v4" />
									<path d="M8 3v4" />
									<path d="M2 11h20" />
								</svg>
							</div>
							<h3>Samsung</h3>
							<p>Authorized dealer for Samsung air conditioning and appliances. Digital inverter technology, smart home integration, and energy-efficient systems.</p>
						</article>

						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="10" />
									<path d="M12 6v6l4 2" />
								</svg>
							</div>
							<h3>LG</h3>
							<p>Authorized dealer for LG air conditioning systems and appliances. Multi V VRF systems, residential AC units, and smart appliances.</p>
						</article>

						<article class="card">
							<div class="icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
									<circle cx="12" cy="7" r="4" />
								</svg>
							</div>
							<h3>Carrier</h3>
							<p>Authorized dealer for Carrier HVAC systems. Commercial and industrial air conditioning, chillers, and building management solutions.</p>
						</article>
					<?php endif; ?>

					<article class="card wide">
						<h3>Professional Installation & Support</h3>
						<p>
							Certified installation services for all brands. We provide complete system design, installation, commissioning, and after-sales support.
							Maintenance contracts available for residential, commercial, and industrial clients.
						</p>
					</article>

					<article class="card wide">
						<h3>Fire Protection & Sprinkler Systems</h3>
						<p>
							Complete fire protection solutions including sprinkler system design and installation, fire alarm systems, and compliance with safety regulations.
							Process piping for air and gas lines, chilled water piping systems.
						</p>
					</article>
				</div>
			</section>

			<section id="gallery" aria-label="Project gallery">
				<div class="sectionHead">
					<div>
						<h2>Gallery</h2>
						<p class="sectionDesc">Recent installations, equipment highlights, and project snapshots.</p>
					</div>
				</div>

				<div class="grid">
					<?php if ($galleryPosts): ?>
						<?php foreach ($galleryPosts as $galleryPost): ?>
							<?php
								$modalImages = array_map(
									static function (array $image) use ($galleryPost): array {
										return [
											"src" => $image["image_path"],
											"alt" => $image["alt_text"] ?: $galleryPost["title"],
										];
									},
									$galleryPost["images"] ?? []
								);
							?>
							<article class="card galleryPost">
								<button
									type="button"
									class="galleryTrigger"
									data-images="<?php echo htmlspecialchars((string)json_encode($modalImages, JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>"
									data-title="<?php echo htmlspecialchars($galleryPost["title"]); ?>"
									data-caption="<?php echo htmlspecialchars($galleryPost["caption"] ?? ""); ?>"
									aria-label="Open gallery post: <?php echo htmlspecialchars($galleryPost["title"]); ?>"
								>
									<div class="galleryPostMedia">
										<img src="<?php echo htmlspecialchars($galleryPost["cover_image_path"]); ?>" alt="<?php echo htmlspecialchars($galleryPost["title"]); ?>" loading="lazy" />
										<?php if (count($modalImages) > 1): ?>
											<span class="galleryCountBadge"><?php echo count($modalImages); ?> photos</span>
										<?php endif; ?>
									</div>
								</button>
								<div class="galleryBody">
									<h3><?php echo htmlspecialchars($galleryPost["title"]); ?></h3>
									<?php if (!empty($galleryPost["caption"])): ?>
										<p><?php echo htmlspecialchars($galleryPost["caption"]); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					<?php else: ?>
						<article class="card wide">
							<h3>Gallery updates coming soon</h3>
							<p>New project photos and installation highlights will appear here as they are published.</p>
						</article>
					<?php endif; ?>
				</div>

				<div class="galleryModal" id="galleryModal" hidden aria-hidden="true">
					<div class="galleryModalBackdrop" data-gallery-close></div>
					<div class="galleryModalDialog" role="dialog" aria-modal="true" aria-labelledby="galleryModalTitle">
						<button class="galleryModalClose" id="galleryModalClose" type="button" aria-label="Close gallery">×</button>
						<button class="galleryNav prev" id="galleryPrev" type="button" aria-label="Previous image">‹</button>
						<div class="galleryStage">
							<img id="galleryModalImage" src="" alt="" />
							<div class="galleryModalMeta">
								<div class="galleryCounter" id="galleryCounter"></div>
								<h3 id="galleryModalTitle"></h3>
								<p id="galleryModalCaption"></p>
							</div>
						</div>
						<button class="galleryNav next" id="galleryNext" type="button" aria-label="Next image">›</button>
						<div class="galleryThumbStrip" id="galleryThumbStrip" aria-label="Gallery thumbnails"></div>
					</div>
				</div>
			</section>

			<section id="social" class="sectionAlt" aria-label="Social media">
				<div class="sectionHead">
					<div>
						<h2>Connect with us</h2>
						<p class="sectionDesc">Follow Ace‑mtech for project updates, product launches, and maintenance tips.</p>
					</div>
				</div>

				<div class="socialGrid">
					<?php if (!$socialLinks): ?>
						<article class="socialCard">
							<div class="socialIcon" aria-hidden="true">i</div>
							<h3>Social links unavailable</h3>
							<p>Please update social links in admin to show this section.</p>
						</article>
					<?php else: ?>
						<?php foreach ($socialLinks as $socialLink): ?>
							<article class="socialCard">
								<div class="socialIcon" aria-hidden="true">
									<?php if (!empty($socialLink["icon_path"])): ?>
										<img src="<?php echo htmlspecialchars((string)$socialLink["icon_path"]); ?>" alt="" loading="lazy" />
									<?php else: ?>
										<?php echo $socialLink["icon"]; ?>
									<?php endif; ?>
								</div>
								<h3><?php echo htmlspecialchars($socialLink["name"]); ?></h3>
								<p><?php echo htmlspecialchars($socialLink["description"]); ?></p>
								<a class="socialLink" href="<?php echo htmlspecialchars($socialLink["url"]); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($socialLink["button_label"]); ?></a>
							</article>
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
					<a href="ceo.php">Meet Our CEO</a>
					<a href="contact.php">Contact</a>
				</div>
			</div>
		</div>
	</footer>

<?php include 'scripts.php'; ?>
</body>
</html>
