<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();

$message = '';
$error = '';
$editCat = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = sanitize($_POST['name'] ?? '');
  $icon = sanitize($_POST['icon'] ?? 'fas fa-map-marker-alt');
  $color = sanitize($_POST['color'] ?? '#2d6a4f');
  $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));

  if ($_POST['form_action'] === 'add') {
    $base = $slug;
    $i = 1;
    while ($db->query("SELECT id FROM categories WHERE slug='$slug'")->num_rows > 0) {
      $slug = "$base-$i";
      $i++;
    }
    if ($db->query("INSERT INTO categories (name, slug, icon, color) VALUES ('$name','$slug','$icon','$color')")) {
      $message = "Category \"$name\" added successfully!";
    } else {
      $error = 'Failed to add category: ' . $db->error;
    }
  } elseif ($_POST['form_action'] === 'edit') {
    $id = (int) $_POST['cat_id'];
    $base = $slug;
    $i = 1;
    while ($db->query("SELECT id FROM categories WHERE slug='$slug' AND id!=$id")->num_rows > 0) {
      $slug = "$base-$i";
      $i++;
    }
    if ($db->query("UPDATE categories SET name='$name', slug='$slug', icon='$icon', color='$color' WHERE id=$id")) {
      $message = "Category updated successfully!";
    } else {
      $error = 'Failed to update: ' . $db->error;
    }
  }
}

if (isset($_GET['delete'])) {
  $delId = (int) $_GET['delete'];
  $cnt = $db->query("SELECT COUNT(*) as c FROM listings WHERE category_id=$delId")->fetch_assoc()['c'];
  if ($cnt > 0) {
    $error = "Cannot delete: this category has $cnt listing(s). Remove them first.";
  } else {
    $db->query("DELETE FROM categories WHERE id=$delId");
    $message = 'Category deleted.';
  }
}

if (isset($_GET['edit'])) {
  $editId = (int) $_GET['edit'];
  $r = $db->query("SELECT * FROM categories WHERE id=$editId");
  $editCat = $r ? $r->fetch_assoc() : null;
}

