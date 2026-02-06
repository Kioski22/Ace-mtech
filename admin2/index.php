<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";

require_admin_login();

$clients = get_clients();
$partners = get_partners();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Ace-mtech Admin Dashboard</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		body{background:var(--bg-soft)}
		.adminShell{max-width:1100px;margin:0 auto;padding:32px}
		.adminNav{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px}
		.adminNav a{text-decoration:none}
		.adminCard{border:1px solid var(--stroke);background:var(--panel);border-radius:16px;padding:20px;box-shadow:var(--shadow-sm)}
		.adminGrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
		@media (max-width:900px){.adminGrid{grid-template-columns:1fr}}
	</style>
</head>
<body>
	<div class="adminShell">
		<h1 style="margin-bottom:10px">Admin dashboard</h1>
		<p class="sectionDesc" style="margin-bottom:22px">Manage client and partner logos shown on the public pages.</p>

		<nav class="adminNav" aria-label="Admin navigation">
			<a class="ghost" href="clients.php">Client list</a>
			<a class="ghost" href="partners.php">Partners list</a>
			<a class="ghost" href="products.php">Products</a>
			<a class="ghost" href="../index.php">View site</a>
			<a class="ghost" href="logout.php">Log out</a>
		</nav>

		<div class="adminGrid">
			<div class="adminCard">
				<h3 style="margin-top:0">Clients</h3>
				<p class="sectionDesc">Total: <b><?php echo count($clients); ?></b></p>
				<a class="cta" href="clients.php"><span>Manage clients</span><span aria-hidden="true">→</span></a>
			</div>
			<div class="adminCard">
				<h3 style="margin-top:0">Partners</h3>
				<p class="sectionDesc">Total: <b><?php echo count($partners); ?></b></p>
				<a class="cta" href="partners.php"><span>Manage partners</span><span aria-hidden="true">→</span></a>
			</div>
			<div class="adminCard">
				<h3 style="margin-top:0">Products</h3>
				<p class="sectionDesc">Total: <b><?php echo count(get_products()); ?></b></p>
				<a class="cta" href="products.php"><span>Manage products</span><span aria-hidden="true">→</span></a>
			</div>
		</div>
	</div>
</body>
</html>
