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
  $db->query("UPDATE listings l SET rating = (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.listing_id = l.id)");
  $message = 'Review deleted.';
}

$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];

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
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews - Admin Panel</title>
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
        <button class="d-lg-none" onclick="toggleSidebar()"
          class="topbar-menu-btn"><i
            class="fas fa-bars"></i></button>
        <span class="topbar-title">Reviews Management</span>
        <div class="topbar-breadcrumb"><?= $totalReviews ?> total reviews · Avg <?= number_format($avgRating, 1) ?>★
        </div>
      </div>
    </div>

    <div class="admin-main">
      <?php if ($message): ?>
        <div
          class="admin-alert success">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <!-- Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon stat-icon-gold"><i
                class="fas fa-star"></i></div>
            <div class="stat-value"><?= $totalReviews ?></div>
            <div class="stat-name">Total Reviews</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon stat-icon-teal"><i
                class="fas fa-trophy"></i></div>
            <div class="stat-value"><?= number_format($avgRating, 1) ?></div>
            <div class="stat-name">Average Rating</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon stat-icon-sky"><i
                class="fas fa-thumbs-up"></i></div>
            <div class="stat-value"><?= count(array_filter($reviews, fn($r) => $r['rating'] >= 4)) ?></div>
            <div class="stat-name">4-5 Star Reviews</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="dash-stat-card">
            <div class="stat-icon stat-icon-crimson"><i
                class="fas fa-thumbs-down"></i></div>
            <div class="stat-value"><?= count(array_filter($reviews, fn($r) => $r['rating'] <= 2)) ?></div>
            <div class="stat-name">1-2 Star Reviews</div>
          </div>
        </div>
      </div>

      <!-- Reviews Table -->
      <div class="admin-table-wrap">
        <div class="admin-table-header">
          <div class="admin-table-title"><i class="fas fa-star me-2 section-icon-gold"></i>All Reviews</div>
          <div class="admin-search">
            <i class="fas fa-search"></i>
            <input type="text" id="reviewSearch" placeholder="Search reviews...">
          </div>
        </div>

        <?php if ($reviews): ?>
          <div class="table-scroll">
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
                    <td class="td-muted"><?= $i + 1 ?></td>
                    <td>
                      <div class="reviewer-row">
                        <div
                          class="reviewer-avatar">
                          <?= strtoupper(substr($review['reviewer_name'] ?: 'A', 0, 1)) ?>
                        </div>
                        <span
                          class="reviewer-name"><?= htmlspecialchars($review['reviewer_name'] ?: 'Anonymous') ?></span>
                      </div>
                    </td>
                    <td>
                      <a href="../listing.php?slug=<?= urlencode($review['listing_slug']) ?>" target="_blank"
                        class="review-listing-link">
                        <?= htmlspecialchars($review['listing_name']) ?> <i class="fas fa-external-link-alt"
                          ></i>
                      </a>
                    </td>
                    <td>
                      <div class="review-stars">
                        <div class="review-stars-row">
                          <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="<?= $s <= $review['rating'] ? 'fas' : 'far' ?> fa-star"
                              class="<?= $s <= $review['rating'] ? 'star-filled' : 'star-empty' ?>"></i>
                          <?php endfor; ?>
                        </div>
                        <span class="review-rating-val"><?= $review['rating'] ?></span>
                      </div>
                    </td>
                    <td>
                      <div
                        class="review-comment"
                        title="<?= htmlspecialchars($review['comment']) ?>">
                        <?= htmlspecialchars($review['comment']) ?>
                      </div>
                    </td>
                    <td class="td-small" style="white-space:nowrap;">
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
          <div class="empty-state">
            <i class="fas fa-star empty-icon-lg"></i>
            <div class="empty-title">No reviews yet</div>
            <div class="empty-body">Visitor reviews will appear here once submitted.</div>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>


  <?php require_once 'scripts.php'; ?>
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
        <div class="toast-inner">
            <i class="fas fa-star"></i>
            <div>
                <div class="toast-title">${title}</div>
                <div class="toast-body">${message}</div>
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