<?php
require_once 'includes/functions.php';
$db = getDB();
$categories = getCategories();
$selectedCat = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$listings = getAllListings($selectedCat, $search, 24);
$pageTitle = $selectedCat ? ucfirst($selectedCat) : ($search ? "Search: $search" : 'All Destinations');

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="assets/images/san-enrique-logo.jpg">
  <title>Explore - <?= SITE_NAME ?></title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* ── Base ───────────────────────────────────────────────── */
    body { font-family:'Outfit',sans-serif; margin:0; padding:0; }
    .section-title,.page-hero-title { font-family:'Cormorant Garamond',serif !important; }

    /* ── Full-page parallax background ─────────────────────── */
    body {
      background:
        url('assets/images/13.jpeg')
        center center / cover fixed no-repeat !important;
      background-color: #0d2b1e !important;
    }

    /* Dark overlay on the photo — sits BELOW navbar & content */
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 1;
      background: rgba(8, 22, 13, 0.42);
      pointer-events: none;
    }

    /* ── NAVBAR: sticky, green, always on top ───────────────── */
    .navbar-main {
      position: fixed !important;
      top: 0; left: 0; right: 0;
      z-index: 1000 !important;
      background: rgba(27, 67, 50, 0.97) !important;
      backdrop-filter: blur(20px) !important;
      -webkit-backdrop-filter: blur(20px) !important;
      border-bottom: 1px solid rgba(82, 183, 136, 0.25) !important;
      box-shadow: 0 2px 24px rgba(0,0,0,0.3) !important;
      transition: background .35s, box-shadow .35s !important;
      padding: 0.75rem 0 !important;
    }
    .navbar-main.scrolled {
      background: rgba(13, 43, 30, 0.99) !important;
      box-shadow: 0 4px 32px rgba(0,0,0,0.4) !important;
    }
    .brand-name  { color: #fff !important; }
    .brand-sub   { color: rgba(255,255,255,0.65) !important; }
    .nav-link-main { color: rgba(255,255,255,0.88) !important; }
    .nav-link-main:hover,
    .nav-link-main.active { color: #fff !important; background: rgba(82,183,136,0.18) !important; }

    /* Push page down so content isn't hidden under fixed navbar */
    body > .page-loader ~ * { /* handled by padding on page-hero */ }
    .page-hero { padding-top: 50px !important; }

    /* ── All content layers above the body::before overlay ─── */
    #backToTop, .back-to-top,
    .page-hero, .footer-main { position: relative; z-index: 10; }
    .container.py-5 { position: relative; z-index: 10; }

    /* ── PAGE HERO ──────────────────────────────────────────── */
    .page-hero {
      background: transparent !important;
      padding-bottom: 1rem !important;
      min-height: 220px;
      display: flex; align-items: flex-end;
    }
    .page-hero::after { display: none !important; }
    .page-hero-title {
      font-family: 'Cormorant Garamond',serif !important;
      font-size: clamp(2rem,5vw,3.4rem) !important;
      font-weight: 700 !important;
      color: #fff !important;
      text-shadow: 0 2px 16px rgba(0,0,0,0.4);
      animation: fadeUp .85s cubic-bezier(.2,0,.2,1) both;
    }
    #listingCount {
      color: rgba(255,255,255,0.72) !important;
      animation: fadeUp .85s .12s cubic-bezier(.2,0,.2,1) both;
    }
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(20px); }
      to   { opacity:1; transform:none; }
    }

    /* ── FILTER SIDEBAR → frosted glass ────────────────────── */
    .filter-sidebar {
      background: rgba(13,43,30,0.55) !important;
      backdrop-filter: blur(22px) !important;
      -webkit-backdrop-filter: blur(22px) !important;
      border: 1px solid rgba(82,183,136,0.25) !important;
      border-radius: 20px !important;
      box-shadow: 0 8px 40px rgba(0,0,0,0.3) !important;
    }
    .filter-sidebar .filter-title {
      color: #fff !important;
      border-bottom-color: rgba(255,255,255,0.15) !important;
    }
    /* Search input row */
    .filter-sidebar div[style*="background:var(--gray-50)"],
    .filter-sidebar div[style*="background: var(--gray-50)"] {
      background: rgba(255,255,255,0.1) !important;
      border-color: rgba(255,255,255,0.2) !important;
      border-radius: 10px !important;
    }
    .filter-sidebar input[type="text"] {
      background: transparent !important;
      color: #fff !important;
    }
    .filter-sidebar input[type="text"]::placeholder { color: rgba(255,255,255,.5) !important; }
    .filter-sidebar .fa-search { color: rgba(255,255,255,.65) !important; }

    /* Search submit button */
    .filter-sidebar .btn-primary-main {
      background: linear-gradient(135deg, #1b4332, #40916c) !important;
      border: none !important;
      color: #fff !important;
      font-weight: 700;
      border-radius: 10px !important;
      box-shadow: 0 4px 14px rgba(0,0,0,0.25) !important;
      transition: opacity .2s, transform .2s !important;
    }
    .filter-sidebar .btn-primary-main:hover { opacity:.88; transform:translateY(-1px); }

    /* Category items */
    .category-filter-item {
      color: rgba(255,255,255,0.80) !important;
      border-radius: 10px !important;
      transition: background .2s, color .2s !important;
    }
    .category-filter-item:hover {
      background: rgba(82,183,136,0.18) !important;
      color: #fff !important;
    }
    .category-filter-item.active {
      background: rgba(82,183,136,0.32) !important;
      color: #fff !important;
      border: 1px solid rgba(82,183,136,0.5) !important;
    }
    /* Divider above map button */
    .filter-sidebar div[style*="border-top"] {
      border-top-color: rgba(255,255,255,0.12) !important;
    }
    .filter-sidebar .btn-outline-main {
      border-color: rgba(255,255,255,0.3) !important;
      color: #fff !important;
      background: rgba(255,255,255,0.06) !important;
    }
    .filter-sidebar .btn-outline-main:hover { background: rgba(255,255,255,0.16) !important; }

    /* ── LISTING CARDS → frosted glass ─────────────────────── */
    .listing-card {
      background: rgba(13,43,30,0.45) !important;
      backdrop-filter: blur(18px) !important;
      -webkit-backdrop-filter: blur(18px) !important;
      border: 1px solid rgba(82,183,136,0.22) !important;
      border-radius: 20px !important;
      box-shadow: 0 8px 32px rgba(0,0,0,0.28) !important;
      transition: transform .4s cubic-bezier(.2,0,.2,1), box-shadow .4s, background .3s !important;
      overflow: hidden;
    }
    .listing-card:hover {
      transform: translateY(-10px) !important;
      background: rgba(13,43,30,0.65) !important;
      box-shadow: 0 24px 60px rgba(0,0,0,0.4) !important;
      border-color: rgba(82,183,136,0.45) !important;
    }
    .listing-card-img { position:relative; overflow:hidden; height:220px; display:block; }
    .listing-card-img img {
      width:100%; height:100%; object-fit:cover; display:block;
      transition: transform .6s cubic-bezier(.2,0,.2,1) !important;
    }
    .listing-card:hover .listing-card-img img { transform: scale(1.08) !important; }
    .listing-card-img::after {
      content:''; position:absolute; inset:0;
      background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 60%);
      pointer-events: none;
    }
    .listing-card-body { padding: 1.1rem 1.3rem 1.3rem; }
    .listing-card-title {
      font-family: 'Cormorant Garamond',serif !important;
      font-size: 1.18rem !important; font-weight: 700 !important;
      color: #fff !important;
    }
    .listing-card-desc  { color: rgba(255,255,255,0.68) !important; font-size:.85rem !important; }
    .listing-card-meta span { color: rgba(255,255,255,0.60) !important; }
    .listing-card-meta i    { color: #52b788 !important; }

    .btn-card {
      background: rgba(82,183,136,0.28) !important;
      border: 1px solid rgba(82,183,136,0.5) !important;
      color: #fff !important; border-radius: 10px !important;
      font-weight: 600 !important;
      transition: background .2s, transform .2s !important;
      display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-card:hover { background: rgba(82,183,136,0.52) !important; transform:translateX(3px); }

    .listing-badge {
      background: rgba(0,0,0,0.50) !important;
      backdrop-filter: blur(6px) !important;
      border: 1px solid rgba(255,255,255,0.18) !important;
      color: #fff !important; border-radius: 20px !important;
    }
    .featured-badge { background: rgba(212,160,23,0.88) !important; }

    /* ── Empty state ────────────────────────────────────────── */
    .empty-state {
      background: rgba(13,43,30,0.5) !important;
      backdrop-filter: blur(16px) !important;
      border: 1px solid rgba(82,183,136,0.2) !important;
      border-radius: 20px !important;
    }
    .empty-state i, .empty-state h4, .empty-state p { color: #fff !important; }

    /* ── FOOTER on parallax ─────────────────────────────────── */
    .footer-main {
      padding-top: 1.5rem !important;
      position: relative; z-index: 10;
      background: rgba(8,22,13,0.96) !important;
      backdrop-filter: blur(10px) !important;
    }

    /* ── Scroll-reveal ──────────────────────────────────────── */
    .animate-on-scroll {
      opacity:0; transform:translateY(26px);
      transition: opacity .7s cubic-bezier(.2,0,.2,1), transform .7s cubic-bezier(.2,0,.2,1);
    }
    .animate-on-scroll.visible { opacity:1; transform:none; }
    .animate-on-scroll.delay-1 { transition-delay:.08s; }
    .animate-on-scroll.delay-2 { transition-delay:.16s; }
    .animate-on-scroll.delay-3 { transition-delay:.24s; }
    .animate-on-scroll.delay-4 { transition-delay:.32s; }
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


  <!-- PAGE LOADER -->
  <!-- <div id="pageLoader" class="page-loader">
    <div class="brand-logo"
      style="width:60px;height:60px;background:linear-gradient(135deg,#52b788,#d4a017);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2rem;">
      🌿</div>
    <div class="loader-logo"><?= SITE_NAME ?></div>
    <div class="loader-bar">
      <div class="loader-bar-fill"></div>
    </div>
  </div> -->

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
          <!-- <div class="brand-logo">🌿</div> -->
           <img src="assets/images/san-enrique-logo.jpg" alt="San Enrique" class="navbar-brand-logo-img">
          <div class="brand-text-wrap">
            <div class="brand-name">San Enrique</div>
            <div class="brand-sub">Tourism Hub</div>
          </div>
        </a>
        <div class="d-none d-lg-flex align-items-center gap-1">
          <a href="index.php" class="nav-link-main">Home</a>
          <a href="explore.php" class="nav-link-main active">Explore</a>
          <!-- <a href="map.php" class="nav-link-main">Map</a> -->
          <a href="index.php#events" class="nav-link-main">Events</a>
          <a href="index.php#about" class="nav-link-main">About</a>
          <a href="index.php#contact" class="nav-link-main">Contact</a>
          <!-- <a href="admin/login.php" class="btn-nav-admin ms-3"><i class="fas fa-shield-alt me-1"></i> Admin</a> -->
        </div>
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <div class="collapse" id="mobileNav">
        <div class="d-flex flex-column gap-1 py-2">
          <a href="index.php" class="nav-link-main">Home</a>
          <!-- <a href="explore.php" class="nav-link-main active">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a> -->
        </div>
      </div>
    </div>
  </nav>

  <!-- PAGE HERO -->
  <div class="page-hero">
    <div class="container">
      <!-- <nav aria-label="breadcrumb" class="breadcrumb-nav">
      <ol class="breadcrumb mb-3" style="font-size:0.82rem;">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Explore</li>
        <?php if ($selectedCat): ?><li class="breadcrumb-item active"><?= htmlspecialchars(ucfirst($selectedCat)) ?></li><?php endif; ?>
      </ol>
    </nav> -->
      <h1 class="page-hero-title">
        <?= $search ? "Search: \"$search\"" : ($selectedCat ? ucfirst($selectedCat) : 'Explore Destinations') ?></h1>
      <p id="listingCount" style="color:rgba(255,255,255,0.7);margin-top:0.5rem;">
        <?= count($listings) ?> destination<?= count($listings) !== 1 ? 's' : '' ?> found
      </p>
    </div>
  </div>

  <div class="container py-5">
    <div class="row g-4">
      <!-- SIDEBAR FILTERS -->
      <div class="col-lg-3">
        <div class="filter-sidebar">
          <div class="filter-title"><i class="fas fa-filter me-2" style="color:var(--accent);"></i> Filter by Category
          </div>

          <!-- Search Input -->
          <form action="explore.php" method="GET" class="mb-3">
            <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCat) ?>">
            <div
              style="display:flex;align-items:center;gap:8px;background:var(--gray-50);border:1.5px solid var(--gray-200);border-radius:8px;padding:0.5rem 0.8rem;">
              <i class="fas fa-search" style="color:var(--text-muted);font-size:0.82rem;"></i>
              <input type="text" id="exploreSearch" name="search" value="<?= htmlspecialchars($search) ?>"
                placeholder="Search..."
                style="border:none;background:transparent;outline:none;font-size:0.87rem;font-family:'Nunito',sans-serif;width:100%;"
                autocomplete="off">
            </div>
            <button type="submit" class="btn-primary-main w-100 mt-2" style="padding:0.55rem;">Search</button>
          </form>

          <!-- Category List -->
          <a href="explore.php<?= $search ? '?search=' . urlencode($search) : '' ?>"
            class="category-filter-item <?= !$selectedCat ? 'active' : '' ?>">
            <i class="fas fa-map-marker-alt" style="color:var(--accent);font-size:0.85rem;"></i> All Categories
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="explore.php?category=<?= htmlspecialchars($cat['slug']) ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
              class="category-filter-item <?= $selectedCat === $cat['slug'] ? 'active' : '' ?>">
              <i class="<?= htmlspecialchars($cat['icon']) ?>"
                style="color:<?= htmlspecialchars($cat['color']) ?>;font-size:0.85rem;width:16px;text-align:center;"></i>
              <?= htmlspecialchars($cat['name']) ?>
            </a>
          <?php endforeach; ?>

          <div class="mt-3 pt-3" style="border-top:1px solid var(--gray-100);">
            <a href="map.php" class="btn-outline-main w-100" style="justify-content:center;padding:0.6rem;">
              <i class="fas fa-map me-1"></i> View on Map
            </a>
          </div>
        </div>
      </div>

      <!-- LISTINGS GRID -->
      <div class="col-lg-9" id="listingsContainer">
        <?php if ($listings): ?>
          <div class="row g-4" id="listingsGrid">
            <?php foreach ($listings as $i => $listing): ?>
              <div class="col-md-6 col-xl-4 animate-on-scroll delay-<?= ($i % 4) + 1 ?>">
                <div class="listing-card">
                  <a href="listing.php?slug=<?= urlencode($listing['slug']) ?>" class="listing-card-img" style="text-decoration:none;">
                    <img src="<?= htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 600, 400)) ?>"
                      alt="<?= htmlspecialchars($listing['name']) ?>" loading="lazy"
                      onerror="this.src='https://placehold.co/600x400/1b4332/ffffff?text=No+Image'">
                    <div class="listing-badge" style="color:<?= htmlspecialchars($listing['color']) ?>">
                      <i class="<?= htmlspecialchars($listing['icon']) ?>"></i>
                      <?= htmlspecialchars($listing['category_name']) ?>
                    </div>
                    <?php if ($listing['is_featured']): ?>
                      <div class="featured-badge">★ Featured</div>
                    <?php endif; ?>
                  </a>
                  <div class="listing-card-body">
                    <h3 class="listing-card-title"><?= htmlspecialchars($listing['name']) ?></h3>
                    <?php
                      // Strip all HTML tags, decode entities, then truncate to 120 chars
                      $rawDesc = html_entity_decode(strip_tags($listing['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                      $rawDesc = preg_replace('/\s+/', ' ', trim($rawDesc)); // collapse whitespace
                      $shortDesc = mb_strlen($rawDesc) > 120
                        ? mb_substr($rawDesc, 0, 120) . '...'
                        : $rawDesc;
                    ?>
                    <p class="listing-card-desc"><?= htmlspecialchars($shortDesc) ?></p>
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
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <h4 style="color:var(--primary);margin-bottom:0.5rem;">No destinations found</h4>
            <p>Try adjusting your search or filter criteria.</p>
            <a href="explore.php" class="btn-primary-main mt-3">Clear Filters</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

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
    }, 190000);
  });
</script>

  <script src="assets/js/main.js"></script>

  <script>
  // Scroll reveal
  (function(){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target);}
      });
    },{threshold:0.1});
    document.querySelectorAll('.animate-on-scroll').forEach(function(el){io.observe(el);});
  })();
  // Navbar glass
  (function(){
    var nav = document.querySelector('.navbar-main');
    if(!nav)return;
    window.addEventListener('scroll',function(){
      nav.classList.toggle('scrolled',window.scrollY>50);
    });
  })();
  </script>

  <script>
  // Scroll reveal
  (function(){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target);}
      });
    },{threshold:0.1});
    document.querySelectorAll('.animate-on-scroll').forEach(function(el){io.observe(el);});
  })();
  // Navbar glass on scroll
  (function(){
    var nav=document.querySelector('.navbar-main');
    if(!nav)return;
    window.addEventListener('scroll',function(){
      nav.classList.toggle('scrolled',window.scrollY>50);
    });
  })();
  </script>
</body>

</html>