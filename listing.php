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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($listing['name']) ?> - <?= SITE_NAME ?></title>
  <meta name="description" content="<?= htmlspecialchars(substr($listing['description'], 0, 155)) ?>">
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Fix long email/text overflow in sidebar info rows */
    .info-row {
      align-items: flex-start;
    }

    .ir-value {
      min-width: 0;
      word-break: break-all;
      overflow-wrap: anywhere;
    }

    .ir-value a {
      color: var(--primary-mid);
    }
  </style>
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
          <a href="explore.php" class="nav-link-main">Explore</a>
          <a href="map.php" class="nav-link-main">Map</a>
          <a href="index.php#events" class="nav-link-main">Events</a>
          <a href="index.php#about" class="nav-link-main">About</a>
          <a href="index.php#contact" class="nav-link-main">Contact</a>
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
        <div class="listing-badge"
          style="color:<?= htmlspecialchars($listing['color']) ?>;background:rgba(255,255,255,0.15);border-radius:100px;padding:6px 16px;font-size:0.8rem;font-weight:700;border:1px solid rgba(255,255,255,0.3);">
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
          alt="<?= htmlspecialchars($listing['name']) ?>" class="listing-detail-img mb-4"
          onerror="this.src='https://placehold.co/1200x600/1b4332/ffffff?text=<?= urlencode($listing['name']) ?>'">

        <!-- Gallery Photos -->
        <?php
          $galleryPhotos = json_decode($listing['gallery'] ?? '[]', true) ?: [];
          if (!empty($galleryPhotos)):
        ?>
        <div class="listing-info-box mb-4">
          <h3 style="font-size:1.1rem;color:var(--primary);margin-bottom:1rem;">
            <i class="fas fa-images me-2" style="color:var(--accent);"></i> Photo Gallery
          </h3>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
            <?php foreach ($galleryPhotos as $i => $gPhoto): ?>
            <div style="border-radius:10px;overflow:hidden;aspect-ratio:4/3;cursor:pointer;"
                 onclick="openLightbox(<?= $i ?>)">
              <img src="<?= htmlspecialchars(listingImage($gPhoto, $listing['name'], 400, 300)) ?>"
                   alt="<?= htmlspecialchars($listing['name']) ?> photo <?= $i+1 ?>"
                   style="width:100%;height:100%;object-fit:cover;transition:transform .3s;"
                   onmouseover="this.style.transform='scale(1.05)'"
                   onmouseout="this.style.transform='scale(1)'"
                   onerror="this.src='https://placehold.co/400x300/1b4332/ffffff?text=Photo'">
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Lightbox -->
        <div id="galleryLightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column;">
          <button onclick="closeLightbox()" style="position:absolute;top:18px;right:22px;background:none;border:none;color:white;font-size:1.8rem;cursor:pointer;line-height:1;">&#10005;</button>
          <button onclick="prevPhoto()" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.15);border:none;color:white;font-size:1.6rem;width:44px;height:44px;border-radius:50%;cursor:pointer;">&#8249;</button>
          <img id="lightboxImg" src="" alt="" style="max-width:90vw;max-height:82vh;object-fit:contain;border-radius:8px;">
          <div id="lightboxCaption" style="color:rgba(255,255,255,.6);font-size:0.82rem;margin-top:10px;"></div>
          <button onclick="nextPhoto()" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.15);border:none;color:white;font-size:1.6rem;width:44px;height:44px;border-radius:50%;cursor:pointer;">&#8250;</button>
        </div>
        <script>
          var galleryPhotos = <?= json_encode(array_map(fn($p) => listingImage($p, $listing['name'], 1200, 800), $galleryPhotos)) ?>;
          var lightboxIdx = 0;
          function openLightbox(i) {
            lightboxIdx = i;
            showLightboxPhoto();
            document.getElementById('galleryLightbox').style.display = 'flex';
            document.body.style.overflow = 'hidden';
          }
          function closeLightbox() {
            document.getElementById('galleryLightbox').style.display = 'none';
            document.body.style.overflow = '';
          }
          function showLightboxPhoto() {
            document.getElementById('lightboxImg').src = galleryPhotos[lightboxIdx];
            document.getElementById('lightboxCaption').textContent = (lightboxIdx + 1) + ' / ' + galleryPhotos.length;
          }
          function prevPhoto() { lightboxIdx = (lightboxIdx - 1 + galleryPhotos.length) % galleryPhotos.length; showLightboxPhoto(); }
          function nextPhoto() { lightboxIdx = (lightboxIdx + 1) % galleryPhotos.length; showLightboxPhoto(); }
          document.addEventListener('keydown', function(e) {
            if (document.getElementById('galleryLightbox').style.display === 'flex') {
              if (e.key === 'ArrowLeft') prevPhoto();
              if (e.key === 'ArrowRight') nextPhoto();
              if (e.key === 'Escape') closeLightbox();
            }
          });
        </script>
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

          <?php if ($reviews): ?>
            <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;">
              <?php foreach ($reviews as $review): ?>
                <div
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
        <a href="javascript:history.back()" class="btn-outline-main w-100 mb-4"
          style="justify-content:center;display:flex;align-items:center;gap:0.5rem;padding:0.7rem;">
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
          <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--gray-100);">
            <div
              style="font-size:0.8rem;font-weight:700;color:var(--text-muted);margin-bottom:0.6rem;text-transform:uppercase;letter-spacing:0.06em;">
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
</body>

</html>