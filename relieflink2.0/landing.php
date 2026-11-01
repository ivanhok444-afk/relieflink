<?php
require_once __DIR__ . '/config/SessionHandler.php';
AppSessionHandler::startSession();

if (isset($_SESSION['user_id'])) {
	$destination = $_SESSION['role'] === 'admin'
		? 'views/admin/dashboard.php'
		: 'views/user/dashboard.php';
	header('Location: ' . $destination);
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#173b35">
	<title>ReliefLink</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap" rel="stylesheet">
	<style>
		:root {
			color-scheme: light;
			--ink: #173b35;
			--paper: #f5f3ec;
			--lime: #d9ec86;
		}
		* { box-sizing: border-box; }
		body { margin: 0; background: var(--paper); color: var(--ink); font-family: 'DM Sans', sans-serif; }
		a { color: inherit; }
		.topbar {
			align-items: center;
			display: flex;
			justify-content: space-between;
			margin: 0 auto;
			max-width: 1320px;
			padding: 24px 6%;
		}
		.brand { align-items: center; display: flex; font-size: 15px; font-weight: 700; gap: 11px; text-decoration: none; }
		.brand-wordmark { font-size: 17px; font-weight: 800; letter-spacing: -.045em; }
		.brand-accent { color: #6c8539; }
		.brand-mark {
			align-items: center;
			background: var(--lime);
			border-radius: 50%;
			display: inline-flex;
			height: 36px;
			justify-content: center;
			position: relative;
			width: 36px;
		}
		.brand-mark::before, .brand-mark::after { background: var(--ink); border-radius: 2px; content: ''; position: absolute; }
		.brand-mark::before { height: 15px; width: 4px; }
		.brand-mark::after { height: 4px; width: 15px; }
		.top-link { font-size: 14px; font-weight: 600; text-decoration-thickness: 1px; text-underline-offset: 4px; }
		.hero {
			background-color: #24473f;
			background-image: linear-gradient(90deg, rgba(16, 45, 39, .94) 0%, rgba(16, 45, 39, .76) 46%, rgba(16, 45, 39, .12) 100%), url('https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=2000&q=85');
			background-position: center;
			background-size: cover;
			color: #fff;
			min-height: 560px;
		}
		.hero-inner {
			display: flex;
			flex-direction: column;
			justify-content: center;
			margin: 0 auto;
			max-width: 1320px;
			min-height: 560px;
			padding: 70px 8%;
		}
		.eyebrow { color: var(--lime); font-size: 12px; font-weight: 700; letter-spacing: 1.8px; margin: 0 0 20px; text-transform: uppercase; }
		.hero h1 { color: #fff; font: 400 64px/1.04 'DM Serif Display', Georgia, serif; margin: 0; max-width: 650px; }
		.hero-copy { color: rgba(255,255,255,.82); font-size: 17px; line-height: 1.7; margin: 22px 0 30px; max-width: 470px; }
		.actions { align-items: center; display: flex; flex-wrap: wrap; gap: 14px; }
		.button {
			align-items: center;
			border: 1px solid transparent;
			border-radius: 3px;
			display: inline-flex;
			font-size: 14px;
			font-weight: 700;
			justify-content: center;
			min-height: 48px;
			padding: 0 22px;
			text-decoration: none;
			transition: transform .18s ease, background-color .18s ease;
		}
		.button:hover { transform: translateY(-2px); }
		.button-primary { background: var(--lime); color: var(--ink); }
		.button-primary:hover { background: #e4f49f; }
		.button-secondary { border-color: rgba(255,255,255,.6); color: #fff; }
		.button-secondary:hover { background: rgba(255,255,255,.12); }
		.access-row { align-items: center; display: flex; gap: 9px; margin-top: 26px; }
		.access-row span, .access-row a { font-size: 13px; }
		.access-row span { color: rgba(255,255,255,.68); }
		.access-row a { color: #fff; font-weight: 600; text-underline-offset: 3px; }
		.lower {
			align-items: center;
			display: flex;
			gap: 36px;
			justify-content: space-between;
			margin: 0 auto;
			max-width: 1320px;
			padding: 32px 8%;
		}
		.lower-label { color: #75817b; font-size: 11px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; }
		.lower-copy { font: 400 21px/1.35 'DM Serif Display', Georgia, serif; margin: 5px 0 0; }
		.lower-links { display: flex; gap: 28px; }
		.lower-links a { font-size: 13px; font-weight: 600; text-decoration: none; }
		.lower-links a:hover { text-decoration: underline; text-underline-offset: 4px; }
		@media (max-width: 680px) {
			.topbar { padding: 18px 6%; }
			.brand { font-size: 13px; }
			.hero { background-image: linear-gradient(90deg, rgba(16,45,39,.92), rgba(16,45,39,.70)), url('https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=1000&q=80'); }
			.hero, .hero-inner { min-height: 560px; }
			.hero-inner { padding: 58px 7%; }
			h1 { font-size: 48px; }
			.hero-copy { font-size: 15px; }
			.lower { align-items: flex-start; flex-direction: column; gap: 20px; padding: 28px 7%; }
			.lower-links { flex-wrap: wrap; gap: 18px 24px; }
		}
		@media (prefers-reduced-motion: reduce) {
			*, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
		}
	</style>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
	<header class="topbar">
		<a class="brand" href="landing.php"><span class="brand-mark" aria-hidden="true"></span><span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span></a>
		<a class="top-link" href="views/user/login.php">Sign in</a>
	</header>
	<main>
		<section class="hero">
			<div class="hero-inner">
				<p class="eyebrow">Community support portal</p>
				<h1>Help, organized around people.</h1>
				<p class="hero-copy">A shared place for households to request relief goods and follow their support, and for teams to coordinate distribution.</p>
				<div class="actions">
					<a class="button button-primary" href="views/user/login.php">Sign in to your account</a>
					<a class="button button-secondary" href="views/register.php">Create an account</a>
				</div>
				<div class="access-row"><span>Managing distribution?</span><a href="views/admin/login.php">Admin portal</a></div>
			</div>
		</section>
		<section class="lower" aria-label="Account access">
			<div>
				<div class="lower-label">ReliefLink</div>
				<p class="lower-copy">Support that reaches households with care.</p>
			</div>
			<nav class="lower-links" aria-label="Account links">
				<a href="views/user/login.php">User sign in</a>
				<a href="views/register.php">Register</a>
				<a href="views/admin/login.php">Staff access</a>
			</nav>
		</section>
	</main>
</body>
</html>