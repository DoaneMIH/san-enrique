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
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings — Admin Panel</title>
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
        <button class="d-lg-none" onclick="toggleSidebar()"
          class="topbar-menu-btn"><i
            class="fas fa-bars"></i></button>
        <div class="topbar-title">Settings</div>
        <div class="topbar-breadcrumb">Account Settings &amp; System Info</div>
      </div>
    </div>

    <div class="admin-main">
      <?php if ($message): ?>
        <div
          class="admin-alert success">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div
          class="admin-alert error">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">

        <!-- PROFILE SETTINGS -->
        <div class="col-lg-6">
          <div class="admin-form-card">
            <div class="admin-form-header">
              <div class="form-section-header">
                <div
                  class="form-section-icon stat-icon-green">
                  <i class="fas fa-user"></i>
                </div>
                <div>
                  <div class="form-section-title">Profile
                    Information</div>
                  <div class="form-section-sub">Update your account details</div>
                </div>
              </div>
            </div>
            <form method="POST" action="">
              <input type="hidden" name="form_action" value="update_profile">
              <div class="admin-form-body-flex">
                <!-- Avatar Preview -->
                <div class="profile-avatar-block">
                  <div
                    class="profile-avatar-icon">
                    <?= strtoupper(substr($adminInfo['full_name'], 0, 1)) ?>
                  </div>
                  <div
                    class="profile-avatar-name">
                    <?= htmlspecialchars($adminInfo['full_name']) ?></div>
                  <div class="profile-avatar-role"><?= ucfirst($adminInfo['role']) ?></div>
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
                    disabled class="admin-input admin-input-disabled">
                  <div class="input-hint-sm"><i
                      class="fas fa-info-circle me-1"></i>Username cannot be changed.</div>
                </div>
                <div>
                  <label class="admin-label">Role</label>
                  <input type="text" class="admin-input" value="<?= ucfirst($adminInfo['role']) ?>" disabled
                    class="admin-input admin-input-disabled">
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
              <div class="form-section-header">
                <div
                  class="form-section-icon form-header-icon-amber">
                  <i class="fas fa-lock"></i>
                </div>
                <div>
                  <div class="form-section-title">Change
                    Password</div>
                  <div class="form-section-sub">Update your login password</div>
                </div>
              </div>
            </div>
            <form method="POST" action="" id="passwordForm">
              <input type="hidden" name="form_action" value="change_password">
              <div class="admin-form-body-flex">
                <div>
                  <label class="admin-label">Current Password *</label>
                  <div class="pw-field-wrap">
                    <input type="password" name="current_password" class="admin-input" id="cp" required
                      placeholder="Enter current password">
                    <button type="button" onclick="togglePw('cp','eye1')" class="input-toggle-btn">
                      <i class="fas fa-eye" id="eye1"></i>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="admin-label">New Password *</label>
                  <div class="pw-field-wrap">
                    <input type="password" name="new_password" class="admin-input" id="np" required
                      placeholder="At least 6 characters" oninput="checkStrength(this.value)">
                    <button type="button" onclick="togglePw('np','eye2')" class="input-toggle-btn">
                      <i class="fas fa-eye" id="eye2"></i>
                    </button>
                  </div>
                  <!-- Password Strength -->
                  <div class="pw-strength-wrap">
                    <div class="pw-strength-bar-bg">
                      <div id="strengthBar" class="pw-strength-bar"></div>
                    </div>
                    <div id="strengthLabel" class="pw-strength-label"></div>
                  </div>
                </div>
                <div>
                  <label class="admin-label">Confirm New Password *</label>
                  <div class="pw-field-wrap">
                    <input type="password" name="confirm_password" class="admin-input" id="cnp" required
                      placeholder="Repeat new password">
                    <button type="button" onclick="togglePw('cnp','eye3')" class="input-toggle-btn">
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
          <div class="admin-form-card mt-3">
            <div class="admin-form-header">
              <div class="form-header-title"><i class="fas fa-info-circle me-2"></i>System Information
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
                  class="sys-info-row sys-info-row-sm">
                  <span class="sys-info-label"><?= $label ?></span>
                  <span class="sys-info-value"><?= htmlspecialchars($value) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- QUICK LINKS -->
        <div class="col-12">
          <div class="admin-form-card">
            <div class="admin-form-header">
              <div class="form-header-title"><i class="fas fa-bolt me-2 section-icon-gold"></i>Quick Setup Guide
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
                      class="setup-guide-item h-100">
                      <div class="setup-guide-header">
                        <div
                          style="width:36px;height:36px;background:<?= $color ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:0.9rem;flex-shrink:0;">
                          <i class="<?= $icon ?>"></i>
                        </div>
                        <div>
                          <div
                            class="setup-guide-step-label">
                            Step <?= $num ?></div>
                          <div class="setup-guide-title"><?= $title ?></div>
                        </div>
                      </div>
                      <p class="setup-guide-desc"><?= $desc ?></p>
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

  <?php require_once 'scripts.php'; ?>
<script>
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