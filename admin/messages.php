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
  <?php require_once 'sidebar.php'; ?>

  <div class="admin-content">
    <div class="admin-topbar">
      <div>
        <button class="d-lg-none" onclick="toggleSidebar()"
          class="topbar-menu-btn"><i
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
              <div class="admin-table-title"><i class="fas fa-inbox me-2 section-icon-accent"></i>Inbox</div>
              <?php if ($unreadMsgs > 0): ?>
                <span class="status-badge active"><?= $unreadMsgs ?> Unread</span>
              <?php endif; ?>
            </div>

            <?php if ($messages): ?>
              <div class="msg-list-scroll">
                <?php foreach ($messages as $msg):
                  $isActive = $viewMsg && $viewMsg['id'] == $msg['id'];
                  $isUnread = !$msg['is_read'];
                  ?>
                  <a href="messages.php?view=<?= $msg['id'] ?>" class="msg-list-item">
                    <div class="msg-list-row" >
                      <div class="msg-row-top">
                        <div class="msg-row-left">
                          <div class="msg-sender-wrap">
                            <div
                              class="msg-avatar">
                              <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                            </div>
                            <div class="msg-sender-info">
                              <div
                                class="msg-sender-name <?= $isUnread ? 'unread' : 'read' ?>">
                                <?= htmlspecialchars($msg['name']) ?>
                                <?php if ($isUnread): ?><span
                                    class="msg-unread-dot"></span><?php endif; ?>
                              </div>
                              <div
                                class="msg-email">
                                <?= htmlspecialchars($msg['email']) ?></div>
                            </div>
                          </div>
                          <div class="msg-subject">
                            <?= htmlspecialchars(substr($msg['subject'], 0, 45)) ?>    <?= strlen($msg['subject']) > 45 ? '...' : '' ?>
                          </div>
                          <div class="msg-preview">
                            <?= htmlspecialchars(substr($msg['message'], 0, 60)) ?>...</div>
                        </div>
                        <div
                          class="msg-row-right">
                          <div class="msg-date">
                            <?= date('M j', strtotime($msg['created_at'])) ?></div>
                          <button onclick="event.preventDefault();event.stopPropagation();deleteMsg(<?= $msg['id'] ?>)"
                            class="btn-inline-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-inbox"
                  class="empty-icon-lg"></i>
                <div class="empty-title">No messages yet</div>
                <div class="empty-body">Messages from the contact form will appear here.</div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- MESSAGE DETAIL -->
        <?php if ($viewMsg): ?>
          <div class="col-lg-7">
            <div class="admin-form-card h-100">
              <div class="admin-form-header admin-form-header-full">
                <div>
                  <div
                    class="form-header-title">
                    <?= htmlspecialchars($viewMsg['subject']) ?>
                  </div>
                  <div class="msg-detail-header-meta">
                    <i class="fas fa-clock me-1"></i><?= date('F j, Y \a\t g:i A', strtotime($viewMsg['created_at'])) ?>
                  </div>
                </div>
                <button onclick="deleteMsg(<?= $viewMsg['id'] ?>)" class="btn-admin-danger flex-shrink-0">
                  <i class="fas fa-trash me-1"></i> Delete
                </button>
              </div>

              <div class="admin-form-body">
                <!-- Sender Info Card -->
                <div
                  class="msg-sender-card">
                  <div
                    class="msg-sender-card-avatar">
                    <?= strtoupper(substr($viewMsg['name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="msg-sender-card-name">
                      <?= htmlspecialchars($viewMsg['name']) ?></div>
                    <div class="msg-sender-card-email">
                      <?= htmlspecialchars($viewMsg['email']) ?></div>
                  </div>
                  <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>" target="_blank"
                    class="btn-reply">
                    <i class="fas fa-envelope"></i> Email
                  </a>
                </div>

                <!-- Message Body -->
                <div
                  class="msg-body">
                  <?= htmlspecialchars($viewMsg['message']) ?>
                </div>

                <!-- Reply Button -->
                <div
                  class="msg-actions">
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
        <div class="toast-inner">
            <i class="fas fa-check-circle"></i>
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
      setInterval(pollForNewMessages, ADMIN_POLL_INTERVAL);
    } else {
      window.addEventListener('load', () => {
        setInterval(pollForNewMessages, ADMIN_POLL_INTERVAL);
      });
    }
  </script>
</body>

</html>