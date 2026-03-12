<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";

require_admin_login();

$clients = get_clients();
$partners = get_partners();
$products = get_products();
$galleryPosts = get_product_gallery_posts(true);
$socialLinks = get_social_links(true);
$newsPosts = get_news_posts(true);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Dashboard · Ace-mtech Admin</title>
	<style>
		*{box-sizing:border-box;margin:0;padding:0}
		body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f8fafc;color:#0f172a;line-height:1.6}
		.adminLayout{display:flex;min-height:100vh}
		.adminSidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:linear-gradient(180deg,#1e293b 0%,#0f172a 100%);color:#fff;padding:0;overflow-y:auto;z-index:100;box-shadow:4px 0 24px rgba(0,0,0,0.2)}
		.adminSidebar::-webkit-scrollbar{width:6px}
		.adminSidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.2);border-radius:3px}
		.adminBrand{padding:28px 24px;border-bottom:1px solid rgba(255,255,255,0.1);background:rgba(0,0,0,0.2)}
		.adminBrand h2{margin:0 0 6px;font-size:22px;font-weight:800;color:#fff;letter-spacing:-0.5px}
		.adminBrand p{margin:0;font-size:11px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;font-weight:600}
		.adminNav{list-style:none;padding:16px 0;margin:0}
		.adminNav li{margin:0}
		.adminNav a{display:flex;align-items:center;gap:12px;padding:12px 24px;color:rgba(255,255,255,0.75);text-decoration:none;font-weight:500;transition:all 0.2s ease;font-size:14px;position:relative}
		.adminNav a:hover{background:rgba(255,255,255,0.08);color:#fff}
		.adminNav a.active{background:linear-gradient(90deg,rgba(22,163,74,0.2),transparent);color:#fff;font-weight:600}
		.adminNav a.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#22c55e,#16a34a);box-shadow:0 0 12px rgba(34,197,94,0.5)}
		.adminNav svg{width:20px;height:20px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
		.adminNavDivider{height:1px;background:rgba(255,255,255,0.08);margin:12px 16px}
		.adminMain{flex:1;margin-left:260px;padding:40px;max-width:calc(100% - 260px);min-height:100vh}
		.adminPageHeader{margin-bottom:32px;padding-bottom:24px;border-bottom:2px solid #e2e8f0}
		.adminPageHeader h1{margin:0 0 10px;font-size:36px;font-weight:800;color:#0f172a;letter-spacing:-1px}
		.adminPageHeader p{margin:0;font-size:16px;color:#64748b;max-width:70ch;line-height:1.7}
		.adminGridCards{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px}
		.adminCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.04);transition:all 0.3s cubic-bezier(0.4,0,0.2,1);position:relative;overflow:hidden}
		.adminCard::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#16a34a,#22c55e);opacity:0;transition:opacity 0.3s}
		.adminCard:hover{box-shadow:0 12px 32px rgba(0,0,0,0.12);transform:translateY(-4px);border-color:#16a34a}
		.adminCard:hover::before{opacity:1}
		.adminCard h3{margin:0 0 16px;font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px}
		.adminStatValue{font-size:48px;font-weight:900;margin:12px 0 16px;background:linear-gradient(135deg,#16a34a,#059669);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:-2px}
		.adminCard p{margin:0 0 20px;font-size:14px;color:#64748b;line-height:1.6}
		.adminBtnPrimary{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:14px;transition:all 0.3s;box-shadow:0 4px 12px rgba(22,163,74,0.25);border:none;cursor:pointer}
		.adminBtnPrimary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(22,163,74,0.35)}
		.adminBtnPrimary:active{transform:translateY(0)}
		@media (max-width:1024px){.adminSidebar{width:220px}.adminMain{margin-left:220px;max-width:calc(100% - 220px);padding:32px 28px}.adminPageHeader h1{font-size:30px}}
		@media (max-width:768px){
			.adminSidebar{width:70px}.adminSidebar .adminBrand h2,.adminSidebar .adminBrand p,.adminNav span{display:none}
			.adminNav a{justify-content:center;padding:14px}.adminMain{margin-left:70px;max-width:calc(100% - 70px);padding:24px 16px}
			.adminPageHeader h1{font-size:26px}.adminGridCards{grid-template-columns:1fr}
		}
	</style>
</head>
<body>
	<div class="adminLayout">
		<aside class="adminSidebar">
			<div class="adminBrand">
				<h2>Ace-mtech</h2>
				<p>Admin Panel</p>
			</div>
			<ul class="adminNav">
				<li><a href="index.php" class="active"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Dashboard</span></a></li>
				<li class="adminNavDivider"></li>
				<li><a href="clients.php"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Clients</span></a></li>
				<li><a href="partners.php"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg><span>Partners</span></a></li>
				<li><a href="products.php"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Products</span></a></li>
				<li><a href="gallery.php"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Gallery</span></a></li>
				<li><a href="news.php"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>News</span></a></li>
				<li><a href="social-links.php"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg><span>Social Links</span></a></li>
				<li class="adminNavDivider"></li>
				<li><a href="../admin/messages/admin.html"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>Messages</span></a></li>
				<li><a href="../index.php"><svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><span>View Site</span></a></li>
				<li><a href="logout.php"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Log Out</span></a></li>
			</ul>
		</aside>
		<main class="adminMain">
			<div class="adminPageHeader">
				<h1>Dashboard</h1>
				<p>Manage your website content including clients, partners, products, gallery, and news updates.</p>
			</div>

			<div class="adminGridCards">
				<div class="adminCard">
					<h3>Clients</h3>
					<div class="adminStatValue"><?php echo count($clients); ?></div>
					<p>Client logos and company names displayed on your website</p>
					<a class="adminBtnPrimary" href="clients.php"><span>Manage Clients</span><span>→</span></a>
				</div>
				<div class="adminCard">
					<h3>Partners</h3>
					<div class="adminStatValue"><?php echo count($partners); ?></div>
					<p>Partner brands and technology suppliers</p>
					<a class="adminBtnPrimary" href="partners.php"><span>Manage Partners</span><span>→</span></a>
				</div>
				<div class="adminCard">
					<h3>Products</h3>
					<div class="adminStatValue"><?php echo count($products); ?></div>
					<p>Authorized dealer products and equipment</p>
					<a class="adminBtnPrimary" href="products.php"><span>Manage Products</span><span>→</span></a>
				</div>
				<div class="adminCard">
					<h3>Gallery</h3>
					<div class="adminStatValue"><?php echo count($galleryPosts); ?></div>
					<p>Project photos and installation highlights</p>
					<a class="adminBtnPrimary" href="gallery.php"><span>Manage Gallery</span><span>→</span></a>
				</div>
				<div class="adminCard">
					<h3>Social Links</h3>
					<div class="adminStatValue"><?php echo count($socialLinks); ?></div>
					<p>Social media and connect with us entries</p>
					<a class="adminBtnPrimary" href="social-links.php"><span>Manage Links</span><span>→</span></a>
				</div>
				<div class="adminCard">
					<h3>News & Achievements</h3>
					<div class="adminStatValue"><?php echo count($newsPosts); ?></div>
					<p>Company news, milestones, and updates</p>
					<a class="adminBtnPrimary" href="news.php"><span>Manage News</span><span>→</span></a>
				</div>
			</div>
		</main>
	</div>
	<script type="module" src="messages-unread-badge.js"></script>
</body>
</html>
