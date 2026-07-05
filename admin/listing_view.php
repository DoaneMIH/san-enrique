<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$unreadMsgs = getDB()->query("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
if (!$slug) {
  header('Location: listings.php');
  exit;
}

// Load listing (regardless of status for admin view)
$db = getDB();
$slugEsc = $db->real_escape_string($slug);
$result = $db->query("SELECT l.*, c.name as category_name, c.icon, c.color, c.slug as cat_slug
                       FROM listings l
                       JOIN categories c ON l.category_id = c.id
                       WHERE l.slug = '$slugEsc' LIMIT 1");
if (!$result || $result->num_rows === 0) {
  header('Location: listings.php');
  exit;
}
$listing = $result->fetch_assoc();

$reviews = $db->query("SELECT * FROM reviews WHERE listing_id = {$listing['id']} ORDER BY created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($listing['name']) ?> - Admin View</title>
    <link rel="shortcut icon" type="x-icon" href="../assets/images/san-enrique-logo.jpg">

  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

  <!-- SIDEBAR -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
      <div class="brand-logo">🌿</div>
      <div>
        <div class="brand-text">San Enrique</div>
        <div class="brand-sub">Tourism Hub Admin</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <a href="dashboard.php" class="admin-nav-link">
        <i class="fas fa-home"></i> Dashboard
      </a>
      <a href="listings.php" class="admin-nav-link active">
        <i class="fas fa-map-marker-alt"></i> Listings
      </a>
      <a href="categories.php" class="admin-nav-link">
        <i class="fas fa-th-large"></i> Categories
      </a>
      <a href="events.php" class="admin-nav-link">
        <i class="fas fa-calendar-alt"></i> Events
      </a>

      <div class="nav-section-label">Communication</div>
      <a href="messages.php" class="admin-nav-link">
        <i class="fas fa-envelope"></i> Messages
        <?php if ($unreadMsgs > 0): ?>
          <span class="sidebar-badge"><?= $unreadMsgs ?></span>
        <?php endif; ?>
      </a>
      <a href="reviews.php" class="admin-nav-link">
        <i class="fas fa-star"></i> Reviews
      </a>

      <div class="nav-section-label">System</div>
      <a href="../index.php" class="admin-nav-link" target="_blank">
        <i class="fas fa-external-link-alt"></i> View Website
      </a>
      <a href="../map.php" class="admin-nav-link" target="_blank">
        <i class="fas fa-map"></i> View Map
      </a>
      <a href="settings.php" class="admin-nav-link">
        <i class="fas fa-cog"></i> Settings
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
        <div>
          <div class="user-name"><?= htmlspecialchars($admin['name']) ?></div>
          <div class="user-role"><?= ucfirst($admin['role']) ?></div>
        </div>
        <a href="logout.php" class="btn-logout" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="admin-content">
    <div class="admin-topbar">
      <div>
        <button class="d-lg-none" onclick="toggleSidebar()"
          class="topbar-menu-btn">
          <i class="fas fa-bars"></i>
        </button>
        <span class="topbar-title">Listing Detail</span>
        <div class="topbar-breadcrumb">
          <a href="listings.php" class="topbar-breadcrumb">Listings</a>
          <span class="separator">/</span>
          <?= htmlspecialchars($listing['name']) ?>
        </div>
      </div>
      <div class="topbar-actions">
        <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-primary">
          <i class="fas fa-pencil-alt"></i> Edit Listing
        </a>
      </div>
    </div>

    <div class="admin-main">

      <!-- Action Bar -->
      <div class="lv-action-bar">
        <a href="listings.php" class="btn-admin-secondary">
          <i class="fas fa-arrow-left me-1"></i> Back to Listings
        </a>
        <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-primary">
          <i class="fas fa-pencil-alt me-1"></i> Edit
        </a>
        <a href="../listing.php?slug=<?= urlencode($listing['slug']) ?>" target="_blank" class="btn-admin-secondary">
          <i class="fas fa-external-link-alt me-1"></i> Public View
        </a>
      </div>

      <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">

          <!-- Hero Image -->
          <div class="lv-hero">
            <img src="<?= htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 900, 400)) ?>"
              alt="<?= htmlspecialchars($listing['name']) ?>"
              onerror="this.src='https://placehold.co/900x400/2d6a4f/ffffff?text=No+Image'">
            <div class="lv-hero-overlay">
              <div class="lv-badge-row">
                <span class="lv-cat-badge">
                  <i class="<?= htmlspecialchars($listing['icon']) ?> me-1"></i>
                  <?= htmlspecialchars($listing['category_name']) ?>
                </span>
                <?php if ($listing['is_featured']): ?>
                  <span class="lv-feat-badge">★ Featured</span>
                <?php endif; ?>
                <span class="lv-status-badge <?= $listing['status'] ?>">
                  <?= ucfirst($listing['status']) ?>
                </span>
              </div>
              <div class="lv-hero-title"><?= htmlspecialchars($listing['name']) ?></div>
              <?php if ($listing['address']): ?>
                <div class="lv-hero-color-text">
                  <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($listing['address']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Description -->
          <?php
          /**
           * Strip Word/Outlook "MsoNormal" junk and reduce to clean semantic HTML.
           * Keeps: b, strong, i, em, u, ul, ol, li, p, br, span (inline styles only),
           *        div, h1-h4, blockquote — discards class/lang/office attributes.
           */
          function cleanRichContent(string $html): string {
            // 1. Remove <o:p> and other Office namespace tags
            $html = preg_replace('#</?o:[^>]*>#i', '', $html);
            // 2. Unwrap <font> tags — keep inner content, discard the tag itself
            //    (execCommand bold wraps in <font color="..."> due to CSS inheritance)
            $html = preg_replace('#<font[^>]*>(.*?)</font>#is', '$1', $html);
            // 3. Strip color: and background-color: from inline styles injected by execCommand
            $html = preg_replace('/\s*color\s*:\s*[^;";]+;?\s*/i', '', $html);
            $html = preg_replace('/\s*background-color\s*:\s*[^;";]+;?\s*/i', '', $html);
            // 4. Strip class="Mso*" and lang="*" and mso-* inline styles
            $html = preg_replace('/\s+class="[^"]*Mso[^"]*"/i', '', $html);
            $html = preg_replace('/\s+lang="[^"]*"/i', '', $html);
            $html = preg_replace('/\bmso-[^;";]+;?\s*/i', '', $html);
            // 5. Strip font-size inline styles
            $html = preg_replace('/\s*font-size\s*:\s*[^;";]+;?\s*/i', '', $html);
            // 6. Remove empty style="" and color="" attributes left behind
            $html = preg_replace('/\s+style="\s*"/i', '', $html);
            $html = preg_replace('/\s+color="[^"]*"/i', '', $html);
            // 7. Unwrap <span> tags that have no remaining attributes
            $html = preg_replace('#<span\s*>([^<]*)</span>#i', '$1', $html);
            // 8. Collapse runs of &nbsp; into a single space
            $html = preg_replace('/(\s*&nbsp;\s*)+/', ' ', $html);
            // 9. Remove empty <p> tags
            $html = preg_replace('#<p[^>]*>\s*</p>#i', '', $html);
            // 10. Trim
            return trim($html);
          }
          $cleanDesc = cleanRichContent($listing['description'] ?? '');
          ?>
          <div class="lv-card">
            <div class="lv-card-title">
              <i class="fas fa-align-left"></i> Description
            </div>
            <?php if ($cleanDesc): ?>
              <div class="lv-rich-content"><?= $cleanDesc ?></div>
            <?php else: ?>
              <p class="lv-no-desc">No description provided.</p>
            <?php endif; ?>
          </div>

          <!-- Gallery -->
          <?php
            $galleryPhotos = json_decode($listing['gallery'] ?? '[]', true) ?: [];
          ?>
          <?php if (!empty($galleryPhotos)): ?>
          <div class="lv-card">
            <div class="lv-card-title lv-card-title-between">
              <span><i class="fas fa-images"></i> Photo Gallery</span>
              <span class="lv-card-photo-count"><?= count($galleryPhotos) ?> photo<?= count($galleryPhotos) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="lv-gallery-grid">
              <?php foreach ($galleryPhotos as $gi => $gPhoto):
                if (strpos($gPhoto, 'http://') === 0 || strpos($gPhoto, 'https://') === 0) {
                  $gUrl = $gPhoto;
                } else {
                  $gClean = preg_replace('#^(\.\./)+#', '', $gPhoto);
                  $gUrl   = BASE_URL . '/' . ltrim($gClean, '/');
                }
              ?>
              <div class="lv-gallery-item">
                <img src="<?= htmlspecialchars($gUrl) ?>"
                     style="width:100%;height:100%;object-fit:cover;" onerror="this.src='https://placehold.co/130x98/1b4332/fff?text=IMG'">
                <form method="POST" action="gallery_delete.php" class="lv-gallery-delete-form"
                      onsubmit="return confirm('Remove this photo from the gallery?');">
                  <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                  <input type="hidden" name="photo_path" value="<?= htmlspecialchars($gPhoto, ENT_QUOTES) ?>">
                  <button type="submit"
                    class="lv-gallery-delete-btn"
                    title="Remove photo">
                    <i class="fas fa-times"></i>
                  </button>
                </form>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="lv-gallery-note">
              <i class="fas fa-info-circle me-1"></i>
              To add more photos or remove all, use <a href="listings.php?action=edit&id=<?= $listing['id'] ?>">Edit Listing</a>.
            </div>
          </div>
          <?php endif; ?>

          <!-- Video -->
          <?php if (!empty($listing['video'])): ?>
          <?php
            $vid = $listing['video'];
            $isUploadedVid = strpos($vid, '../uploads/') === 0;
            $vidPublicUrl  = '';
            if ($isUploadedVid) {
              $vClean = preg_replace('#^(\.\./)+#', '', $vid);
              $vidPublicUrl = BASE_URL . '/' . ltrim($vClean, '/');
            }
            // Detect YouTube or Vimeo for embed
            $isYoutube = preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $vid, $ytm);
            $isVimeo   = preg_match('/vimeo\.com\/(\d+)/', $vid, $vim);
          ?>
          <div class="lv-card">
            <div class="lv-card-title">
              <i class="fas fa-video"></i> Listing Video
            </div>
            <?php if ($isYoutube): ?>
              <div class="lv-video-wrap">
                <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytm[1]) ?>"
                  style="position:absolute;inset:0;width:100%;height:100%;border:none;" allowfullscreen loading="lazy"></iframe>
              </div>
            <?php elseif ($isVimeo): ?>
              <div class="lv-video-wrap">
                <iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($vim[1]) ?>"
                  style="position:absolute;inset:0;width:100%;height:100%;border:none;" allowfullscreen loading="lazy"></iframe>
              </div>
            <?php elseif ($isUploadedVid): ?>
              <video src="<?= htmlspecialchars($vidPublicUrl) ?>" controls
                style="width:100%;border-radius:10px;max-height:320px;background:#000;display:block;">
                Your browser does not support the video tag.
              </video>
            <?php else: ?>
              <div class="lv-video-wrap">
                <iframe src="<?= htmlspecialchars($vid) ?>"
                  style="position:absolute;inset:0;width:100%;height:100%;border:none;" allowfullscreen loading="lazy"></iframe>
              </div>
            <?php endif; ?>
            <div class="lv-gallery-note">
              <i class="fas fa-info-circle me-1"></i>
              To change or remove this video, use
              <a href="listings.php?action=edit&id=<?= $listing['id'] ?>">Edit Listing</a>.
            </div>
          </div>
          <?php endif; ?>

          <!-- Amenities -->
          <?php if ($listing['amenities']): ?>
            <div class="lv-card">
              <div class="lv-card-title">
                <i class="fas fa-check-circle"></i> Amenities &amp; Features
              </div>
              <div class="lv-amenities-wrap">
                <?php foreach (explode(',', $listing['amenities']) as $amenity): ?>
                  <span class="lv-amenity-tag">
                    <i class="fas fa-check lv-amenity-check"></i>
                    <?= htmlspecialchars(trim($amenity)) ?>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Map -->
          <?php if ($listing['latitude'] && $listing['longitude']): ?>
            <div class="lv-card">
              <div class="lv-card-title d-flex justify-content-between align-items-center"
                class="lv-card-title lv-card-title-borderless">
                <span><i class="fas fa-map"></i> Location Map</span>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $listing['latitude'] ?>,<?= $listing['longitude'] ?>"
                  target="_blank" class="btn-admin-secondary btn-map-link">
                  <i class="fas fa-directions me-1"></i> Get Directions
                </a>
              </div>
              <div class="lv-map">
                <div id="lvMap"></div>
              </div>
              <div class="lv-map-hint">
                Coordinates: <?= number_format((float) $listing['latitude'], 6) ?>,
                <?= number_format((float) $listing['longitude'], 6) ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Reviews -->
          <div class="lv-card">
            <div class="lv-card-title">
              <i class="fas fa-star lv-stars"></i> Reviews
              <span
                class="lv-count-label"><?= count($reviews) ?>
                total</span>
            </div>
            <?php if ($reviews): ?>
              <?php
              $avg = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
              ?>
              <div
                class="lv-review-summary">
                <div class="lv-review-avg-val"><?= $avg ?></div>
                <div>
                  <div class="lv-review-avg-stars">
                    <?= str_repeat('★', round($avg)) . str_repeat('☆', 5 - round($avg)) ?></div>
                  <div class="lv-review-avg-count">Average of <?= count($reviews) ?>
                    review<?= count($reviews) !== 1 ? 's' : '' ?></div>
                </div>
              </div>
              <?php foreach ($reviews as $review): ?>
                <div class="lv-review">
                  <div class="lv-review-header">
                    <span class="lv-reviewer"><?= htmlspecialchars($review['reviewer_name'] ?: 'Anonymous') ?></span>
                    <span
                      class="lv-stars"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></span>
                  </div>
                  <p class="lv-review-text"><?= htmlspecialchars($review['comment']) ?></p>
                  <div class="lv-review-date"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="lv-no-reviews">No reviews yet.</p>
            <?php endif; ?>
          </div>

        </div><!-- /col-lg-8 -->

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">
          <div class="lv-card" style="position:sticky;top:80px;">
            <div class="lv-card-title">
              <i class="fas fa-info-circle"></i> Listing Info
            </div>

            <div class="lv-info-grid">

              <div class="lv-info-item">
                <span class="lv-info-label">ID</span>
                <span class="lv-info-value">#<?= $listing['id'] ?></span>
              </div>

              <div class="lv-info-item">
                <span class="lv-info-label">Status</span>
                <span class="lv-info-value">
                  <span class="lv-status-badge <?= $listing['status'] ?>"><?= ucfirst($listing['status']) ?></span>
                </span>
              </div>

              <div class="lv-info-item">
                <span class="lv-info-label">Category</span>
                <span class="lv-info-value">
                  <i class="<?= htmlspecialchars($listing['icon']) ?> me-1"
                    style="color:<?= htmlspecialchars($listing['color']) ?>;"></i>
                  <?= htmlspecialchars($listing['category_name']) ?>
                </span>
              </div>

              <div class="lv-info-item">
                <span class="lv-info-label">Featured</span>
                <span class="lv-info-value"><?= $listing['is_featured'] ? '★ Yes' : 'No' ?></span>
              </div>

              <?php if ($listing['barangay']): ?>
                <div class="lv-info-item">
                  <span class="lv-info-label">Barangay</span>
                  <span class="lv-info-value"><?= htmlspecialchars($listing['barangay']) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['operating_hours']): ?>
                <div class="lv-info-item">
                  <span class="lv-info-label">Hours</span>
                  <span class="lv-info-value"><?= htmlspecialchars($listing['operating_hours']) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['entrance_fee']): ?>
                <div class="lv-info-item">
                  <span class="lv-info-label">Entrance Fee</span>
                  <span class="lv-info-value"><?= htmlspecialchars($listing['entrance_fee']) ?></span>
                </div>
              <?php endif; ?>

              <div class="lv-info-item">
                <span class="lv-info-label">Views</span>
                <span class="lv-info-value"><?= number_format($listing['views']) ?></span>
              </div>

              <?php if ($listing['slug']): ?>
                <div class="lv-info-item lv-info-item-full">
                  <span class="lv-info-label">Slug</span>
                  <span class="lv-info-value"
                    class="lv-slug-code"><?= htmlspecialchars($listing['slug']) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['contact']): ?>
                <div class="lv-info-item lv-info-item-full">
                  <span class="lv-info-label">Contact</span>
                  <span class="lv-info-value"><?= htmlspecialchars($listing['contact']) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['email']): ?>
                <div class="lv-info-item lv-info-item-full">
                  <span class="lv-info-label">Email</span>
                  <span class="lv-info-value">
                    <a
                      href="mailto:<?= htmlspecialchars($listing['email']) ?>"><?= htmlspecialchars($listing['email']) ?></a>
                  </span>
                </div>
              <?php endif; ?>

              <?php if ($listing['website']): ?>
                <div class="lv-info-item lv-info-item-full">
                  <span class="lv-info-label">Website</span>
                  <span class="lv-info-value">
                    <a href="<?= htmlspecialchars($listing['website']) ?>" target="_blank">
                      <?= htmlspecialchars($listing['website']) ?>
                    </a>
                  </span>
                </div>
              <?php endif; ?>

              <?php if ($listing['created_at']): ?>
                <div class="lv-info-item">
                  <span class="lv-info-label">Created</span>
                  <span class="lv-info-value"><?= date('M j, Y', strtotime($listing['created_at'])) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['updated_at']): ?>
                <div class="lv-info-item">
                  <span class="lv-info-label">Updated</span>
                  <span class="lv-info-value"><?= date('M j, Y', strtotime($listing['updated_at'])) ?></span>
                </div>
              <?php endif; ?>

            </div><!-- /lv-info-grid -->

            <!-- Quick Actions -->
            <div class="lv-action-footer">
              <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-primary btn-full-center">
                <i class="fas fa-pencil-alt me-2"></i>Edit This Listing
              </a>
              <a href="../listing.php?slug=<?= urlencode($listing['slug']) ?>" target="_blank"
                class="btn-admin-secondary btn-full-center">
                <i class="fas fa-external-link-alt me-2"></i>Public View
              </a>
            </div>
          </div>
        </div><!-- /col-lg-4 -->
      </div><!-- /row -->
    </div><!-- /admin-main -->
  </div><!-- /admin-content -->

  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleSidebar() {
      $('#adminSidebar').toggleClass('open');
      $('#sidebarOverlay').toggleClass('d-none');
    }
  </script>
  <?php if ($listing['latitude'] && $listing['longitude']): ?>
    <script>
      function initAdminMap() {
        var pos = { lat: <?= (float) $listing['latitude'] ?>, lng: <?= (float) $listing['longitude'] ?> };
        var map = new google.maps.Map(document.getElementById('lvMap'), {
          center: pos, zoom: 15,
          mapTypeControl: false,
          streetViewControl: false,
          fullscreenControl: false,
          styles: [
            { featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] }
          ]
        });
        new google.maps.Marker({
          position: pos, map: map,
          title: '<?= addslashes(htmlspecialchars($listing['name'])) ?>',
          icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
          }
        });
      }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initAdminMap"
      defer></script>
  <?php endif; ?>
</body>

</html>