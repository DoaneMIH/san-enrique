<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();

$message = '';
$error = '';
$editCat = null;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($_POST['name']  ?? '');
    $icon  = sanitize($_POST['icon']  ?? 'fas fa-map-marker-alt');
    $color = sanitize($_POST['color'] ?? '#2d6a4f');
    $slug  = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));

    if ($_POST['form_action'] === 'add') {
        // Unique slug
        $base = $slug; $i = 1;
        while ($db->query("SELECT id FROM categories WHERE slug='$slug'")->num_rows > 0) {
            $slug = "$base-$i"; $i++;
        }
        if ($db->query("INSERT INTO categories (name, slug, icon, color) VALUES ('$name','$slug','$icon','$color')")) {
            $message = "Category \"$name\" added successfully!";
        } else {
            $error = 'Failed to add category: ' . $db->error;
        }
    } elseif ($_POST['form_action'] === 'edit') {
        $id = (int)$_POST['cat_id'];
        $base = $slug; $i = 1;
        while ($db->query("SELECT id FROM categories WHERE slug='$slug' AND id!=$id")->num_rows > 0) {
            $slug = "$base-$i"; $i++;
        }
        if ($db->query("UPDATE categories SET name='$name', slug='$slug', icon='$icon', color='$color' WHERE id=$id")) {
            $message = "Category updated successfully!";
        } else {
            $error = 'Failed to update: ' . $db->error;
        }
    }
}

// Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    // Prevent delete if has listings
    $cnt = $db->query("SELECT COUNT(*) as c FROM listings WHERE category_id=$delId")->fetch_assoc()['c'];
    if ($cnt > 0) {
        $error = "Cannot delete: this category has $cnt listing(s). Remove them first.";
    } else {
        $db->query("DELETE FROM categories WHERE id=$delId");
        $message = 'Category deleted.';
    }
}

// Edit fetch
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $r = $db->query("SELECT * FROM categories WHERE id=$editId");
    $editCat = $r ? $r->fetch_assoc() : null;
}

