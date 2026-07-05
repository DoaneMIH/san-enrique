<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();
$categories = getCategories();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = sanitize($_POST['name'] ?? '');
  $category_id = (int) ($_POST['category_id'] ?? 0);
  $description = $db->real_escape_string($_POST['description'] ?? ''); // raw HTML — escape for SQL only, do NOT sanitize
  $address = sanitize($_POST['address'] ?? '');
  $barangay = sanitize($_POST['barangay'] ?? '');
  $contact = sanitize($_POST['contact'] ?? '');
  $email = sanitize($_POST['email'] ?? '');
  $website = sanitize($_POST['website'] ?? '');
  // If admin unchecked "Show Map", save NULL so the public page hides the map
  $showMap = isset($_POST['show_map']) ? 1 : 0;
  $lat = $showMap ? (float) ($_POST['latitude'] ?? 0) : 'NULL';
  $lng = $showMap ? (float) ($_POST['longitude'] ?? 0) : 'NULL';
  $hours = sanitize($_POST['operating_hours'] ?? '');
  $fee = sanitize($_POST['entrance_fee'] ?? '');
  $amenities = sanitize($_POST['amenities'] ?? '');
  $status = sanitize($_POST['status'] ?? 'active');
  $featured = isset($_POST['is_featured']) ? 1 : 0;

  // ── Video: uploaded file > URL input > keep existing value ───────────────
  $video = sanitize($_POST['video_url'] ?? '');
  if (empty($video)) $video = sanitize($_POST['old_video'] ?? '');
  $videoUploadDir = '../uploads/listings/videos/';
  if (!is_dir($videoUploadDir)) mkdir($videoUploadDir, 0755, true);
  if (!empty($_FILES['video_upload']['name'])) {
    $vFile  = $_FILES['video_upload'];
    $vExt   = strtolower(pathinfo($vFile['name'], PATHINFO_EXTENSION));
    $vAllow = ['mp4','webm','ogg','mov'];
    if (!in_array($vExt, $vAllow)) {
      $error = 'Invalid video type. Allowed: MP4, WEBM, OGG, MOV.';
    } elseif ($vFile['size'] > 200 * 1024 * 1024) {
      $error = 'Video too large. Max 200 MB.';
    } elseif ($vFile['error'] !== UPLOAD_ERR_OK) {
      $error = 'Video upload error (code ' . $vFile['error'] . ').';
    } else {
      $vName = 'video_' . time() . '_' . mt_rand(100,999) . '.' . $vExt;
      if (move_uploaded_file($vFile['tmp_name'], $videoUploadDir . $vName)) {
        $oldVid = sanitize($_POST['old_video'] ?? '');
        if ($oldVid && strpos($oldVid, '../uploads/') === 0 && file_exists($oldVid)) unlink($oldVid);
        $video = '../uploads/listings/videos/' . $vName;
      } else {
        $error = 'Failed to save video. Check uploads/listings/videos/ is writable.';
      }
    }
  }
  // ─────────────────────────────────────────────────────────────────────────
  // Featured image: keep existing → URL input → file upload (last wins)
  $img = sanitize($_POST['featured_image'] ?? '');
  $uploadDir = '../uploads/listings/';
  if (!is_dir($uploadDir))
    mkdir($uploadDir, 0755, true);

  // URL input overrides existing if provided
  $featuredImageUrl = trim($_POST['featured_image_url'] ?? '');
  if (!empty($featuredImageUrl) && filter_var($featuredImageUrl, FILTER_VALIDATE_URL)) {
    $img = convertGdriveUrl($featuredImageUrl);
  }

  // File upload overrides URL if both provided
  if (!empty($_FILES['featured_image_upload']['name'])) {
    $file = $_FILES['featured_image_upload'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
      $error = 'Invalid image type. Allowed: JPG, PNG, WEBP, GIF.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
      $error = 'Image too large. Max 5 MB.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
      $error = 'Upload error (code ' . $file['error'] . '). Please try again.';
    } else {
      $newName = 'listing_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
      $destPath = $uploadDir . $newName;
      if (move_uploaded_file($file['tmp_name'], $destPath)) {
        $oldImg = sanitize($_POST['old_image'] ?? '');
        if ($oldImg && strpos($oldImg, '../uploads/listings/') === 0 && file_exists($oldImg))
          unlink($oldImg);
        $img = '../uploads/listings/' . $newName;
      } else {
        $error = 'Failed to save image. Check uploads/listings/ folder is writable.';
      }
    }
  }

  // ── Gallery: file upload + URL input ───────────────────────────────────
  // For EDIT: fetch current gallery from DB so existing images are PRESERVED
  $galleryPaths = [];
  if ($_POST['form_action'] === 'edit') {
    $editId = (int) $_POST['listing_id'];
    $gRes = $db->query("SELECT gallery FROM listings WHERE id = $editId");
    if ($gRes && $gRow = $gRes->fetch_assoc()) {
      $galleryPaths = json_decode($gRow['gallery'] ?? '[]', true) ?: [];
    }
  }
  // Remove any photos the admin deleted via the hidden remove inputs
  $toRemove = $_POST['remove_gallery'] ?? [];
  foreach ($toRemove as $removePath) {
    $removePath = sanitize($removePath);
    if (strpos($removePath, '../uploads/listings/') === 0 && file_exists($removePath)) {
      unlink($removePath);
    }
    $galleryPaths = array_filter($galleryPaths, fn($p) => $p !== $removePath);
  }
  $galleryPaths = array_values($galleryPaths);

  // Add gallery image URLs (auto-convert Google Drive links)
  $galleryUrlInputs = $_POST['gallery_urls'] ?? [];
  foreach ($galleryUrlInputs as $gUrl) {
    $gUrl = trim($gUrl);
    if (!empty($gUrl) && filter_var($gUrl, FILTER_VALIDATE_URL)) {
      $galleryPaths[] = convertGdriveUrl($gUrl);
    }
  }

  // Save newly uploaded gallery files
  if (!empty($_FILES['gallery_upload']['name'][0])) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    foreach ($_FILES['gallery_upload']['tmp_name'] as $k => $tmp) {
      if ($_FILES['gallery_upload']['error'][$k] !== UPLOAD_ERR_OK) continue;
      $origName = $_FILES['gallery_upload']['name'][$k];
      $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) continue;
      if ($_FILES['gallery_upload']['size'][$k] > 5 * 1024 * 1024) continue;
      $newName  = 'gallery_' . time() . '_' . mt_rand(1000, 9999) . '_' . $k . '.' . $ext;
      $destPath = $uploadDir . $newName;
      if (move_uploaded_file($tmp, $destPath)) {
        $galleryPaths[] = '../uploads/listings/' . $newName;
      }
    }
  }
  $galleryJson = $db->real_escape_string(json_encode(array_values($galleryPaths)));
  // ─────────────────────────────────────────────────────────────────────────

  // Auto-generate slug
  $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
  $slug = trim($slug, '-');

  if ($_POST['form_action'] === 'add') {
    // Ensure unique slug
    $baseSlug = $slug;
    $i = 1;
    while ($db->query("SELECT id FROM listings WHERE slug = '$slug'")->num_rows > 0) {
      $slug = "$baseSlug-$i";
      $i++;
    }
    $videoEsc = $db->real_escape_string($video);
    $sql = "INSERT INTO listings (category_id, name, slug, description, address, barangay, contact, email, website, latitude, longitude, featured_image, gallery, video, operating_hours, entrance_fee, amenities, status, is_featured) VALUES ($category_id, '$name', '$slug', '$description', '$address', '$barangay', '$contact', '$email', '$website', $lat, $lng, '$img', '$galleryJson', '$videoEsc', '$hours', '$fee', '$amenities', '$status', $featured)";
    if ($db->query($sql)) {
      $message = 'Listing added successfully!';
      $action = 'list';
    } else {
      $error = 'Error adding listing: ' . $db->error;
    }
  } elseif ($_POST['form_action'] === 'edit') {
    $editId = (int) $_POST['listing_id'];
    // Check slug uniqueness (exclude self)
    $baseSlug = $slug;
    $i = 1;
    while ($db->query("SELECT id FROM listings WHERE slug = '$slug' AND id != $editId")->num_rows > 0) {
      $slug = "$baseSlug-$i";
      $i++;
    }
    $videoEsc = $db->real_escape_string($video);
    $sql = "UPDATE listings SET category_id=$category_id, name='$name', slug='$slug', description='$description', address='$address', barangay='$barangay', contact='$contact', email='$email', website='$website', latitude=$lat, longitude=$lng, featured_image='$img', gallery='$galleryJson', video='$videoEsc', operating_hours='$hours', entrance_fee='$fee', amenities='$amenities', status='$status', is_featured=$featured WHERE id=$editId";
    if ($db->query($sql)) {
      $message = 'Listing updated successfully!';
      $action = 'list';
    } else {
      $error = 'Error updating listing: ' . $db->error;
    }
  }
}

