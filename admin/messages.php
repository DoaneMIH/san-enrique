<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();

// Delete message
if (isset($_GET['delete'])) {
  $delId = (int) $_GET['delete'];
  $db->query("DELETE FROM messages WHERE id = $delId");
  header('Location: messages.php');
  exit;
}

$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];

// View specific message
$viewMsg = null;
if (isset($_GET['view'])) {
  $viewId = (int) $_GET['view'];
  $r = $db->query("SELECT * FROM messages WHERE id = $viewId");
  $viewMsg = $r ? $r->fetch_assoc() : null;
  if ($viewMsg && !$viewMsg['is_read']) {
    $db->query("UPDATE messages SET is_read = 1 WHERE id = $viewId");
    $unreadMsgs = max(0, $unreadMsgs - 1);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages - Admin Panel</title>
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
      <a href="messages.php" class="admin-nav-link active">
        <i class="fas fa-envelope"></i> Messages
        <?php if ($unreadMsgs > 0): ?><span class="sidebar-badge"><?= $unreadMsgs ?></span><?php endif; ?>
      </a>
      <a href="reviews.php" class="admin-nav-link"><i class="fas fa-star"></i> Reviews</a>
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
        <span class="topbar-title">Messages</span>
        <div class="topbar-breadcrumb"><?= count($messages) ?> total · <?= $unreadMsgs ?> unread</div>
      </div>
    </div>

    <div class="admin-main">
      <div class="row g-4">

        <!-- MESSAGE LIST -->
        <div class="col-lg-<?= $viewMsg ? '5' : '12' ?>">
          <div class="admin-table-wrap">
            <div class="admin-table-header">
              <div class="admin-table-title"><i class="fas fa-inbox me-2" style="color:var(--accent);"></i>Inbox</div>
              <?php if ($unreadMsgs > 0): ?>
                <span class="status-badge active"><?= $unreadMsgs ?> Unread</span>
              <?php endif; ?>
            </div>

            <?php if ($messages): ?>
              <div style="max-height:620px;overflow-y:auto;">
                <?php foreach ($messages as $msg):
                  $isActive = $viewMsg && $viewMsg['id'] == $msg['id'];
                  $isUnread = !$msg['is_read'];
                  ?>
                  <a href="messages.php?view=<?= $msg['id'] ?>" style="display:block;text-decoration:none;">
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);
                          background:<?= $isActive ? 'var(--accent-pale)' : ($isUnread ? 'var(--content-bg)' : 'white') ?>;
                          border-left:3px solid <?= $isActive ? 'var(--accent)' : 'transparent' ?>;
                          transition:all 0.2s;"
                      onmouseover="if(!<?= $isActive ? 'true' : 'false' ?>)this.style.background='var(--gray-50)'"
                      onmouseout="this.style.background='<?= $isActive ? 'var(--accent-pale)' : ($isUnread ? 'var(--content-bg)' : 'white') ?>'">
                      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;">
                        <div style="flex:1;min-width:0;">
                          <div style="display:flex;align-items:center;gap:6px;">
                            <div
                              style="width:30px;height:30px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;font-weight:700;flex-shrink:0;">
                              <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                            </div>
                            <div style="min-width:0;">
                              <div
                                style="font-weight:<?= $isUnread ? '700' : '600' ?>;font-size:0.86rem;color:var(--primary);display:flex;align-items:center;gap:5px;">
                                <?= htmlspecialchars($msg['name']) ?>
                                <?php if ($isUnread): ?><span
                                    style="width:7px;height:7px;background:var(--accent);border-radius:50%;display:inline-block;flex-shrink:0;"></span><?php endif; ?>
                              </div>
                              <div
                                style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($msg['email']) ?></div>
                            </div>
                          </div>
                          <div style="font-size:0.82rem;color:var(--primary-mid);margin-top:5px;font-weight:600;">
                            <?= htmlspecialchars(substr($msg['subject'], 0, 45)) ?>    <?= strlen($msg['subject']) > 45 ? '...' : '' ?>
                          </div>
                          <div style="font-size:0.78rem;color:var(--text-muted);margin-top:2px;">
                            <?= htmlspecialchars(substr($msg['message'], 0, 60)) ?>...</div>
                        </div>
                        <div
                          style="text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                          <div style="font-size:0.7rem;color:var(--gray-500);white-space:nowrap;">
                            <?= date('M j', strtotime($msg['created_at'])) ?></div>
                          <button onclick="event.preventDefault();event.stopPropagation();deleteMsg(<?= $msg['id'] ?>)"
                            style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:0.78rem;padding:2px 4px;border-radius:4px;"
                            title="Delete">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="text-align:center;padding:4rem 2rem;color:var(--text-muted);">
                <i class="fas fa-inbox"
                  style="font-size:3.5rem;color:var(--gray-200);display:block;margin-bottom:1rem;"></i>
                <div style="font-weight:600;color:var(--primary);margin-bottom:0.25rem;">No messages yet</div>
                <div style="font-size:0.85rem;">Messages from the contact form will appear here.</div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- MESSAGE DETAIL -->
        <?php if ($viewMsg): ?>
          <div class="col-lg-7">
            <div class="admin-form-card" style="height:100%;">
              <div class="admin-form-header"
                style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;">
                <div>
                  <div
                    style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);margin-bottom:3px;">
                    <?= htmlspecialchars($viewMsg['subject']) ?>
                  </div>
                  <div style="font-size:0.78rem;color:var(--text-muted);">
                    <i class="fas fa-clock me-1"></i><?= date('F j, Y \a\t g:i A', strtotime($viewMsg['created_at'])) ?>
                  </div>
                </div>
                <button onclick="deleteMsg(<?= $viewMsg['id'] ?>)" class="btn-admin-danger" style="flex-shrink:0;">
                  <i class="fas fa-trash me-1"></i> Delete
                </button>
              </div>

              <div class="admin-form-body">
                <!-- Sender Info Card -->
                <div
                  style="display:flex;gap:14px;align-items:center;padding:1.1rem 1.25rem;background:var(--content-bg);border-radius:12px;margin-bottom:1.5rem;border:1px solid var(--border);">
                  <div
                    style="width:50px;height:50px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:14px;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.3rem;flex-shrink:0;">
                    <?= strtoupper(substr($viewMsg['name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div style="font-weight:700;color:var(--primary);font-size:1rem;">
                      <?= htmlspecialchars($viewMsg['name']) ?></div>
                    <div style="font-size:0.83rem;color:var(--text-muted);margin-top:1px;">
                      <?= htmlspecialchars($viewMsg['email']) ?></div>
                  </div>
                  <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>" target="_blank"
                    style="margin-left:auto;background:var(--primary);color:white;border-radius:9px;padding:7px 14px;font-size:0.8rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:5px;flex-shrink:0;">
                    <i class="fas fa-envelope"></i> Email
                  </a>
                </div>

                <!-- Message Body -->
                <div
                  style="background:white;border:1px solid var(--border);border-radius:12px;padding:1.5rem;line-height:1.9;color:var(--text);font-size:0.92rem;white-space:pre-wrap;min-height:180px;">
                  <?= htmlspecialchars($viewMsg['message']) ?>
                </div>

                <!-- Reply Button -->
                <div
                  style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;flex-wrap:wrap;">
                  <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject']) ?>&body=<?= urlencode("Dear " . $viewMsg['name'] . ",\n\nThank you for your message.\n\n") ?>"
                    class="btn-admin-primary">
                    <i class="fas fa-reply me-1"></i> Reply via Email
                  </a>
                  <a href="messages.php" class="btn-admin-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Inbox
                  </a>
                </div>
              </div>
            </div>
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
    function deleteMsg(id) {
      Swal.fire({
        title: 'Delete Message?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b8c73',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
      }).then(result => {
        if (result.isConfirmed) window.location.href = 'messages.php?delete=' + id;
      });
    }

    // Live-update polling for messages
    const ADMIN_POLL_INTERVAL = 15000; // 15 seconds
    let messagesTimestamp = null;
    let messageCount = <?= count($messages) ?>;

    function pollForNewMessages() {
      fetch('/san-enrique/api/admin-updates.php?page=messages', { cache: 'no-store' })
        .then(res => res.json())
        .then(data => {
          if (!data.success) return;

          // Check if there are new messages
          if (data.count > messageCount) {
            showAdminToast('New message received!', 'A visitor just sent a message.');
            messageCount = data.count;

            // Refresh page after 2 seconds
            setTimeout(() => {
              location.reload();
            }, 2000);
          }

          // Update unread badge if changed
          const badge = document.querySelector('.sidebar-badge');
          if (badge && parseInt(badge.textContent) !== data.unreadCount) {
            if (data.unreadCount > 0) {
              badge.textContent = data.unreadCount;
            } else {
              badge.remove();
            }
          }

          messagesTimestamp = data.timestamp;
        })
        .catch(err => console.log('Poll error:', err));
    }

    function showAdminToast(title, message) {
      const toast = document.createElement('div');
      toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #52b788, #2d6a4f);
        color: white;
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
            <i class="fas fa-check-circle"></i>
            <div>
                <div style="font-weight: 700;">${title}</div>
                <div style="font-size: 0.85rem; opacity: 0.9;">${message}</div>
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
      setInterval(pollForNewMessages, ADMIN_POLL_INTERVAL);
    } else {
      window.addEventListener('load', () => {
        setInterval(pollForNewMessages, ADMIN_POLL_INTERVAL);
      });
    }
  </script>
</body>

</html>