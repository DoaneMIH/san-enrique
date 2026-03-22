<?php
require_once 'includes/functions.php';
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
if (!$slug) {
  header('Location: explore.php');
  exit;
}
$listing = getListing($slug);
if (!$listing) {
  header('Location: explore.php');
  exit;
}
$db = getDB();
$reviews = $db->query("SELECT * FROM reviews WHERE listing_id = {$listing['id']} ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="assets/images/logo.png">
  <title><?= htmlspecialchars($listing['name']) ?> - <?= SITE_NAME ?></title>
  <meta name="description" content="<?= htmlspecialchars(substr($listing['description'], 0, 155)) ?>">
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* ── Listing page: info-row overrides for glass background ── */
    .info-row {
      align-items: flex-start;
      padding: 0.9rem 0.5rem !important;
      border-bottom: 1px solid rgba(82,183,136,0.20) !important;
      gap: 0.85rem !important;
    }
    .info-row:last-child {
      border-bottom: none !important;
    }
    .info-row .ir-icon {
      color: var(--gold-light) !important;
      margin-top: 2px;
      width: 20px;
      flex-shrink: 0;
    }
    .info-row .ir-label {
      color: rgba(180,230,200,0.85) !important;
      font-weight: 600 !important;
      min-width: 90px;
      font-size: 0.85rem;
    }
    .info-row .ir-value {
      color: rgba(255,255,255,0.92) !important;
      min-width: 0;
      word-break: break-all;
      overflow-wrap: anywhere;
      font-size: 0.9rem;
    }
    .ir-value a {
      color: var(--gold-light) !important;
    }

    /* ── Go Back button: green-tinted to match backdrop ── */
    .btn-outline-listing-back {
      background: rgba(82,183,136,0.12);
      color: rgba(255,255,255,0.92);
      font-weight: 700;
      padding: 0.75rem 1.75rem;
      border-radius: 12px;
      border: 1.5px solid rgba(82,183,136,0.45);
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-family: 'Nunito', sans-serif;
      backdrop-filter: blur(10px);
      text-decoration: none;
      width: 100%;
      justify-content: center;
    }
    .btn-outline-listing-back:hover {
      background: rgba(82,183,136,0.28);
      color: #fff;
      border-color: rgba(82,183,136,0.75);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(82,183,136,0.2);
    }

    /* ── Share section separator and text ── */
    .share-sep {
      border-top: 1px solid rgba(82,183,136,0.20) !important;
    }
    .share-label {
      color: rgba(180,230,200,0.75) !important;
    }
  </style>

  <!-- ═══════════ LISTING UX CSS ═══════════ -->
  <style>
    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Parallax body — same nature photo as explore
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    body {
      background:
        url('https://images.unsplash.com/photo-1501854140801-50d01698950b?w=1800&q=80')
        center center / cover fixed no-repeat !important;
      background-color: #0d2418 !important;
    }
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 1;
      background: rgba(8, 20, 12, 0.45);
      pointer-events: none;
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Navbar — fixed, green, always on top
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .navbar-main {
      position: fixed !important;
      top: 0; left: 0; right: 0;
      z-index: 1000 !important;
      background: rgba(27, 67, 50, 0.97) !important;
      backdrop-filter: blur(20px) !important;
      -webkit-backdrop-filter: blur(20px) !important;
      border-bottom: 1px solid rgba(82, 183, 136, 0.25) !important;
      box-shadow: 0 2px 24px rgba(0,0,0,0.3) !important;
      transition: var(--transition) !important;
    }
    .navbar-main.scrolled {
      background: rgba(13, 43, 30, 0.99) !important;
      box-shadow: 0 4px 32px rgba(0,0,0,0.45) !important;
    }
    .brand-name  { color: #fff !important; }
    .brand-sub   { color: rgba(255,255,255,0.6) !important; }
    .nav-link-main { color: rgba(255,255,255,0.88) !important; }
    .nav-link-main:hover,
    .nav-link-main.active {
      color: #fff !important;
      background: rgba(82,183,136,0.18) !important;
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Z-index — lift all content above body::before
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    #backToTop, .back-to-top,
    .page-hero, .footer-main { position: relative; z-index: 10; }
    .container { position: relative; z-index: 5; }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Page hero — transparent so parallax shows
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .page-hero {
      background: transparent !important;
      padding: 80px 0 10px !important; /* top clears fixed navbar */
    }
    .page-hero::after { display: none !important; }
    .page-hero-title {
      font-family: 'Playfair Display', serif !important; /* uses style.css font */
      font-size: clamp(2rem, 5vw, 3.4rem) !important;
      font-weight: 700 !important;
      color: #fff !important;
      text-shadow: 0 2px 16px rgba(0,0,0,0.45);
      animation: lxFadeUp .85s cubic-bezier(.2,0,.2,1) both;
    }
    .page-hero .listing-badge,
    .page-hero .featured-badge {
      position: static !important;
      display: inline-flex !important;
      align-items: center;
      margin: 0 !important;
      animation: lxFadeUp .8s .12s cubic-bezier(.2,0,.2,1) both;
    }
    .page-hero .featured-badge {
      margin-bottom: 0 !important; /* override style.css margin-bottom:20px */
    }
    @keyframes lxFadeUp {
      from { opacity:0; transform:translateY(22px); }
      to   { opacity:1; transform:none; }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Featured image
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .listing-detail-img {
      width: 100%;
      aspect-ratio: 16/9;
      object-fit: cover;
      border-radius: var(--radius-lg) !important;
      box-shadow: var(--shadow-xl) !important;
      animation: lxReveal 1s cubic-bezier(.2,0,.2,1) both;
    }
    @keyframes lxReveal {
      from { opacity:0; transform:scale(.97); }
      to   { opacity:1; transform:scale(1); }
    }

    

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Reviews section
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /* Section heading */
    .listing-info-box h3 > i.fa-star { color: var(--gold-light) !important; }

    /* Individual review cards */
    .listing-info-box > div[style*="flex-direction:column"] > div,
    .listing-info-box > div[style*="flex-direction: column"] > div {
      background: rgba(255,255,255,0.07) !important;
      border-left: 3px solid rgba(82,183,136,0.6) !important;
      border-radius: 14px !important;
      transition: background .25s, transform .25s;
    }
    .listing-info-box > div[style*="flex-direction:column"] > div:hover,
    .listing-info-box > div[style*="flex-direction: column"] > div:hover {
      background: rgba(255,255,255,0.12) !important;
      transform: translateX(4px);
    }

    /* Reviewer name */
    .listing-info-box > div[style*="flex-direction:column"] strong,
    .listing-info-box > div[style*="flex-direction: column"] strong {
      color: #fff !important;
      font-size: 0.92rem;
    }
    /* Star rating */
    .listing-info-box .stars { color: var(--gold-light) !important; }

    /* Review comment text */
    .listing-info-box > div[style*="flex-direction:column"] p,
    .listing-info-box > div[style*="flex-direction: column"] p {
      color: rgba(255,255,255,0.72) !important;
    }
    /* Date */
    .listing-info-box > div[style*="flex-direction:column"] div[style*="0.75rem"],
    .listing-info-box > div[style*="flex-direction: column"] div[style*="0.75rem"] {
      color: rgba(255,255,255,0.42) !important;
    }

    /* No reviews text */
    .listing-info-box > p[style*="text-muted"] {
      color: rgba(255,255,255,0.55) !important;
    }

    /* ── Review form box ────────────────────────── */
    .listing-info-box > div[style*="off-white"],
    .listing-info-box > div[style*="var(--off-white)"] {
      background: rgba(255,255,255,0.06) !important;
      border: 1px solid rgba(82,183,136,0.2) !important;
      border-radius: 14px !important;
    }
    /* Form heading */
    .listing-info-box #reviewForm ~ * h4,
    #reviewForm h4,
    div[style*="off-white"] h4 {
      color: #fff !important;
    }
    /* Labels */
    .listing-info-box .form-label-main {
      color: rgba(255,255,255,0.72) !important;
      font-weight: 600;
    }
    /* Inputs & select & textarea */
    .listing-info-box .form-control-main,
    #reviewForm .form-control-main {
      background: rgba(255,255,255,0.08) !important;
      border: 1.5px solid rgba(255,255,255,0.18) !important;
      color: #fff !important;
      border-radius: 10px !important;
    }
    .listing-info-box .form-control-main::placeholder,
    #reviewForm .form-control-main::placeholder {
      color: rgba(255,255,255,0.38) !important;
    }
    .listing-info-box .form-control-main:focus,
    #reviewForm .form-control-main:focus {
      border-color: rgba(82,183,136,0.55) !important;
      box-shadow: 0 0 0 3px rgba(82,183,136,0.15) !important;
      background: rgba(255,255,255,0.12) !important;
      outline: none;
    }
    /* Select option dropdown (browser-native, limited styling) */
    #reviewForm select.form-control-main option {
      background: #1b4332;
      color: #fff;
    }
    /* Submit button */
    #reviewForm .btn-primary-main {
      background: linear-gradient(135deg, var(--accent), var(--accent-light)) !important;
      border: none !important;
      color: #fff !important;
      font-weight: 700;
      border-radius: 10px !important;
      padding: 0.6rem 1.4rem !important;
      box-shadow: 0 4px 16px rgba(45,122,58,0.35) !important;
      transition: var(--transition) !important;
    }
    #reviewForm .btn-primary-main:hover {
      opacity: .88;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 24px rgba(45,122,58,0.45) !important;
    }

      /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Featured image
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .listing-detail-img {
      width: 100%;
      aspect-ratio: 16/9;
      object-fit: cover;
      border-radius: var(--radius-lg) !important;
      box-shadow: var(--shadow-xl) !important;
      animation: lxReveal 1s cubic-bezier(.2,0,.2,1) both;
    }
    @keyframes lxReveal {
      from { opacity:0; transform:scale(.97); }
      to   { opacity:1; transform:scale(1); }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Info boxes — green-tinted glass (main column)
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .listing-info-box {
      background: rgba(13, 43, 30, 0.52) !important;
      backdrop-filter: blur(20px) !important;
      -webkit-backdrop-filter: blur(20px) !important;
      border: 1px solid rgba(82, 183, 136, 0.22) !important;
      border-radius: var(--radius-lg) !important;
      box-shadow: var(--shadow-lg) !important;
      padding: 1.75rem !important;
      animation: lxFadeUp .8s cubic-bezier(.2,0,.2,1) both;
    }
    /* Stagger main column boxes */
    .col-lg-8 > .listing-info-box:nth-child(1) { animation-delay: .05s; }
    .col-lg-8 > .listing-info-box:nth-child(2) { animation-delay: .15s; }
    .col-lg-8 > .listing-info-box:nth-child(3) { animation-delay: .25s; }
    .col-lg-8 > .listing-info-box:nth-child(4) { animation-delay: .35s; }
    .col-lg-4 > .listing-info-box              { animation-delay: .20s; }

    /* Sidebar box — slightly lighter */
    .col-lg-4 .listing-info-box {
      background: rgba(13, 43, 30, 0.65) !important;
      border-color: rgba(82, 183, 136, 0.30) !important;
    }

    /* ── Text inside info boxes ─────────────────── */
    .listing-info-box h3,
    .listing-info-box h4,
    .listing-info-box strong { color: #fff !important; }
    .listing-info-box p,
    .listing-info-box span,
    .listing-info-box li,
    .listing-info-box label { color: rgba(255,255,255,0.78) !important; }
    .listing-info-box a      { color: var(--gold-light) !important; }
    .listing-info-box a:hover { color: #fff !important; }

    /* Section heading accent line inside boxes */
    .listing-info-box h3 i,
    .listing-info-box h4 i { color: var(--gold-light) !important; }

    /* ── Amenity tags on glass ───────────────────── */
    .listing-info-box span[style*="accent-pale"] {
      background: rgba(82,183,136,0.25) !important;
      color: #fff !important;
      border: 1px solid rgba(82,183,136,0.4) !important;
      transition: transform .2s, box-shadow .2s;
    }
    .listing-info-box span[style*="accent-pale"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 14px rgba(0,0,0,0.2);
    }

    /* ── Detail info rows (hours, phone, email) ─── */
    .listing-info-box .ci-icon,
    .listing-info-box .fi-icon { background: rgba(82,183,136,0.22) !important; }


        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Buttons
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .btn-primary-main {
      background: linear-gradient(135deg, var(--accent), var(--accent-light)) !important;
      border-radius: var(--radius-sm) !important;
      box-shadow: 0 4px 16px rgba(45,122,58,0.35) !important;
      transition: var(--transition) !important;
    }
    .btn-primary-main:hover {
      opacity: .9;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 28px rgba(45,122,58,0.45) !important;
    }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       Form inputs inside glass boxes
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .listing-info-box .form-control-main {
      background: rgba(255,255,255,0.1) !important;
      border: 1.5px solid rgba(255,255,255,0.2) !important;
      color: #fff !important;
      border-radius: var(--radius-sm) !important;
    }
    .listing-info-box .form-control-main::placeholder { color: rgba(255,255,255,.45) !important; }
    .listing-info-box .form-control-main:focus {
      border-color: rgba(82,183,136,0.5) !important;
      box-shadow: 0 0 0 3px rgba(82,183,136,0.15) !important;
    }
    .listing-info-box .form-label-main { color: rgba(255,255,255,0.75) !important; }

   

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
      Detail map
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .detail-map {
      border-radius: var(--radius) !important;
      overflow: hidden;
      box-shadow: var(--shadow-md) !important;
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
      Footer
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    .footer-main {
      position: relative; z-index: 10;
      background: rgba(8, 20, 12, 0.97) !important;
      backdrop-filter: blur(10px) !important;
    }
  </style>
</head>

<body>

  <!-- <div id="pageLoader" class="page-loader">
    <div class="brand-logo"
      style="width:60px;height:60px;background:linear-gradient(135deg,#52b788,#d4a017);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2rem;">
      🌿</div>
    <div class="loader-logo"><?= SITE_NAME ?></div>
    <div class="loader-bar">
      <div class="loader-bar-fill"></div>
    </div>
  </div> -->

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
    <div class="loader-emblem">🌿</div>

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


  <button id="backToTop" class="back-to-top"><i class="fas fa-chevron-up"></i></button>

  <!-- NAVBAR -->
  <nav class="navbar-main scrolled">
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
          <div class="brand-logo">🌿</div>
          <div class="brand-text-wrap">
            <div class="brand-name">San Enrique</div>
            <div class="brand-sub">Tourism Hub</div>
          </div>
        </a>
        <!-- <div class="d-none d-lg-flex align-items-center gap-1">
          <a href="index.php" class="nav-link-main">Home</a>
          <a href="explore.php" class="nav-link-main">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a>
          <a href="index.php#events" class="nav-link-main">Events</a>
          <a href="index.php#about" class="nav-link-main">About</a>
          <a href="index.php#contact" class="nav-link-main">Contact</a>
        </div> -->
      </div>
    </div>
  </nav>

  <!-- PAGE HERO -->
  <div class="page-hero">
    <div class="container">
      <!-- <nav aria-label="breadcrumb" class="breadcrumb-nav">
      <ol class="breadcrumb mb-3" style="font-size:0.82rem;">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="explore.php">Explore</a></li>
        <li class="breadcrumb-item"><a href="explore.php?category=<?= htmlspecialchars($listing['cat_slug'] ?? '') ?>"><?= htmlspecialchars($listing['category_name']) ?></a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($listing['name']) ?></li>
      </ol>
    </nav> -->
      <div class="d-flex align-items-center flex-wrap" style="gap:0.6rem;margin-bottom:0.25rem;">
        <div class="listing-badge"
          style="color:<?= htmlspecialchars($listing['color']) ?>;background:rgba(255,255,255,0.15);border-radius:100px;padding:6px 18px;font-size:0.8rem;font-weight:700;border:1px solid rgba(255,255,255,0.35);display:inline-flex;align-items:center;backdrop-filter:blur(10px);">
          <i class="<?= htmlspecialchars($listing['icon']) ?> me-1"></i>
          <?= htmlspecialchars($listing['category_name']) ?>
        </div>
        <?php if ($listing['is_featured']): ?>
          <div class="featured-badge">★ FEATURED</div>
        <?php endif; ?>
      </div>
      <h1 class="page-hero-title mt-2"><?= htmlspecialchars($listing['name']) ?></h1>
      <p style="color:rgba(255,255,255,0.7);font-size:0.92rem;margin-top:0.5rem;">
        <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($listing['address'] ?: 'San Enrique, Iloilo') ?>
      </p>
    </div>
  </div>

  <!-- LISTING DETAIL -->
  <div class="container py-5">
    <div class="row g-4">
      <!-- MAIN CONTENT -->
      <div class="col-lg-8">
        <!-- Featured Image -->
        <img src="<?= htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 1200, 600)) ?>"
          alt="<?= htmlspecialchars($listing['name']) ?>" class="listing-detail-img mb-4"
          onerror="this.src='https://placehold.co/1200x600/1b4332/ffffff?text=<?= urlencode($listing['name']) ?>'">

        <!-- Gallery Carousel -->
        <?php
          $galleryPhotos = json_decode($listing['gallery'] ?? '[]', true) ?: [];
          if (!empty($galleryPhotos)):
          $galleryUrls = array_map(fn($p) => listingImage($p, $listing['name'], 900, 600), $galleryPhotos);
          $galleryFull = array_map(fn($p) => listingImage($p, $listing['name'], 1400, 900), $galleryPhotos);
          $total = count($galleryPhotos);
        ?>
        <style>
          /* ── Carousel wrapper ─────────────────── */
          .gc-wrap { position:relative; margin-bottom:0; }

          /* ── Main stage ───────────────────────── */
          .gc-stage {
            position:relative;
            border-radius:18px;
            overflow:hidden;
            background:#0d1f17;
            aspect-ratio:16/9;
            cursor:zoom-in;
            box-shadow:0 12px 48px rgba(0,0,0,0.22);
          }
          .gc-slides {
            display:flex;
            height:100%;
            transition:transform .52s cubic-bezier(.4,0,.2,1);
            will-change:transform;
          }
          .gc-slide {
            flex:0 0 100%;
            height:100%;
            position:relative;
          }
          .gc-slide img {
            width:100%;height:100%;
            object-fit:cover;display:block;
          }
          /* subtle dark vignette */
          .gc-stage::after {
            content:'';
            position:absolute;inset:0;
            background:radial-gradient(ellipse at center, transparent 55%, rgba(0,0,0,.35) 100%);
            pointer-events:none;z-index:1;
          }

          /* ── Prev / Next buttons ──────────────── */
          .gc-btn {
            position:absolute;top:50%;transform:translateY(-50%);
            z-index:5;border:none;cursor:pointer;
            width:44px;height:44px;border-radius:50%;
            background:rgba(255,255,255,0.18);
            backdrop-filter:blur(10px);
            border:1px solid rgba(255,255,255,0.3);
            color:#fff;font-size:1.25rem;
            display:flex;align-items:center;justify-content:center;
            transition:background .2s, transform .2s, box-shadow .2s;
            box-shadow:0 4px 16px rgba(0,0,0,0.25);
          }
          .gc-btn:hover {
            background:rgba(255,255,255,0.32);
            transform:translateY(-50%) scale(1.1);
            box-shadow:0 6px 22px rgba(0,0,0,0.3);
          }
          .gc-btn.prev { left:14px; }
          .gc-btn.next { right:14px; }

          /* ── Counter pill ─────────────────────── */
          .gc-counter {
            position:absolute;top:14px;right:14px;z-index:5;
            background:rgba(0,0,0,0.45);backdrop-filter:blur(8px);
            border:1px solid rgba(255,255,255,0.2);
            color:#fff;font-size:.73rem;font-weight:700;
            padding:4px 11px;border-radius:20px;letter-spacing:.04em;
          }

          /* ── Thumbnail strip ──────────────────── */
          .gc-thumbs {
            display:flex;
            gap:8px;
            margin-top:10px;
            overflow-x:auto;
            padding-bottom:4px;
            scrollbar-width:thin;
            scrollbar-color:rgba(27,67,50,.3) transparent;
          }
          .gc-thumbs::-webkit-scrollbar { height:4px; }
          .gc-thumbs::-webkit-scrollbar-thumb { background:rgba(27,67,50,.3);border-radius:4px; }

          .gc-thumb {
            flex:0 0 80px;height:58px;
            border-radius:9px;overflow:hidden;
            cursor:pointer;
            border:2.5px solid transparent;
            transition:border-color .22s, opacity .22s, transform .22s;
            opacity:.6;
            box-shadow:0 2px 8px rgba(0,0,0,0.12);
          }
          .gc-thumb img {
            width:100%;height:100%;object-fit:cover;display:block;
            transition:transform .3s;
          }
          .gc-thumb:hover { opacity:.85; transform:translateY(-2px); }
          .gc-thumb.active {
            border-color:var(--accent,#52b788);
            opacity:1;
            transform:translateY(-3px);
            box-shadow:0 4px 14px rgba(82,183,136,0.35);
          }

          /* ── Lightbox ─────────────────────────── */
          #gcLightbox {
            display:none;position:fixed;inset:0;
            background:rgba(0,0,0,.94);z-index:9999;
            align-items:center;justify-content:center;flex-direction:column;
          }
          #gcLightbox.open { display:flex; }
          #gcLbImg {
            max-width:92vw;max-height:84vh;
            object-fit:contain;border-radius:10px;display:block;
            opacity:0;transition:opacity .3s;
          }
          #gcLbImg.loaded { opacity:1; }
          .gc-lb-btn {
            position:absolute;top:50%;transform:translateY(-50%);
            background:rgba(255,255,255,0.13);backdrop-filter:blur(6px);
            border:1px solid rgba(255,255,255,0.2);
            color:#fff;width:48px;height:48px;border-radius:50%;
            cursor:pointer;font-size:1.5rem;
            display:flex;align-items:center;justify-content:center;
            transition:background .2s;
          }
          .gc-lb-btn:hover { background:rgba(255,255,255,0.26); }
          .gc-lb-btn.prev { left:20px; }
          .gc-lb-btn.next { right:20px; }
          .gc-lb-close {
            position:absolute;top:18px;right:20px;
            background:none;border:none;color:white;
            font-size:1.9rem;cursor:pointer;line-height:1;z-index:2;
            transition:transform .2s;
          }
          .gc-lb-close:hover { transform:rotate(90deg); }
          .gc-lb-caption {
            color:rgba(255,255,255,.5);
            font-size:.78rem;margin-top:10px;letter-spacing:.04em;
          }
        </style>

        <div class="listing-info-box mb-4">
          <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:1rem;">
            <i class="fas fa-images me-2" style="color:var(--accent);"></i> Photo Gallery
            <span style="font-size:.75rem;color:var(--text-muted);font-weight:400;margin-left:6px;"><?= $total ?> photo<?= $total!==1?'s':'' ?></span>
          </h3>

          <div class="gc-wrap">
            <!-- Main stage -->
            <div class="gc-stage" id="gcStage" onclick="gcOpenLightbox(gcIdx)">
              <div class="gc-slides" id="gcSlides">
                <?php foreach ($galleryUrls as $i => $url): ?>
                <div class="gc-slide">
                  <img src="<?= htmlspecialchars($url) ?>"
                       alt="<?= htmlspecialchars($listing['name']) ?> photo <?= $i+1 ?>"
                       loading="<?= $i===0?'eager':'lazy' ?>"
                       onerror="this.src='https://placehold.co/900x600/1b4332/fff?text=Photo'">
                </div>
                <?php endforeach; ?>
              </div>
              <?php if ($total > 1): ?>
              <button class="gc-btn prev" onclick="event.stopPropagation();gcGo(gcIdx-1)">&#8249;</button>
              <button class="gc-btn next" onclick="event.stopPropagation();gcGo(gcIdx+1)">&#8250;</button>
              <?php endif; ?>
              <div class="gc-counter" id="gcCounter">1 / <?= $total ?></div>
            </div>

            <!-- Thumbnail strip -->
            <?php if ($total > 1): ?>
            <div class="gc-thumbs" id="gcThumbs">
              <?php foreach ($galleryUrls as $i => $url): ?>
              <div class="gc-thumb <?= $i===0?'active':'' ?>" id="gcThumb<?= $i ?>" onclick="gcGo(<?= $i ?>)">
                <img src="<?= htmlspecialchars($url) ?>"
                     alt="thumb <?= $i+1 ?>"
                     loading="lazy"
                     onerror="this.src='https://placehold.co/80x58/1b4332/fff?text=<?= $i+1 ?>'">
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Lightbox -->
        <div id="gcLightbox">
          <button class="gc-lb-close" onclick="gcCloseLightbox()">&#10005;</button>
          <button class="gc-lb-btn prev" onclick="gcLbPrev()">&#8249;</button>
          <img id="gcLbImg" src="" alt="">
          <button class="gc-lb-btn next" onclick="gcLbNext()">&#8250;</button>
          <div class="gc-lb-caption" id="gcLbCaption"></div>
        </div>

        <script>
          var gcPhotos = <?= json_encode($galleryFull) ?>;
          var gcIdx    = 0;
          var gcLbIdx  = 0;
          var gcTotal  = <?= $total ?>;

          /* ── Carousel ───────────────────────── */
          function gcGo(i) {
            gcIdx = ((i % gcTotal) + gcTotal) % gcTotal;
            document.getElementById('gcSlides').style.transform = 'translateX(-' + (gcIdx*100) + '%)';
            document.getElementById('gcCounter').textContent    = (gcIdx+1) + ' / ' + gcTotal;
            // update thumbnails
            document.querySelectorAll('.gc-thumb').forEach(function(t,j){
              t.classList.toggle('active', j===gcIdx);
            });
            // scroll active thumb into view
            var active = document.getElementById('gcThumb'+gcIdx);
            if(active) active.scrollIntoView({behavior:'smooth',block:'nearest',inline:'center'});
          }

          /* ── Touch / swipe on stage ─────────── */
          (function(){
            var el = document.getElementById('gcStage');
            if(!el) return;
            var sx=0;
            el.addEventListener('touchstart',function(e){sx=e.touches[0].clientX;},{passive:true});
            el.addEventListener('touchend',function(e){
              var diff=sx-e.changedTouches[0].clientX;
              if(Math.abs(diff)>36){ diff>0?gcGo(gcIdx+1):gcGo(gcIdx-1); }
            },{passive:true});
          })();

          /* ── Lightbox ───────────────────────── */
          function gcOpenLightbox(i) {
            gcLbIdx = i;
            gcShowLb();
            document.getElementById('gcLightbox').classList.add('open');
            document.body.style.overflow = 'hidden';
          }
          function gcCloseLightbox() {
            document.getElementById('gcLightbox').classList.remove('open');
            document.body.style.overflow = '';
          }
          function gcShowLb() {
            var img = document.getElementById('gcLbImg');
            img.classList.remove('loaded');
            img.onload = function(){ img.classList.add('loaded'); };
            img.src = gcPhotos[gcLbIdx];
            document.getElementById('gcLbCaption').textContent = (gcLbIdx+1) + ' / ' + gcTotal;
          }
          function gcLbPrev() { gcLbIdx=(gcLbIdx-1+gcTotal)%gcTotal; gcShowLb(); }
          function gcLbNext() { gcLbIdx=(gcLbIdx+1)%gcTotal;         gcShowLb(); }

          /* ── Keyboard ───────────────────────── */
          document.addEventListener('keydown', function(e){
            var lb = document.getElementById('gcLightbox');
            if(lb.classList.contains('open')){
              if(e.key==='ArrowLeft')  gcLbPrev();
              if(e.key==='ArrowRight') gcLbNext();
              if(e.key==='Escape')     gcCloseLightbox();
            } else {
              if(e.key==='ArrowLeft')  gcGo(gcIdx-1);
              if(e.key==='ArrowRight') gcGo(gcIdx+1);
            }
          });
        </script>
        <?php endif; ?>

        <!-- Video -->
        <?php
          $vid = $listing['video'] ?? '';
          $isUploadedVid = $vid && strpos($vid, '../uploads/') === 0;
          $vidPublicUrl  = '';
          if ($isUploadedVid) {
            $vClean = preg_replace('#^(\.\./)+#', '', $vid);
            $vidPublicUrl = BASE_URL . '/' . ltrim($vClean, '/');
          }
          $isYoutube = $vid && preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $vid, $ytm);
          $isVimeo   = $vid && preg_match('/vimeo\.com\/(\d+)/', $vid, $vim);
        ?>
        <?php if ($vid): ?>
        <div class="listing-info-box mb-4">
          <h3 style="font-size:1.1rem;margin-bottom:1rem;">
            <i class="fas fa-video me-2" style="color:var(--gold-light);"></i> Video
          </h3>
          <?php if ($isYoutube): ?>
            <div style="position:relative;padding-bottom:56.25%;border-radius:14px;overflow:hidden;background:#000;box-shadow:0 8px 32px rgba(0,0,0,0.35);">
              <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytm[1]) ?>?rel=0&modestbranding=1&autoplay=1&mute=1&loop=1&playlist=<?= htmlspecialchars($ytm[1]) ?>"
                style="position:absolute;inset:0;width:100%;height:100%;border:none;"
                allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe>
            </div>
          <?php elseif ($isVimeo): ?>
            <div style="position:relative;padding-bottom:56.25%;border-radius:14px;overflow:hidden;background:#000;box-shadow:0 8px 32px rgba(0,0,0,0.35);">
              <iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($vim[1]) ?>?badge=0&autopause=0&autoplay=1&muted=1&loop=1"
                style="position:absolute;inset:0;width:100%;height:100%;border:none;"
                allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe>
            </div>
          <?php elseif ($isUploadedVid): ?>
            <video src="<?= htmlspecialchars($vidPublicUrl) ?>" controls autoplay muted loop playsinline preload="auto"
              style="width:100%;border-radius:14px;max-height:400px;background:#000;display:block;box-shadow:0 8px 32px rgba(0,0,0,0.35);">
              Your browser does not support the video tag.
            </video>
          <?php else: ?>
            <div style="position:relative;padding-bottom:56.25%;border-radius:14px;overflow:hidden;background:#000;box-shadow:0 8px 32px rgba(0,0,0,0.35);">
              <iframe src="<?= htmlspecialchars($vid) ?>"
                style="position:absolute;inset:0;width:100%;height:100%;border:none;"
                allow="autoplay; fullscreen" allowfullscreen loading="lazy"></iframe>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Description -->
        <div class="listing-info-box mb-4">
          <h3 style="font-size:1.3rem;color:var(--primary);margin-bottom:1rem;">About this Place</h3>
          <p style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($listing['description'])) ?>
          </p>
        </div>

        <!-- Amenities -->
        <?php if ($listing['amenities']): ?>
          <div class="listing-info-box mb-4">
            <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:1rem;">Amenities & Features</h3>
            <div style="display:flex;flex-wrap:wrap;gap:0.6rem;">
              <?php foreach (explode(',', $listing['amenities']) as $amenity): ?>
                <span
                  style="background:var(--accent-pale);color:var(--primary);padding:5px 14px;border-radius:100px;font-size:0.82rem;font-weight:600;">
                  <i class="fas fa-check me-1" style="color:var(--accent);"></i>
                  <?= htmlspecialchars(trim($amenity)) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Map -->
        <?php if ($listing['latitude'] && $listing['longitude']): ?>
          <div class="listing-info-box mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h3 style="font-size:1.1rem;color:var(--primary);margin:0;">Location Map</h3>
              <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $listing['latitude'] ?>,<?= $listing['longitude'] ?>"
                target="_blank" class="btn-primary-main" style="padding:0.5rem 1.2rem;font-size:0.82rem;">
                <i class="fas fa-directions me-1"></i> Get Directions
              </a>
            </div>
            <div id="detailMap" class="detail-map"></div>
          </div>
        <?php endif; ?>

        <!-- Reviews -->
        <div class="listing-info-box">
          <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:1.25rem;">
            <i class="fas fa-star me-2" style="color:var(--gold);"></i> Reviews (<?= count($reviews) ?>)
          </h3>

          <div id="reviewsList" data-listing-id="<?= $listing['id'] ?>">
          <?php if ($reviews): ?>
            <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;">
              <?php foreach ($reviews as $review): ?>
                <div class="review-item" data-review-id="<?= $review['id'] ?>"
                  style="background:var(--gray-50);border-radius:12px;padding:1.25rem;border-left:3px solid var(--accent);">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <strong
                      style="color:var(--primary);font-size:0.92rem;"><?= htmlspecialchars($review['reviewer_name'] ?: 'Anonymous') ?></strong>
                    <div class="stars" style="font-size:0.85rem;">
                      <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></div>
                  </div>
                  <p style="color:var(--text-muted);font-size:0.87rem;margin:0;"><?= htmlspecialchars($review['comment']) ?>
                  </p>
                  <div style="font-size:0.75rem;color:var(--gray-500);margin-top:0.5rem;">
                    <?= date('F j, Y', strtotime($review['created_at'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="color:var(--text-muted);font-size:0.88rem;">No reviews yet. Be the first to leave one!</p>
          <?php endif; ?>
          </div><!-- /#reviewsList -->

          <!-- Review Form -->
          <div style="background:var(--off-white);border-radius:12px;padding:1.5rem;border:1px solid var(--gray-100);">
            <h4 style="font-size:1rem;color:var(--primary);margin-bottom:1rem;">Leave a Review</h4>
            <form id="reviewForm">
              <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label-main">Your Name</label>
                  <input type="text" name="reviewer_name" class="form-control-main" placeholder="Juan dela Cruz">
                </div>
                <div class="col-md-6">
                  <label class="form-label-main">Rating</label>
                  <select name="rating" class="form-control-main" required>
                    <option value="">Select Rating</option>
                    <option value="5">★★★★★ Excellent (5)</option>
                    <option value="4">★★★★☆ Very Good (4)</option>
                    <option value="3">★★★☆☆ Good (3)</option>
                    <option value="2">★★☆☆☆ Fair (2)</option>
                    <option value="1">★☆☆☆☆ Poor (1)</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label-main">Your Review</label>
                  <textarea name="comment" class="form-control-main" rows="3" placeholder="Share your experience..."
                    required></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn-primary-main">
                    <i class="fas fa-star me-2"></i>Submit Review
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- SIDEBAR -->
      <div class="col-lg-4">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="btn-outline-listing-back mb-4">
          <i class="fas fa-arrow-left"></i> Go Back
        </a>

        <!-- Info Card -->
        <div class="listing-info-box mb-4" style="position:sticky;top:90px;">
          <h4
            style="font-size:1.1rem;color:var(--primary);margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100);">
            <i class="fas fa-info-circle me-2" style="color:var(--accent);"></i> Place Information
          </h4>

          <?php if ($listing['barangay']): ?>
            <div class="info-row">
              <i class="fas fa-map-marker-alt ir-icon"></i>
              <div class="ir-label">Barangay</div>
              <div class="ir-value"><?= htmlspecialchars($listing['barangay']) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($listing['operating_hours']): ?>
            <div class="info-row">
              <i class="fas fa-clock ir-icon"></i>
              <div class="ir-label">Hours</div>
              <div class="ir-value"><?= htmlspecialchars($listing['operating_hours']) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($listing['entrance_fee']): ?>
            <div class="info-row">
              <i class="fas fa-ticket-alt ir-icon"></i>
              <div class="ir-label">Fee</div>
              <div class="ir-value"><?= htmlspecialchars($listing['entrance_fee']) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($listing['contact']): ?>
            <div class="info-row">
              <i class="fas fa-phone ir-icon"></i>
              <div class="ir-label">Contact</div>
              <div class="ir-value"><?= htmlspecialchars($listing['contact']) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($listing['email']): ?>
            <div class="info-row" style="align-items:flex-start;">
              <i class="fas fa-envelope ir-icon" style="margin-top:3px;"></i>
              <div class="ir-label">Email</div>
              <div class="ir-value" style="word-break:break-all;overflow-wrap:anywhere;min-width:0;font-size:0.82rem;"><a
                  href="mailto:<?= htmlspecialchars($listing['email']) ?>"
                  style="color:var(--primary-mid);"><?= htmlspecialchars($listing['email']) ?></a></div>
            </div>
          <?php endif; ?>

          <?php if ($listing['website']): ?>
            <div class="info-row">
              <i class="fas fa-globe ir-icon"></i>
              <div class="ir-label">Website</div>
              <div class="ir-value"><a href="<?= htmlspecialchars($listing['website']) ?>" target="_blank"
                  style="color:var(--primary-mid);">Visit Website</a></div>
            </div>
          <?php endif; ?>

          <div class="info-row">
            <i class="fas fa-eye ir-icon"></i>
            <div class="ir-label">Views</div>
            <div class="ir-value"><?= number_format($listing['views']) ?> times</div>
          </div>

          <!-- Direction Button -->
          <?php if ($listing['latitude'] && $listing['longitude']): ?>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $listing['latitude'] ?>,<?= $listing['longitude'] ?>"
              target="_blank" class="btn-primary-main w-100 mt-3" style="justify-content:center;padding:0.85rem;">
              <i class="fas fa-directions me-2"></i> Get Directions
            </a>
          <?php endif; ?>

          <!-- Share Buttons -->
          <div class="share-sep" style="margin-top:1rem;padding-top:1rem;">
            <div class="share-label"
              style="font-size:0.8rem;font-weight:700;margin-bottom:0.6rem;text-transform:uppercase;letter-spacing:0.06em;">
              Share this Place</div>
            <div style="display:flex;gap:0.5rem;">
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") ?>"
                target="_blank"
                style="background:#1877f2;color:white;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?= urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") ?>&text=<?= urlencode($listing['name'] . ' - San Enrique Tourism') ?>"
                target="_blank"
                style="background:#1da1f2;color:white;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                <i class="fab fa-twitter"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer-main">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-4">
          <div class="footer-logo">
            <div class="d-flex align-items-center gap-3">
              <img src="assets/images/logo-tourism.svg" alt="San Enrique" class="footer-logo-img">
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
          <div class="footer-social">
            <a href="#" class="social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-btn" title="Twitter"><i class="fab fa-twitter"></i></a>
          </div>
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

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/main.js"></script>
  <?php if ($listing['latitude'] && $listing['longitude']): ?>
    <script>
      var detailMapData = {
        lat: <?= (float) $listing['latitude'] ?>,
        lng: <?= (float) $listing['longitude'] ?>,
        name: '<?= addslashes(htmlspecialchars($listing['name'])) ?>'
      };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap" defer></script>
  <?php endif; ?>

  <!-- ═══════════ LISTING UX JS ═══════════ -->
  <script>
  // Navbar scroll glass effect
  (function(){
    var nav = document.querySelector('.navbar-main');
    if(!nav) return;
    window.addEventListener('scroll',function(){
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
  })();
  </script>

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
    }, 190000);
  });
</script>
  <script src="assets/js/live-update.js"></script>
</body>

</html>