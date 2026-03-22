<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$stats = getStats();
$db = getDB();

$recentListings = $db->query("SELECT l.*, c.name as cat_name FROM listings l JOIN categories c ON l.category_id = c.id ORDER BY l.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$recentMessages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$unreadMsgs     = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];
$pendingListings= $db->query("SELECT COUNT(*) as c FROM listings WHERE status='pending'")->fetch_assoc()['c'];
$totalViews     = $db->query("SELECT COALESCE(SUM(views),0) as c FROM listings")->fetch_assoc()['c'];
$totalReviews   = $db->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c'];
$avgRating      = $db->query("SELECT COALESCE(AVG(rating),0) as c FROM reviews")->fetch_assoc()['c'];
$upcomingEvents = $db->query("SELECT COUNT(*) as c FROM events WHERE status='active' AND event_date >= CURDATE()")->fetch_assoc()['c'];
$isSuperAdmin   = ($admin['role'] === 'superadmin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php require_once 'sidebar.php'; ?>

<div class="admin-content">
  <!-- TOPBAR -->
  <div class="admin-topbar">
    <div class="topbar-left">
      <button class="d-lg-none topbar-icon-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
      </button>
      <div>
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-breadcrumb">Welcome back, <?= htmlspecialchars($admin['name']) ?> 👋</div>
      </div>
    </div>
    <div class="topbar-actions">
      <a href="messages.php" class="topbar-icon-btn" title="Messages">
        <i class="fas fa-envelope"></i>
        <?php if ($unreadMsgs > 0): ?>
          <span class="topbar-badge"><?= min($unreadMsgs,9) ?></span>
        <?php endif; ?>
      </a>
      <a href="listings.php?action=add" class="btn-admin-primary">
        <i class="fas fa-plus"></i> Add Listing
      </a>
    </div>
  </div>

  <div class="admin-main">

    <!-- WELCOME BANNER -->
    <div class="dash-welcome-banner">
      <div class="banner-shape banner-shape-1"></div>
      <div class="banner-shape banner-shape-2"></div>
      <div class="banner-content">
        <div class="banner-title">San Enrique Tourism Hub</div>
        <div class="banner-meta">
          <span><i class="fas fa-map-marker-alt me-1"></i>San Enrique, Iloilo</span>
          <span>·</span>
          <span><i class="fas fa-calendar me-1"></i><?= date('l, F j, Y') ?></span>
        </div>
      </div>
      <div class="banner-actions">
        <a href="../index.php" target="_blank" class="btn-banner">
          <i class="fas fa-globe"></i> Live Website
        </a>
        <a href="../map.php" target="_blank" class="btn-banner">
          <i class="fas fa-map"></i> Map
        </a>
      </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-green">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <div class="stat-value"><?= number_format($stats['listings']) ?></div>
          <div class="stat-name">Active Listings</div>
          <?php if ($pendingListings > 0): ?>
            <div class="stat-chip stat-chip-warning"><?= $pendingListings ?> pending review</div>
          <?php endif; ?>
          <i class="fas fa-map-marked-alt stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-blue">
            <i class="fas fa-eye"></i>
          </div>
          <div class="stat-value"><?= number_format($totalViews) ?></div>
          <div class="stat-name">Total Views</div>
          <div class="stat-chip stat-chip-info">All time</div>
          <i class="fas fa-chart-line stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-purple">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="stat-value"><?= $upcomingEvents ?></div>
          <div class="stat-name">Upcoming Events</div>
          <div class="stat-chip stat-chip-purple">Active</div>
          <i class="fas fa-calendar-check stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-red">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="stat-value"><?= $unreadMsgs ?></div>
          <div class="stat-name">Unread Messages</div>
          <?php if ($unreadMsgs > 0): ?>
            <a href="messages.php" class="stat-chip stat-chip-danger">View all →</a>
          <?php else: ?>
            <div class="stat-chip stat-chip-success">All caught up!</div>
          <?php endif; ?>
          <i class="fas fa-mail-bulk stat-bg-icon"></i>
        </div>
      </div>
    </div>

    <!-- SECONDARY STATS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="mini-stat-card">
          <div class="mini-stat-icon stat-icon-amber"><i class="fas fa-th-large"></i></div>
          <div>
            <div class="mini-stat-value"><?= $stats['categories'] ?></div>
            <div class="mini-stat-label">Categories</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mini-stat-card">
          <div class="mini-stat-icon stat-icon-faint-amber"><i class="fas fa-star"></i></div>
          <div>
            <div class="mini-stat-value"><?= number_format($avgRating,1) ?></div>
            <div class="mini-stat-label">Avg Rating</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mini-stat-card">
          <div class="mini-stat-icon stat-icon-cyan"><i class="fas fa-comment-dots"></i></div>
          <div>
            <div class="mini-stat-value"><?= $totalReviews ?></div>
            <div class="mini-stat-label">Reviews</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="mini-stat-card">
          <div class="mini-stat-icon stat-icon-dark-green"><i class="fas fa-map"></i></div>
          <div>
            <div class="mini-stat-value"><?= $stats['barangays'] ?></div>
            <div class="mini-stat-label">Barangays</div>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN GRID -->
    <div class="row g-4">
      <!-- RECENT LISTINGS -->
      <div class="col-lg-8">
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title"><i class="fas fa-clock me-2 section-icon-accent"></i>Recent Listings</div>
            <div class="d-flex gap-2">
              <a href="listings.php" class="btn-admin-secondary btn-sm">View All</a>
              <a href="listings.php?action=add" class="btn-admin-primary btn-sm"><i class="fas fa-plus"></i> Add</a>
            </div>
          </div>
          <div class="table-scroll">
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
                    <div class="listing-info">
                      <img src="<?= $listing['featured_image'] ?: 'https://placehold.co/48x38/dcfce7/0f5132?text=?' ?>"
                           class="listing-thumb" alt=""
                           onerror="this.src='https://placehold.co/48x38/dcfce7/0f5132?text=?'">
                      <div class="listing-info-text">
                        <div class="listing-name"><?= htmlspecialchars($listing['name']) ?></div>
                        <div class="listing-slug"><?= htmlspecialchars($listing['barangay'] ?: 'N/A') ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="tag-pill"><?= htmlspecialchars($listing['cat_name']) ?></span>
                  </td>
                  <td><span class="status-badge <?= $listing['status'] ?>"><?= ucfirst($listing['status']) ?></span></td>
                  <td class="td-small"><?= number_format($listing['views']) ?></td>
                  <td>
                    <div class="table-actions">
                      <a href="listings.php?action=edit&id=<?= $listing['id'] ?>" class="btn-admin-edit btn-sm">
                        <i class="fas fa-pencil-alt"></i>
                      </a>
                      <a href="listing_view.php?slug=<?= urlencode($listing['slug']) ?>" class="btn-admin-view btn-sm">
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
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title"><i class="fas fa-inbox me-2 section-icon-accent"></i>Recent Messages</div>
            <?php if ($unreadMsgs > 0): ?>
              <span class="status-badge pending"><?= $unreadMsgs ?> new</span>
            <?php endif; ?>
          </div>
          <?php if ($recentMessages): ?>
            <?php foreach ($recentMessages as $msg):
              $isUnread = !$msg['is_read']; ?>
              <a href="messages.php?view=<?= $msg['id'] ?>" class="msg-list-item">
                <div class="msg-list-row <?= $isUnread ? '' : '' ?>">
                  <div class="msg-sender-wrap">
                    <div class="msg-avatar"><?= strtoupper(substr($msg['name'],0,1)) ?></div>
                    <div class="msg-sender-info">
                      <div class="msg-sender-name <?= $isUnread ? 'unread' : 'read' ?>">
                        <?= htmlspecialchars($msg['name']) ?>
                        <?php if ($isUnread): ?><span class="msg-unread-dot"></span><?php endif; ?>
                      </div>
                      <div class="msg-subject"><?= htmlspecialchars(substr($msg['subject'],0,40)) ?></div>
                    </div>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
            <div class="p-3 text-center">
              <a href="messages.php" class="btn-admin-secondary btn-sm">View All Messages</a>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-inbox empty-icon-lg"></i>
              <div class="empty-title">No messages yet</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div><!-- /admin-main -->
</div><!-- /admin-content -->

<div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'scripts.php'; ?>
</body>
</html>
