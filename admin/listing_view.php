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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($listing['name']) ?> - Admin View</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    /* ── Listing View Specific ── */
    .lv-hero {
      position: relative;
      width: 100%;
      height: 280px;
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 1.5rem;
      background: var(--sidebar-bg, #1b4332);
    }

    .lv-hero img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .lv-hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.65) 0%, transparent 55%);
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 1.5rem 1.75rem;
    }

    .lv-hero-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.75rem;
      font-weight: 700;
      color: #fff;
      margin: 0 0 0.35rem;
      line-height: 1.25;
    }

    .lv-badge-row {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      align-items: center;
    }

    .lv-cat-badge {
      background: rgba(255, 255, 255, 0.18);
      border: 1px solid rgba(255, 255, 255, 0.35);
      color: #fff;
      border-radius: 100px;
      padding: 4px 14px;
      font-size: 0.78rem;
      font-weight: 700;
      backdrop-filter: blur(4px);
    }

    .lv-feat-badge {
      background: #d4a017;
      color: #fff;
      border-radius: 100px;
      padding: 4px 12px;
      font-size: 0.78rem;
      font-weight: 700;
    }

    .lv-status-badge {
      border-radius: 100px;
      padding: 4px 12px;
      font-size: 0.78rem;
      font-weight: 700;
    }

    .lv-status-badge.active {
      background: rgba(82, 183, 136, 0.2);
      color: #2d6a4f;
    }

    .lv-status-badge.inactive {
      background: rgba(220, 53, 69, 0.12);
      color: #c0392b;
    }

    .lv-status-badge.draft {
      background: rgba(255, 193, 7, 0.18);
      color: #856404;
    }

    .lv-card {
      background: var(--content-bg, #fff);
      border: 1px solid var(--border, #e8ede8);
      border-radius: 14px;
      padding: 1.5rem;
      margin-bottom: 1.25rem;
    }

    .lv-card-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--primary, #1b4332);
      margin-bottom: 1rem;
      padding-bottom: 0.6rem;
      border-bottom: 1px solid var(--border, #e8ede8);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .lv-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem 1.25rem;
    }

    @media (max-width: 576px) {
      .lv-info-grid {
        grid-template-columns: 1fr;
      }
    }

    .lv-info-item {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .lv-info-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: var(--text-muted, #6c8c74);
    }

    .lv-info-value {
      font-size: 0.88rem;
      color: var(--primary, #1b4332);
      word-break: break-word;
    }

    .lv-info-value a {
      color: var(--accent, #52b788);
    }

    .lv-amenity-tag {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: rgba(82, 183, 136, 0.1);
      color: #1b4332;
      border-radius: 100px;
      padding: 4px 14px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .lv-map {
      width: 100%;
      height: 260px;
      border-radius: 10px;
      border: 1px solid var(--border, #e8ede8);
      overflow: hidden;
    }

    #lvMap {
      width: 100%;
      height: 100%;
    }

    .lv-review {
      padding: 1rem;
      border-radius: 10px;
      border-left: 3px solid var(--accent, #52b788);
      background: var(--sidebar-bg, #f7faf7);
      margin-bottom: 0.75rem;
    }

    .lv-review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.3rem;
    }

    .lv-reviewer {
      font-weight: 700;
      font-size: 0.88rem;
      color: var(--primary, #1b4332);
    }

    .lv-stars {
      font-size: 0.82rem;
      color: #d4a017;
    }

    .lv-review-text {
      font-size: 0.84rem;
      color: var(--text-muted);
      margin: 0;
    }

    .lv-review-date {
      font-size: 0.72rem;
      color: var(--gray-500, #adb5bd);
      margin-top: 0.3rem;
    }

    .lv-action-bar {
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
      margin-bottom: 1.5rem;
    }

    .lv-desc-text {
      font-size: 0.9rem;
      color: var(--text-muted);
      line-height: 1.8;
      white-space: pre-wrap;
      margin: 0;
    }
  </style>
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
          style="background:none;border:none;color:var(--primary);font-size:1.1rem;cursor:pointer;margin-right:0.75rem;">
          <i class="fas fa-bars"></i>
        </button>
        <span class="topbar-title">Listing Detail</span>
        <div class="topbar-breadcrumb">
          <a href="listings.php" style="color:var(--text-muted);text-decoration:none;">Listings</a>
          <span style="margin:0 6px;color:var(--text-muted);">/</span>
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
              <div class="lv-badge-row" style="margin-bottom:0.5rem;">
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
                <div style="color:rgba(255,255,255,0.75);font-size:0.82rem;">
                  <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($listing['address']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Description -->
          <div class="lv-card">
            <div class="lv-card-title">
              <i class="fas fa-align-left" style="color:var(--accent);"></i> Description
            </div>
            <?php if ($listing['description']): ?>
              <p class="lv-desc-text"><?= htmlspecialchars($listing['description']) ?></p>
            <?php else: ?>
              <p style="color:var(--text-muted);font-size:0.87rem;font-style:italic;">No description provided.</p>
            <?php endif; ?>
          </div>

          <!-- Gallery -->
          <?php
            $galleryPhotos = json_decode($listing['gallery'] ?? '[]', true) ?: [];
          ?>
          <?php if (!empty($galleryPhotos)): ?>
          <div class="lv-card">
            <div class="lv-card-title" style="justify-content:space-between;">
              <span><i class="fas fa-images" style="color:var(--accent);"></i> Photo Gallery</span>
              <span style="font-size:0.78rem;color:var(--text-muted);font-weight:500;"><?= count($galleryPhotos) ?> photo<?= count($galleryPhotos) !== 1 ? 's' : '' ?></span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;">
              <?php foreach ($galleryPhotos as $gi => $gPhoto):
                $gClean = preg_replace('#^(\.\./)+#', '', $gPhoto);
                $gUrl   = BASE_URL . '/' . ltrim($gClean, '/');
              ?>
              <div style="position:relative;border-radius:8px;overflow:hidden;aspect-ratio:4/3;border:1px solid var(--border);">
                <img src="<?= htmlspecialchars($gUrl) ?>"
                     style="width:100%;height:100%;object-fit:cover;display:block;"
                     onerror="this.src='https://placehold.co/130x98/1b4332/fff?text=IMG'">
                <form method="POST" action="gallery_delete.php" style="position:absolute;top:4px;right:4px;"
                      onsubmit="return confirm('Remove this photo from the gallery?');">
                  <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                  <input type="hidden" name="photo_path" value="<?= htmlspecialchars($gPhoto, ENT_QUOTES) ?>">
                  <button type="submit"
                    style="background:rgba(220,38,38,.85);color:white;border:none;width:22px;height:22px;border-radius:50%;cursor:pointer;font-size:0.65rem;display:flex;align-items:center;justify-content:center;"
                    title="Remove photo">
                    <i class="fas fa-times"></i>
                  </button>
                </form>
              </div>
              <?php endforeach; ?>
            </div>
            <div style="margin-top:0.75rem;font-size:0.78rem;color:var(--text-muted);">
              <i class="fas fa-info-circle me-1"></i>
              To add more photos or remove all, use <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" style="color:var(--accent);">Edit Listing</a>.
            </div>
          </div>
          <?php endif; ?>

          <!-- Amenities -->
          <?php if ($listing['amenities']): ?>
            <div class="lv-card">
              <div class="lv-card-title">
                <i class="fas fa-check-circle" style="color:var(--accent);"></i> Amenities &amp; Features
              </div>
              <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                <?php foreach (explode(',', $listing['amenities']) as $amenity): ?>
                  <span class="lv-amenity-tag">
                    <i class="fas fa-check" style="color:var(--accent);font-size:0.7rem;"></i>
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
                style="border:none;padding-bottom:0.75rem;">
                <span><i class="fas fa-map" style="color:var(--accent);"></i> Location Map</span>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $listing['latitude'] ?>,<?= $listing['longitude'] ?>"
                  target="_blank" class="btn-admin-secondary" style="font-size:0.78rem;padding:5px 12px;">
                  <i class="fas fa-directions me-1"></i> Get Directions
                </a>
              </div>
              <div class="lv-map">
                <div id="lvMap"></div>
              </div>
              <div style="font-size:0.77rem;color:var(--text-muted);margin-top:0.5rem;">
                Coordinates: <?= number_format((float) $listing['latitude'], 6) ?>,
                <?= number_format((float) $listing['longitude'], 6) ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Reviews -->
          <div class="lv-card">
            <div class="lv-card-title">
              <i class="fas fa-star" style="color:#d4a017;"></i> Reviews
              <span
                style="margin-left:auto;font-size:0.82rem;color:var(--text-muted);font-weight:500;"><?= count($reviews) ?>
                total</span>
            </div>
            <?php if ($reviews): ?>
              <?php
              $avg = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
              ?>
              <div
                style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;padding:0.75rem 1rem;background:var(--sidebar-bg,#f7faf7);border-radius:10px;">
                <div style="font-size:2rem;font-weight:800;color:var(--primary);line-height:1;"><?= $avg ?></div>
                <div>
                  <div style="color:#d4a017;font-size:1rem;">
                    <?= str_repeat('★', round($avg)) . str_repeat('☆', 5 - round($avg)) ?></div>
                  <div style="font-size:0.78rem;color:var(--text-muted);">Average of <?= count($reviews) ?>
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
              <p style="color:var(--text-muted);font-size:0.87rem;font-style:italic;">No reviews yet.</p>
            <?php endif; ?>
          </div>

        </div><!-- /col-lg-8 -->

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">
          <div class="lv-card" style="position:sticky;top:80px;">
            <div class="lv-card-title">
              <i class="fas fa-info-circle" style="color:var(--accent);"></i> Listing Info
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
                <div class="lv-info-item" style="grid-column:1/-1;">
                  <span class="lv-info-label">Slug</span>
                  <span class="lv-info-value"
                    style="font-family:monospace;font-size:0.82rem;"><?= htmlspecialchars($listing['slug']) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['contact']): ?>
                <div class="lv-info-item" style="grid-column:1/-1;">
                  <span class="lv-info-label">Contact</span>
                  <span class="lv-info-value"><?= htmlspecialchars($listing['contact']) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($listing['email']): ?>
                <div class="lv-info-item" style="grid-column:1/-1;">
                  <span class="lv-info-label">Email</span>
                  <span class="lv-info-value">
                    <a
                      href="mailto:<?= htmlspecialchars($listing['email']) ?>"><?= htmlspecialchars($listing['email']) ?></a>
                  </span>
                </div>
              <?php endif; ?>

              <?php if ($listing['website']): ?>
                <div class="lv-info-item" style="grid-column:1/-1;">
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
            <div
              style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border,#e8ede8);display:flex;flex-direction:column;gap:0.5rem;">
              <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-primary"
                style="justify-content:center;text-align:center;width:100%;">
                <i class="fas fa-pencil-alt me-2"></i>Edit This Listing
              </a>
              <a href="../listing.php?slug=<?= urlencode($listing['slug']) ?>" target="_blank"
                class="btn-admin-secondary" style="justify-content:center;text-align:center;width:100%;">
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