// Get all categories with listing count
$categories = $db->query("SELECT c.*, COUNT(l.id) as listing_count FROM categories c LEFT JOIN listings l ON c.id=l.category_id GROUP BY c.id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];

// FontAwesome icon suggestions
$iconSuggestions = [
    'fas fa-umbrella-beach','fas fa-home','fas fa-landmark','fas fa-utensils',
    'fas fa-seedling','fas fa-mountain','fas fa-tree','fas fa-map-marker-alt',
    'fas fa-church','fas fa-fish','fas fa-leaf','fas fa-water','fas fa-campground',
    'fas fa-hiking','fas fa-hotel','fas fa-store','fas fa-tractor','fas fa-sun'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">🌿</div>
    <div><div class="brand-text">San Enrique</div><div class="brand-sub">Tourism Hub Admin</div></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="admin-nav-link"><i class="fas fa-home"></i> Dashboard</a>
    <a href="listings.php" class="admin-nav-link"><i class="fas fa-map-marker-alt"></i> Listings</a>
    <a href="categories.php" class="admin-nav-link active"><i class="fas fa-th-large"></i> Categories</a>
    <a href="events.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Events</a>
    <div class="nav-section-label">Communication</div>
    <a href="messages.php" class="admin-nav-link">
      <i class="fas fa-envelope"></i> Messages
      <?php if ($unreadMsgs > 0): ?><span class="sidebar-badge"><?= $unreadMsgs ?></span><?php endif; ?>
    </a>
    <a href="reviews.php" class="admin-nav-link"><i class="fas fa-star"></i> Reviews</a>
    <div class="nav-section-label">System</div>
    <a href="../index.php" target="_blank" class="admin-nav-link"><i class="fas fa-external-link-alt"></i> View Website</a>
    <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog"></i> Settings</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= strtoupper(substr($admin['name'],0,1)) ?></div>
      <div><div class="user-name"><?= htmlspecialchars($admin['name']) ?></div><div class="user-role"><?= ucfirst($admin['role']) ?></div></div>
      <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</aside>

<div class="admin-content">
  <div class="admin-topbar">
    <div>
      <button class="d-lg-none" onclick="toggleSidebar()" style="background:none;border:none;color:var(--primary);font-size:1.1rem;cursor:pointer;margin-right:0.75rem;"><i class="fas fa-bars"></i></button>
      <span class="topbar-title">Categories</span>
      <div class="topbar-breadcrumb"><?= count($categories) ?> categories</div>
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

    <div class="row g-4">

      <!-- CATEGORY FORM -->
      <div class="col-lg-4">
        <div class="admin-form-card">
          <div class="admin-form-header">
            <div style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);">
              <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
            </div>
          </div>
          <form method="POST" action="categories.php">
            <input type="hidden" name="form_action" value="<?= $editCat ? 'edit' : 'add' ?>">
            <?php if ($editCat): ?>
            <input type="hidden" name="cat_id" value="<?= $editCat['id'] ?>">
            <?php endif; ?>
            <div class="admin-form-body" style="display:flex;flex-direction:column;gap:1rem;">

              <div>
                <label class="admin-label">Category Name *</label>
                <input type="text" name="name" class="admin-input" required
                       value="<?= htmlspecialchars($editCat['name'] ?? '') ?>"
                       placeholder="e.g. Resorts">
              </div>

              <div>
                <label class="admin-label">FontAwesome Icon Class</label>
                <input type="text" name="icon" id="iconInput" class="admin-input"
                       value="<?= htmlspecialchars($editCat['icon'] ?? 'fas fa-map-marker-alt') ?>"
                       placeholder="fas fa-umbrella-beach">
                <!-- Icon Picker -->
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                  <?php foreach ($iconSuggestions as $ico): ?>
                  <button type="button" onclick="setIcon('<?= $ico ?>')"
                          title="<?= $ico ?>"
                          style="width:36px;height:36px;border-radius:8px;border:1.5px solid var(--border);background:white;cursor:pointer;font-size:0.9rem;color:var(--primary-mid);transition:all 0.2s;"
                          onmouseover="this.style.borderColor='var(--accent)';this.style.background='var(--accent-pale)'"
                          onmouseout="this.style.borderColor='var(--border)';this.style.background='white'">
                    <i class="<?= $ico ?>"></i>
                  </button>
                  <?php endforeach; ?>
                </div>
                <!-- Icon Preview -->
                <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
                  <div id="iconPreview" style="width:44px;height:44px;border-radius:12px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-size:1.2rem;">
                    <i class="<?= htmlspecialchars($editCat['icon'] ?? 'fas fa-map-marker-alt') ?>" id="previewIcon"></i>
                  </div>
                  <span style="font-size:0.8rem;color:var(--text-muted);">Icon preview</span>
                </div>
              </div>

              <div>
                <label class="admin-label">Accent Color</label>
                <div style="display:flex;align-items:center;gap:10px;">
                  <input type="color" name="color" id="colorPicker"
                         value="<?= htmlspecialchars($editCat['color'] ?? '#2d6a4f') ?>"
                         style="width:50px;height:40px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;padding:2px;">
                  <input type="text" id="colorText" class="admin-input" style="flex:1;"
                         value="<?= htmlspecialchars($editCat['color'] ?? '#2d6a4f') ?>"
                         placeholder="#2d6a4f" oninput="syncColor(this.value)">
                </div>
                <!-- Color Presets -->
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                  <?php foreach (['#1b4332','#2d6a4f','#40916c','#52b788','#d4a017','#e63946','#1b6fb0','#7b2d8b','#e07b39','#2d4739'] as $c): ?>
                  <button type="button" onclick="setColor('<?= $c ?>')"
                          style="width:26px;height:26px;border-radius:6px;background:<?= $c ?>;border:2px solid white;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,0.2);"
                          title="<?= $c ?>"></button>
                  <?php endforeach; ?>
                </div>
              </div>

            </div>
            <div class="admin-form-footer">
              <?php if ($editCat): ?>
              <a href="categories.php" class="btn-admin-secondary"><i class="fas fa-times me-1"></i> Cancel</a>
              <?php endif; ?>
              <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save me-1"></i> <?= $editCat ? 'Update' : 'Add Category' ?>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- CATEGORY LIST -->
      <div class="col-lg-8">
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title">All Categories (<?= count($categories) ?>)</div>
          </div>
          <div style="overflow-x:auto;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Slug</th>
                  <th>Icon</th>
                  <th>Color</th>
                  <th>Listings</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                      <div style="width:38px;height:38px;border-radius:10px;background:<?= htmlspecialchars($cat['color']) ?>;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;">
                        <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                      </div>
                      <span style="font-weight:700;color:var(--primary);font-size:0.9rem;"><?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                  </td>
                  <td style="font-size:0.8rem;color:var(--text-muted);font-family:monospace;"><?= htmlspecialchars($cat['slug']) ?></td>
                  <td style="font-size:0.8rem;color:var(--text-muted);font-family:monospace;"><?= htmlspecialchars($cat['icon']) ?></td>
                  <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                      <div style="width:20px;height:20px;border-radius:5px;background:<?= htmlspecialchars($cat['color']) ?>;border:1px solid rgba(0,0,0,0.1);"></div>
                      <span style="font-size:0.8rem;color:var(--text-muted);font-family:monospace;"><?= htmlspecialchars($cat['color']) ?></span>
                    </div>
                  </td>
                  <td>
                    <a href="../explore.php?category=<?= htmlspecialchars($cat['slug']) ?>" target="_blank"
                       style="font-size:0.85rem;font-weight:700;color:var(--primary-mid);">
                      <?= $cat['listing_count'] ?> listing<?= $cat['listing_count'] != 1 ? 's' : '' ?>
                    </a>
                  </td>
                  <td>
                    <div style="display:flex;gap:6px;">
                      <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn-admin-edit">
                        <i class="fas fa-pencil-alt"></i> Edit
                      </a>
                      <?php if ($cat['listing_count'] == 0): ?>
                      <button onclick="confirmDeleteCat(<?= $cat['id'] ?>, '<?= addslashes($cat['name']) ?>')" class="btn-admin-danger">
                        <i class="fas fa-trash"></i>
                      </button>
                      <?php else: ?>
                      <button disabled title="Has listings" style="background:rgba(0,0,0,0.04);color:var(--gray-500);border:1px solid var(--border);border-radius:7px;padding:0.5rem 0.9rem;font-size:0.82rem;cursor:not-allowed;">
                        <i class="fas fa-lock"></i>
                      </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('d-none');
}

function setIcon(iconClass) {
  document.getElementById('iconInput').value = iconClass;
  document.getElementById('previewIcon').className = iconClass;
}

document.getElementById('iconInput').addEventListener('input', function() {
  document.getElementById('previewIcon').className = this.value;
});

// Sync color picker and text input
document.getElementById('colorPicker').addEventListener('input', function() {
  document.getElementById('colorText').value = this.value;
  document.getElementById('iconPreview').style.background = this.value;
});

function syncColor(val) {
  if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
    document.getElementById('colorPicker').value = val;
    document.getElementById('iconPreview').style.background = val;
  }
}

function setColor(hex) {
  document.getElementById('colorPicker').value = hex;
  document.getElementById('colorText').value = hex;
  document.getElementById('iconPreview').style.background = hex;
}

// Init icon preview bg color
document.getElementById('iconPreview').style.background = document.getElementById('colorPicker').value;

function confirmDeleteCat(id, name) {
  Swal.fire({
    title: 'Delete Category?',
    html: `Are you sure you want to delete <strong>${name}</strong>?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b8c73',
    confirmButtonText: 'Yes, Delete'
  }).then(r => {
    if (r.isConfirmed) window.location.href = 'categories.php?delete=' + id;
  });
}
</script>
</body>
</html>
