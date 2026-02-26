<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();
$categories = getCategories();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $barangay = sanitize($_POST['barangay'] ?? '');
    $contact = sanitize($_POST['contact'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $website = sanitize($_POST['website'] ?? '');
    $lat = (float)($_POST['latitude'] ?? 0);
    $lng = (float)($_POST['longitude'] ?? 0);
    $hours = sanitize($_POST['operating_hours'] ?? '');
    $fee = sanitize($_POST['entrance_fee'] ?? '');
    $amenities = sanitize($_POST['amenities'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    // Keep existing image path from hidden field; override if new file uploaded
    $img = sanitize($_POST['featured_image'] ?? '');
    $uploadDir = '../uploads/listings/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!empty($_FILES['featured_image_upload']['name'])) {
        $file    = $_FILES['featured_image_upload'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid image type. Allowed: JPG, PNG, WEBP, GIF.';
        } elseif ($file['size'] > 5*1024*1024) {
            $error = 'Image too large. Max 5 MB.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload error (code '.$file['error'].'). Please try again.';
        } else {
            $newName  = 'listing_'.time().'_'.mt_rand(100,999).'.'.$ext;
            $destPath = $uploadDir.$newName;
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Delete old uploaded file when replacing
                $oldImg = sanitize($_POST['old_image'] ?? '');
                if ($oldImg && strpos($oldImg,'../uploads/listings/')===0 && file_exists($oldImg)) unlink($oldImg);
                $img = '../uploads/listings/'.$newName;
            } else {
                $error = 'Failed to save image. Check uploads/listings/ folder is writable.';
            }
        }
    }

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
        $sql = "INSERT INTO listings (category_id, name, slug, description, address, barangay, contact, email, website, latitude, longitude, featured_image, operating_hours, entrance_fee, amenities, status, is_featured) VALUES ($category_id, '$name', '$slug', '$description', '$address', '$barangay', '$contact', '$email', '$website', $lat, $lng, '$img', '$hours', '$fee', '$amenities', '$status', $featured)";
        if ($db->query($sql)) {
            $message = 'Listing added successfully!';
            $action = 'list';
        } else {
            $error = 'Error adding listing: ' . $db->error;
        }
    } elseif ($_POST['form_action'] === 'edit') {
        $editId = (int)$_POST['listing_id'];
        // Check slug uniqueness (exclude self)
        $baseSlug = $slug;
        $i = 1;
        while ($db->query("SELECT id FROM listings WHERE slug = '$slug' AND id != $editId")->num_rows > 0) {
            $slug = "$baseSlug-$i";
            $i++;
        }
        $sql = "UPDATE listings SET category_id=$category_id, name='$name', slug='$slug', description='$description', address='$address', barangay='$barangay', contact='$contact', email='$email', website='$website', latitude=$lat, longitude=$lng, featured_image='$img', operating_hours='$hours', entrance_fee='$fee', amenities='$amenities', status='$status', is_featured=$featured WHERE id=$editId";
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
    $delId = (int)$_GET['delete'];
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
$listings = $db->query("SELECT l.*, c.name as cat_name, c.color FROM listings l JOIN categories c ON l.category_id = c.id ORDER BY l.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Listings Management - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<!-- SIDEBAR (shared) -->
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
    <a href="dashboard.php" class="admin-nav-link"><i class="fas fa-home"></i> Dashboard</a>
    <a href="listings.php" class="admin-nav-link active"><i class="fas fa-map-marker-alt"></i> Listings</a>
    <a href="categories.php" class="admin-nav-link"><i class="fas fa-th-large"></i> Categories</a>
    <a href="events.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Events</a>
    <div class="nav-section-label">Communication</div>
    <a href="messages.php" class="admin-nav-link"><i class="fas fa-envelope"></i> Messages</a>
    <a href="reviews.php" class="admin-nav-link"><i class="fas fa-star"></i> Reviews</a>
    <div class="nav-section-label">System</div>
    <a href="../index.php" class="admin-nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
    <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog"></i> Settings</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= strtoupper(substr($admin['name'],0,1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($admin['name']) ?></div>
        <div class="user-role"><?= ucfirst($admin['role']) ?></div>
      </div>
      <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</aside>

<div class="admin-content">
  <div class="admin-topbar">
    <div>
      <button class="d-lg-none" onclick="toggleSidebar()" style="background:none;border:none;color:var(--primary);font-size:1.1rem;cursor:pointer;margin-right:0.75rem;"><i class="fas fa-bars"></i></button>
      <span class="topbar-title"><?= $action === 'list' ? 'Listings Management' : ($action === 'add' ? 'Add New Listing' : 'Edit Listing') ?></span>
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
    <div style="background:#dcfce7;color:#15803d;border-radius:10px;padding:12px 16px;font-size:0.87rem;font-weight:600;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div style="background:#fee2e2;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:0.87rem;font-weight:600;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
    <!-- LISTINGS TABLE -->
    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">All Listings (<?= count($listings) ?>)</div>
        <div class="admin-search">
          <i class="fas fa-search"></i>
          <input type="text" id="tableSearch" placeholder="Search listings...">
        </div>
      </div>
      <div style="overflow-x:auto;">
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
            <tr>
              <td style="color:var(--text-muted);font-size:0.8rem;"><?= $i + 1 ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <img src="<?= $listing['featured_image'] ?: 'https://placehold.co/48x38/1b4332/ffffff?text=?' ?>"
                       class="listing-thumb" alt=""
                       onerror="this.src='https://placehold.co/48x38/1b4332/ffffff?text=?'">
                  <div>
                    <div style="font-weight:700;font-size:0.87rem;color:var(--primary);"><?= htmlspecialchars($listing['name']) ?></div>
                    <div style="font-size:0.72rem;color:var(--text-muted);"><?= htmlspecialchars($listing['slug']) ?></div>
                  </div>
                </div>
              </td>
              <td><span style="font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($listing['cat_name']) ?></span></td>
              <td style="font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($listing['barangay'] ?: '—') ?></td>
              <td style="text-align:center;"><?= $listing['is_featured'] ? '<i class="fas fa-star" style="color:var(--gold);"></i>' : '<i class="far fa-star" style="color:var(--gray-200);"></i>' ?></td>
              <td><span class="status-badge <?= $listing['status'] ?>"><?= ucfirst($listing['status']) ?></span></td>
              <td style="font-size:0.85rem;"><?= number_format($listing['views']) ?></td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap;">
                  <a href="?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-edit">
                    <i class="fas fa-pencil-alt"></i> Edit
                  </a>
                  <a href="listing_view.php?slug=<?= urlencode($listing['slug']) ?>" class="btn-admin-edit" style="background:rgba(27,111,100,0.05);" title="Preview (Admin View)">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button onclick="confirmDelete(<?= $listing['id'] ?>, '<?= addslashes($listing['name']) ?>')" class="btn-admin-danger">
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
        <div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--primary);">
          <?= $action === 'add' ? 'Add New Listing' : 'Edit: '.htmlspecialchars($editListing['name'] ?? '') ?>
        </div>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="form_action" value="<?= $action ?>">
        <!-- Preserves current image path when no new file is uploaded -->
        <input type="hidden" name="featured_image" id="currentImgPath"
               value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>">
        <input type="hidden" name="old_image"
               value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>">
        <?php if ($action === 'edit'): ?>
        <input type="hidden" name="listing_id" value="<?= $editListing['id'] ?>">
        <?php endif; ?>
        <div class="admin-form-body">
          <div class="row g-3">
            <!-- Name & Category -->
            <div class="col-md-8">
              <label class="admin-label">Listing Name *</label>
              <input type="text" name="name" class="admin-input" required
                     value="<?= htmlspecialchars($editListing['name'] ?? '') ?>" placeholder="e.g. Paradise Cove Resort">
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

            <!-- Description -->
            <div class="col-12">
              <label class="admin-label">Description</label>
              <textarea name="description" class="admin-input" rows="4" placeholder="Describe this place..."><?= htmlspecialchars($editListing['description'] ?? '') ?></textarea>
            </div>

            <!-- Address & Barangay -->
            <div class="col-md-8">
              <label class="admin-label">Full Address</label>
              <input type="text" name="address" class="admin-input"
                     value="<?= htmlspecialchars($editListing['address'] ?? '') ?>" placeholder="Street, Barangay, San Enrique, Iloilo">
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
                     value="<?= htmlspecialchars($editListing['operating_hours'] ?? '') ?>" placeholder="6:00 AM - 10:00 PM">
            </div>
            <div class="col-md-6">
              <label class="admin-label">Entrance Fee</label>
              <input type="text" name="entrance_fee" class="admin-input"
                     value="<?= htmlspecialchars($editListing['entrance_fee'] ?? '') ?>" placeholder="₱150 per person / Free">
            </div>

            <!-- Amenities -->
            <div class="col-12">
              <label class="admin-label">Amenities (comma-separated)</label>
              <input type="text" name="amenities" class="admin-input"
                     value="<?= htmlspecialchars($editListing['amenities'] ?? '') ?>" placeholder="Swimming Pool, Restaurant, WiFi, Parking, Cottage">
            </div>

            <!-- Featured Photo Upload -->
            <div class="col-12">
              <label class="admin-label">
                <i class="fas fa-camera me-1" style="color:var(--accent);"></i> Featured Photo
              </label>
              <?php
                $existingImg = $editListing['featured_image'] ?? '';
                // Build display URL: strip ../ prefix for browser
                $displayImg = '';
                if ($existingImg) {
                    if (strpos($existingImg,'http')===0) { $displayImg=$existingImg; }
                    else { $clean=preg_replace('#^(\.\./)+#','',$existingImg); $displayImg=BASE_URL.'/'.ltrim($clean,'/'); }
                }
                $hasImg = !empty($displayImg);
              ?>
              <div id="imgPreviewWrap" style="<?= $hasImg?'':'display:none;' ?>border-radius:12px;overflow:hidden;border:2px solid var(--border);position:relative;margin-bottom:8px;">
                <img id="imgPreview" src="<?= htmlspecialchars($displayImg) ?>" alt="Preview"
                     style="width:100%;height:200px;object-fit:cover;display:block;"
                     onerror="this.src='https://placehold.co/600x200/1b4332/ffffff?text=Preview'">
                <span style="position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,.55);color:white;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;" id="imgBadgeLabel">
                  <?= $hasImg?'Current photo':'Selected photo' ?>
                </span>
                <button type="button" onclick="removeImage()"
                        style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.55);color:white;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center;"
                        title="Remove photo">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div id="uploadZone" style="<?= $hasImg?'display:none;':'' ?>border:2px dashed var(--border);border-radius:12px;padding:2rem 1rem;text-align:center;cursor:pointer;background:var(--content-bg);position:relative;transition:all .2s;"
                   onmouseover="this.style.borderColor='var(--accent)';this.style.background='var(--accent-pale)'"
                   onmouseout="this.style.borderColor='var(--border)';this.style.background='var(--content-bg)'">
                <input type="file" name="featured_image_upload" id="fileInput"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;"
                       onchange="handleFileSelect(this)">
                <i class="fas fa-cloud-upload-alt" style="font-size:2.2rem;color:var(--gray-300);display:block;margin-bottom:.6rem;"></i>
                <div style="font-size:.87rem;color:var(--text-muted);font-weight:700;">Click to upload or drag &amp; drop a photo</div>
                <div style="font-size:.75rem;color:var(--gray-400);margin-top:3px;">JPG, PNG, WEBP or GIF &mdash; max 5 MB</div>
              </div>
              <div style="margin-top:.5rem;font-size:.78rem;color:var(--text-muted);">
                <i class="fas fa-info-circle me-1"></i>
                Photos are saved to <code>uploads/listings/</code> and shown automatically on the website.
              </div>
            </div>

            <!-- GPS Coordinates -->
            <div class="col-12">
              <label class="admin-label"><i class="fas fa-map-pin me-1" style="color:var(--accent);"></i> GPS Coordinates — Click on the map to set location</label>
              <div class="coord-fields mb-2">
                <div>
                  <label class="admin-label" style="font-size:0.75rem;">Latitude</label>
                  <input type="number" name="latitude" id="latitude" class="admin-input" step="any"
                         value="<?= $editListing['latitude'] ?? '10.9178' ?>" placeholder="10.9178">
                </div>
                <div>
                  <label class="admin-label" style="font-size:0.75rem;">Longitude</label>
                  <input type="number" name="longitude" id="longitude" class="admin-input" step="any"
                         value="<?= $editListing['longitude'] ?? '122.8845' ?>" placeholder="122.8845">
                </div>
              </div>
              <div id="adminMapPicker"></div>
            </div>

            <!-- Status & Featured -->
            <div class="col-md-6">
              <label class="admin-label">Status</label>
              <select name="status" class="admin-input">
                <option value="active" <?= ($editListing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($editListing['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="pending" <?= ($editListing['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
              </select>
            </div>
            <div class="col-md-6" style="display:flex;align-items:flex-end;">
              <div class="form-check" style="margin-bottom:0.5rem;">
                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                       <?= ($editListing['is_featured'] ?? 0) ? 'checked' : '' ?> value="1">
                <label class="form-check-label" for="isFeatured" style="font-size:0.87rem;font-weight:600;color:var(--text-muted);">
                  <i class="fas fa-star me-1" style="color:var(--gold);"></i> Mark as Featured Listing
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
  lat: <?= (float)($editListing['latitude'] ?? 10.9178) ?>,
  lng: <?= (float)($editListing['longitude'] ?? 122.8845) ?>
};
</script>
<?php endif; ?>
<script src="../assets/js/main.js"></script>
<script>
function toggleSidebar() {
  $('#adminSidebar').toggleClass('open');
  $('#sidebarOverlay').toggleClass('d-none');
}

// Table search
$('#tableSearch').on('input', function() {
  const q = $(this).val().toLowerCase();
  $('#listingsTable tbody tr').each(function() {
    $(this).toggle($(this).text().toLowerCase().includes(q));
  });
});

// Image upload handlers
function handleFileSelect(input) {
  const file = input.files[0];
  if (!file) return;
  const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  if (!allowed.includes(file.type)) {
    Swal.fire({icon:'error',title:'Invalid File',text:'Please upload JPG, PNG, WEBP, or GIF.',confirmButtonColor:'#1b4332'});
    input.value = ''; return;
  }
  if (file.size > 5*1024*1024) {
    Swal.fire({icon:'error',title:'Too Large',text:'Max file size is 5 MB.',confirmButtonColor:'#1b4332'});
    input.value = ''; return;
  }
  const reader = new FileReader();
  reader.onload = function(e) {
    $('#imgPreview').attr('src', e.target.result);
    $('#imgBadgeLabel').text(file.name);
    $('#imgPreviewWrap').show();
    $('#uploadZone').hide();
    $('#currentImgPath').val(''); // clear so upload takes over
  };
  reader.readAsDataURL(file);
}
function removeImage() {
  document.getElementById('fileInput').value = '';
  $('#currentImgPath').val('');
  $('#imgPreviewWrap').hide();
  $('#uploadZone').show();
}
// Drag & drop
const zone = document.getElementById('uploadZone');
if (zone) {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='var(--accent)'; zone.style.background='var(--accent-pale)'; });
  zone.addEventListener('dragleave', () => { zone.style.borderColor='var(--border)'; zone.style.background='var(--content-bg)'; });
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.style.borderColor='var(--border)'; zone.style.background='var(--content-bg)';
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

// Admin map picker
<?php if ($action === 'add' || $action === 'edit'): ?>
function initMap() {
  const lat = parseFloat($('#latitude').val()) || 10.9178;
  const lng = parseFloat($('#longitude').val()) || 122.8845;
  initAdminMapPicker(lat, lng);
}
<?php endif; ?>
</script>


<?php if ($action === 'add' || $action === 'edit'): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap" defer></script>
<?php endif; ?>
</body>
</html>