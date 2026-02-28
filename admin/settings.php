<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();

$message = '';
$error = '';
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'change_password') {
  $currentPass = $_POST['current_password'] ?? '';
  $newPass = $_POST['new_password'] ?? '';
  $confirmPass = $_POST['confirm_password'] ?? '';

  $r = $db->query("SELECT password FROM admins WHERE id=" . (int) $admin['id']);
  $row = $r->fetch_assoc();

  if (!password_verify($currentPass, $row['password'])) {
    $error = 'Current password is incorrect.';
  } elseif (strlen($newPass) < 6) {
    $error = 'New password must be at least 6 characters.';
  } elseif ($newPass !== $confirmPass) {
    $error = 'New passwords do not match.';
  } else {
    $hashed = password_hash($newPass, PASSWORD_DEFAULT);
    $db->query("UPDATE admins SET password='$hashed' WHERE id=" . (int) $admin['id']);
    $message = 'Password changed successfully!';
  }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_action'] === 'update_profile') {
  $fullName = sanitize($_POST['full_name'] ?? '');
  $email = sanitize($_POST['email'] ?? '');

  if (empty($fullName) || empty($email)) {
    $error = 'Name and email are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email address.';
  } else {
    $db->query("UPDATE admins SET full_name='$fullName', email='$email' WHERE id=" . (int) $admin['id']);
    $_SESSION['admin_name'] = $fullName;
    $message = 'Profile updated successfully!';
    $admin = currentAdmin();
    $admin['name'] = $fullName;
  }
}

