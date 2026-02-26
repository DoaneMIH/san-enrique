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
    $img = sanitize($_POST['featured_image'] ?? '');

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
                  <a href="../listing.php?slug=<?= urlencode($listing['slug']) ?>" target="_blank" class="btn-admin-edit" style="background:rgba(27,111,100,0.05);">
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
      <form method="POST" action="">
        <input type="hidden" name="form_action" value="<?= $action ?>">
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

            <!-- Featured Image URL -->
            <div class="col-12">
              <label class="admin-label">Featured Image URL</label>
              <input type="url" name="featured_image" class="admin-input" id="imgUrl"
                     value="<?= htmlspecialchars($editListing['featured_image'] ?? '') ?>" placeholder="https://example.com/image.jpg">
              <?php if ($editListing['featured_image'] ?? ''): ?>
              <img src="<?= htmlspecialchars($editListing['featured_image']) ?>" style="margin-top:8px;height:80px;border-radius:8px;object-fit:cover;" alt="Preview" id="imgPreview">
              <?php else: ?>
              <img src="" style="margin-top:8px;height:80px;border-radius:8px;object-fit:cover;display:none;" alt="Preview" id="imgPreview">
              <?php endif; ?>
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

// Image preview
$('#imgUrl').on('input', function() {
  const url = $(this).val();
  if (url) { $('#imgPreview').attr('src', url).show(); }
  else { $('#imgPreview').hide(); }
});

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
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap"></script>
<?php endif; ?>
</body>
</html>
