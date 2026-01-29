<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="theme-color" content="#16a34a" />
<title>Ace‑mtech Group | Construction & Engineering Company</title>
  <meta name="description" content="Ace‑mtech Group provides architectural, civil, electrical, and mechanical engineering services. Construction, building automation, HVAC, fire protection, and maintenance solutions." />

	<style>
		:root{
			--bg: #ffffff;
			--bg-soft: #f0fdf4;
			--panel: #f8fffb;
			--text: #0f172a;
			--text-heading: #020617;
			--muted: #64748b;
			--muted-2: #94a3b8;
			--brand: #16a34a;
			--brand-2: #22c55e;
			--brand-dark: #065f46;
			--accent: #10b981;
			--stroke: rgba(22, 163, 74, 0.15);
			--shadow: 0 20px 50px rgba(15, 118, 110, 0.15);
			--shadow-sm: 0 4px 20px rgba(15, 118, 110, 0.08);
			--shadow-card: 0 8px 32px rgba(0, 0, 0, 0.08);
			--radius: 24px;
			--radius-sm: 16px;
			--max: 1180px;
			--pad: clamp(18px, 2.5vw, 32px);
			--h1: clamp(38px, 4.5vw, 64px);
			--h2: clamp(24px, 2.8vw, 38px);
			--h3: clamp(18px, 1.8vw, 24px);
			--mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			--sans: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
		}

		[data-theme="dark"]{
			--bg: #0a0f1a;
			--bg-soft: #0f1520;
			--panel: #141b28;
			--text: #e2e8f0;
			--text-heading: #f8fafc;
			--muted: #94a3b8;
			--muted-2: #64748b;
			--brand: #22c55e;
			--brand-2: #4ade80;
			--brand-dark: #86efac;
			--accent: #34d399;
			--stroke: rgba(52, 211, 153, 0.15);
			--shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
			--shadow-sm: 0 4px 20px rgba(0, 0, 0, 0.3);
			--shadow-card: 0 8px 32px rgba(0, 0, 0, 0.4);
		}

		*{box-sizing:border-box}
		html{scroll-behavior:smooth}
		body{
			margin:0;
			font-family:var(--sans);
			color:var(--text);
			background:
				radial-gradient(ellipse 1400px 900px at 15% -5%, rgba(34,197,94,.1), transparent 65%),
				radial-gradient(ellipse 1000px 800px at 90% 8%, rgba(16,185,129,.08), transparent 65%),
				linear-gradient(180deg, var(--bg-soft) 0%, var(--bg) 45%, var(--bg-soft) 100%);
			min-height:100vh;
			overflow-x:hidden;
			transition: background 0.4s ease, color 0.4s ease;
		}
		a{color:inherit; text-decoration:none}

		.skip{
			position:absolute;
			left:-999px;
			top:12px;
			background:#0f172a;
			color:#fff;
			padding:8px 12px;
			border-radius:10px;
			text-decoration:none;
			z-index:999;
		}
		.skip:focus{left:12px}

		.container{max-width:var(--max); margin:0 auto; padding:0 var(--pad)}

		header{
			position:sticky; top:0; z-index:50;
			backdrop-filter: blur(20px) saturate(180%);
			background: rgba(255, 255, 255, .75);
			border-bottom: 1px solid var(--stroke);
			transition: all 0.3s ease;
		}
		[data-theme="dark"] header{
			background: rgba(10, 15, 26, .8);
		}
		.nav{
			display:flex; align-items:center; justify-content:space-between;
			padding: 18px 0;
			gap: 16px;
		}
		.brand{
			display:flex; gap:12px; align-items:center;
		}
		.mark{
			width: 100px; height:100px;
			object-fit: contain;
			transition: transform 0.3s ease, filter 0.3s ease;
		}
		.brand:hover .mark{
			transform: translateY(-2px) scale(1.05);
			filter: brightness(1.1);
		}
		.brand span{display:block; line-height:1.1}
		.brand .title{font-weight:800; letter-spacing:-.2px; font-size: 17px}
		.brand .tag{font-size:11px; color:var(--muted); font-family:var(--mono); font-weight:500}

		.themeToggle{
			width:48px; height:48px;
			border-radius:14px;
			border:1px solid var(--stroke);
			background: var(--panel);
			color: var(--text);
			cursor:pointer;
			display:flex; align-items:center; justify-content:center;
			transition: all 0.3s ease;
			box-shadow: var(--shadow-sm);
		}
		.themeToggle:hover{
			background: var(--bg-soft);
			border-color: var(--brand);
			transform: translateY(-2px);
		}
		.themeToggle svg{
			width:22px; height:22px;
			transition: transform 0.3s ease;
		}
		.themeToggle:hover svg{transform: rotate(20deg)}

		.menuBtn{
			display:none;
			align-items:center; gap:8px;
			border:1px solid var(--stroke);
			background: var(--panel);
			color: var(--text);
			padding:12px 16px;
			border-radius:14px;
			cursor:pointer;
			transition: all 0.3s ease;
		}
		.menuBtn:hover{
			background: var(--bg-soft);
			border-color: var(--brand);
		}

		.navlinks{
			display:flex; align-items:center; gap: 6px;
		}
		.navlinks a{
			padding:10px 16px;
			color:var(--muted);
			border-radius:12px;
			border: 1px solid transparent;
			transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
			font-weight:500;
			position:relative;
		}
		.navlinks a:not(.cta):hover{
			color:var(--text);
			background: var(--panel);
			border-color: var(--stroke);
			box-shadow: var(--shadow-sm);
		}

		.cta{
		display:inline-flex; align-items:center; justify-content:center; gap:10px;
		padding:12px 24px;
		border-radius:14px;
		background: linear-gradient(135deg, var(--brand), var(--accent));
		color: #ffffff !important;
		font-weight:700;
		box-shadow: 0 8px 24px rgba(16,185,129,.25);
		border: 1px solid rgba(255,255,255,.2);
		white-space:nowrap;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		position:relative;
		overflow:hidden;
	}
	.cta span{
		color: #ffffff !important;
	}
	.cta:before{
		content:'';
		position:absolute; inset:0;
		background: linear-gradient(135deg, transparent, rgba(255,255,255,.2), transparent);
			transition: transform 0.6s;
		}
		.cta:hover{
			transform: translateY(-3px);
			box-shadow: 0 12px 36px rgba(16,185,129,.35);
		}
		.cta:hover:before{transform: translateX(100%)}
		.cta:active{transform: translateY(-1px)}

		.ghost{
			display:inline-flex; align-items:center; gap:10px;
			padding:12px 24px;
			border-radius:14px;
			border:1.5px solid var(--stroke);
			background: var(--panel);
			color: var(--brand);
			font-weight:600;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			box-shadow: var(--shadow-sm);
		}
		.ghost:hover{
			background: var(--bg-soft);
			border-color: var(--brand);
			transform: translateY(-2px);
			box-shadow: 0 8px 28px rgba(16,185,129,.2);
		}

		main{padding-bottom: 80px}
		section{padding: 48px 0}
		.sectionAlt{
			background: var(--panel);
			border: 1px solid var(--stroke);
			border-radius: var(--radius);
			padding: 36px;
			box-shadow: var(--shadow-sm);
			transition: all 0.3s ease;
		}

		.hero{padding: 72px 0 32px}
		.heroGrid{
			display:grid;
			grid-template-columns: 1.25fr .85fr;
			gap: 32px;
			align-items:stretch;
		}
		.kicker{
			display:inline-flex; align-items:center; gap:10px;
			border:1px solid var(--stroke);
			padding:10px 20px;
			border-radius:999px;
			background: var(--panel);
			color: var(--brand);
			font-family: var(--mono);
			font-size: 13px;
			font-weight:600;
			box-shadow: var(--shadow-sm);
			animation: fadeInUp 0.6s ease;
		}
		.dot{
			width:8px; height:8px; border-radius:999px;
			background: var(--brand);
			box-shadow: 0 0 0 8px rgba(22,163,74,.15);
			animation: pulse 2s infinite;
		}
		@keyframes pulse{
			0%, 100%{opacity:1}
			50%{opacity:0.5}
		}
		@keyframes fadeInUp{
			from{opacity:0; transform:translateY(20px)}
			to{opacity:1; transform:translateY(0)}
		}
		h1{margin:20px 0 14px; font-size: var(--h1); letter-spacing:-.8px; color:var(--text-heading); font-weight:900; line-height:1.15; animation: fadeInUp 0.7s ease 0.1s both}
		.lead{margin:0 0 24px; color: var(--muted); font-size: 17px; line-height:1.75; max-width: 60ch; animation: fadeInUp 0.8s ease 0.2s both}
		.heroActions{display:flex; gap: 14px; flex-wrap:wrap; align-items:center; animation: fadeInUp 0.9s ease 0.3s both}

		.stats{
			margin-top: 28px;
			display:grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 16px;
		}
		.stat{
			border:1px solid var(--stroke);
			background: var(--panel);
			border-radius: var(--radius-sm);
			padding: 20px;
			box-shadow: var(--shadow-card);
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			backdrop-filter: blur(10px);
		}
		.stat:hover{
			transform: translateY(-4px);
			box-shadow: 0 12px 40px rgba(16,185,129,.15);
			border-color: var(--brand);
		}
		.stat b{display:block; font-size: 17px; color:var(--text-heading); font-weight:700}
		.stat span{color: var(--muted); font-size: 13px; font-family: var(--mono); font-weight:500}

		.heroCard{
			border:1px solid var(--stroke);
			background: var(--panel);
			border-radius: var(--radius);
			box-shadow: var(--shadow);
			padding: 28px;
			position:relative;
			overflow:hidden;
			backdrop-filter: blur(10px);
			animation: fadeInUp 1s ease 0.4s both;
		}
		.heroCard:before{
			content:"";
			position:absolute; inset:-80px;
			background:
				radial-gradient(circle at 18% 18%, rgba(22,163,74,.18), transparent 50%),
				radial-gradient(circle at 82% 28%, rgba(34,197,94,.14), transparent 45%);
			opacity: 0.7;
			animation: rotate 20s linear infinite;
		}
		@keyframes rotate{
			from{transform: rotate(0deg)}
			to{transform: rotate(360deg)}
		}
		.heroCard > *{position:relative}
		.cardTitle{font-weight:800; letter-spacing:-.2px; font-size: 15px; color:var(--text-heading)}
		.cardSub{margin-top:8px; color: var(--muted); line-height:1.75; font-size: 15px}

		.chipRow{display:flex; flex-wrap:wrap; gap: 10px; margin-top: 18px}
		.chip{
			border:1px solid var(--stroke);
			background: var(--bg-soft);
			padding: 10px 18px;
			border-radius: 999px;
			font-size: 13px;
			color: var(--brand);
			font-family: var(--mono);
			font-weight:600;
			transition: all 0.3s ease;
			box-shadow: var(--shadow-sm);
		}
		.chip:hover{
			transform: translateY(-2px);
			border-color: var(--brand);
			box-shadow: 0 6px 20px rgba(16,185,129,.15);
		}

		.sectionHead{display:flex; align-items:flex-end; justify-content:space-between; gap: 20px; margin-bottom: 24px}
		h2{margin:0; font-size: var(--h2); letter-spacing:-.5px; color:var(--text-heading); font-weight:800; line-height:1.2}
		h3{margin: 8px 0 10px; font-size: var(--h3); font-weight:700; color:var(--text-heading)}
		.sectionDesc{margin:4px 0 0; color: var(--muted); line-height:1.75; max-width: 65ch; font-size:16px}

		.grid{
			display:grid;
			grid-template-columns: repeat(12, minmax(0, 1fr));
			gap: 20px;
		}
		.card{
			grid-column: span 4;
			border:1px solid var(--stroke);
			background: var(--panel);
			border-radius: var(--radius);
			padding: 24px;
			box-shadow: var(--shadow-card);
			transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
			backdrop-filter: blur(10px);
		}
		.card:hover{
			transform: translateY(-6px);
			box-shadow: 0 16px 48px rgba(16,185,129,.2);
			border-color: var(--brand);
		}
		.card p{margin:0; color: var(--muted); line-height:1.75; font-size: 15px}

		.icon{
			width:56px; height:56px;
			border-radius: 18px;
			background: linear-gradient(135deg, rgba(22,163,74,.15), rgba(34,197,94,.12));
			border: 1px solid var(--stroke);
			display:grid; place-items:center;
			box-shadow: 0 8px 20px rgba(16,185,129,.15);
			transition: all 0.3s ease;
		}
		.card:hover .icon{
			transform: scale(1.1) rotate(5deg);
			box-shadow: 0 12px 28px rgba(16,185,129,.25);
		}
		.icon svg{width:26px; height:26px; stroke:var(--brand); stroke-width:2.5}

		.wide{grid-column: span 6}
		.full{grid-column: span 12}

		.logos{
			display:grid;
			grid-template-columns: repeat(6, minmax(0, 1fr));
			gap: 14px;
			margin-top: 20px;
		}
		.logo{
			border:1.5px dashed var(--stroke);
			background: var(--panel);
			border-radius: 18px;
			padding: 22px;
			text-align:center;
			color: var(--brand);
			font-family: var(--mono);
			font-size: 13px;
			font-weight:600;
			min-height: 70px;
			display:flex;
			align-items:center;
			justify-content:center;
			transition: all 0.3s ease;
			backdrop-filter: blur(10px);
		}
		.logo:hover{
			border-color: var(--brand);
			border-style: solid;
			transform: translateY(-4px);
			box-shadow: var(--shadow-card);
		}

		.ctaPanel{
			border:1px solid var(--stroke);
			background: var(--panel);
			border-radius: calc(var(--radius) + 10px);
			padding: 40px;
			box-shadow: var(--shadow);
			display:flex;
			align-items:center;
			justify-content:space-between;
			gap: 24px;
			flex-wrap:wrap;
			position:relative;
			overflow:hidden;
			backdrop-filter: blur(10px);
		}
		.ctaPanel:before{
			content:'';
			position:absolute;
			top:-35%; right:-12%;
			width:420px; height:420px;
			background: radial-gradient(circle, rgba(34,197,94,.2), transparent 65%);
			border-radius: 50%;
			animation: pulse 4s ease-in-out infinite;
		}
		.ctaPanel > *{position:relative}
		.ctaPanel p{margin:8px 0 0; color: var(--muted); line-height:1.75; font-size:16px}

		.contactGrid{display:grid; grid-template-columns: 1fr 1fr; gap: 20px}
		.contactCard{
			border:1px solid var(--stroke);
			background: var(--panel);
			border-radius: var(--radius);
			padding: 28px;
			box-shadow: var(--shadow-card);
			backdrop-filter: blur(10px);
			transition: all 0.3s ease;
		}
		.contactCard:hover{
			transform: translateY(-4px);
			box-shadow: 0 12px 40px rgba(16,185,129,.15);
		}
		.contactCard h3{margin:0 0 16px; font-size: var(--h3)}
		.contactRow{display:flex; gap: 14px; align-items:flex-start; padding: 14px 0; border-top: 1px solid var(--stroke)}
		.contactRow:first-of-type{border-top: 0; padding-top: 0}
		.contactRow b{display:block; font-weight:600; color:var(--text-heading)}
		.contactRow span{display:block; color: var(--muted); font-size: 14px; line-height:1.7}

		form{display:grid; gap: 16px}
		label{font-size: 14px; color: var(--muted); font-weight:500}
		input, textarea{
			width:100%;
			padding: 16px 18px;
			border-radius: 14px;
			border: 1.5px solid var(--stroke);
			background: var(--bg);
			color: var(--text);
			outline: none;
			transition: all 0.3s ease;
			font-size: 15px;
			font-family: var(--sans);
		}
		input:hover, textarea:hover{
			border-color: var(--brand);
			background: var(--bg-soft);
		}
		textarea{min-height: 150px; resize: vertical}
		input:focus, textarea:focus{
			border-color: var(--brand);
			box-shadow: 0 0 0 4px rgba(16,185,129,.1);
			background: var(--bg);
		}
		.formHelp{color: var(--muted); font-size: 13px; line-height:1.7}

		footer{
			border-top: 1px solid var(--stroke);
			padding: 32px 0;
			color: var(--muted);
			background: var(--bg-soft);
		}
		.foot{
			display:flex; align-items:center; justify-content:space-between; gap: 16px; flex-wrap:wrap;
		}
		.foot a{
			color: var(--muted);
			transition: color 0.3s ease;
			font-weight:500;
		}
		.foot a:hover{color: var(--brand)}

		.note{
			display:inline-flex;
			gap:14px;
			align-items:flex-start;
			padding: 16px 20px;
			border-radius: 14px;
			border: 1px solid var(--stroke);
			background: var(--panel);
			color: var(--muted);
			font-size: 14px;
			line-height:1.7;
			box-shadow: var(--shadow-sm);
			backdrop-filter: blur(10px);
		}

		@media (max-width: 940px){
			.heroGrid{grid-template-columns: 1fr}
			.stats{grid-template-columns: repeat(3, minmax(0, 1fr))}
			.logos{grid-template-columns: repeat(3, minmax(0, 1fr))}
			.hero{padding: 56px 0 24px}
		}
		@media (max-width: 760px){
			.themeToggle{width:44px; height:44px}
			.menuBtn{display:inline-flex}
			.navlinks{
				display:none;
				position:absolute;
				left: var(--pad);
				right: var(--pad);
				top: 76px;
				padding: 16px;
				border-radius: 20px;
				border: 1px solid var(--stroke);
				background: var(--panel);
				backdrop-filter: blur(20px) saturate(180%);
				flex-direction: column;
				align-items:stretch;
				box-shadow: 0 16px 48px rgba(16,185,129,.2);
			}
			.navlinks a{padding: 14px 16px}
			.navlinks.open{display:flex; animation: fadeInUp 0.3s ease}
			.cta{justify-content:center}
			.card{grid-column: span 12}
			.wide{grid-column: span 12}
			.logos{grid-template-columns: repeat(2, minmax(0, 1fr))}
			.contactGrid{grid-template-columns: 1fr}
			.sectionAlt{padding: 24px}
			.ctaPanel{padding: 28px}
			.hero{padding: 40px 0 20px}
			h1{font-size: clamp(32px, 8vw, 48px)}
		}

		@media (prefers-reduced-motion: reduce){
			html{scroll-behavior:auto}
			*{transition:none !important; animation:none !important}
		}
	</style>

	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "Organization",
		"name": "Ace-mtech Group Incorporated",
		"url": "http://localhost/Ace-mtech/",
		"description": "Construction and engineering company providing architectural, civil, electrical, and mechanical works. Authorized dealer for Daikin, Samsung, LG, Carrier.",
		"sameAs": []
	}
	</script>
