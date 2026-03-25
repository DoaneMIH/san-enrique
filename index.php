<?php
require_once 'includes/functions.php';
$db = getDB();
$categories = getCategories();
$featured = getFeaturedListings(6);
$events = getUpcomingEvents(3);
$stats = getStats();
// Fix barangays count: use the Barangays category listing count (matches admin panel)
// instead of counting distinct barangay field values (which inflates the number)
$_brgy = $db->query("SELECT COUNT(l.id) as c FROM listings l JOIN categories c ON l.category_id=c.id WHERE c.slug='barangays' AND l.status='active'");
if ($_brgy) $stats['barangays'] = (int)$_brgy->fetch_assoc()['c'];
$mapListings = getAllListingsForMap();

// Embed initial DB timestamp for the live-update poller
$initTs = 0;
foreach (['listings', 'categories', 'events'] as $_t) {
  $r = $db->query("SELECT UNIX_TIMESTAMP(MAX(created_at)) as t FROM `$_t`");
  if ($r) {
    $v = (int) $r->fetch_assoc()['t'];
    if ($v > $initTs)
      $initTs = $v;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" type="x-icon" href="assets/images/san-enrique-logo.jpg">
  <title><?= SITE_NAME ?> - <?= SITE_TAGLINE ?></title>
  <meta name="description"
    content="Discover the hidden paradise of San Enrique, Iloilo. Explore resorts, cultural sites, local food, and beautiful barangays.">
  <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

  <!-- Fonts & Icons -->
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- ═══════════ UX ENHANCEMENT CSS ═══════════ -->
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ── Root overrides ────────────────────────── */
    :root {
      --glass-bg: rgba(255,255,255,0.12);
      --glass-border: rgba(255,255,255,0.25);
      --glass-shadow: 0 8px 32px rgba(0,0,0,0.18);
      --glass-blur: blur(14px);
      --green-deep: #0d2b1e;
      --green-mid: #1b4332;
      --green-light: #40916c;
      --gold: #d4a017;
      --gold-light: #f0c040;
      /* --off-white: #f8faf8b2; */
    }

    /* ── HERO: full-screen nature parallax ─────── */
    .hero-section {
      min-height: 100vh !important;
      background:
        linear-gradient(160deg, rgba(13,43,30,0.82) 0%, rgba(27,67,50,0.65) 50%, rgba(64,145,108,0.45) 100%),
        url('assets/images/7.jpg') center/cover no-repeat fixed !important;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
      padding: 80px 0 80px !important;
    }
    .hero-section::before {
      content:'';
      position:absolute;inset:0;
      background: radial-gradient(ellipse at 70% 50%, rgba(64,145,108,0.18) 0%, transparent 65%);
      z-index:1;pointer-events:none;
    }
    .hero-section::after {
      content:'';
      position:absolute;bottom:0;left:0;right:0;height:120px;
      background:linear-gradient(to bottom,transparent,var(--off-white,#f8faf8));
      z-index:1;pointer-events:none;
    }
    .hero-bg-pattern,.hero-particles,.hero-floating-shapes,.float-shape {
      display:none !important;
    }

    /* ── Animated headline ─────────────────────── */
    .hero-title {
      font-family:'Cormorant Garamond',serif !important;
      font-size: clamp(2.6rem,6vw,4.8rem) !important;
      font-weight: 700 !important;
      color: #fff !important;
      line-height: 1.1 !important;
      letter-spacing: -0.01em;
      animation: heroFadeUp 1s cubic-bezier(.2,0,.2,1) both;
    }
    .hero-title .highlight {
      background: linear-gradient(90deg,#f0c040,#52b788);
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;
      background-clip:text;
      position:relative;
    }
    .hero-subtitle {
      color: rgba(255,255,255,0.82) !important;
      font-family:'Outfit',sans-serif;
      font-size:1.08rem !important;
      animation: heroFadeUp 1s .2s cubic-bezier(.2,0,.2,1) both;
    }
    .hero-badge {
      animation: heroFadeUp 1s .05s cubic-bezier(.2,0,.2,1) both;
      background: var(--glass-bg) !important;
      backdrop-filter: var(--glass-blur) !important;
      border: 1px solid var(--glass-border) !important;
      color: #fff !important;
    }
    .hero-actions {
      animation: heroFadeUp 1s .35s cubic-bezier(.2,0,.2,1) both;
    }
    .hero-stats {
      animation: heroFadeUp 1s .5s cubic-bezier(.2,0,.2,1) both;
    }

    /* ── Floating particles ─────────────────────── */
    .hero-section .particles-canvas {
      position:absolute;inset:0;z-index:1;pointer-events:none;
    }

    /* ── Map preview glass card ─────────────────── */
    .hero-map-preview {
      animation: heroFadeUp 1s .25s cubic-bezier(.2,0,.2,1) both;
    }
    .hero-map-preview iframe {
      border-radius: 20px !important;
      box-shadow: 0 24px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1) !important;
    }

    @keyframes heroFadeUp {
      from { opacity:0; transform:translateY(32px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* ── SEARCH BAR glass ───────────────────────── */
    .search-hero-bar {
      background: #fff !important;
      backdrop-filter: none !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18) !important;
    }
    .search-hero-bar input { color: var(--text, #1a2e1a) !important; }
    .search-hero-bar input::placeholder { color: #9aab9a !important; }

    /* ── CATEGORY CARDS: glassmorphism ──────────── */
    .category-card {
      background: rgba(255,255,255,0.75) !important;
      backdrop-filter: blur(12px) !important;
      border: 1px solid rgba(255,255,255,0.6) !important;
      border-radius: 22px !important;
      box-shadow: 0 4px 20px rgba(27,67,50,0.08), 0 1px 4px rgba(0,0,0,0.04) !important;
      transition: transform .35s cubic-bezier(.2,0,.2,1), box-shadow .35s cubic-bezier(.2,0,.2,1), background .35s !important;
      cursor: pointer;
      overflow: hidden;
      position: relative;
    }
    .category-card::before {
      content:'';
      position:absolute;inset:0;
      background: linear-gradient(135deg, rgba(82,183,136,0.08) 0%, transparent 60%);
      opacity:0;transition:opacity .35s;border-radius:22px;
    }
    .category-card:hover {
      transform: translateY(-10px) scale(1.03) !important;
      box-shadow: 0 20px 60px rgba(27,67,50,0.18), 0 4px 12px rgba(0,0,0,0.08) !important;
      background: rgba(255,255,255,0.95) !important;
    }
    .category-card:hover::before { opacity:1; }
    .cat-icon {
      width: 68px !important; height: 68px !important;
      border-radius: 18px !important;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      transition: transform .35s cubic-bezier(.2,0,.2,1);
    }
    .category-card:hover .cat-icon { transform: scale(1.12) rotate(-4deg); }
    .cat-name {
      font-family:'Outfit',sans-serif !important;
      font-weight:600 !important;
      font-size:0.88rem !important;
      color: var(--green-mid) !important;
    }
    .cat-count {
      font-size:0.75rem !important;
      color: var(--green-light) !important;
      font-weight:600 !important;
    }

    /* ── FEATURED LISTING CARDS: glassmorphism ─── */
    .listing-card {
      border-radius: 20px !important;
      overflow: hidden;
      background: rgba(255,255,255,0.82) !important;
      backdrop-filter: blur(10px) !important;
      border: 1px solid rgba(255,255,255,0.55) !important;
      box-shadow: 0 4px 24px rgba(27,67,50,0.1), 0 1px 3px rgba(0,0,0,0.05) !important;
      transition: transform .4s cubic-bezier(.2,0,.2,1), box-shadow .4s !important;
    }
    .listing-card:hover {
      transform: translateY(-12px) !important;
      box-shadow: 0 28px 70px rgba(27,67,50,0.2), 0 6px 16px rgba(0,0,0,0.1) !important;
    }
    .listing-card-img {
      position:relative;overflow:hidden;height:220px;
    }
    .listing-card-img img {
      width:100%;height:100%;object-fit:cover;
      transition: transform .6s cubic-bezier(.2,0,.2,1) !important;
    }
    .listing-card:hover .listing-card-img img {
      transform: scale(1.08) !important;
    }
    .listing-card-img::after {
      content:'';position:absolute;inset:0;
      background:linear-gradient(to top, rgba(13,43,30,0.55) 0%, transparent 50%);
      pointer-events:none;
    }
    .listing-card-body {
      background: transparent !important;
    }
    .listing-card-title {
      font-family:'Cormorant Garamond',serif !important;
      font-size:1.22rem !important;
      font-weight:700 !important;
      color: var(--green-mid) !important;
    }
    .btn-card {
      background: linear-gradient(135deg, var(--green-mid), var(--green-light)) !important;
      color:#fff !important;
      border-radius:10px !important;
      font-weight:600 !important;
      transition:opacity .2s, transform .2s !important;
      display:inline-flex;align-items:center;gap:6px;
    }
    .btn-card:hover { opacity:.88; transform:translateX(3px); }

    /* ── EVENT CARDS ────────────────────────────── */
    .event-card {
      background: rgba(255,255,255,0.8) !important;
      backdrop-filter: blur(12px) !important;
      border: 1px solid rgba(255,255,255,0.55) !important;
      border-radius: 20px !important;
      box-shadow: 0 4px 20px rgba(27,67,50,0.09) !important;
      transition: transform .35s, box-shadow .35s !important;
    }
    .event-card:hover {
      transform: translateY(-8px) !important;
      box-shadow: 0 20px 50px rgba(27,67,50,0.16) !important;
    }

    /* ── STATS SECTION: glass tiles ─────────────── */
    .stats-section {
      background:
        linear-gradient(135deg, rgba(13,43,30,0.92) 0%, rgba(27,67,50,0.88) 100%),
        url('assets/images/8.jpg') center/cover no-repeat !important;
    }
    .stat-card {
      background: var(--glass-bg) !important;
      backdrop-filter: var(--glass-blur) !important;
      border: 1px solid var(--glass-border) !important;
      box-shadow: var(--glass-shadow) !important;
      border-radius:0 !important;
      transition: background .3s !important;
    }
    .stat-card:hover { background: rgba(255,255,255,0.2) !important; }
    .stat-icon { color: var(--gold-light) !important; }
    .stat-number { color: #fff !important; }
    .stat-title { color: rgba(255,255,255,0.75) !important; }

    /* ── NAVBAR: glassmorphism on scroll ─────────── */
    .navbar-main {
      background: rgba(13,43,30,0.55) !important;
      backdrop-filter: blur(20px) !important;
      border-bottom: 1px solid rgba(255,255,255,0.12) !important;
      transition: background .4s, box-shadow .4s !important;
    }
    .navbar-main.scrolled {
      background: rgba(13,43,30,0.92) !important;
      box-shadow: 0 4px 30px rgba(0,0,0,0.25) !important;
    }
    .brand-name, .brand-sub { color:#fff !important; }
    .nav-link-main { color:rgba(255,255,255,0.85) !important; }
    .nav-link-main:hover, .nav-link-main.active { color:#fff !important; }

    /* ── ABOUT SECTION image wrap ───────────────── */
    .about-img-wrap img {
      border-radius:24px !important;
      box-shadow:0 20px 60px rgba(0,0,0,0.2) !important;
      transition:transform .5s !important;
    }
    .about-img-wrap:hover img { transform:scale(1.02) rotate(0.5deg); }
    .about-highlight {
      background: rgba(255,255,255,0.88) !important;
      backdrop-filter: blur(12px) !important;
      border: 1px solid rgba(255,255,255,0.6) !important;
      border-radius: 16px !important;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important;
    }

    /* ── CONTACT FORM glass ──────────────────────── */
    .contact-form-wrap {
      background: rgba(255,255,255,0.85) !important;
      backdrop-filter: blur(16px) !important;
      border: 1px solid rgba(255,255,255,0.6) !important;
      border-radius:24px !important;
      box-shadow: 0 8px 40px rgba(27,67,50,0.12) !important;
    }
    .contact-info-card {
      background: linear-gradient(160deg, rgba(13,43,30,0.9), rgba(27,67,50,0.85)) !important;
      backdrop-filter:blur(12px) !important;
      border: 1px solid rgba(255,255,255,0.15) !important;
      border-radius:24px !important;
      box-shadow: 0 8px 40px rgba(0,0,0,0.18) !important;
    }

    /* ── Section backgrounds — nature photo parallax ── */

    /* Categories: soft aerial rice-field overhead */
    .categories-section {
      background:
        linear-gradient(180deg, rgba(240,247,242,0.92) 0%, rgba(220,240,228,0.88) 100%),
        url('assets/images/6.JPG') center/cover no-repeat fixed !important;
      position: relative;
    }

    /* Featured: tropical forest canopy, dark overlay so cards pop */
    #featured {
      background:
        linear-gradient(160deg, rgba(10,30,18,0.78) 0%, rgba(22,55,38,0.72) 50%, rgba(40,90,60,0.68) 100%),
        url('assets/images/12.JPG') center/cover no-repeat fixed !important;
    }
    /* Section title & subtitle contrast fix for dark bg */
    #featured .section-title { color: #fff !important; text-shadow: 0 2px 12px rgba(0,0,0,0.4); }
    #featured .section-label { color: var(--gold-light, #f0c040) !important; }
    #featured .section-subtitle { color: rgba(255,255,255,0.75) !important; }
    #featured .btn-outline-main {
      color:#fff !important; border-color:rgba(255,255,255,0.45) !important;
    }
    #featured .btn-outline-main:hover { background:rgba(255,255,255,0.12) !important; }

    /* Events: golden hour beach / water */
    .events-section {
      background:
        linear-gradient(160deg, rgba(10,25,18,0.80) 0%, rgba(18,48,32,0.75) 55%, rgba(35,75,50,0.70) 100%),
        url('assets/images/8.jpg') center/cover no-repeat fixed !important;
    }
    .events-section .section-title { color:#fff !important; text-shadow:0 2px 12px rgba(0,0,0,0.35); }
    .events-section .section-label { color: var(--gold-light, #f0c040) !important; }
    .events-section .section-subtitle { color:rgba(255,255,255,0.72) !important; }

    /* About: light — rice fields / pastoral */
    .about-strip {
      background:
        linear-gradient(160deg, rgba(248,253,248,0.94) 0%, rgba(230,245,235,0.90) 100%),
        url('assets/images/9.JPG') center/cover no-repeat fixed !important;
    }

    /* Contact: deep dusk forest */
    .contact-section {
      background:
        linear-gradient(160deg, rgba(8,22,14,0.85) 0%, rgba(18,48,32,0.80) 55%, rgba(30,65,45,0.78) 100%),
        url('assets/images/10.JPG') center 30%/cover no-repeat fixed !important;
    }
    .contact-section .section-title { color:#fff !important; text-shadow:0 2px 12px rgba(0,0,0,0.4); }
    .contact-section .section-label { color: var(--gold-light, #f0c040) !important; }
    .contact-section .section-subtitle { color:rgba(255,255,255,0.72) !important; }

    .section-floating-shapes { display:none !important; }

    /* ── Divider fades between sections ─────────────── */
    #featured::before {
      content:''; position:absolute; top:0; left:0; right:0; height:80px;
      background: linear-gradient(to bottom, var(--off-white,transparent), transparent);
      z-index:1; pointer-events:none;
    }
    #featured::after {
      content:''; position:absolute; bottom:0; left:0; right:0; height:80px;
      background: linear-gradient(to top, var(--off-white,#f8faf8), transparent);
      z-index:1; pointer-events:none;
    }
    #featured .container { position:relative; z-index:2; }

    .events-section::before {
      content:''; position:absolute; top:0; left:0; right:0; height:80px;
      background: linear-gradient(to bottom, var(--off-white,#f8faf8), transparent);
      z-index:1; pointer-events:none;
    }
    .events-section::after {
      content:''; position:absolute; bottom:0; left:0; right:0; height:80px;
      background: linear-gradient(to top, var(--off-white,#f8faf8), transparent);
      z-index:1; pointer-events:none;
    }
    .events-section .container { position:relative; z-index:2; }

    .contact-section::before {
      content:''; position:absolute; top:0; left:0; right:0; height:80px;
      background: linear-gradient(to bottom, var(--off-white,#f8faf8), transparent);
      z-index:1; pointer-events:none;
    }
    .contact-section .container { position:relative; z-index:2; }

    /* ── Products section: agricultural parallax ──── */
    .products-section {
      background:
        linear-gradient(160deg, rgba(13,43,30,0.88) 0%, rgba(27,67,50,0.82) 50%, rgba(64,145,108,0.65) 100%),
        url('assets/images/11.JPG') center/cover no-repeat fixed !important;
      position: relative;
      overflow: hidden;
    }
    .products-section::before {
      content:''; position:absolute; top:0; left:0; right:0; height:80px;
      background: linear-gradient(to bottom, var(--off-white,#f8faf8), transparent);
      z-index:1; pointer-events:none;
    }
    .products-section::after {
      content:''; position:absolute; bottom:0; left:0; right:0; height:80px;
      background: linear-gradient(to top, var(--off-white,#f8faf8), transparent);
      z-index:1; pointer-events:none;
    }
    .products-section .container { position:relative; z-index:2; }
    .products-section .section-title { color: #fff !important; text-shadow: 0 2px 12px rgba(0,0,0,0.4); }
    .products-section .section-label { color: var(--gold-light, #f0c040) !important; }
    .products-section .section-subtitle { color: rgba(255,255,255,0.75) !important; }

    /* ── Map section background ─────────────────────── */
    .map-section {
      background:
        linear-gradient(160deg, rgba(240,247,242,0.93) 0%, rgba(220,238,228,0.90) 100%),
        url('assets/images/12.JPG') center/cover no-repeat fixed !important;
      position: relative;
    }
    .map-section .container { position:relative; z-index:2; }

    /* ══════════════════════════════════════════════
       HERO SLIDESHOW — 3-D fan style (Agcararao-inspired)
    ══════════════════════════════════════════════ */

    .hero-slider-wrap {
      perspective: 1200px;
      position: relative;
      height: 480px;
      max-width: 600px;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: heroFadeUp 1s .25s cubic-bezier(.2,0,.2,1) both;
    }

    /* Stage holds all slides and drives the 3-D transform */
    .hs-stage {
      position: relative;
      width: 100%;
      height: 100%;
    }

    /* Each slide */
    .hs-slide {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: var(--bg) center/cover no-repeat;
      border-radius: var(--radius-lg);
      overflow: hidden;
      transition: transform .65s cubic-bezier(.4,0,.2,1),
                  opacity  .65s cubic-bezier(.4,0,.2,1),
                  filter   .65s cubic-bezier(.4,0,.2,1),
                  z-index    0s  .1s;
      will-change: transform, opacity;
      cursor: pointer;
    }

    /* Dark vignette on every slide */
    .hs-slide::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(160deg,
        rgba(8,22,12,0.18) 0%,
        rgba(8,22,12,0.52) 100%);
      border-radius: var(--radius-lg);
      pointer-events: none;
    }

    /* ── Positions: centre, left-1, right-1, left-2, right-2 ── */
    /* Active (centre) */
    .hs-slide[data-pos="0"] {
      z-index: 5;
      transform: translateX(0) scale(1) rotateY(0deg);
      opacity: 1;
      filter: none;
      box-shadow: var(--shadow-xl), 0 0 0 2px rgba(255,255,255,0.12);
    }
    /* One left */
    .hs-slide[data-pos="-1"] {
      z-index: 4;
      transform: translateX(-52%) scale(0.82) rotateY(14deg);
      opacity: 0.72;
      filter: brightness(0.7);
    }
    /* One right */
    .hs-slide[data-pos="1"] {
      z-index: 4;
      transform: translateX(52%) scale(0.82) rotateY(-14deg);
      opacity: 0.72;
      filter: brightness(0.7);
    }
    /* Two left */
    .hs-slide[data-pos="-2"] {
      z-index: 3;
      transform: translateX(-82%) scale(0.65) rotateY(22deg);
      opacity: 0.38;
      filter: brightness(0.5);
    }
    /* Two right */
    .hs-slide[data-pos="2"] {
      z-index: 3;
      transform: translateX(82%) scale(0.65) rotateY(-22deg);
      opacity: 0.38;
      filter: brightness(0.5);
    }
    /* Hidden */
    .hs-slide[data-pos="hidden"] {
      z-index: 1;
      opacity: 0;
      transform: translateX(0) scale(0.5);
      pointer-events: none;
    }

    /* ── Prev / Next buttons ── */
    .hs-btn {
      position: absolute;
      top: 50%; transform: translateY(-50%);
      z-index: 10;
      width: 42px; height: 42px;
      border-radius: 50%;
      border: 1px solid var(--glass-border);
      background: var(--glass-bg);
      backdrop-filter: var(--glass-blur);
      color: #fff;
      font-size: 0.9rem;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition);
      box-shadow: var(--glass-shadow);
    }
    .hs-btn:hover {
      background: rgba(255,255,255,0.28);
      transform: translateY(-50%) scale(1.1);
    }
    .hs-prev { left: 10px; }
    .hs-next { right: 10px; }

    /* ── Dot indicators ── */
    .hs-dots {
      position: absolute;
      bottom: 14px; left: 50%; transform: translateX(-50%);
      z-index: 10;
      display: flex; gap: 7px; align-items: center;
    }
    .hs-dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: rgba(255,255,255,0.45);
      cursor: pointer;
      transition: var(--transition);
      border: none;
      padding: 0;
    }
    .hs-dot.active {
      background: #fff;
      width: 22px;
      border-radius: 4px;
    }

    /* ── Caption badge ── */
    .hs-caption {
      position: absolute;
      top: 16px; left: 50%; transform: translateX(-50%);
      z-index: 10;
      background: var(--glass-bg);
      backdrop-filter: var(--glass-blur);
      border: 1px solid var(--glass-border);
      color: #fff;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      padding: 5px 14px;
      border-radius: 100px;
      white-space: nowrap;
    }

    /* Responsive — flatten on small screens */
    @media (max-width: 768px) {
      .hero-slider-wrap { height: 320px; }
      .hs-slide[data-pos="-1"],
      .hs-slide[data-pos="1"]  { opacity: 0.55; transform: translateX(±48%) scale(0.78); }
      .hs-slide[data-pos="-2"],
      .hs-slide[data-pos="2"]  { opacity: 0; pointer-events:none; }
    }

    /* ── Scroll-reveal base ──────────────────────── */
    .animate-on-scroll {
      opacity:0;transform:translateY(28px);
      transition:opacity .7s cubic-bezier(.2,0,.2,1),transform .7s cubic-bezier(.2,0,.2,1);
    }
    .animate-on-scroll.visible {
      opacity:1;transform:none;
    }
    .animate-on-scroll.delay-1 { transition-delay:.1s; }
    .animate-on-scroll.delay-2 { transition-delay:.2s; }
    .animate-on-scroll.delay-3 { transition-delay:.3s; }
    .animate-on-scroll.delay-4 { transition-delay:.4s; }

    /* ── Scroll indicator pulse ──────────────────── */
    .scroll-indicator {
      animation: bounce 2s infinite !important;
      color:rgba(255,255,255,.7) !important;
    }
    @keyframes bounce {
      0%,100%{transform:translateX(-50%) translateY(0);}
      50%{transform:translateX(-50%) translateY(8px);}
    }
  </style>
</head>

<body>

  <!-- ═══════════════════════════════════════════
     PAGE LOADER
═══════════════════════════════════════════ -->
<div id="pageLoader" class="page-loader">

  <!-- Stars -->
  <div class="loader-stars" id="loaderStars"></div>

  <!-- Floating clouds -->
  <div class="loader-cloud c1"></div>
  <div class="loader-cloud c2"></div>
  <div class="loader-cloud c3"></div>

  <!-- Rising sun glow -->
  <div class="loader-sun"></div>

  <!-- Mountain silhouettes -->
  <div class="loader-mountains">
    <svg class="mountain-svg" viewBox="0 0 1440 320" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <!-- Back mountains (lighter) -->
      <path d="M0,320 L0,200 L120,120 L240,180 L360,80 L480,160 L560,60 L640,140 L720,40 L800,130 L900,70 L1000,160 L1100,50 L1200,140 L1320,90 L1440,160 L1440,320 Z"
            fill="rgba(27,67,50,0.55)"/>
      <!-- Mid mountains -->
      <path d="M0,320 L0,240 L80,190 L180,250 L280,160 L400,220 L500,140 L600,210 L700,120 L820,200 L920,150 L1020,220 L1140,140 L1260,200 L1360,160 L1440,200 L1440,320 Z"
            fill="rgba(13,43,30,0.75)"/>
      <!-- Front hills -->
      <path d="M0,320 L0,280 L100,250 L220,290 L340,240 L460,280 L560,250 L680,285 L780,248 L900,280 L1020,252 L1160,280 L1280,255 L1440,270 L1440,320 Z"
            fill="rgba(8,22,12,0.92)"/>
      <!-- Rice field terraces hint -->
      <path d="M0,320 L0,305 L360,295 L720,300 L1080,295 L1440,305 L1440,320 Z"
            fill="rgba(4,14,8,0.98)"/>
    </svg>
  </div>

  <!-- Waves -->
  <div class="loader-waves">
    <div class="wave wave-1"></div>
    <div class="wave wave-2"></div>
    <div class="wave wave-3"></div>
  </div>

  <!-- Center content -->
  <div class="loader-content">
    <!-- <div class="loader-emblem">🌿</div> -->
     <img src="assets/images/san-enrique-logo.jpg" alt="San Enrique" class="loader-emblem">

    <div class="loader-site-name">San <span>Enrique</span></div>
    <div class="loader-tagline">Tourism Hub &nbsp;·&nbsp; Iloilo</div>

    <div class="loader-divider"></div>
    <div class="loader-welcome">Discover the hidden paradise awaiting you</div>

    <div class="loader-progress-wrap">
      <div class="loader-dots">
        <div class="loader-dot"></div>
        <div class="loader-dot"></div>
        <div class="loader-dot"></div>
        <div class="loader-dot"></div>
        <div class="loader-dot"></div>
      </div>
      <div class="loader-bar">
        <div class="loader-bar-fill"></div>
      </div>
      <div class="loader-status">Loading experience&hellip;</div>
    </div>
  </div>

</div>


  <!-- BACK TO TOP -->
  <button id="backToTop" class="back-to-top" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
  </button>

  <!-- NAVBAR -->
  <nav class="navbar-main">
    <!-- <div class="container">
      <div class="d-flex align-items-center justify-content-between w-100">
        <a href="index.php" class="navbar-brand-wrap text-decoration-none">
          <img src="assets/images/logo-tourism.svg" alt="San Enrique" class="navbar-brand-logo-img">
          <div class="brand-text-wrap">
            <div class="brand-name">San Enrique</div>
            <div class="brand-sub">Tourism Hub</div>
          </div> -->
          <div class="container">
      <div class="d-flex align-items-center justify-content-between w-100">
        <a href="index.php" class="navbar-brand-wrap text-decoration-none">
          <!-- <div class="brand-logo"> -->
            <img src="assets/images/san-enrique-logo.jpg" alt="San Enrique" class="navbar-brand-logo-img">
          <!-- </div> -->
          <div class="brand-text-wrap">
            <div class="brand-name">San Enrique</div>
            <div class="brand-sub">Tourism Hub</div>
          </div>
        </a>
        <!-- Desktop Nav -->
        <div class="d-none d-lg-flex align-items-center gap-1">
          <a href="#home" class="nav-link-main active">Home</a>
          <a href="#categories" class="nav-link-main">Explore</a>
          <!-- <a href="map.php" class="nav-link-main">Map</a> -->
          <a href="#events" class="nav-link-main">Events</a>
          <a href="#about" class="nav-link-main">About</a>
          <a href="#contact" class="nav-link-main">Contact</a>
          <!-- <a href="admin/login.php" class="btn-nav-admin ms-3">
          <i class="fas fa-shield-alt me-1"></i> Admin
        </a> -->
        </div>
        <!-- Mobile Toggle -->
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <!-- Mobile Nav -->
      <div class="collapse" id="mobileNav">
        <div class="d-flex flex-column gap-1 py-2">
          <a href="#home" class="nav-link-main">Home</a>
          <a href="#categories" class="nav-link-main">Explore</a>
          <!-- <a href="map.php" class="nav-link-main">Map</a> -->
          <a href="#events" class="nav-link-main">Events</a>
          <a href="#about" class="nav-link-main">About</a>
          <a href="#contact" class="nav-link-main">Contact</a>
          <!-- <a href="admin/login.php" class="btn-nav-admin mt-2 text-center" style="max-width:140px;">
            <i class="fas fa-shield-alt me-1"></i> Admin
          </a> -->
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section id="home" class="hero-section">
    <div class="hero-bg-pattern"></div>
    <div class="hero-particles"></div>
    <canvas class="particles-canvas" id="heroParticles"></canvas>
    
    <!-- Decorative floating shapes -->
    <div class="hero-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>

    <div class="container-fluid position-relative px-lg-4" style="z-index:2;padding-top:20px;">
      <div class="row align-items-center g-4">
        <div class="col-lg-6 ps-lg-5">
          <div class="hero-content">
            <div class="hero-badge">
              <i class="fas fa-map-marker-alt"></i>
              <?= MUNICIPALITY ?>
            </div>
            <h1 class="hero-title">
              Discover the<br>
              <span class="highlight">Hidden Paradise</span><br>
              of San Enrique
            </h1>
            <p class="hero-subtitle">
              Your all-in-one guide to resorts, cultural heritage, local cuisine, agri-tourism, and vibrant barangays.
              Explore San Enrique like never before.
            </p>

            <!-- Search Bar -->
            <form id="heroSearchForm" class="search-hero-bar mb-4">
              <span class="search-icon"><i class="fas fa-search"></i></span>
              <input type="text" id="heroSearch" placeholder="Search resorts, places, food..." autocomplete="off">
              <button type="submit"><i class="fas fa-arrow-right"></i> Explore</button>
            </form>

            <div class="hero-actions">
              <a href="explore.php" class="btn-hero-primary">
                <i class="fas fa-compass"></i> Explore Destinations
              </a>
              <a href="map.php" class="btn-hero-secondary">
                <i class="fas fa-map"></i> View Map
              </a>
            </div>

            <div class="hero-stats">
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['listings'] ?>"><?= $stats['listings'] ?></span>+</div>
                <div class="stat-label">Destinations</div>
              </div>
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['categories'] ?>"><?= $stats['categories'] ?></span></div>
                <div class="stat-label">Categories</div>
              </div>
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['barangays'] ?>"><?= $stats['barangays'] ?></span>+</div>
                <div class="stat-label">Barangays</div>
              </div>
              <div class="hero-stat">
                <div class="stat-num"><span class="count-up"
                    data-target="<?= $stats['events'] ?>"><?= $stats['events'] ?></span></div>
                <div class="stat-label">Events</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="hero-slider-wrap">

            <!-- ═══ HERO SLIDESHOW ═══ -->
            <div class="hs-stage" id="hsStage">

              <!-- Slides — beautiful San Enrique-style nature photos -->
              <div class="hs-slide" style="--bg:url('assets/images/1.jpg')"></div>
              <div class="hs-slide" style="--bg:url('assets/images/2.jpg')"></div>
              <div class="hs-slide" style="--bg:url('assets/images/3.jpg')"></div>
              <div class="hs-slide" style="--bg:url('assets/images/4.jpg')"></div>
              <div class="hs-slide" style="--bg:url('assets/images/5.JPG')"></div>

              <!-- Prev / Next -->
              <button class="hs-btn hs-prev" onclick="hsPrev()" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button class="hs-btn hs-next" onclick="hsNext()" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
              </button>

              <!-- Dot indicators -->
              <div class="hs-dots" id="hsDots"></div>

              <!-- Caption badge -->
              <div class="hs-caption">
                <i class="fas fa-map-marker-alt me-1"></i> San Enrique, Iloilo
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="scroll-indicator">
      <span style="font-size:0.7rem;letter-spacing:0.1em;">SCROLL</span>
      <i class="fas fa-chevron-down"></i>
    </div>
  </section>

  <!-- CATEGORIES SECTION -->
  <section id="categories" class="categories-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="text-center mb-5 animate-on-scroll">
        <div class="section-label justify-content-center"><span></span>Browse by Category<span></span></div>
        <h2 class="section-title">What Would You Like to Explore?</h2>
        <p class="section-subtitle mx-auto">From pristine beaches to heritage sites, discover everything San Enrique has
          to offer.</p>
      </div>
      <div class="row g-4 align-items-stretch">
        <?php foreach ($categories as $i => $cat): ?>
          <div class="col-6 col-md-4 col-lg-2 animate-on-scroll delay-<?= ($i % 4) + 1 ?>">
            <div class="category-card" data-slug="<?= htmlspecialchars($cat['slug']) ?>" role="button"
              style="height:100%;min-height:170px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
              <div class="cat-icon"
                style="background: linear-gradient(135deg, <?= htmlspecialchars($cat['color']) ?>, <?= htmlspecialchars($cat['color']) ?>88);flex-shrink:0;">
                <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
              </div>
              <div class="cat-name" style="min-height:2.8em;display:flex;align-items:center;justify-content:center;">
                <?= htmlspecialchars($cat['name']) ?></div>
              <div class="cat-count">Explore &rarr;</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FEATURED LISTINGS -->
  <section id="featured" style="position:relative;overflow:hidden;">    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="d-flex align-items-end justify-content-between mb-5">
        <div class="animate-on-scroll">
          <div class="section-label">Featured Destinations</div>
          <h2 class="section-title mb-1">Must-Visit Places</h2>
          <p class="section-subtitle">Handpicked favorites by the San Enrique LGU</p>
        </div>
        <a href="explore.php" class="btn-outline-main d-none d-md-inline-flex animate-on-scroll">
          View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="row g-4" id="featuredGrid">
        <?php foreach ($featured as $i => $listing): ?>
          <div class="col-md-6 col-lg-4 animate-on-scroll delay-<?= ($i % 4) + 1 ?>">
            <div class="listing-card">
              <div class="listing-card-img">
                <img src="<?= htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 600, 400)) ?>"
                  alt="<?= htmlspecialchars($listing['name']) ?>" loading="lazy"
                  onerror="this.src='https://placehold.co/600x400/1b4332/ffffff?text=<?= urlencode($listing['name']) ?>'">
                <div class="listing-badge" style="color:<?= htmlspecialchars($listing['color']) ?>">
                  <i class="<?= htmlspecialchars($listing['icon']) ?>"></i>
                  <?= htmlspecialchars($listing['category_name']) ?>
                </div>
                <?php if ($listing['is_featured']): ?>
                  <div class="featured-badge">★ Featured</div>
                <?php endif; ?>
              </div>
              <div class="listing-card-body">
                <h3 class="listing-card-title"><?= htmlspecialchars($listing['name']) ?></h3>
                <p class="listing-card-desc"><?= htmlspecialchars($listing['description']) ?></p>
                <div class="listing-card-meta">
                  <?php if ($listing['barangay']): ?>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($listing['barangay']) ?></span>
                  <?php endif; ?>
                  <?php if ($listing['entrance_fee']): ?>
                    <span><i class="fas fa-ticket-alt"></i> <?= htmlspecialchars($listing['entrance_fee']) ?></span>
                  <?php endif; ?>
                </div>
                <a href="listing.php?slug=<?= urlencode($listing['slug']) ?>" class="btn-card">
                  View Details <i class="fas fa-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-4 d-md-none">
        <a href="explore.php" class="btn-primary-main">View All Destinations <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </section>

  <!-- MAP TEASER SECTION -->
  <section id="map-teaser" class="map-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="row align-items-center g-5 mb-4">
        <div class="col-lg-6 animate-on-scroll">
          <div class="section-label">Interactive Map</div>
          <h2 class="section-title">Find Any Destination<br>with Ease</h2>
          <p class="section-subtitle mb-3">
            Our Google Maps integration pins every resort, barangay, cultural site, and food spot. Filter by category
            and get real-time directions in one click.
          </p>
          <div class="map-filter-pills">
            <span class="filter-pill active" data-category="all">
              <i class="fas fa-map"></i> All
            </span>
            <?php foreach ($categories as $cat): ?>
              <span class="filter-pill" data-category="<?= htmlspecialchars($cat['slug']) ?>">
                <i class="<?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
              </span>
            <?php endforeach; ?>
          </div>
          <a href="map.php" class="btn-primary-main mt-2">
            <i class="fas fa-map-marked-alt me-1"></i> Open Full Map
          </a>
        </div>
        <div class="col-lg-6 animate-on-scroll delay-2">
          <div id="interactiveMap"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- EVENTS SECTION -->
  <section id="events" class="events-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="d-flex align-items-end justify-content-between mb-5">
        <div class="animate-on-scroll">
          <div class="section-label">Upcoming Events</div>
          <h2 class="section-title mb-1">What's Happening in San Enrique</h2>
          <p class="section-subtitle">Join the celebrations and community events</p>
        </div>
      </div>
      <div class="row g-4" id="eventsGrid">
        <?php if ($events): ?>
          <?php foreach ($events as $i => $event): ?>
            <div class="col-md-4 animate-on-scroll delay-<?= $i + 1 ?>">
              <div class="event-card">
                <div class="event-date-badge">
                  <i class="fas fa-calendar-alt"></i>
                  <?= date('F j, Y', strtotime($event['event_date'])) ?>
                  <?php if ($event['end_date'] && $event['end_date'] !== $event['event_date']): ?>
                    – <?= date('F j', strtotime($event['end_date'])) ?>
                  <?php endif; ?>
                </div>
                <h4 class="event-title"><?= htmlspecialchars($event['title']) ?></h4>
                <p class="event-desc"><?= htmlspecialchars(substr($event['description'], 0, 120)) ?>...</p>
                <?php if ($event['location']): ?>
                  <div class="event-location">
                    <i class="fas fa-map-pin"></i> <?= htmlspecialchars($event['location']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="empty-state">
              <i class="fas fa-calendar-times"></i>
              <p>No upcoming events at the moment. Check back soon!</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- STATS SECTION -->
  <section class="stats-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="row g-0">
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll">
            <i class="fas fa-umbrella-beach stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statListings"
                data-target="<?= $stats['listings'] ?>"><?= $stats['listings'] ?></span>+</div>
            <div class="stat-title">Listed Destinations</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll delay-1">
            <i class="fas fa-home stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statBarangays"
                data-target="<?= $stats['barangays'] ?>"><?= $stats['barangays'] ?></span></div>
            <div class="stat-title">Barangays</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll delay-2">
            <i class="fas fa-calendar-alt stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statEvents"
                data-target="<?= $stats['events'] ?>"><?= $stats['events'] ?></span></div>
            <div class="stat-title">Annual Events</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card animate-on-scroll delay-3">
            <i class="fas fa-th-large stat-icon"></i>
            <div class="stat-number"><span class="count-up" id="statCategories"
                data-target="<?= $stats['categories'] ?>"><?= $stats['categories'] ?></span></div>
            <div class="stat-title">Categories</div>
          </div>
        </div>
      </div>
    </div>
  </section>

    <!-- ABOUT SECTION -->
  <section id="about" class="about-strip" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">

      <!-- Section Header -->
      <div class="text-center mb-5 animate-on-scroll">
        <div class="section-label justify-content-center"><span></span>Our Story<span></span></div>
        <h2 class="section-title">About San Enrique</h2>
        <p class="section-subtitle mx-auto">A hidden gem nestled in the heart of Iloilo Province — where nature, culture, and community thrive together.</p>
      </div>

      <!-- Main content row -->
      <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-5 animate-on-scroll">
          <div class="about-img-wrap">
            <img src="assets/images/San Enrique LGU.png" alt="San Enrique Municipal Hall" loading="lazy">
            <div class="about-highlight">
              <div class="about-highlight-label">Official Record</div>
              <div class="about-highlight-title">Sangguniang Bayan<br>Resolution No. 2006-53</div>
              <div class="about-highlight-sub">Official Municipal History, April 19, 2006</div>
            </div>
          </div>
        </div>
        <div class="col-lg-7 animate-on-scroll delay-2">
          <div class="about-history-card">
            <div class="about-history-icon"><i class="fas fa-scroll"></i></div>
            <h3 class="about-history-title">History &amp; Heritage</h3>
            <p class="about-history-text">
              San Enrique is a 3rd-class municipality in the province of Iloilo, Philippines, situated in the central
              part of the island of Panay. Named after Saint Enrique, the municipality is home to <strong>28 barangays</strong>
              spread across its fertile plains and rolling hills.
            </p>
            <p class="about-history-text">
              With a rich history documented in the official municipal history by Rodrigo P. Ponte and formally adopted
              through <em>Sangguniang Bayan Resolution No. 2006-53</em> on April 19, 2006, San Enrique stands proud as a
              community deeply rooted in Ilonggo tradition, faith, and agricultural heritage.
            </p>
            <p class="about-history-text">
              The municipality thrives on agriculture — with sugarcane, rice, and corn among its primary crops — while
              also nurturing a growing agri-tourism sector, cold springs, river sanctuaries, and vibrant cultural sites
              that attract visitors from across the region.
            </p>
            <div class="about-source">
              <i class="fas fa-book-open me-2"></i>
              Source: <a href="https://iloiloelibrary.com/items/show/1208" target="_blank" rel="noopener">
                Iloilo Province eLibrary — Municipal History of San Enrique
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Facts Bar -->
      <div class="row g-3 mb-5">
        <div class="col-6 col-md-3 animate-on-scroll delay-1">
          <div class="about-fact-card">
            <div class="about-fact-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="about-fact-value">3rd Class</div>
            <div class="about-fact-label">Municipality</div>
          </div>
        </div>
        <div class="col-6 col-md-3 animate-on-scroll delay-2">
          <div class="about-fact-card">
            <div class="about-fact-icon"><i class="fas fa-home"></i></div>
            <div class="about-fact-value">28</div>
            <div class="about-fact-label">Barangays</div>
          </div>
        </div>
        <div class="col-6 col-md-3 animate-on-scroll delay-3">
          <div class="about-fact-card">
            <div class="about-fact-icon"><i class="fas fa-seedling"></i></div>
            <div class="about-fact-value">Agri</div>
            <div class="about-fact-label">Tourism Hub</div>
          </div>
        </div>
        <div class="col-6 col-md-3 animate-on-scroll delay-4">
          <div class="about-fact-card">
            <div class="about-fact-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="about-fact-value">2006</div>
            <div class="about-fact-label">History Adopted</div>
          </div>
        </div>
      </div>

      <!-- Feature highlights -->
      <div class="row g-4 animate-on-scroll delay-2">
        <div class="col-md-4">
          <div class="about-feature-card">
            <div class="about-feature-icon" style="background:linear-gradient(135deg,#40916c,#52b788);">
              <i class="fas fa-water"></i>
            </div>
            <h4 class="about-feature-title">Natural Wonders</h4>
            <p class="about-feature-desc">
              Crystal-clear cold springs, scenic rivers, and lush farmscapes await adventurers and nature lovers.
              From Cabas-an Cold Spring to the San Antonio River, nature is always close.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="about-feature-card">
            <div class="about-feature-icon" style="background:linear-gradient(135deg,#b7791f,#d4a017);">
              <i class="fas fa-landmark"></i>
            </div>
            <h4 class="about-feature-title">Cultural Heritage</h4>
            <p class="about-feature-desc">
              Ancient simboryos, historic churches, and native delicacies like Kalamay Hati and Bibingka tell
              the story of a people proud of their Ilonggo roots and traditions.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="about-feature-card">
            <div class="about-feature-icon" style="background:linear-gradient(135deg,#2d6a4f,#40916c);">
              <i class="fas fa-tractor"></i>
            </div>
            <h4 class="about-feature-title">Agri-Tourism</h4>
            <p class="about-feature-desc">
              Thriving farms like Gumbans Amat Amat Farm and BC Farm open their gates to visitors, offering
              fresh produce, farm life experiences, and a taste of rural Iloilo.
            </p>
          </div>
        </div>
      </div>

      <div class="text-center mt-5 animate-on-scroll">
        <a href="explore.php" class="btn-primary-main me-3">
          <i class="fas fa-compass me-1"></i> Explore Destinations
        </a>
        <a href="map.php" class="btn-outline-main">
          <i class="fas fa-map me-1"></i> View on Map
        </a>
      </div>

    </div>
  </section>

  <!-- LOCAL PRODUCTS SECTION -->
  <!-- <section id="products" class="products-section" style="position:relative;overflow:hidden;">
    <div class="container" style="position:relative;z-index:2;">
      <div class="text-center mb-4 animate-on-scroll">
        <div class="section-label justify-content-center"><span></span>Farm to Market<span></span></div>
        <h2 class="section-title">Local Products &amp; Industries</h2>
        <p class="section-subtitle mx-auto">San Enrique's fertile lands and hardworking people produce a rich variety of agricultural goods, handicrafts, and emerging industries.</p>
      </div> -->

      <!-- Filter tabs -->
      <!-- <div class="products-filter animate-on-scroll delay-1">
        <button class="products-filter-btn active" data-filter="all"><i class="fas fa-th-large"></i> All</button>
        <button class="products-filter-btn" data-filter="crops"><i class="fas fa-seedling"></i> Crops</button>
        <button class="products-filter-btn" data-filter="crafts"><i class="fas fa-hands"></i> Handicrafts</button>
        <button class="products-filter-btn" data-filter="industry"><i class="fas fa-industry"></i> Industries</button>
      </div> -->

      <!-- Products grid -->
      <!-- <div class="products-grid">
        <div class="product-card animate-on-scroll delay-1" data-category="crops">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1560493676-04071c5f467b?w=600&q=80" alt="Sugarcane" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Primary Crop</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-candy-cane"></i></div>
            <h3 class="product-card-title">Sugarcane</h3>
            <p class="product-card-desc">A cornerstone crop in San Enrique. Seasonal workers join the planting and harvesting seasons, fueling the local economy.</p>
            <div class="product-card-tags"><span><i class="fas fa-calendar-alt me-1"></i>Seasonal</span><span><i class="fas fa-map-marker-alt me-1"></i>Municipality-wide</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-2" data-category="crops">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1536304993881-460e27c6fc23?w=600&q=80" alt="Rice Paddy" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Staple Crop</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-leaf"></i></div>
            <h3 class="product-card-title">Rice (Palay)</h3>
            <p class="product-card-desc">One of the primary crops cultivated across the municipality. Local rice mills support production from field to table.</p>
            <div class="product-card-tags"><span><i class="fas fa-warehouse me-1"></i>Rice Mills</span><span><i class="fas fa-utensils me-1"></i>Staple Food</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-3" data-category="crops">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=600&q=80" alt="Corn" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Major Crop</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-spa"></i></div>
            <h3 class="product-card-title">Corn</h3>
            <p class="product-card-desc">Another major agricultural product grown in San Enrique, serving both as food and essential livestock feed.</p>
            <div class="product-card-tags"><span><i class="fas fa-drumstick-bite me-1"></i>Food &amp; Feed</span><span><i class="fas fa-tractor me-1"></i>Wide Farming</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-1" data-category="crops">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1580984969071-a8da8c45583a?w=600&q=80" alt="Coconut" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Multi-use</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-tree"></i></div>
            <h3 class="product-card-title">Coconut</h3>
            <p class="product-card-desc">Used for coconut oil and by-products. Strong potential for coco oil processing industries and value-added products.</p>
            <div class="product-card-tags"><span><i class="fas fa-oil-can me-1"></i>Coco Oil</span><span><i class="fas fa-flask me-1"></i>Processing</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-2" data-category="crops">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1590165482129-1b8b27698780?w=600&q=80" alt="Root Crops" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Root Crops</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-carrot"></i></div>
            <h3 class="product-card-title">Cassava &amp; Camote</h3>
            <p class="product-card-desc">Locally grown root crops with versatile uses. Cassava has potential for ethanol production and food processing.</p>
            <div class="product-card-tags"><span><i class="fas fa-recycle me-1"></i>Ethanol Potential</span><span><i class="fas fa-home me-1"></i>Local Staple</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-3" data-category="crafts">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1590736969955-71cc94901144?w=600&q=80" alt="Handicrafts" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Aeta Heritage</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-hand-holding-heart"></i></div>
            <h3 class="product-card-title">Handicrafts</h3>
            <p class="product-card-desc">Crafted by the Aeta community — intricate baskets, woven mats, and cottage industry products embodying traditional artistry.</p>
            <div class="product-card-tags"><span><i class="fas fa-shopping-basket me-1"></i>Baskets</span><span><i class="fas fa-border-all me-1"></i>Mat Weaving</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-1" data-category="industry">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=600&q=80" alt="Livestock" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Emerging</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-piggy-bank"></i></div>
            <h3 class="product-card-title">Livestock Production</h3>
            <p class="product-card-desc">Swine production projects are actively being developed, supporting local livelihoods and the agricultural economy.</p>
            <div class="product-card-tags"><span><i class="fas fa-chart-line me-1"></i>Growing</span><span><i class="fas fa-handshake me-1"></i>LGU Supported</span></div>
          </div>
        </div> -->

        <!-- <div class="product-card animate-on-scroll delay-2" data-category="industry">
          <div class="product-card-img">
            <img src="https://images.unsplash.com/photo-1534483509719-8127d8d15df6?w=600&q=80" alt="Marine Products" loading="lazy">
            <div class="product-card-overlay"><span class="product-tag">Trade</span></div>
          </div>
          <div class="product-card-body">
            <div class="product-icon-wrap"><i class="fas fa-fish"></i></div>
            <h3 class="product-card-title">Marine Products</h3>
            <p class="product-card-desc">Fish and seafood brought from nearby coastal towns and traded in San Enrique markets, providing essential nutrition.</p>
            <div class="product-card-tags"><span><i class="fas fa-exchange-alt me-1"></i>Traded Goods</span><span><i class="fas fa-store me-1"></i>Local Markets</span></div>
          </div>
        </div>
      </div> -->

      <!-- <div class="products-note animate-on-scroll delay-3">
        <div class="products-note-inner">
          <div class="products-note-icon"><i class="fas fa-info-circle"></i></div>
          <div><strong>Did you know?</strong> San Enrique is primarily known for rice, corn, and sugarcane production, complemented by coconut and root crops, traditional Aeta handicrafts, and a growing livestock industry.</div>
        </div>
      </div>
    </div>
  </section> -->

  <!-- CONTACT SECTION -->
  <section id="contact" class="contact-section" style="position:relative;overflow:hidden;">
    <div class="section-floating-shapes">
      <div class="float-shape float-shape-1"></div>
      <div class="float-shape float-shape-2"></div>
      <div class="float-shape float-shape-3"></div>
    </div>
    <div class="container" style="position:relative;z-index:2;">
      <div class="text-center mb-5 animate-on-scroll">
        <div class="section-label justify-content-center"><span></span>Get In Touch<span></span></div>
        <h2 class="section-title">Contact San Enrique LGU</h2>
        <p class="section-subtitle mx-auto">Have questions or need assistance? Reach out to the Tourism Office.</p>
      </div>
      <div class="row g-4">
        <div class="col-lg-4 animate-on-scroll">
          <div class="contact-info-card">
            <h4 style="font-family:'Playfair Display',serif;color:white;font-size:1.2rem;margin-bottom:2rem;">Tourism
              Office Information</h4>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <div class="ci-label">Address</div>
                <div class="ci-value">Municipal Hall, Poblacion, San Enrique, Iloilo, Philippines</div>
              </div>
            </div>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-phone"></i></div>
              <div>
                <div class="ci-label">Phone</div>
                <div class="ci-value">(033) 123-4567</div>
              </div>
            </div>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-envelope"></i></div>
              <div>
                <div class="ci-label">Email</div>
                <div class="ci-value">tourism@sanenrique.gov.ph</div>
              </div>
            </div>

            <div class="contact-info-item">
              <div class="ci-icon"><i class="fas fa-clock"></i></div>
              <div>
                <div class="ci-label">Office Hours</div>
                <div class="ci-value">Mon–Fri, 8:00 AM – 5:00 PM</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-8 animate-on-scroll delay-2">
          <div class="contact-form-wrap">
            <h4
              style="font-family:'Playfair Display',serif;color:var(--primary);font-size:1.2rem;margin-bottom:1.75rem;">
              Send Us a Message</h4>
            <form id="contactForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label-main">Full Name *</label>
                  <input type="text" name="name" class="form-control-main" placeholder="Juan dela Cruz" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label-main">Email Address *</label>
                  <input type="email" name="email" class="form-control-main" placeholder="juan@example.com" required>
                </div>
                <div class="col-12">
                  <label class="form-label-main">Subject *</label>
                  <input type="text" name="subject" class="form-control-main"
                    placeholder="Inquiry about San Enrique Tourism" required>
                </div>
                <div class="col-12">
                  <label class="form-label-main">Message *</label>
                  <textarea name="message" class="form-control-main" rows="5"
                    placeholder="Tell us how we can help you..." required style="resize:vertical;"></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane me-2"></i>Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer-main">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-4">
          <div class="footer-logo">
            <div class="d-flex align-items-center gap-3">
              <img src="assets/images/san-enrique-logo.jpg" alt="San Enrique" class="footer-logo-img">
              <div>
                <div class="footer-logo-title">San
                  Enrique Tourism Hub</div>
                <div class="footer-logo-sub">
                  Official LGU Tourism Platform</div>
              </div>
            </div>
          </div>
          <p class="footer-desc">
            Your official digital gateway to the beauty, culture, and hospitality of San Enrique, Iloilo. A proud
            initiative of the San Enrique Local Government Unit.
          </p>
          <!-- <div class="footer-social">
            <a href="#" class="social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-btn" title="Twitter"><i class="fab fa-twitter"></i></a>
          </div> -->
        </div>

        <div class="col-6 col-lg-2">
          <h5 class="footer-heading">Explore</h5>
          <ul class="footer-links">
            <?php foreach ($categories as $cat): ?>
              <li><a href="explore.php?category=<?= htmlspecialchars($cat['slug']) ?>"><i
                    class="fas fa-chevron-right"></i> <?= htmlspecialchars($cat['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="col-6 col-lg-2">
          <h5 class="footer-heading">Quick Links</h5>
          <ul class="footer-links">
            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
            <li><a href="explore.php"><i class="fas fa-chevron-right"></i> All Listings</a></li>
            <li><a href="map.php"><i class="fas fa-chevron-right"></i> Interactive Map</a></li>
            <li><a href="#events"><i class="fas fa-chevron-right"></i> Events</a></li>
            <li><a href="#about"><i class="fas fa-chevron-right"></i> About</a></li>
            <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
          </ul>
        </div>

        <div class="col-lg-4">
          <h5 class="footer-heading">Contact Us</h5>
          <div style="display:flex;flex-direction:column;gap:0.9rem;">
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <i class="fas fa-map-marker-alt" style="color:var(--accent);margin-top:3px;flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">Municipal Hall, Poblacion, San Enrique,
                Iloilo</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <i class="fas fa-phone" style="color:var(--accent);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">(033) 123-4567</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <i class="fas fa-envelope" style="color:var(--accent);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">tourism@sanenrique.gov.ph</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <i class="fas fa-clock" style="color:var(--accent);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:rgba(255,255,255,0.5);">Mon–Fri, 8:00 AM – 5:00 PM</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <span>&copy; <?= date('Y') ?> San Enrique Tourism Hub. All rights reserved. | San Enrique LGU</span>
          <span>Developed for San Enrique, Iloilo, Philippines 🌿</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Loading Spinner -->
  <script>
  /* ── Generate stars ── */
  const starsEl = document.getElementById('loaderStars');
  for (let i = 0; i < 60; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    s.style.cssText = `
      top:${Math.random()*65}%;
      left:${Math.random()*100}%;
      --d:${1.5+Math.random()*2.5}s;
      --delay:${Math.random()*3}s;
      --min-op:${0.1+Math.random()*0.3};
      --max-op:${0.5+Math.random()*0.5};
      width:${1+Math.random()*2}px;
      height:${1+Math.random()*2}px;
    `;
    starsEl.appendChild(s);
  }

  /* ── Generate fireflies ── */
  const loader = document.getElementById('pageLoader');
  for (let i = 0; i < 12; i++) {
    const f = document.createElement('div');
    f.className = 'firefly';
    const dx = (Math.random()-0.5)*80;
    const dy = -(20+Math.random()*60);
    f.style.cssText = `
      left:${10+Math.random()*80}%;
      top:${40+Math.random()*45}%;
      --d:${3+Math.random()*4}s;
      --delay:${Math.random()*5}s;
      --dx:${dx}px;
      --dy:${dy}px;
    `;
    loader.appendChild(f);
  }

  /* ── Dismiss on page load ── */
  window.addEventListener('load', function () {
    setTimeout(function () {
      loader.classList.add('hidden');
    }, 1100000);
  });
</script>

  <!-- Map Data -->
  <script>
    const mapListings = <?= json_encode($mapListings) ?>;

    window.liveUpdateConfig = { page: 'home' };
  </script>

  <!-- Custom JS -->
  <script src="assets/js/main.js"></script>
  <!-- Live update engine -->
  <script src="assets/js/live-update.js"></script>

  <!-- Google Maps API - Replace YOUR_GOOGLE_MAPS_API_KEY -->
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap&libraries=places">
    </script>

  <!-- ═══════════ UX ENHANCEMENT JS ═══════════ -->
  <script>
  // ── Scroll-reveal ─────────────────────────────────────────────────────────
  (function(){
    var els = document.querySelectorAll('.animate-on-scroll');
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); }
      });
    },{threshold:0.12});
    els.forEach(function(el){ io.observe(el); });
  })();

  // ── Navbar glass on scroll ────────────────────────────────────────────────
  (function(){
    var nav = document.querySelector('.navbar-main');
    if(!nav) return;
    window.addEventListener('scroll',function(){
      nav.classList.toggle('scrolled', window.scrollY > 60);
    });
  })();

  // ── Floating particles canvas ─────────────────────────────────────────────
  (function(){
    var c = document.getElementById('heroParticles');
    if(!c) return;
    var ctx = c.getContext('2d');
    var W, H, particles = [];
    function resize(){ W = c.width = window.innerWidth; H = c.height = c.parentElement.offsetHeight; }
    resize();
    window.addEventListener('resize', resize);
    for(var i=0;i<55;i++){
      particles.push({
        x: Math.random()*1920, y: Math.random()*900,
        r: Math.random()*2.2+0.4,
        dx: (Math.random()-.5)*0.35, dy: -Math.random()*0.45-0.15,
        o: Math.random()*0.5+0.15
      });
    }
    function draw(){
      ctx.clearRect(0,0,W,H);
      particles.forEach(function(p){
        ctx.beginPath();
        ctx.arc(p.x%W, p.y, p.r, 0, Math.PI*2);
        ctx.fillStyle = 'rgba(240,192,64,'+p.o+')';
        ctx.fill();
        p.x += p.dx; p.y += p.dy;
        if(p.y < -4){ p.y = H+4; p.x = Math.random()*W; }
      });
      requestAnimationFrame(draw);
    }
    draw();
  })();

  // ── Category card click → explore ─────────────────────────────────────────
  document.querySelectorAll('.category-card').forEach(function(card){
    card.addEventListener('click',function(){
      var slug = card.getAttribute('data-slug');
      if(slug) window.location.href = 'explore.php?category='+slug;
    });
  });
  </script>
  <script>
  /* ════ HERO SLIDESHOW JS ════ */
  (function () {
    var slides, dots, total, cur = 0, timer;

    var POSITIONS = [0, 1, 2, -2, -1]; // maps index offset → data-pos values cyclically

    function posFor(offset) {
      // offset: distance from active slide (-2,-1,0,1,2 → visible; rest hidden)
      if (offset === 0)  return '0';
      if (offset === 1)  return '1';
      if (offset === -1) return '-1';
      if (offset === 2)  return '2';
      if (offset === -2) return '-2';
      return 'hidden';
    }

    function render() {
      slides.forEach(function (sl, i) {
        var offset = i - cur;
        // Wrap around
        if (offset >  Math.floor(total / 2)) offset -= total;
        if (offset < -Math.floor(total / 2)) offset += total;
        sl.setAttribute('data-pos', posFor(offset));
      });
      dots.forEach(function (d, i) {
        d.classList.toggle('active', i === cur);
      });
    }

    function goTo(n) {
      cur = (n + total) % total;
      render();
    }

    window.hsPrev = function () { goTo(cur - 1); resetTimer(); };
    window.hsNext = function () { goTo(cur + 1); resetTimer(); };

    function resetTimer() {
      clearInterval(timer);
      timer = setInterval(function () { goTo(cur + 1); }, 4000);
    }

    document.addEventListener('DOMContentLoaded', function () {
      slides = Array.from(document.querySelectorAll('.hs-slide'));
      var dotsWrap = document.getElementById('hsDots');
      total = slides.length;
      if (!total) return;

      // Build dots
      dots = slides.map(function (_, i) {
        var d = document.createElement('button');
        d.className = 'hs-dot';
        d.setAttribute('aria-label', 'Slide ' + (i + 1));
        d.addEventListener('click', function () { goTo(i); resetTimer(); });
        dotsWrap.appendChild(d);
        return d;
      });

      // Click side slides to navigate
      slides.forEach(function (sl, i) {
        sl.addEventListener('click', function () {
          var pos = sl.getAttribute('data-pos');
          if (pos === '1' || pos === '2')  { goTo(cur + 1); resetTimer(); }
          if (pos === '-1' || pos === '-2') { goTo(cur - 1); resetTimer(); }
        });
      });

      // Touch / swipe
      var startX = 0;
      var stage = document.getElementById('hsStage');
      stage.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, {passive:true});
      stage.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) { dx < 0 ? hsNext() : hsPrev(); }
      }, {passive:true});

      render();
      resetTimer();
    });
  })();
  </script>

  <!-- Products filter -->
  <script>
  (function(){
    var btns = document.querySelectorAll('.products-filter-btn');
    var cards = document.querySelectorAll('.product-card');
    if (!btns.length) return;
    btns.forEach(function(btn){
      btn.addEventListener('click', function(){
        btns.forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        var filter = btn.getAttribute('data-filter');
        cards.forEach(function(card, i){
          var cat = card.getAttribute('data-category');
          if (filter === 'all' || cat === filter) {
            card.style.display = '';
            card.style.animation = 'productFadeIn 0.4s ' + (i * 0.06) + 's both';
          } else {
            card.style.display = 'none';
            card.style.animation = '';
          }
        });
      });
    });
  })();
  </script>
</body>

</html>