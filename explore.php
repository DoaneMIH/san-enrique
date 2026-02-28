<?php
require_once 'includes/functions.php';
$db = getDB();
$categories = getCategories();
$selectedCat = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$listings = getAllListings($selectedCat, $search, 24);
$pageTitle = $selectedCat ? ucfirst($selectedCat) : ($search ? "Search: $search" : 'All Destinations');

// Initial DB timestamp for live-update poller
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Explore - <?= SITE_NAME ?></title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <div id="pageLoader" class="page-loader">
    <div class="brand-logo"
      style="width:60px;height:60px;background:linear-gradient(135deg,#52b788,#d4a017);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2rem;">
      🌿</div>
    <div class="loader-logo"><?= SITE_NAME ?></div>
    <div class="loader-bar">
      <div class="loader-bar-fill"></div>
    </div>
  </div>

  <button id="backToTop" class="back-to-top"><i class="fas fa-chevron-up"></i></button>

  <!-- NAVBAR -->
  <nav class="navbar-main scrolled">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between w-100">
        <a href="index.php" class="navbar-brand-wrap text-decoration-none">
          <div class="brand-logo">🌿</div>
          <div class="brand-text-wrap">
            <div class="brand-name">San Enrique</div>
            <div class="brand-sub">Tourism Hub</div>
          </div>
        </a>
        <div class="d-none d-lg-flex align-items-center gap-1">
          <a href="index.php" class="nav-link-main">Home</a>
          <a href="explore.php" class="nav-link-main active">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a>
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
          <a href="explore.php" class="nav-link-main active">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a>
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
                  <div class="listing-card-img">
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
              <div class="brand-logo"
                style="width:44px;height:44px;background:linear-gradient(135deg,#52b788,#d4a017);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">
                🌿</div>
              <div>
                <div style="font-family:'Playfair Display',serif;color:white;font-size:1.1rem;font-weight:700;">San
                  Enrique Tourism Hub</div>
                <div
                  style="font-size:0.7rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.08em;">
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
  <script>
    // Live-update poller bootstrap
    window.pageLoadTimestamp = <?= $initTs ?>;
    window.liveUpdateConfig = {
      page: 'explore',
      category: <?= json_encode($selectedCat) ?>,
      search: <?= json_encode($search) ?>
    };
  </script>
  <script src="assets/js/main.js"></script>
</body>

</html>