</head>

<body>
	<a class="skip" href="#content">Skip to content</a>

	<header>
		<div class="container">
			<nav class="nav" aria-label="Primary">
				<a class="brand" href="#top" aria-label="Ace-mtech Home">
					<img src="source_img/Ace-mtech-logo.png" alt="Ace-mtech logo" class="mark" />
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
					<a href="#work">Work</a>
					<a href="#products">Products</a>
					<a href="#clients">Clients</a>
					<a href="#partners">Partners</a>
					<a href="#contact">Contact</a>
					<a class="cta" href="#contact">
						<span>Request a Quote</span>
						<span aria-hidden="true">→</span>
					</a>
				</div>
			</nav>
		</div>
	</header>

	<main id="content">
		<div class="container" id="top">
			<section class="hero" aria-label="Hero">
				<div class="heroGrid">
					<div>
						<div class="kicker">
							<span class="dot" aria-hidden="true"></span>
							<span>Precision engineering • Quality fabrication</span>
						</div>
						<h1>Complete construction & engineering solutions for your projects.</h1>
            <p class="lead">
              Ace‑mtech Group Incorporated delivers architectural, civil, electrical, and mechanical engineering services.
              From design to installation and maintenance—we handle construction, HVAC, electrical systems, and facility management.
						</p>

						<div class="heroActions">
							<a class="cta" href="#work">
								<span>Explore Our Work</span>
								<span aria-hidden="true">→</span>
							</a>
							<a class="ghost" href="#products">
								<span>View Products</span>
								<span aria-hidden="true">↗</span>
							</a>
							<a class="ghost" href="#contact">
								<span>Talk to an Expert</span>
								<span aria-hidden="true">✉</span>
							</a>
						</div>

						<div class="stats" role="list" aria-label="Highlights">
							<div class="stat" role="listitem">
								<b>Full-service engineering</b>
								<span>Architecture → Electrical → Mechanical</span>
							</div>
							<div class="stat" role="listitem">
								<b>Authorized dealer</b>
								<span>Daikin, Samsung, LG, Carrier</span>
							</div>
							<div class="stat" role="listitem">
								<b>Complete solutions</b>
								<span>Design, installation, maintenance</span>
							</div>
						</div>
					</div>

					<aside class="heroCard" aria-label="What we do">
						<div class="cardTitle">What Ace‑mtech delivers</div>
						<div class="cardSub">
							Comprehensive construction and engineering services that improve efficiency, reduce downtime, and deliver reliable results—tailored to your project scope.
						</div>

							<div class="chipRow" aria-label="Capabilities">
								<div class="chip">Architectural Design</div>
								<div class="chip">Civil & Structural</div>
								<div class="chip">Electrical Systems</div>
								<div class="chip">Mechanical & HVAC</div>
								<div class="chip">Fire Protection</div>
								<div class="chip">Facilities Maintenance</div>
							</div>
							<div class="note" role="note">
							<span aria-hidden="true">Tip</span>
							<span>
								Replace the placeholders (services, products, clients, partners) with your real names and catalogs.
								I can wire in your logo, WhatsApp link, and product PDFs if you share them.
							</span>
						</div>
					</aside>
				</div>
			</section>

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
				</div>
			</section>

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

			<section id="clients" aria-label="Clients">
				<div class="sectionHead">
					<div>
						<h2>Clients</h2>
						<p class="sectionDesc">
							Below are placeholders—replace with your real client names or logo images.
						</p>
					</div>
				</div>

				<div class="logos" aria-label="Client list">
					<div class="logo">Client A</div>
					<div class="logo">Client B</div>
					<div class="logo">Client C</div>
					<div class="logo">Client D</div>
					<div class="logo">Client E</div>
					<div class="logo">Client F</div>
				</div>
			</section>

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
					<div class="logo">Partner 1</div>
					<div class="logo">Partner 2</div>
					<div class="logo">Partner 3</div>
					<div class="logo">Partner 4</div>
					<div class="logo">Partner 5</div>
					<div class="logo">Partner 6</div>
				</div>
			</section>

			<section aria-label="Call to action">
				<div class="ctaPanel">
					<div>
						<h2 style="margin:0">Ready to start your construction project?</h2>
						<p>Contact us for architectural, civil, electrical, and mechanical engineering services. We provide complete solutions from design to installation.</p>
					</div>
					<div style="display:flex; gap:10px; flex-wrap:wrap">
						<a class="cta" href="#contact"><span>Get a Quote</span><span aria-hidden="true">→</span></a>
						<a class="ghost" href="#products"><span>View Our Partners</span><span aria-hidden="true">↗</span></a>
					</div>
				</div>
			</section>

			<section id="contact" class="sectionAlt" aria-label="Contact">
				<div class="sectionHead">
					<div>
						<h2>Contact</h2>
						<p class="sectionDesc">
							Use the quick form to draft an email (no backend required), or update the details below with your real contact info.
						</p>
					</div>
				</div>

				<div class="contactGrid">
					<div class="contactCard">
						<h3>Reach Ace‑mtech</h3>

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
						<form id="contactForm">
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
								<span>Send Email</span>
								<span aria-hidden="true">→</span>
							</button>
							<div class="formHelp">
								This opens your email app using <span style="font-family:var(--mono)">mailto:</span> (static site; no server processing).
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
					<a href="#work">Work</a>
					<a href="#products">Products</a>
					<a href="#clients">Clients</a>
					<a href="#partners">Partners</a>
					<a href="#contact">Contact</a>
				</div>
			</div>
		</div>
	</footer>

		<!-- Messenger Floating Button and Popup UI for Users -->
		<link rel="stylesheet" href="admin/messages/messenger.css">
		<div id="messenger-fab" title="Messages">
			<!-- Messenger Icon SVG -->
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
		</div>
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
		})();
	</script>
	<!-- Messenger Chat plugin Code -->
<div id="fb-root"></div>
<div id="fb-customer-chat" class="fb-customerchat"></div>


</body>
</html>