// Handle delete
if (isset($_GET['delete'])) {
  $delId = (int) $_GET['delete'];
  $db->query("DELETE FROM listings WHERE id = $delId");
  $message = 'Listing deleted.';
}

// Fetch listing for edit
$editListing = null;
if ($action === 'edit' && $id) {
  $r = $db->query("SELECT * FROM listings WHERE id = $id");
  $editListing = $r ? $r->fetch_assoc() : null;
}

// Fetch all listings for list view
$listings = $db->query("SELECT l.*, c.name as cat_name, c.slug as cat_slug, c.color FROM listings l JOIN categories c ON l.category_id = c.id ORDER BY l.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listings Management - Admin</title>
    <link rel="shortcut icon" type="x-icon" href="../assets/images/san-enrique-logo.jpg">

  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

  <?php require_once 'sidebar.php'; ?>
  <div class="admin-content">
    <div class="admin-topbar">
      <div>
        <button class="d-lg-none topbar-menu-btn" onclick="toggleSidebar()"><i
            class="fas fa-bars"></i></button>
        <span
          class="topbar-title"><?= $action === 'list' ? 'Listings Management' : ($action === 'add' ? 'Add New Listing' : 'Edit Listing') ?></span>
      </div>
      <div class="topbar-actions">
        <?php if ($action === 'list'): ?>
          <a href="?action=add" class="btn-admin-primary"><i class="fas fa-plus me-1"></i> Add Listing</a>
        <?php else: ?>
          <a href="listings.php" class="btn-admin-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
        <?php endif; ?>
      </div>
    </div>



    <div class="admin-main">
      <?php if ($message): ?>
        <div id="successAlert"
          class="admin-alert success" style="transition:opacity 0.5s ease;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div
          class="admin-alert error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($action === 'list'): ?>
        <!-- LISTINGS TABLE -->
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title">All Listings (<span id="visibleCount"><?= count($listings) ?></span>)</div>
            <div class="admin-search">
              <i class="fas fa-search"></i>
              <input type="text" id="tableSearch" placeholder="Search listings...">
            </div>
          </div>
          <!-- Category filter pills -->
          <div id="categoryFilterBar" style="display:flex;flex-wrap:wrap;gap:8px;padding:12px 16px;border-bottom:1px solid var(--border,#e5ede8);background:var(--g8,#f9fdfb);">
            <button class="cat-filter-pill active" data-cat="all"
              style="display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:100px;border:1.5px solid var(--accent,#40916c);background:var(--accent,#40916c);color:#fff;font-size:0.78rem;font-weight:600;cursor:pointer;transition:all .2s;">
              <i class="fas fa-th-large" style="font-size:0.7rem;"></i> All
            </button>
            <?php foreach ($categories as $cat): ?>
            <button class="cat-filter-pill" data-cat="<?= htmlspecialchars($cat['slug']) ?>"
              style="display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:100px;border:1.5px solid var(--border,#d1e8d8);background:#fff;color:var(--primary,#1b4332);font-size:0.78rem;font-weight:600;cursor:pointer;transition:all .2s;">
              <i class="<?= htmlspecialchars($cat['icon']) ?>" style="color:<?= htmlspecialchars($cat['color']) ?>;font-size:0.7rem;"></i>
              <?= htmlspecialchars($cat['name']) ?>
            </button>
            <?php endforeach; ?>
          </div>
          <div class="table-scroll">
            <table class="admin-table" id="listingsTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Listing</th>
                  <th>Category</th>
                  <th>Barangay</th>
                  <th>Featured</th>
                  <th>Status</th>
                  <th>Views</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($listings as $i => $listing): ?>
                  <tr data-cat="<?= htmlspecialchars($listing['cat_slug']) ?>">
                    <td class="td-muted listing-row-num"><?= $i + 1 ?></td>
                    <td>
                      <div class="listing-info">
                        <img src="<?= $listing['featured_image'] ? listingImage($listing['featured_image'], $listing['name'], 96, 76) : 'https://placehold.co/48x38/1b4332/ffffff?text=?' ?>"
                          class="listing-thumb" alt="" onerror="this.src='https://placehold.co/48x38/1b4332/ffffff?text=?'">
                        <div>
                          <div class="listing-name">
                            <?= htmlspecialchars(html_entity_decode($listing['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?></div>
                          <div class="listing-slug"><?= htmlspecialchars($listing['slug']) ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td><span class="cat-pill"><?= htmlspecialchars($listing['cat_name']) ?></span>
                    </td>
                    <td class="td-small">
                      <?= htmlspecialchars($listing['barangay'] ?: '—') ?></td>
                    <td class="td-center">
                      <?= $listing['is_featured'] ? '<i class="fas fa-star icon-star-gold"></i>' : '<i class="far fa-star icon-star-empty"></i>' ?>
                    </td>
                    <td><span class="status-badge <?= $listing['status'] ?>"><?= ucfirst($listing['status']) ?></span></td>
                    <td class="td-small"><?= number_format($listing['views']) ?></td>
                    <td>
                      <div class="table-actions">
                        <a href="?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-edit">
                          <i class="fas fa-pencil-alt"></i> Edit
                        </a>
                        <a href="listing_view.php?slug=<?= urlencode($listing['slug']) ?>" class="btn-admin-edit btn-admin-preview" title="Preview (Admin View)">
                          <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="confirmDelete(<?= $listing['id'] ?>, '<?= addslashes($listing['name']) ?>')"
                          class="btn-admin-danger">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php else: ?>
        <!-- ADD/EDIT FORM -->
        <div class="admin-form-card">
          <div class="admin-form-header">
            <div class="form-header-text">
              <?= $action === 'add' ? 'Add New Listing' : 'Edit: ' . htmlspecialchars(html_entity_decode($editListing['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>
            </div>
          </div>
          <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="form_action" value="<?= $action ?>">
            <!-- Preserves current image path when no new file is uploaded -->
            <input type="hidden" name="featured_image" id="currentImgPath"
              value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>">
            <?php if ($action === 'edit'): ?>
              <input type="hidden" name="listing_id" value="<?= $editListing['id'] ?>">
            <?php endif; ?>
            <div class="admin-form-body">
              <div class="row g-3">
                <!-- Name & Category -->
                <div class="col-md-8">
                  <label class="admin-label">Listing Name *</label>
                  <input type="text" name="name" class="admin-input" required
                    value="<?= htmlspecialchars(html_entity_decode($editListing['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?>" placeholder="e.g. Paradise Cove Resort">
                </div>
                <div class="col-md-4">
                  <label class="admin-label">Category *</label>
                  <select name="category_id" class="admin-input" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?= $cat['id'] ?>" <?= ($editListing['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Description — Rich Text Editor -->
                <div class="col-12">
                  <label class="admin-label">Description</label>
                  <div class="rte-wrapper">
                    <!-- Toolbar -->
                    <div class="rte-toolbar" id="rteToolbar">
                      <!-- Formatting group -->
                      <div class="rte-group">
                        <button type="button" class="rte-btn" data-cmd="bold" title="Bold (Ctrl+B)">
                          <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" class="rte-btn" data-cmd="italic" title="Italic (Ctrl+I)">
                          <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" class="rte-btn" data-cmd="underline" title="Underline (Ctrl+U)">
                          <i class="fas fa-underline"></i>
                        </button>
                      </div>
                      <div class="rte-divider"></div>
                      <!-- Lists group -->
                      <div class="rte-group">
                        <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullet List">
                          <i class="fas fa-list-ul"></i>
                        </button>
                        <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbered List">
                          <i class="fas fa-list-ol"></i>
                        </button>
                      </div>
                      <div class="rte-divider"></div>
                      <!-- Layout group -->
                      <div class="rte-group">
                        <button type="button" class="rte-btn" id="rteTwoCol" title="Toggle 2-Column Layout" onclick="rteToggleTwoCol()">
                          <i class="fas fa-columns"></i>
                        </button>
                      </div>
                      <div class="rte-divider"></div>
                      <!-- Alignment group -->
                      <div class="rte-group">
                        <button type="button" class="rte-btn" data-cmd="justifyLeft" title="Align Left">
                          <i class="fas fa-align-left"></i>
                        </button>
                        <button type="button" class="rte-btn" data-cmd="justifyCenter" title="Align Center">
                          <i class="fas fa-align-center"></i>
                        </button>
                        <button type="button" class="rte-btn" data-cmd="justifyRight" title="Align Right">
                          <i class="fas fa-align-right"></i>
                        </button>
                      </div>
                      <div class="rte-divider"></div>
                      <!-- Clear formatting -->
                      <div class="rte-group">
                        <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear Formatting">
                          <i class="fas fa-remove-format"></i>
                        </button>
                      </div>
                    </div>
                    <!-- Editable area -->
                    <div class="rte-editor" id="rteEditor" contenteditable="true"
                      data-placeholder="Describe this place..."></div>
                    <!-- Hidden textarea synced on submit -->
                    <textarea name="description" id="rteHidden" style="display:none;"><?= $editListing['description'] ?? '' ?></textarea>
                  </div>
                </div>

                <!-- Address & Barangay -->
                <div class="col-md-8">
                  <label class="admin-label">Full Address</label>
                  <input type="text" name="address" class="admin-input"
                    value="<?= htmlspecialchars($editListing['address'] ?? '') ?>"
                    placeholder="Street, Barangay, San Enrique, Iloilo">
                </div>
                <div class="col-md-4">
                  <label class="admin-label">Barangay</label>
                  <input type="text" name="barangay" class="admin-input"
                    value="<?= htmlspecialchars($editListing['barangay'] ?? '') ?>" placeholder="Poblacion">
                </div>

                <!-- Contact Info -->
                <div class="col-md-4">
                  <label class="admin-label">Contact Number</label>
                  <input type="text" name="contact" class="admin-input"
                    value="<?= htmlspecialchars($editListing['contact'] ?? '') ?>" placeholder="(033) 123-4567">
                </div>
                <div class="col-md-4">
                  <label class="admin-label">Email Address</label>
                  <input type="email" name="email" class="admin-input"
                    value="<?= htmlspecialchars($editListing['email'] ?? '') ?>" placeholder="info@resort.com">
                </div>
                <div class="col-md-4">
                  <label class="admin-label">Website URL</label>
                  <input type="url" name="website" class="admin-input"
                    value="<?= htmlspecialchars($editListing['website'] ?? '') ?>" placeholder="https://resort.com">
                </div>

                <!-- Hours & Fee -->
                <div class="col-md-6">
                  <label class="admin-label">Operating Hours</label>
                  <input type="text" name="operating_hours" class="admin-input"
                    value="<?= htmlspecialchars($editListing['operating_hours'] ?? '') ?>"
                    placeholder="6:00 AM - 10:00 PM">
                </div>
                <div class="col-md-6">
                  <label class="admin-label">Entrance Fee</label>
                  <input type="text" name="entrance_fee" class="admin-input"
                    value="<?= htmlspecialchars($editListing['entrance_fee'] ?? '') ?>"
                    placeholder="₱150 per person / Free">
                </div>

                <!-- Amenities -->
                <div class="col-12">
                  <label class="admin-label">Amenities (comma-separated)</label>
                  <input type="text" name="amenities" class="admin-input"
                    value="<?= htmlspecialchars($editListing['amenities'] ?? '') ?>"
                    placeholder="Swimming Pool, Restaurant, WiFi, Parking, Cottage">
                </div>

                <!-- Featured Photo (Upload or URL) -->
                <div class="col-12">
                  <label class="admin-label">
                    <i class="fas fa-camera me-1"></i> Featured Photo
                  </label>
                  <?php
                  $existingImg = $editListing['featured_image'] ?? '';
                  $displayImg = '';
                  if ($existingImg) {
                    if (strpos($existingImg, 'http') === 0) {
                      $displayImg = $existingImg;
                    } else {
                      $clean = preg_replace('#^(\.\./)+#', '', $existingImg);
                      $displayImg = BASE_URL . '/' . ltrim($clean, '/');
                    }
                  }
                  $hasImg = !empty($displayImg);
                  $isUrlImg = $existingImg && strpos($existingImg, 'http') === 0;
                  ?>
                  <!-- Current image preview -->
                  <div id="imgPreviewWrap" class="img-preview-wrap" style="<?= $hasImg ? '' : 'display:none;' ?>">
                    <img id="imgPreview" src="<?= htmlspecialchars($displayImg) ?>" alt="Preview"
                      onerror="this.src='https://placehold.co/600x200/1b4332/ffffff?text=Preview'">
                    <span class="img-preview-label" id="imgBadgeLabel">
                      <?= $hasImg ? ($isUrlImg ? 'URL photo' : 'Uploaded photo') : 'Selected photo' ?>
                    </span>
                    <button type="button" onclick="removeImage()" class="img-preview-remove" title="Remove photo">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>

                  <!-- Tab toggle: Upload File vs Paste URL -->
                  <div id="imgTabRow" class="photo-tab-row" style="<?= $hasImg ? 'display:none;' : '' ?>">
                    <button type="button" id="imgTabUpload" onclick="switchImgTab('upload')" class="btn-photo-tab active">
                      <i class="fas fa-upload me-1"></i>Upload File
                    </button>
                    <button type="button" id="imgTabUrl" onclick="switchImgTab('url')" class="btn-photo-tab inactive">
                      <i class="fas fa-link me-1"></i>Paste URL
                    </button>
                  </div>

                  <!-- Upload pane -->
                  <div id="imgUploadPane" style="<?= $hasImg ? 'display:none;' : '' ?>">
                    <div id="uploadZone" class="upload-zone">
                      <input type="file" name="featured_image_upload" id="fileInput" accept="image/jpeg,image/png,image/webp,image/gif" onchange="handleFileSelect(this)">
                      <i class="fas fa-cloud-upload-alt upload-zone-icon"></i>
                      <div class="upload-zone-title">Click to upload or drag &amp; drop a photo</div>
                      <div class="upload-zone-hint">JPG, PNG, WEBP or GIF &mdash; max 5 MB</div>
                    </div>
                  </div>

                  <!-- URL pane -->
                  <div id="imgUrlPane" style="display:none;">
                    <div class="url-input-row">
                      <div class="url-input-wrap">
                        <i class="fas fa-link url-input-icon"></i>
                        <input type="url" name="featured_image_url" id="featuredImgUrlInput" class="admin-input url-input-field"
                          placeholder="https://drive.google.com/file/d/... or any image URL"
                          value="<?= htmlspecialchars($isUrlImg ? $existingImg : '') ?>"
                          oninput="previewFeaturedUrl(this.value)">
                      </div>
                    </div>
                    <div id="featuredUrlPreview" class="url-thumb-preview" style="display:none;">
                      <img id="featuredUrlPreviewImg" src="" alt="Preview"
                        onerror="this.src='https://placehold.co/120x80/dc2626/fff?text=Invalid+URL'">
                      <span class="url-thumb-status"><i class="fas fa-check-circle me-1"></i>Image loaded</span>
                    </div>
                    <div class="form-hint form-hint-gdrive">
                      <i class="fab fa-google-drive me-1"></i>
                      <strong>Google Drive supported!</strong> Paste a Drive share link — it will be auto-converted.
                      Make sure the file is set to <em>"Anyone with the link"</em> can view.
                    </div>
                  </div>

                  <input type="hidden" name="featured_image" id="currentImgPath"
                    value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>">
                  <input type="hidden" name="old_image" value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>">

                  <div class="form-hint"><i class="fas fa-info-circle me-1"></i>
                    Upload a file locally, or paste an image URL / Google Drive link to save storage.
                  </div>
                </div>

                <!-- Gallery Photos (Upload + URL — both visible) -->
                <div class="col-12">
                  <label class="admin-label">
                    <i class="fas fa-images me-1"></i> Gallery Photos
                    <span class="label-sub">(upload files and/or paste image URLs — mix both freely)</span>
                  </label>
                  <?php
                    $existingGallery = json_decode($editListing['gallery'] ?? '[]', true) ?: [];
                  ?>
                  <!-- Existing gallery thumbnails with remove & type badge -->
                  <?php if (!empty($existingGallery)): ?>
                  <div id="existingGalleryWrap" class="gallery-existing-wrap">
                    <?php foreach ($existingGallery as $gImg): ?>
                    <?php
                      if (strpos($gImg, 'http') === 0) {
                        $gUrl  = $gImg;
                        $gType = 'url';
                      } else {
                        $gClean = preg_replace('#^(\.\./)+#', '', $gImg);
                        $gUrl   = BASE_URL . '/' . ltrim($gClean, '/');
                        $gType  = 'file';
                      }
                    ?>
                    <div class="gallery-thumb-wrap">
                      <img src="<?= htmlspecialchars($gUrl) ?>" onerror="this.src='https://placehold.co/100x80/1b4332/fff?text=IMG'">
                      <button type="button" onclick="removeGalleryPhoto(this, '<?= htmlspecialchars($gImg, ENT_QUOTES) ?>')" class="gallery-thumb-remove" title="Remove">
                        <i class="fas fa-times"></i>
                      </button>
                      <span class="gallery-thumb-type <?= $gType ?>"><?= $gType === 'url' ? '<i class="fas fa-link"></i>' : '<i class="fas fa-hdd"></i>' ?></span>
                    </div>
                    <?php endforeach; ?>
                  </div>
                  <div id="removedGalleryInputs"></div>
                  <?php endif; ?>

                  <!-- Upload from device (always visible) -->
                  <div class="gallery-method-block">
                    <div class="gallery-method-label"><i class="fas fa-upload me-1"></i> Upload from Device</div>
                    <div id="galleryUploadZone" class="upload-zone upload-zone-sm">
                      <input type="file" name="gallery_upload[]" id="galleryInput" multiple accept="image/jpeg,image/png,image/webp,image/gif" onchange="handleGallerySelect(this)">
                      <i class="fas fa-images upload-zone-icon upload-zone-icon-sm"></i>
                      <div class="upload-zone-title">Click to select multiple photos</div>
                      <div class="upload-zone-hint">JPG, PNG, WEBP or GIF &mdash; max 5 MB each</div>
                    </div>
                    <div id="galleryPreviewRow" class="gallery-preview-row"></div>
                  </div>

                  <!-- Divider -->
                  <div class="gallery-or-divider"><span>AND / OR</span></div>

                  <!-- Paste URLs (always visible) -->
                  <div class="gallery-method-block">
                    <div class="gallery-method-label"><i class="fas fa-link me-1"></i> Paste Image URLs</div>
                    <div id="galleryUrlInputs" class="gallery-url-inputs-wrap">
                      <!-- Dynamic URL rows inserted by JS -->
                    </div>
                    <button type="button" onclick="addGalleryUrlRow()" class="btn-add-gallery-url">
                      <i class="fas fa-plus me-1"></i> Add Image URL
                    </button>
                    <div class="form-hint form-hint-gdrive">
                      <i class="fab fa-google-drive me-1"></i>
                      <strong>Google Drive supported!</strong> Paste Drive share links — auto-converted.
                      Make sure files are set to <em>"Anyone with the link"</em>.
                    </div>
                  </div>

                  <div class="form-hint"><i class="fas fa-info-circle me-1"></i>
                    You can use both methods at once — uploaded files and URL images all appear in the gallery together.
                  </div>
                </div>

                <!-- Video Upload -->
                <div class="col-12">
                  <label class="admin-label">
                    <i class="fas fa-video me-1"></i> Listing Video
                    <span class="label-sub">(upload MP4/WEBM or paste YouTube/Vimeo URL &mdash; max 200 MB)</span>
                  </label>

                  <?php
                    $existingVideo = $editListing['video'] ?? '';
                    $isUploadedVideo = $existingVideo && strpos($existingVideo, '../uploads/') === 0;
                    $isUrlVideo = $existingVideo && !$isUploadedVideo;
                  ?>

                  <?php if ($existingVideo): ?>
                  <div id="existingVideoWrap" class="video-existing-wrap">
                    <i class="fas fa-video video-existing-icon"></i>
                    <div class="video-existing-info">
                      <div class="video-existing-title">
                        <?= $isUploadedVideo ? 'Uploaded video file' : 'Video URL' ?>
                      </div>
                      <div class="video-existing-path">
                        <?= htmlspecialchars($existingVideo) ?>
                      </div>
                    </div>
                    <button type="button" onclick="clearExistingVideo()" class="btn-admin-danger flex-shrink-0">
                      <i class="fas fa-times me-1"></i>Remove
                    </button>
                  </div>
                  <input type="hidden" name="old_video" id="oldVideoInput" value="<?= htmlspecialchars($existingVideo, ENT_QUOTES) ?>">
                  <?php endif; ?>

                  <div class="video-tab-row">
                    <button type="button" id="tabUpload" onclick="switchVideoTab('upload')" class="btn-video-tab active">
                      <i class="fas fa-upload me-1"></i>Upload File
                    </button>
                    <button type="button" id="tabUrl" onclick="switchVideoTab('url')" class="btn-video-tab inactive">
                      <i class="fas fa-link me-1"></i>Paste URL
                    </button>
                  </div>

                  <div id="videoUploadPane">
                    <div id="videoUploadZone" class="upload-zone upload-zone-sm">
                      <input type="file" name="video_upload" id="videoInput" accept="video/mp4,video/webm,video/ogg,video/quicktime" onchange="handleVideoSelect(this)">
                      <i class="fas fa-film upload-zone-icon upload-zone-icon-sm"></i>
                      <div class="upload-zone-title">Click to select a video file</div>
                      <div class="upload-zone-hint">MP4, WEBM, OGG or MOV &mdash; max 200 MB</div>
                    </div>
                    <div id="videoPreview" style="display:none;" class="mt-2"></div>
                  </div>

                  <div id="videoUrlPane" style="display:none;" class="mt-1">
                    <input type="text" name="video_url" id="videoUrlInput" class="admin-input"
                      placeholder="https://www.youtube.com/watch?v=... or direct video URL"
                      value="<?= htmlspecialchars($isUrlVideo ? $existingVideo : '', ENT_QUOTES) ?>">
                    <div class="form-hint">
                      <i class="fas fa-info-circle me-1"></i>
                      Supports YouTube, Vimeo, or direct MP4/WEBM URLs.
                    </div>
                  </div>
                </div>

                <!-- GPS Coordinates -->
                <div class="col-12">
                  <?php $hasCoords = !empty($editListing['latitude']) && $editListing['latitude'] != 0; ?>
                  <!-- Show Map toggle -->
                  <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:var(--g7,#f6faf7);border-radius:10px;border:1px solid var(--border-solid,#d1e8d8);">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" name="show_map" id="showMapToggle"
                        <?= $hasCoords ? 'checked' : '' ?> value="1"
                        onchange="toggleMapSection(this.checked)">
                      <label class="form-check-label fw-semibold" for="showMapToggle" style="color:var(--primary,#1b4332);">
                        <i class="fas fa-map-marked-alt me-1" style="color:var(--accent,#40916c);"></i>
                        Show Map on Listing Page
                      </label>
                    </div>
                    <span style="font-size:0.8rem;color:var(--text-muted,#6b8c73);">
                      Uncheck to hide the map (e.g. for food stalls, native delicacies)
                    </span>
                  </div>
                  <!-- Map fields — hidden when toggle is off -->
                  <div id="mapFieldsWrap" style="<?= $hasCoords ? '' : 'display:none;' ?>">
                    <label class="admin-label"><i class="fas fa-map-pin me-1"></i> GPS
                      Coordinates — Click on the map to set location</label>
                    <div class="coord-fields mb-2">
                      <div>
                        <label class="admin-label label-sm">Latitude</label>
                        <input type="number" name="latitude" id="latitude" class="admin-input" step="any"
                          value="<?= $editListing['latitude'] ?? '10.9178' ?>" placeholder="10.9178">
                      </div>
                      <div>
                        <label class="admin-label label-sm">Longitude</label>
                        <input type="number" name="longitude" id="longitude" class="admin-input" step="any"
                          value="<?= $editListing['longitude'] ?? '122.8845' ?>" placeholder="122.8845">
                      </div>
                    </div>
                    <div id="adminMapPicker"></div>
                  </div>
                </div>

                <!-- Status & Featured -->
                <div class="col-md-6">
                  <label class="admin-label">Status</label>
                  <select name="status" class="admin-input">
                    <option value="active" <?= ($editListing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active
                    </option>
                    <option value="inactive" <?= ($editListing['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive
                    </option>
                    <option value="pending" <?= ($editListing['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending
                    </option>
                  </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                      <?= ($editListing['is_featured'] ?? 0) ? 'checked' : '' ?> value="1">
                    <label class="form-check-label featured-check-label" for="isFeatured">
                      <i class="fas fa-star me-1 icon-star-gold"></i> Mark as Featured Listing
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="admin-form-footer">
              <a href="listings.php" class="btn-admin-secondary"><i class="fas fa-times me-1"></i> Cancel</a>
              <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save me-1"></i> <?= $action === 'add' ? 'Save Listing' : 'Update Listing' ?>
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- adminMapData MUST be defined before main.js loads so initMap() can read it -->
    <script>
      var adminMapData = {
        lat: <?= (float) ($editListing['latitude'] ?? 10.9178) ?>,
        lng: <?= (float) ($editListing['longitude'] ?? 122.8845) ?>
      };
    </script>
  <?php endif; ?>
  <script src="../assets/js/main.js"></script>
  <script>
    function toggleSidebar() {
      $('#adminSidebar').toggleClass('open');
      $('#sidebarOverlay').toggleClass('d-none');
    }

    // Auto-dismiss success alert after 3 seconds
    (function () {
      var alert = document.getElementById('successAlert');
      if (!alert) return;
      setTimeout(function () {
        alert.style.opacity = '0';
        setTimeout(function () {
          if (alert.parentNode) alert.parentNode.removeChild(alert);
        }, 500);
      }, 3000);
    })();

    // Table search
    $('#tableSearch').on('input', function () {
      const q = $(this).val().toLowerCase();
      $('#listingsTable tbody tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().includes(q));
      });
    });

    // ── Category filter + search (persistent via localStorage) ──────────
    var _activeCat = localStorage.getItem('listingFilterCat') || 'all';

    function applyFilters() {
      var q   = $('#tableSearch').val().toLowerCase();
      var cat = _activeCat;
      var visible = 0;
      $('#listingsTable tbody tr').each(function () {
        var rowCat  = $(this).data('cat') || '';
        var rowText = $(this).text().toLowerCase();
        var show = (cat === 'all' || rowCat === cat) && (!q || rowText.includes(q));
        $(this).toggle(show);
        if (show) { visible++; $(this).find('.listing-row-num').text(visible); }
      });
      $('#visibleCount').text(visible);
      $('.cat-filter-pill').each(function () {
        var isActive = $(this).data('cat') === cat;
        $(this).css(isActive
          ? { background:'var(--accent,#40916c)', color:'#fff', borderColor:'var(--accent,#40916c)' }
          : { background:'#fff', color:'var(--primary,#1b4332)', borderColor:'var(--border,#d1e8d8)' }
        );
      });
    }

    $(document).on('click', '.cat-filter-pill', function () {
      _activeCat = $(this).data('cat');
      localStorage.setItem('listingFilterCat', _activeCat);
      applyFilters();
    });

    $('#tableSearch').on('input', function () { applyFilters(); });
    $(document).ready(function () { applyFilters(); });

    // ══════════════════════════════════════════════════════════════════════
    // Google Drive URL converter (JS mirror of PHP convertGdriveUrl)
    // ══════════════════════════════════════════════════════════════════════
    function extractGdriveId(url) {
      if (!url) return null;
      var ucMatch = url.match(/[?&]id=([a-zA-Z0-9_-]{10,})/);
      if (ucMatch) return ucMatch[1];
      var fileMatch = url.match(/\/file\/d\/([a-zA-Z0-9_-]{10,})/);
      if (fileMatch) return fileMatch[1];
      var openMatch = url.match(/open\?id=([a-zA-Z0-9_-]{10,})/);
      if (openMatch) return openMatch[1];
      return null;
    }
    function convertGdriveUrl(url) {
      if (!url) return url;
      if (url.indexOf('drive.google.com') === -1 &&
          url.indexOf('googleusercontent.com') === -1) return url;
      var id = extractGdriveId(url);
      if (!id) return url;
      return 'https://drive.google.com/thumbnail?id=' + id + '&sz=w1200';
    }

    // ══════════════════════════════════════════════════════════════════════
    // Featured Image — tab toggle
    // ══════════════════════════════════════════════════════════════════════
    function switchImgTab(tab) {
      var uploadPane = document.getElementById('imgUploadPane');
      var urlPane    = document.getElementById('imgUrlPane');
      var tabUpload  = document.getElementById('imgTabUpload');
      var tabUrl     = document.getElementById('imgTabUrl');
      if (!uploadPane) return;
      if (tab === 'upload') {
        uploadPane.style.display = '';
        urlPane.style.display    = 'none';
        tabUpload.className = 'btn-photo-tab active';
        tabUrl.className    = 'btn-photo-tab inactive';
        document.getElementById('featuredImgUrlInput').value = '';
        document.getElementById('featuredUrlPreview').style.display = 'none';
      } else {
        uploadPane.style.display = 'none';
        urlPane.style.display    = '';
        tabUrl.className    = 'btn-photo-tab active';
        tabUpload.className = 'btn-photo-tab inactive';
        document.getElementById('fileInput').value = '';
      }
    }

    // Featured image URL preview with Google Drive support
    var _featuredUrlTimer = null;
    function previewFeaturedUrl(rawUrl) {
      clearTimeout(_featuredUrlTimer);
      var wrap = document.getElementById('featuredUrlPreview');
      var img  = document.getElementById('featuredUrlPreviewImg');
      if (!rawUrl || !rawUrl.match(/^https?:\/\/.+/i)) { wrap.style.display = 'none'; return; }
      _featuredUrlTimer = setTimeout(function() {
        var url = convertGdriveUrl(rawUrl);
        img.onload  = function() { wrap.style.display = ''; };
        img.onerror = function() { img.src = 'https://placehold.co/120x80/dc2626/fff?text=Invalid+URL'; wrap.style.display = ''; };
        img.src = url;
      }, 500);
    }

    // ══════════════════════════════════════════════════════════════════════
    // Featured Image — file upload handlers (unchanged logic)
    // ══════════════════════════════════════════════════════════════════════
    function handleFileSelect(input) {
      const file = input.files[0];
      if (!file) return;
      const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
      if (!allowed.includes(file.type)) {
        Swal.fire({ icon: 'error', title: 'Invalid File', text: 'Please upload JPG, PNG, WEBP, or GIF.', confirmButtonColor: '#1b4332' });
        input.value = ''; return;
      }
      if (file.size > 5 * 1024 * 1024) {
        Swal.fire({ icon: 'error', title: 'Too Large', text: 'Max file size is 5 MB.', confirmButtonColor: '#1b4332' });
        input.value = ''; return;
      }
      const reader = new FileReader();
      reader.onload = function (e) {
        $('#imgPreview').attr('src', e.target.result);
        $('#imgBadgeLabel').text(file.name);
        $('#imgPreviewWrap').show();
        $('#imgTabRow').hide();
        $('#imgUploadPane').hide();
        $('#imgUrlPane').hide();
        $('#currentImgPath').val('');
      };
      reader.readAsDataURL(file);
    }
    function removeImage() {
      document.getElementById('fileInput').value = '';
      var urlInput = document.getElementById('featuredImgUrlInput');
      if (urlInput) urlInput.value = '';
      var urlPreview = document.getElementById('featuredUrlPreview');
      if (urlPreview) urlPreview.style.display = 'none';
      $('#currentImgPath').val('');
      $('#imgPreviewWrap').hide();
      $('#imgTabRow').show();
      $('#imgUploadPane').show();
      $('#imgUrlPane').hide();
      var tabUp = document.getElementById('imgTabUpload');
      var tabUr = document.getElementById('imgTabUrl');
      if (tabUp) tabUp.className = 'btn-photo-tab active';
      if (tabUr) tabUr.className = 'btn-photo-tab inactive';
    }
    // Drag & drop
    const zone = document.getElementById('uploadZone');
    if (zone) {
      zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--accent)'; zone.style.background = 'var(--accent-pale)'; });
      zone.addEventListener('dragleave', () => { zone.style.borderColor = 'var(--border)'; zone.style.background = 'var(--content-bg)'; });
      zone.addEventListener('drop', e => {
        e.preventDefault(); zone.style.borderColor = 'var(--border)'; zone.style.background = 'var(--content-bg)';
        if (e.dataTransfer.files.length) {
          const fi = document.getElementById('fileInput');
          const dt = new DataTransfer(); dt.items.add(e.dataTransfer.files[0]); fi.files = dt.files;
          handleFileSelect(fi);
        }
      });
    }

    // Delete confirm
    function confirmDelete(id, name) {
      Swal.fire({
        title: 'Delete Listing?',
        html: `Are you sure you want to delete <strong>${name}</strong>? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b8c73',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
      }).then(result => {
        if (result.isConfirmed) window.location.href = `listings.php?delete=${id}`;
      });
    }

    // Show/hide map fields based on toggle
    function toggleMapSection(show) {
      var wrap = document.getElementById('mapFieldsWrap');
      if (!wrap) return;
      if (show) {
        wrap.style.display = '';
        if (typeof google !== 'undefined' && typeof initAdminMapPicker !== 'undefined') {
          var lat = parseFloat($('#latitude').val()) || 10.9178;
          var lng = parseFloat($('#longitude').val()) || 122.8845;
          initAdminMapPicker(lat, lng);
        }
      } else {
        wrap.style.display = 'none';
      }
    }

    // Admin map picker
    <?php if ($action === 'add' || $action === 'edit'): ?>
      function initMap() {
        // Only initialise if the map toggle is checked
        var toggle = document.getElementById('showMapToggle');
        if (toggle && !toggle.checked) return;
        var lat = parseFloat($('#latitude').val()) || 10.9178;
        var lng = parseFloat($('#longitude').val()) || 122.8845;
        initAdminMapPicker(lat, lng);
      }
    <?php endif; ?>

    // ══════════════════════════════════════════════════════════════════════
    // Gallery — both upload & URL panes always visible (no tab toggle)
    // ══════════════════════════════════════════════════════════════════════

    // ── Gallery file upload helpers (unchanged) ─────────────────────────
    function handleGallerySelect(input) {
      const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
      const preview = document.getElementById('galleryPreviewRow');
      Array.from(input.files).forEach(file => {
        if (!allowed.includes(file.type)) return;
        if (file.size > 5 * 1024 * 1024) {
          Swal.fire({ icon: 'error', title: 'Too Large', text: file.name + ' exceeds 5 MB.', confirmButtonColor: '#1b4332' });
          return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
          const wrap = document.createElement('div');
          wrap.className = 'gallery-thumb-wrap gallery-thumb-new';
          wrap.innerHTML = '<img src="' + e.target.result + '">'
            + '<span class="gallery-thumb-type file"><i class="fas fa-hdd"></i></span>';
          preview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
      });
    }
    function removeGalleryPhoto(btn, path) {
      const wrap = btn.closest('.gallery-thumb-wrap');
      var container = document.getElementById('removedGalleryInputs');
      if (!container) {
        container = document.createElement('div');
        container.id = 'removedGalleryInputs';
        var existWrap = document.getElementById('existingGalleryWrap');
        if (existWrap) existWrap.after(container);
        else btn.closest('.col-12').appendChild(container);
      }
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'remove_gallery[]';
      inp.value = path;
      container.appendChild(inp);
      wrap.remove();
    }

    // ── Gallery URL helpers (new) ───────────────────────────────────────
    var _galleryUrlIdx = 0;
    function addGalleryUrlRow() {
      var container = document.getElementById('galleryUrlInputs');
      var idx = _galleryUrlIdx++;
      var row = document.createElement('div');
      row.className = 'gallery-url-row';
      row.id = 'galleryUrlRow_' + idx;
      row.innerHTML = '<div class="url-input-wrap">'
        + '<i class="fas fa-link url-input-icon"></i>'
        + '<input type="url" name="gallery_urls[]" class="admin-input url-input-field" '
        + 'placeholder="https://drive.google.com/file/d/... or any image URL" '
        + 'oninput="previewGalleryUrl(' + idx + ', this.value)">'
        + '<button type="button" class="gallery-url-row-remove" onclick="removeGalleryUrlRow(' + idx + ')" title="Remove row">'
        + '<i class="fas fa-times"></i></button>'
        + '</div>'
        + '<div class="gallery-url-row-thumb" id="galleryUrlThumb_' + idx + '" style="display:none;">'
        + '<img id="galleryUrlThumbImg_' + idx + '" src="" alt="" onerror="this.src=\'https://placehold.co/60x45/dc2626/fff?text=Error\'">'
        + '<span class="url-thumb-status"><i class="fas fa-check-circle"></i></span>'
        + '</div>';
      container.appendChild(row);
      row.querySelector('input[type="url"]').focus();
    }
    function removeGalleryUrlRow(idx) {
      var row = document.getElementById('galleryUrlRow_' + idx);
      if (row) row.remove();
    }
    var _galleryUrlTimers = {};
    function previewGalleryUrl(idx, rawUrl) {
      clearTimeout(_galleryUrlTimers[idx]);
      var wrap = document.getElementById('galleryUrlThumb_' + idx);
      var img  = document.getElementById('galleryUrlThumbImg_' + idx);
      if (!wrap || !img) return;
      if (!rawUrl || !rawUrl.match(/^https?:\/\/.+/i)) { wrap.style.display = 'none'; return; }
      _galleryUrlTimers[idx] = setTimeout(function() {
        var url = convertGdriveUrl(rawUrl);
        img.onload  = function() { wrap.style.display = ''; };
        img.onerror = function() { img.src = 'https://placehold.co/60x45/dc2626/fff?text=Error'; wrap.style.display = ''; };
        img.src = url;
      }, 500);
    }
  </script>


  <script>
    // ── Video tab toggle ──────────────────────────────────────
    function switchVideoTab(tab) {
      var uploadPane = document.getElementById('videoUploadPane');
      var urlPane    = document.getElementById('videoUrlPane');
      var tabUpload  = document.getElementById('tabUpload');
      var tabUrl     = document.getElementById('tabUrl');
      if (!uploadPane) return;
      if (tab === 'upload') {
        uploadPane.style.display = '';
        urlPane.style.display    = 'none';
        tabUpload.style.background  = 'var(--accent)';
        tabUpload.style.color       = '#fff';
        tabUpload.style.borderColor = 'var(--accent)';
        tabUrl.style.background     = 'transparent';
        tabUrl.style.color          = 'var(--text-muted)';
        tabUrl.style.borderColor    = 'var(--border)';
        var ui = document.getElementById('videoUrlInput');
        if (ui) ui.value = '';
      } else {
        uploadPane.style.display = 'none';
        urlPane.style.display    = '';
        tabUrl.style.background      = 'var(--accent)';
        tabUrl.style.color           = '#fff';
        tabUrl.style.borderColor     = 'var(--accent)';
        tabUpload.style.background   = 'transparent';
        tabUpload.style.color        = 'var(--text-muted)';
        tabUpload.style.borderColor  = 'var(--border)';
        var fi = document.getElementById('videoInput');
        if (fi) fi.value = '';
        document.getElementById('videoPreview').style.display = 'none';
      }
    }

    function handleVideoSelect(input) {
      var preview = document.getElementById('videoPreview');
      if (!input.files || !input.files[0]) { preview.style.display='none'; return; }
      var file = input.files[0];
      var sizeMB = (file.size / 1024 / 1024).toFixed(1);
      if (file.size > 200 * 1024 * 1024) {
        alert('Video too large (' + sizeMB + ' MB). Max 200 MB.');
        input.value = '';
        preview.style.display = 'none';
        return;
      }
      var url = URL.createObjectURL(file);
      preview.innerHTML = '<video src="' + url + '" controls style="width:100%;border-radius:10px;max-height:220px;background:#000;"></video>'
        + '<div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">'
        + '<i class="fas fa-check-circle me-1" style="color:var(--accent);"></i>'
        + file.name + ' (' + sizeMB + ' MB) &mdash; ready to upload</div>';
      preview.style.display = '';
    }

    function clearExistingVideo() {
      var wrap = document.getElementById('existingVideoWrap');
      var inp  = document.getElementById('oldVideoInput');
      if (wrap) wrap.style.display = 'none';
      if (inp)  inp.value = '';
    }
  </script>

  <?php if ($action === 'add' || $action === 'edit'): ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap" defer></script>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════════════════════════
       RICH TEXT EDITOR — Description field
       ══════════════════════════════════════════════════════════════════ -->
  <script>
  (function () {
    var editor   = document.getElementById('rteEditor');
    var hidden   = document.getElementById('rteHidden');
    var toolbar  = document.getElementById('rteToolbar');
    if (!editor) return;

    // ── Word/HTML paste cleaner + execCommand output cleaner ────────────
    function cleanPastedHtml(html) {
      // Remove Office namespace tags
      html = html.replace(/<\/?o:[^>]*>/gi, '');
      // Unwrap <font> tags (execCommand bold/color injects these)
      html = html.replace(/<font[^>]*>([\s\S]*?)<\/font>/gi, '$1');
      // Strip color: and background-color: from inline styles
      html = html.replace(/\s*color\s*:\s*[^;";]+;?\s*/gi, '');
      html = html.replace(/\s*background-color\s*:\s*[^;";]+;?\s*/gi, '');
      // Strip color="" attributes (old-style font color)
      html = html.replace(/\s+color="[^"]*"/gi, '');
      // Remove MsoNormal classes and lang attributes
      html = html.replace(/\s+class="[^"]*Mso[^"]*"/gi, '');
      html = html.replace(/\s+lang="[^"]*"/gi, '');
      // Remove mso-* style properties
      html = html.replace(/\bmso-[^;";]+;?\s*/gi, '');
      // Remove font-size inline styles
      html = html.replace(/\s*font-size\s*:\s*[^;";]+;?\s*/gi, '');
      // Remove empty style attributes
      html = html.replace(/\s+style="\s*"/gi, '');
      // Unwrap bare <span> with no attributes
      html = html.replace(/<span\s*>([^<]*)<\/span>/gi, '$1');
      // Collapse &nbsp; runs
      html = html.replace(/(\s*&nbsp;\s*)+/g, ' ');
      // Remove empty <p> tags
      html = html.replace(/<p[^>]*>\s*<\/p>/gi, '');
      return html.trim();
    }

    // ── Load existing content — also clean if it was saved dirty ────────
    var stored = hidden ? hidden.value.trim() : '';
    if (stored) {
      var cleaned = cleanPastedHtml(stored);
      editor.innerHTML = cleaned.indexOf('<') === -1
        ? '<p>' + cleaned.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>'
        : cleaned;
      // Write the cleaned version back to hidden so the next save is already clean
      if (hidden) hidden.value = editor.innerHTML;
    }

    // ── Intercept paste — strip Word junk before inserting ──────────────
    editor.addEventListener('paste', function (e) {
      e.preventDefault();
      var html = '';
      if (e.clipboardData.types.indexOf('text/html') !== -1) {
        html = cleanPastedHtml(e.clipboardData.getData('text/html'));
      } else {
        // Plain text fallback — wrap lines in <p>
        var text = e.clipboardData.getData('text/plain') || '';
        html = '<p>' + text.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
      }
      document.execCommand('insertHTML', false, html);
      syncHidden();
    });

    // ── Sync editor → hidden on every input (clean before saving) ───────
    editor.addEventListener('input', syncHidden);
    function syncHidden() {
      if (hidden) hidden.value = cleanPastedHtml(editor.innerHTML);
    }

    // Also sync on form submit (safety net)
    var form = editor.closest('form');
    if (form) form.addEventListener('submit', syncHidden);

    // ── Toolbar button clicks ────────────────────────────────────────────
    if (toolbar) {
      toolbar.addEventListener('mousedown', function (e) {
        var btn = e.target.closest('.rte-btn[data-cmd]');
        if (!btn) return;
        e.preventDefault(); // keep editor focus
        document.execCommand(btn.dataset.cmd, false, null);
        updateActiveStates();
        syncHidden();
      });
    }

    // ── Update active state on toolbar buttons ──────────────────────────
    editor.addEventListener('keyup', updateActiveStates);
    editor.addEventListener('mouseup', updateActiveStates);
    editor.addEventListener('selectionchange', updateActiveStates);

    function updateActiveStates() {
      if (!toolbar) return;
      toolbar.querySelectorAll('.rte-btn[data-cmd]').forEach(function (btn) {
        try {
          var active = document.queryCommandState(btn.dataset.cmd);
          btn.classList.toggle('rte-btn--active', active);
        } catch (e) {}
      });
    }

    // ── Two-column layout toggle ─────────────────────────────────────────
    window.rteToggleTwoCol = function () {
      var is2col = editor.classList.toggle('rte-two-col');
      var colBtn = document.getElementById('rteTwoCol');
      if (colBtn) colBtn.classList.toggle('rte-btn--active', is2col);
      syncHidden();
    };

    // ── Placeholder logic ────────────────────────────────────────────────
    editor.addEventListener('focus', function () {
      editor.classList.remove('rte-placeholder-visible');
    });
    editor.addEventListener('blur', function () {
      checkPlaceholder();
      syncHidden();
    });
    function checkPlaceholder() {
      var empty = editor.innerHTML.replace(/<br\s*\/?>/gi, '').trim() === '' || editor.textContent.trim() === '';
      editor.classList.toggle('rte-placeholder-visible', empty);
    }
    checkPlaceholder();
  })();
  </script>
</body>

</html>