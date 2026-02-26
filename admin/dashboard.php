<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$stats = getStats();
$db = getDB();
$recentListings = $db->query("SELECT l.*, c.name as cat_name FROM listings l JOIN categories c ON l.category_id = c.id ORDER BY l.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$recentMessages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    <a href="dashboard.php" class="admin-nav-link active">
      <i class="fas fa-home"></i> Dashboard
    </a>
    <a href="listings.php" class="admin-nav-link">
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
      <button class="d-lg-none" onclick="toggleSidebar()" style="background:none;border:none;color:var(--primary);font-size:1.1rem;cursor:pointer;margin-right:0.75rem;">
        <i class="fas fa-bars"></i>
      </button>
      <span class="topbar-title">Dashboard</span>
      <div class="topbar-breadcrumb">Welcome back, <?= htmlspecialchars($admin['name']) ?></div>
    </div>
    <div class="topbar-actions">
      <a href="listings.php?action=add" class="btn-admin-primary">
        <i class="fas fa-plus"></i> Add Listing
      </a>
    </div>
  </div>

  <div class="admin-main">
    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#2d6a4f,#52b788);">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <div class="stat-value"><?= $stats['listings'] ?></div>
          <div class="stat-name">Total Listings</div>
          <i class="fas fa-map-marked-alt stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#b7791f,#d4a017);">
            <i class="fas fa-th-large"></i>
          </div>
          <div class="stat-value"><?= $stats['categories'] ?></div>
          <div class="stat-name">Categories</div>
          <i class="fas fa-list stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#1b6fb0,#3b9dd1);">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="stat-value"><?= $stats['events'] ?></div>
          <div class="stat-name">Events</div>
          <i class="fas fa-calendar stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon" style="background:linear-gradient(135deg,#c0392b,#e74c3c);">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="stat-value"><?= $unreadMsgs ?></div>
          <div class="stat-name">Unread Messages</div>
          <i class="fas fa-mail-bulk stat-bg-icon"></i>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- RECENT LISTINGS -->
      <div class="col-lg-8">
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title">Recent Listings</div>
            <div class="d-flex gap-2">
              <a href="listings.php" class="btn-admin-secondary">View All</a>
              <a href="listings.php?action=add" class="btn-admin-primary"><i class="fas fa-plus me-1"></i> Add New</a>
            </div>
          </div>
          <div style="overflow-x:auto;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Listing</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Views</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentListings as $listing): ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                      <img src="<?= $listing['featured_image'] ?: 'https://placehold.co/48x38/1b4332/ffffff?text=?' ?>"
                           class="listing-thumb"
                           alt=""
                           onerror="this.src='https://placehold.co/48x38/1b4332/ffffff?text=?'">
                      <div>
                        <div style="font-weight:700;font-size:0.88rem;color:var(--primary);"><?= htmlspecialchars($listing['name']) ?></div>
                        <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($listing['barangay'] ?: 'N/A') ?></div>
                      </div>
                    </div>
                  </td>
                  <td><span style="font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($listing['cat_name']) ?></span></td>
                  <td><span class="status-badge <?= $listing['status'] ?>"><?= ucfirst($listing['status']) ?></span></td>
                  <td style="font-size:0.85rem;color:var(--text-muted);"><?= number_format($listing['views']) ?></td>
                  <td>
                    <div style="display:flex;gap:6px;">
                      <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-edit">
                        <i class="fas fa-pencil-alt"></i> Edit
                      </a>
                  <a href="listing_view.php?slug=<?= urlencode($listing['slug']) ?>" class="btn-admin-edit" style="background:rgba(27,111,100,0.05);" title="Preview (Admin View)">
                        <i class="fas fa-eye"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- RECENT MESSAGES -->
      <div class="col-lg-4">
        <div class="admin-table-wrap" style="height:100%;">
          <div class="admin-table-header">
            <div class="admin-table-title">Recent Messages</div>
            <a href="messages.php" class="btn-admin-secondary">View All</a>
          </div>
          <div style="padding:0.5rem;">
            <?php if ($recentMessages): ?>
            <?php foreach ($recentMessages as $msg): ?>
            <div style="padding:0.85rem;border-radius:10px;margin-bottom:4px;background:<?= $msg['is_read'] ? 'transparent' : 'var(--content-bg)' ?>;border:1px solid var(--border);transition:var(--transition);">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;">
                <div>
                  <div style="font-weight:700;font-size:0.87rem;color:var(--primary);"><?= htmlspecialchars($msg['name']) ?></div>
                  <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars(substr($msg['subject'],0,35)) ?>...</div>
                </div>
                <?php if (!$msg['is_read']): ?>
                <span style="background:var(--accent);width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:4px;"></span>
                <?php endif; ?>
              </div>
              <div style="font-size:0.72rem;color:var(--gray-500);margin-top:4px;"><?= date('M j, Y', strtotime($msg['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:0.87rem;">
              <i class="fas fa-inbox" style="font-size:2rem;color:var(--gray-200);display:block;margin-bottom:0.5rem;"></i>
              No messages yet
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleSidebar() {
  $('#adminSidebar').toggleClass('open');
  $('#sidebarOverlay').toggleClass('d-none');
}
</script>
</body>
</html>