$categories = $db->query("SELECT c.*, COUNT(l.id) as listing_count FROM categories c LEFT JOIN listings l ON c.id=l.category_id GROUP BY c.id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];

$iconSuggestions = ['fas fa-umbrella-beach', 'fas fa-home', 'fas fa-landmark', 'fas fa-utensils', 'fas fa-seedling', 'fas fa-mountain', 'fas fa-tree', 'fas fa-map-marker-alt', 'fas fa-church', 'fas fa-fish', 'fas fa-leaf', 'fas fa-water', 'fas fa-campground', 'fas fa-hiking', 'fas fa-hotel', 'fas fa-store', 'fas fa-tractor', 'fas fa-sun'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories - Admin Panel</title>
    <link rel="shortcut icon" type="x-icon" href="../assets/images/san-enrique-logo.jpg">

  <link
    href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap"
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
        <button class="d-lg-none" onclick="toggleSidebar()" class="topbar-menu-btn"><i class="fas fa-bars"></i></button>
        <span class="topbar-title">Categories</span>
        <div class="topbar-breadcrumb"><?= count($categories) ?> categories</div>
      </div>
    </div>

    <div class="admin-main">
      <?php if ($message): ?>
        <div class="admin-alert success">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="admin-alert error">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- CATEGORY FORM -->
        <div class="col-lg-4">
          <div class="admin-form-card">
            <div class="admin-form-header">
              <div class="form-header-title">
                <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
              </div>
            </div>
            <form method="POST" action="categories.php">
              <input type="hidden" name="form_action" value="<?= $editCat ? 'edit' : 'add' ?>">
              <?php if ($editCat): ?>
                <input type="hidden" name="cat_id" value="<?= $editCat['id'] ?>">
              <?php endif; ?>
              <div class="admin-form-body-flex">

                <div>
                  <label class="admin-label">Category Name *</label>
                  <input type="text" name="name" class="admin-input" required
                    value="<?= htmlspecialchars($editCat['name'] ?? '') ?>" placeholder="e.g. Resorts">
                </div>

                <div>
                  <label class="admin-label">FontAwesome Icon Class</label>
                  <input type="text" name="icon" id="iconInput" class="admin-input"
                    value="<?= htmlspecialchars($editCat['icon'] ?? 'fas fa-map-marker-alt') ?>"
                    placeholder="fas fa-umbrella-beach">
                  <!-- Icon Picker -->
                  <div class="icon-picker">
                    <?php foreach ($iconSuggestions as $ico): ?>
                      <button type="button" onclick="setIcon('<?= $ico ?>')" title="<?= $ico ?>" class="icon-picker-btn"
                        onmouseover="this.style.borderColor='var(--accent)';this.style.background='var(--accent-pale)'"
                        onmouseout="this.style.borderColor='var(--border)';this.style.background='white'">
                        <i class="<?= $ico ?>"></i>
                      </button>
                    <?php endforeach; ?>
                  </div>
                  <!-- Icon Preview -->
                  <div class="icon-preview-wrap">
                    <div id="iconPreview" class="icon-preview">
                      <i class="<?= htmlspecialchars($editCat['icon'] ?? 'fas fa-map-marker-alt') ?>"
                        id="previewIcon"></i>
                    </div>
                    <span class="icon-preview-label">Icon preview</span>
                  </div>
                </div>

                <div>
                  <label class="admin-label">Accent Color</label>
                  <div class="color-picker-wrap">
                    <input type="color" name="color" id="colorPicker"
                      value="<?= htmlspecialchars($editCat['color'] ?? '#2d6a4f') ?>" class="color-picker-swatch">
                    <input type="text" id="colorText" class="admin-input"
                      value="<?= htmlspecialchars($editCat['color'] ?? '#2d6a4f') ?>" placeholder="#2d6a4f"
                      oninput="syncColor(this.value)">
                  </div>
                  <!-- Color Presets -->
                  <div class="color-swatches">
                    <?php foreach (['#1b4332', '#2d6a4f', '#40916c', '#52b788', '#d4a017', '#e63946', '#1b6fb0', '#7b2d8b', '#e07b39', '#2d4739'] as $c): ?>
                      <button type="button" onclick="setColor('<?= $c ?>')" class="color-swatch-btn"
                        style="background:<?= $c ?>" title="<?= $c ?>"></button>
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
            <div class="table-scroll">
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
                        <div class="color-picker-wrap">
                          <div class="cat-icon-badge" style="background:<?= htmlspecialchars($cat['color']) ?>">
                            <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                          </div>
                          <span class="cat-name-text"><?= htmlspecialchars($cat['name']) ?></span>
                        </div>
                      </td>
                      <td class="cat-slug-text">
                        <?= htmlspecialchars($cat['slug']) ?>
                      </td>
                      <td class="cat-slug-text">
                        <?= htmlspecialchars($cat['icon']) ?>
                      </td>
                      <td>
                        <div class="color-picker-wrap-1">
                          <div class="cat-icon-badge-1" style="background:<?= htmlspecialchars($cat['color']) ?>">
                          </div>
                          <span class="cat-slug-text"><?= htmlspecialchars($cat['color']) ?></span>
                        </div>
                      </td>
                      <td>
                        <a href="../explore.php?category=<?= htmlspecialchars($cat['slug']) ?>" target="_blank"
                          class="review-listing-link">
                          <?= $cat['listing_count'] ?> listing<?= $cat['listing_count'] != 1 ? 's' : '' ?>
                        </a>
                      </td>
                      <td>
                        <div class="table-actions-1">
                          <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn-admin-edit">
                            <i class="fas fa-pencil-alt"></i> Edit
                          </a>
                          <?php if ($cat['listing_count'] == 0): ?>
                            <button onclick="confirmDeleteCat(<?= $cat['id'] ?>, '<?= addslashes($cat['name']) ?>')"
                              class="btn-admin-danger">
                              <i class="fas fa-trash"></i>
                            </button>
                          <?php else: ?>
                            <button disabled title="Has listings" class="btn-admin-secondary btn-disabled">
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


  <?php require_once 'scripts.php'; ?>
  <script>
    function toggleSidebar() {
      document.getElementById('adminSidebar').classList.toggle('open');
      document.getElementById('sidebarOverlay').classList.toggle('d-none');
    }

    function setIcon(iconClass) {
      document.getElementById('iconInput').value = iconClass;
      document.getElementById('previewIcon').className = iconClass;
    }

    document.getElementById('iconInput').addEventListener('input', function () {
      document.getElementById('previewIcon').className = this.value;
    });

    // Sync color picker and text input
    document.getElementById('colorPicker').addEventListener('input', function () {
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