// Fetch current admin info
$adminInfo = $db->query("SELECT * FROM admins WHERE id=" . (int) $admin['id'])->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - Admin Panel</title>
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
      <a href="reviews.php" class="admin-nav-link"><i class="fas fa-star"></i> Reviews</a>
      <div class="nav-section-label">System</div>
      <a href="../index.php" target="_blank" class="admin-nav-link"><i class="fas fa-external-link-alt"></i> View
        Website</a>
      <a href="settings.php" class="admin-nav-link active"><i class="fas fa-cog"></i> Settings</a>
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
        <span class="topbar-title">Settings</span>
        <div class="topbar-breadcrumb">Manage your admin account</div>
      </div>
    </div>

    <div class="admin-main">
      <?php if ($message): ?>
        <div
          style="background:#dcfce7;color:#15803d;border-radius:10px;padding:12px 16px;font-size:0.87rem;font-weight:600;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div
          style="background:#fee2e2;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:0.87rem;font-weight:600;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- PROFILE SETTINGS -->
        <div class="col-lg-6">
          <div class="admin-form-card">
            <div class="admin-form-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <div
                  style="width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem;">
                  <i class="fas fa-user"></i>
                </div>
                <div>
                  <div style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);">Profile
                    Information</div>
                  <div style="font-size:0.75rem;color:var(--text-muted);">Update your account details</div>
                </div>
              </div>
            </div>
            <form method="POST" action="">
              <input type="hidden" name="form_action" value="update_profile">
              <div class="admin-form-body" style="display:flex;flex-direction:column;gap:1rem;">
                <!-- Avatar Preview -->
                <div style="text-align:center;padding:1rem;background:var(--content-bg);border-radius:12px;">
                  <div
                    style="width:72px;height:72px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:20px;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:2rem;margin:0 auto 0.75rem;">
                    <?= strtoupper(substr($adminInfo['full_name'], 0, 1)) ?>
                  </div>
                  <div
                    style="font-family:'Playfair Display',serif;font-size:1rem;color:var(--primary);font-weight:700;">
                    <?= htmlspecialchars($adminInfo['full_name']) ?></div>
                  <div style="font-size:0.78rem;color:var(--text-muted);"><?= ucfirst($adminInfo['role']) ?></div>
                </div>

                <div>
                  <label class="admin-label">Full Name *</label>
                  <input type="text" name="full_name" class="admin-input" required
                    value="<?= htmlspecialchars($adminInfo['full_name']) ?>">
                </div>
                <div>
                  <label class="admin-label">Email Address *</label>
                  <input type="email" name="email" class="admin-input" required
                    value="<?= htmlspecialchars($adminInfo['email']) ?>">
                </div>
                <div>
                  <label class="admin-label">Username</label>
                  <input type="text" class="admin-input" value="<?= htmlspecialchars($adminInfo['username']) ?>"
                    disabled style="background:var(--content-bg);cursor:not-allowed;color:var(--text-muted);">
                  <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;"><i
                      class="fas fa-info-circle me-1"></i>Username cannot be changed.</div>
                </div>
                <div>
                  <label class="admin-label">Role</label>
                  <input type="text" class="admin-input" value="<?= ucfirst($adminInfo['role']) ?>" disabled
                    style="background:var(--content-bg);cursor:not-allowed;color:var(--text-muted);">
                </div>
              </div>
              <div class="admin-form-footer">
                <button type="submit" class="btn-admin-primary">
                  <i class="fas fa-save me-1"></i> Update Profile
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- CHANGE PASSWORD -->
        <div class="col-lg-6">
          <div class="admin-form-card">
            <div class="admin-form-header">
              <div style="display:flex;align-items:center;gap:10px;">
                <div
                  style="width:40px;height:40px;background:linear-gradient(135deg,#b7791f,#d4a017);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem;">
                  <i class="fas fa-lock"></i>
                </div>
                <div>
                  <div style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);">Change
                    Password</div>
                  <div style="font-size:0.75rem;color:var(--text-muted);">Update your login password</div>
                </div>
              </div>
            </div>
            <form method="POST" action="" id="passwordForm">
              <input type="hidden" name="form_action" value="change_password">
              <div class="admin-form-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                  <label class="admin-label">Current Password *</label>
                  <div style="position:relative;">
                    <input type="password" name="current_password" class="admin-input" id="cp" required
                      placeholder="Enter current password">
                    <button type="button" onclick="togglePw('cp','eye1')"
                      style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);">
                      <i class="fas fa-eye" id="eye1"></i>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="admin-label">New Password *</label>
                  <div style="position:relative;">
                    <input type="password" name="new_password" class="admin-input" id="np" required
                      placeholder="At least 6 characters" oninput="checkStrength(this.value)">
                    <button type="button" onclick="togglePw('np','eye2')"
                      style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);">
                      <i class="fas fa-eye" id="eye2"></i>
                    </button>
                  </div>
                  <!-- Password Strength -->
                  <div style="margin-top:6px;">
                    <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden;">
                      <div id="strengthBar" style="height:100%;width:0;border-radius:2px;transition:all 0.3s;"></div>
                    </div>
                    <div id="strengthLabel" style="font-size:0.72rem;color:var(--text-muted);margin-top:3px;"></div>
                  </div>
                </div>
                <div>
                  <label class="admin-label">Confirm New Password *</label>
                  <div style="position:relative;">
                    <input type="password" name="confirm_password" class="admin-input" id="cnp" required
                      placeholder="Repeat new password">
                    <button type="button" onclick="togglePw('cnp','eye3')"
                      style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);">
                      <i class="fas fa-eye" id="eye3"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="admin-form-footer">
                <button type="submit" class="btn-admin-primary">
                  <i class="fas fa-key me-1"></i> Change Password
                </button>
              </div>
            </form>
          </div>

          <!-- SYSTEM INFO -->
          <div class="admin-form-card" style="margin-top:1.25rem;">
            <div class="admin-form-header">
              <div style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);">
                <i class="fas fa-info-circle me-2" style="color:var(--accent);"></i>System Information
              </div>
            </div>
            <div class="admin-form-body">
              <?php
              $sysItems = [
                ['PHP Version', phpversion()],
                ['Server', $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'],
                ['Database', 'MySQL/MariaDB'],
                ['Site URL', BASE_URL],
                ['Admin Since', date('F j, Y', strtotime($adminInfo['created_at']))],
              ];
              foreach ($sysItems as [$label, $value]):
                ?>
                <div
                  style="display:flex;justify-content:space-between;padding:0.65rem 0;border-bottom:1px solid var(--border);font-size:0.86rem;">
                  <span style="color:var(--text-muted);font-weight:600;"><?= $label ?></span>
                  <span style="color:var(--primary);font-family:monospace;"><?= htmlspecialchars($value) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- QUICK LINKS -->
        <div class="col-12">
          <div class="admin-form-card">
            <div class="admin-form-header">
              <div style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);">
                <i class="fas fa-bolt me-2" style="color:var(--gold);"></i>Quick Setup Guide
              </div>
            </div>
            <div class="admin-form-body">
              <div class="row g-3">
                <?php
                $steps = [
                  ['1', 'Set Your API Key', 'Open includes/db.php and replace YOUR_GOOGLE_MAPS_API_KEY with your actual Google Maps API key.', 'fas fa-key', '#d4a017'],
                  ['2', 'Import Database', 'Run the database.sql file in your MySQL/phpMyAdmin to create all tables and sample data.', 'fas fa-database', '#1b6fb0'],
                  ['3', 'Add Listings', 'Go to Listings → Add Listing. Fill in details and click on the map to set GPS coordinates.', 'fas fa-map-marker-alt', '#2d6a4f'],
                  ['4', 'Customize Categories', 'Visit Categories to add or edit destination types with custom icons and colors.', 'fas fa-th-large', '#52b788'],
                ];
                foreach ($steps as [$num, $title, $desc, $icon, $color]):
                  ?>
                  <div class="col-md-6 col-lg-3">
                    <div
                      style="background:var(--content-bg);border-radius:12px;padding:1.25rem;border:1px solid var(--border);height:100%;">
                      <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.75rem;">
                        <div
                          style="width:36px;height:36px;background:<?= $color ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:0.9rem;flex-shrink:0;">
                          <i class="<?= $icon ?>"></i>
                        </div>
                        <div>
                          <div
                            style="font-size:0.68rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">
                            Step <?= $num ?></div>
                          <div style="font-weight:700;color:var(--primary);font-size:0.88rem;"><?= $title ?></div>
                        </div>
                      </div>
                      <p style="font-size:0.82rem;color:var(--text-muted);line-height:1.6;margin:0;"><?= $desc ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
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

    function togglePw(inputId, iconId) {
      const inp = document.getElementById(inputId);
      const ico = document.getElementById(iconId);
      if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        inp.type = 'password';
        ico.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }

    function checkStrength(val) {
      const bar = document.getElementById('strengthBar');
      const lbl = document.getElementById('strengthLabel');
      if (!val) { bar.style.width = '0'; lbl.textContent = ''; return; }
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      const levels = [
        { pct: '20%', color: '#e63946', label: 'Very Weak' },
        { pct: '40%', color: '#e07b39', label: 'Weak' },
        { pct: '60%', color: '#d4a017', label: 'Fair' },
        { pct: '80%', color: '#52b788', label: 'Good' },
        { pct: '100%', color: '#1b4332', label: 'Strong' },
      ];
      const l = levels[Math.min(score - 1, 4)];
      bar.style.width = l.pct;
      bar.style.background = l.color;
      lbl.textContent = l.label;
      lbl.style.color = l.color;
    }

    // Password match validation
    document.getElementById('passwordForm').addEventListener('submit', function (e) {
      const np = document.getElementById('np').value;
      const cnp = document.getElementById('cnp').value;
      if (np !== cnp) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'Mismatch', text: 'New passwords do not match.', confirmButtonColor: '#1b4332' });
      }
    });
  </script>
</body>

</html>