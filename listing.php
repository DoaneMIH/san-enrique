<?php
require_once 'includes/functions.php';
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
if (!$slug) { header('Location: explore.php'); exit; }
$listing = getListing($slug);
if (!$listing) { header('Location: explore.php'); exit; }
$db = getDB();
$reviews = $db->query("SELECT * FROM reviews WHERE listing_id = {$listing['id']} ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($listing['name']) ?> - <?= SITE_NAME ?></title>
<meta name="description" content="<?= htmlspecialchars(substr($listing['description'], 0, 155)) ?>">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Fix long email/text overflow in sidebar info rows */
.info-row { align-items: flex-start; }
.ir-value  { min-width: 0; word-break: break-all; overflow-wrap: anywhere; }
.ir-value a { color: var(--primary-mid); }
</style>
</head>
<body>

<div id="pageLoader" class="page-loader">
  <div class="loader-logo"><?= SITE_NAME ?></div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
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
        <a href="#home" class="nav-link-main active">Home</a>
        <a href="#categories" class="nav-link-main">Explore</a>
        <a href="map.php" class="nav-link-main">Map</a>
        <a href="#events" class="nav-link-main">Events</a>
        <a href="#about" class="nav-link-main">About</a>
        <a href="#contact" class="nav-link-main">Contact</a>
        <a href="admin/login.php" class="btn-nav-admin ms-3">
          <i class="fas fa-shield-alt me-1"></i> Admin
        </a>
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
        <li class="breadcrumb-item"><a href="explore.php">Explore</a></li>
        <li class="breadcrumb-item"><a href="explore.php?category=<?= htmlspecialchars($listing['cat_slug'] ?? '') ?>"><?= htmlspecialchars($listing['category_name']) ?></a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($listing['name']) ?></li>
      </ol>
    </nav> -->
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <div class="listing-badge" style="color:<?= htmlspecialchars($listing['color']) ?>;background:rgba(255,255,255,0.15);border-radius:100px;padding:6px 16px;font-size:0.8rem;font-weight:700;border:1px solid rgba(255,255,255,0.3);">
        <i class="<?= htmlspecialchars($listing['icon']) ?> me-1"></i>
        <?= htmlspecialchars($listing['category_name']) ?>
      </div>
      <?php if ($listing['is_featured']): ?>
      <div class="featured-badge" style="position:static;">★ Featured</div>
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
           alt="<?= htmlspecialchars($listing['name']) ?>"
           class="listing-detail-img mb-4"
           onerror="this.src='https://placehold.co/1200x600/1b4332/ffffff?text=<?= urlencode($listing['name']) ?>'">

      <!-- Description -->
      <div class="listing-info-box mb-4">
        <h3 style="font-size:1.3rem;color:var(--primary);margin-bottom:1rem;">About this Place</h3>
        <p style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
      </div>

      <!-- Amenities -->
      <?php if ($listing['amenities']): ?>
      <div class="listing-info-box mb-4">
        <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:1rem;">Amenities & Features</h3>
        <div style="display:flex;flex-wrap:wrap;gap:0.6rem;">
          <?php foreach (explode(',', $listing['amenities']) as $amenity): ?>
          <span style="background:var(--accent-pale);color:var(--primary);padding:5px 14px;border-radius:100px;font-size:0.82rem;font-weight:600;">
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
             target="_blank"
             class="btn-primary-main"
             style="padding:0.5rem 1.2rem;font-size:0.82rem;">
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

        <?php if ($reviews): ?>
        <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;">
          <?php foreach ($reviews as $review): ?>
          <div style="background:var(--gray-50);border-radius:12px;padding:1.25rem;border-left:3px solid var(--accent);">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <strong style="color:var(--primary);font-size:0.92rem;"><?= htmlspecialchars($review['reviewer_name'] ?: 'Anonymous') ?></strong>
              <div class="stars" style="font-size:0.85rem;"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></div>
            </div>
            <p style="color:var(--text-muted);font-size:0.87rem;margin:0;"><?= htmlspecialchars($review['comment']) ?></p>
            <div style="font-size:0.75rem;color:var(--gray-500);margin-top:0.5rem;"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:var(--text-muted);font-size:0.88rem;">No reviews yet. Be the first to leave one!</p>
        <?php endif; ?>

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
                <textarea name="comment" class="form-control-main" rows="3" placeholder="Share your experience..." required></textarea>
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
      <!-- Info Card -->
      <div class="listing-info-box mb-4" style="position:sticky;top:90px;">
        <h4 style="font-size:1.1rem;color:var(--primary);margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100);">
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
          <div class="ir-value" style="word-break:break-all;overflow-wrap:anywhere;min-width:0;font-size:0.82rem;"><a href="mailto:<?= htmlspecialchars($listing['email']) ?>" style="color:var(--primary-mid);"><?= htmlspecialchars($listing['email']) ?></a></div>
        </div>
        <?php endif; ?>

        <?php if ($listing['website']): ?>
        <div class="info-row">
          <i class="fas fa-globe ir-icon"></i>
          <div class="ir-label">Website</div>
          <div class="ir-value"><a href="<?= htmlspecialchars($listing['website']) ?>" target="_blank" style="color:var(--primary-mid);">Visit Website</a></div>
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
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--gray-100);">
          <div style="font-size:0.8rem;font-weight:700;color:var(--text-muted);margin-bottom:0.6rem;text-transform:uppercase;letter-spacing:0.06em;">Share this Place</div>
          <div style="display:flex;gap:0.5rem;">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") ?>" target="_blank"
               style="background:#1877f2;color:white;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}") ?>&text=<?= urlencode($listing['name'].' - San Enrique Tourism') ?>" target="_blank"
               style="background:#1da1f2;color:white;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
              <i class="fab fa-twitter"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Back Link
      <div class="text-center">
        <a href="javascript:history.back()" class="btn-outline-main w-100" style="justify-content:center;">
          <i class="fas fa-arrow-left me-1"></i> Go Back
        </a>
      </div> -->
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="footer-main">
  <div class="footer-bottom">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>&copy; <?= date('Y') ?> San Enrique Tourism Hub. All rights reserved.</span>
        <a href="explore.php" style="color:var(--accent-light);font-size:0.82rem;">← Back to Explore</a>
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
  lat: <?= (float)$listing['latitude'] ?>,
  lng: <?= (float)$listing['longitude'] ?>,
  name: '<?= addslashes(htmlspecialchars($listing['name'])) ?>'
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap" defer></script>
<?php endif; ?>
</body>
</html>