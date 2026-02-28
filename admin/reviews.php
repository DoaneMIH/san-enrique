<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();

$message = '';

// Delete review
if (isset($_GET['delete'])) {
  $delId = (int) $_GET['delete'];
  $db->query("DELETE FROM reviews WHERE id=$delId");
  // Recalculate rating for that listing
  $r = $db->query("SELECT listing_id FROM reviews WHERE id=$delId");
  // Since we deleted it, recalculate all
  $db->query("UPDATE listings l SET rating = (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.listing_id = l.id)");
  $message = 'Review deleted.';
}

$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];

// Get all reviews with listing name
$reviews = $db->query("
    SELECT rv.*, l.name as listing_name, l.slug as listing_slug
    FROM reviews rv
    JOIN listings l ON rv.listing_id = l.id
    ORDER BY rv.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$totalReviews = count($reviews);
$avgRating = $totalReviews > 0 ? array_sum(array_column($reviews, 'rating')) / $totalReviews : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews - Admin Panel</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

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
      <a href="listings.php" class="admin-nav-link"><i class="fas fa-map-marker-alt"></i> Listings</a>
      <a href="categories.php" class="admin-nav-link"><i class="fas fa-th-large"></i> Categories</a>
      <a href="events.php" class="admin-nav-link"><i class="fas fa-calendar-alt"></i> Events</a>
      <div class="nav-section-label">Communication</div>
      <a href="messages.php" class="admin-nav-link">
        <i class="fas fa-envelope"></i> Messages
        <?php if ($unreadMsgs > 0): ?><span class="sidebar-badge"><?= $unreadMsgs ?></span><?php endif; ?>
      </a>
      <a href="reviews.php" class="admin-nav-link active"><i class="fas fa-star"></i> Reviews</a>
      <div class="nav-section-label">System</div>
      <a href="../index.php" target="_blank" class="admin-nav-link"><i class="fas fa-external-link-alt"></i> View
        Website</a>
      <a href="settings.php" class="admin-nav-link"><i class="fas fa-cog"></i> Settings</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
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
        <button class="d-lg-none" onclick="toggleSidebar()"
          style="background:none;border:none;color:var(--primary);font-size:1.1rem;cursor:pointer;margin-right:0.75rem;"><i
            class="fas fa-bars"></i></button>
        <span class="topbar-title">Reviews Management</span>
        <div class="topbar-breadcrumb"><?= $totalReviews ?> total reviews · Avg <?= number_format($avgRating, 1) ?>★
        </div>
      </div>
    </div>

    <div class="admin-main">
      <?php if ($message): ?>
        <div
          style="background:#dcfce7;color:#15803d;border-radius:10px;padding:12px 16px;font-size:0.87rem;font-weight:600;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <!-- Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#d4a017,#f0c040);"><i
                class="fas fa-star"></i></div>
            <div class="stat-value"><?= $totalReviews ?></div>
            <div class="stat-name">Total Reviews</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#2d6a4f,#52b788);"><i
                class="fas fa-trophy"></i></div>
            <div class="stat-value"><?= number_format($avgRating, 1) ?></div>
            <div class="stat-name">Average Rating</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#1b6fb0,#3b9dd1);"><i
                class="fas fa-thumbs-up"></i></div>
            <div class="stat-value"><?= count(array_filter($reviews, fn($r) => $r['rating'] >= 4)) ?></div>
            <div class="stat-name">4-5 Star Reviews</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#c0392b,#e74c3c);"><i
                class="fas fa-thumbs-down"></i></div>
            <div class="stat-value"><?= count(array_filter($reviews, fn($r) => $r['rating'] <= 2)) ?></div>
            <div class="stat-name">1-2 Star Reviews</div>
          </div>
        </div>
      </div>

      <!-- Reviews Table -->
      <div class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title"><i class="fas fa-star me-2" style="color:var(--gold);"></i>All Reviews</div>
          <div class="admin-search">
            <i class="fas fa-search"></i>
            <input type="text" id="reviewSearch" placeholder="Search reviews...">
          </div>
        </div>

        <?php if ($reviews): ?>
          <div style="overflow-x:auto;">
            <table class="admin-table" id="reviewsTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Reviewer</th>
                  <th>Listing</th>
                  <th>Rating</th>
                  <th>Comment</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reviews as $i => $review): ?>
                  <tr>
                    <td style="color:var(--text-muted);font-size:0.8rem;"><?= $i + 1 ?></td>
                    <td>
                      <div style="display:flex;align-items:center;gap:8px;">
                        <div
                          style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                          <?= strtoupper(substr($review['reviewer_name'] ?: 'A', 0, 1)) ?>
                        </div>
                        <span
                          style="font-weight:600;font-size:0.87rem;color:var(--primary);"><?= htmlspecialchars($review['reviewer_name'] ?: 'Anonymous') ?></span>
                      </div>
                    </td>
                    <td>
                      <a href="../listing.php?slug=<?= urlencode($review['listing_slug']) ?>" target="_blank"
                        style="font-size:0.85rem;font-weight:600;color:var(--primary-mid);text-decoration:none;">
                        <?= htmlspecialchars($review['listing_name']) ?> <i class="fas fa-external-link-alt"
                          style="font-size:0.65rem;"></i>
                      </a>
                    </td>
                    <td>
                      <div style="display:flex;align-items:center;gap:4px;">
                        <div style="display:flex;gap:1px;">
                          <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="<?= $s <= $review['rating'] ? 'fas' : 'far' ?> fa-star"
                              style="font-size:0.82rem;color:<?= $s <= $review['rating'] ? 'var(--gold)' : 'var(--gray-200)' ?>;"></i>
                          <?php endfor; ?>
                        </div>
                        <span style="font-size:0.8rem;font-weight:700;color:var(--primary);"><?= $review['rating'] ?></span>
                      </div>
                    </td>
                    <td style="max-width:240px;">
                      <div
                        style="font-size:0.83rem;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                        title="<?= htmlspecialchars($review['comment']) ?>">
                        <?= htmlspecialchars($review['comment']) ?>
                      </div>
                    </td>
                    <td style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                      <?= date('M j, Y', strtotime($review['created_at'])) ?></td>
                    <td>
                      <button
                        onclick="confirmDeleteReview(<?= $review['id'] ?>, '<?= addslashes($review['reviewer_name'] ?: 'Anonymous') ?>')"
                        class="btn-admin-danger">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="text-align:center;padding:4rem 2rem;color:var(--text-muted);">
            <i class="fas fa-star" style="font-size:3rem;color:var(--gray-200);display:block;margin-bottom:1rem;"></i>
            <div style="font-weight:600;color:var(--primary);margin-bottom:0.25rem;">No reviews yet</div>
            <div style="font-size:0.85rem;">Visitor reviews will appear here once submitted.</div>
          </div>
        <?php endif; ?>
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

    document.getElementById('reviewSearch')?.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      document.querySelectorAll('#reviewsTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });

    function confirmDeleteReview(id, name) {
      Swal.fire({
        title: 'Delete Review?',
        html: `Remove review by <strong>${name}</strong>? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b8c73',
        confirmButtonText: 'Yes, Delete'
      }).then(r => {
        if (r.isConfirmed) window.location.href = 'reviews.php?delete=' + id;
      });
    }

    // Live-update polling for reviews
    const ADMIN_POLL_INTERVAL = 15000; // 15 seconds
    let reviewsTimestamp = null;
    let reviewCount = <?= $totalReviews ?>;

    function pollForNewReviews() {
      fetch('/san-enrique/api/admin-updates.php?page=reviews', { cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
          if (!data.success) return;

          // Check if there are new reviews
          if (data.count > reviewCount) {
            showAdminToast('New review submitted!', 'A visitor just left a review.');
            reviewCount = data.count;

            // Refresh page after 2 seconds
            setTimeout(() => {
              location.reload();
            }, 2000);
          }

          reviewsTimestamp = data.timestamp;
        })
        .catch(err => console.log('Poll error:', err));
    }

    function showAdminToast(title, message) {
      const toast = document.createElement('div');
      toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #d4a017, #f0c040);
        color: #1b1b1b;
        padding: 16px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-weight: 600;
        z-index: 9999;
        max-width: 320px;
        animation: slideInRight 0.3s ease;
    `;
      toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-star"></i>
            <div>
                <div style="font-weight: 700;">${title}</div>
                <div style="font-size: 0.85rem; opacity: 0.85;">${message}</div>
            </div>
        </div>
    `;
      document.body.appendChild(toast);

      setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
    document.head.appendChild(style);

    // Start polling after page loads
    if (document.readyState === 'complete') {
      setInterval(pollForNewReviews, ADMIN_POLL_INTERVAL);
    } else {
      window.addEventListener('load', () => {
        setInterval(pollForNewReviews, ADMIN_POLL_INTERVAL);
      });
    }
  </script>
</body>

</html>