<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();

// Only superadmin can access this page
if ($admin['role'] !== 'superadmin') {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

$db = getDB();
$message = '';
$error = '';
$editAdmin = null;
$action = $_GET['action'] ?? 'list';
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];

// ── HANDLE POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $fullName   = sanitize($_POST['full_name'] ?? '');
    $username   = sanitize($_POST['username'] ?? '');
    $email      = sanitize($_POST['email'] ?? '');
    $role       = sanitize($_POST['role'] ?? 'admin');
    $isActive   = isset($_POST['is_active']) ? 1 : 0;

    // Validate role
    if (!in_array($role, ['superadmin', 'admin'])) $role = 'admin';

    if ($formAction === 'add') {
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $password2) {
            $error = 'Passwords do not match.';
        } else {
            // Check unique username/email
            $check = $db->query("SELECT id FROM admins WHERE username='$username' OR email='$email' LIMIT 1");
            if ($check && $check->num_rows > 0) {
                $error = 'Username or email already exists.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO admins (username, email, password, full_name, role) VALUES ('$username','$email','$hashed','$fullName','$role')";
                if ($db->query($sql)) {
                    $message = "Admin account \"$fullName\" created successfully!";
                    $action = 'list';
                } else {
                    $error = 'Failed to create account: ' . $db->error;
                }
            }
        }
    } elseif ($formAction === 'edit') {
        $id = (int)$_POST['admin_id'];
        // Prevent editing own role/status in a harmful way
        if ($id === (int)$admin['id'] && $isActive === 0) {
            $error = 'You cannot deactivate your own account.';
        } else {
            $sql = "UPDATE admins SET full_name='$fullName', username='$username', email='$email', role='$role' WHERE id=$id";
            if ($db->query($sql)) {
                // Handle password change if provided
                $newPass = $_POST['new_password'] ?? '';
                if (!empty($newPass)) {
                    if (strlen($newPass) < 6) {
                        $error = 'New password must be at least 6 characters.';
                    } else {
                        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                        $db->query("UPDATE admins SET password='$hashed' WHERE id=$id");
                    }
                }
                if (!$error) {
                    $message = 'Account updated successfully!';
                    $action = 'list';
                }
            } else {
                $error = 'Failed to update: ' . $db->error;
            }
        }
    }
}

// ── TOGGLE ACTIVE ───────────────────────────────────────
if (isset($_GET['toggle'])) {
    $togId = (int)$_GET['toggle'];
    if ($togId === (int)$admin['id']) {
        $error = 'You cannot deactivate your own account.';
    } else {
        $row = $db->query("SELECT * FROM admins WHERE id=$togId")->fetch_assoc();
        if ($row) {
            // We use the admins table — if no is_active column, add deactivated via role prefix workaround
            // Check if is_active column exists
            $cols = $db->query("SHOW COLUMNS FROM admins LIKE 'is_active'");
            if ($cols->num_rows === 0) {
                $db->query("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) DEFAULT 1");
            }
            $newActive = $row['is_active'] == 1 ? 0 : 1;
            $db->query("UPDATE admins SET is_active=$newActive WHERE id=$togId");
            $message = $newActive ? 'Account activated.' : 'Account deactivated.';
        }
    }
}

// ── DELETE ───────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId === (int)$admin['id']) {
        $error = 'You cannot delete your own account.';
    } else {
        $db->query("DELETE FROM admins WHERE id=$delId");
        $message = 'Admin account deleted.';
    }
}

// ── FETCH EDIT ───────────────────────────────────────────
if ($action === 'edit' && isset($_GET['id'])) {
    $editAdmin = $db->query("SELECT * FROM admins WHERE id=".(int)$_GET['id'])->fetch_assoc();
    if (!$editAdmin) { $action = 'list'; }
}

// ── ENSURE is_active COLUMN EXISTS ───────────────────────
$cols = $db->query("SHOW COLUMNS FROM admins LIKE 'is_active'");
if ($cols->num_rows === 0) {
    $db->query("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER role");
    $db->query("UPDATE admins SET is_active=1");
}

// Ensure last_login column for display
$colsLL = $db->query("SHOW COLUMNS FROM admins LIKE 'last_login'");
if ($colsLL->num_rows === 0) {
    $db->query("ALTER TABLE admins ADD COLUMN last_login DATETIME NULL AFTER is_active");
}

$admins = $db->query("SELECT * FROM admins ORDER BY role DESC, full_name ASC")->fetch_all(MYSQLI_ASSOC);
$totalAdmins     = count($admins);
$superadminCount = count(array_filter($admins, fn($a) => $a['role'] === 'superadmin'));
$adminCount      = $totalAdmins - $superadminCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="site-base" content="<?= rtrim(BASE_URL, '/') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Accounts - <?= SITE_NAME ?></title>
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
        <div class="topbar-title">Admin Accounts</div>
        <div class="topbar-breadcrumb">Superadmin · User Management</div>
      </div>
    </div>
  </div>

  <div class="admin-main">
    <?php if ($message): ?>
      <div class="admin-alert success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="admin-alert error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- ── ADD / EDIT FORM ── -->
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="admin-form-card">
          <div class="admin-form-header">
            <div class="form-header-icon">
              <i class="fas fa-<?= $action==='add'?'user-plus':'user-edit' ?>"></i>
            </div>
            <div>
              <div class="form-header-title">
                <?= $action==='add'?'Create New Admin Account':'Edit Admin Account' ?>
              </div>
              <div class="form-section-sub">
                <?= $action==='add'?'Fill in the details below to add a new admin.':'Update the admin account details.' ?>
              </div>
            </div>
          </div>
          <form method="POST" action="">
            <input type="hidden" name="form_action" value="<?= $action ?>">
            <?php if ($action==='edit'): ?>
              <input type="hidden" name="admin_id" value="<?= $editAdmin['id'] ?>">
            <?php endif; ?>

            <div class="admin-form-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="admin-label">Full Name <span class="required">*</span></label>
                  <input type="text" name="full_name" class="admin-input"
                    value="<?= htmlspecialchars($editAdmin['full_name'] ?? '') ?>"
                    placeholder="e.g. Maria Santos" required>
                </div>
                <div class="col-md-6">
                  <label class="admin-label">Username <span class="required">*</span></label>
                  <input type="text" name="username" class="admin-input"
                    value="<?= htmlspecialchars($editAdmin['username'] ?? '') ?>"
                    placeholder="e.g. msantos" required>
                </div>
                <div class="col-md-6">
                  <label class="admin-label">Email Address <span class="required">*</span></label>
                  <input type="email" name="email" class="admin-input"
                    value="<?= htmlspecialchars($editAdmin['email'] ?? '') ?>"
                    placeholder="e.g. msantos@sanenrique.gov.ph" required>
                </div>
                <div class="col-md-6">
                  <label class="admin-label">Role <span class="required">*</span></label>
                  <select name="role" class="admin-input">
                    <option value="admin" <?= (($editAdmin['role']??'admin')==='admin')?'selected':'' ?>>Admin — Standard access</option>
                    <option value="superadmin" <?= (($editAdmin['role']??'')==='superadmin')?'selected':'' ?>>Superadmin — Full access</option>
                  </select>
                </div>

                <?php if ($action === 'add'): ?>
                <div class="col-md-6">
                  <label class="admin-label">Password <span class="required">*</span></label>
                  <input type="password" name="password" class="admin-input" placeholder="Min. 6 characters" required>
                </div>
                <div class="col-md-6">
                  <label class="admin-label">Confirm Password <span class="required">*</span></label>
                  <input type="password" name="password2" class="admin-input" placeholder="Repeat password" required>
                </div>
                <?php else: ?>
                <div class="col-12">
                  <div class="permissions-box">
                    <div class="permissions-box-title">
                      <i class="fas fa-key me-1"></i> Change Password (leave blank to keep current)
                    </div>
                    <div class="row g-2">
                      <div class="col-md-6">
                        <input type="password" name="new_password" class="admin-input" placeholder="New password">
                      </div>
                    </div>
                  </div>
                </div>
                <?php endif; ?>
              </div>

              <!-- Role Info Box -->
              <div class="role-info-box">
                <div class="role-info-box-title"><i class="fas fa-info-circle me-1"></i> Role Permissions</div>
                <div class="role-info-box-text">
                  <strong>Admin:</strong> Manage listings, categories, events, messages, reviews.<br>
                  <strong>Superadmin:</strong> All admin permissions + manage admin accounts, full system access.
                </div>
              </div>
            </div>

            <div class="admin-form-footer">
              <a href="admin_accounts.php" class="btn-admin-secondary">Cancel</a>
              <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> <?= $action==='add'?'Create Account':'Save Changes' ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- ── STATS ROW ── -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-green">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-value"><?= $totalAdmins ?></div>
          <div class="stat-name">Total Admins</div>
          <i class="fas fa-user-friends stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-amber">
            <i class="fas fa-crown"></i>
          </div>
          <div class="stat-value"><?= $superadminCount ?></div>
          <div class="stat-name">Superadmins</div>
          <i class="fas fa-shield-alt stat-bg-icon"></i>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="dash-stat-card">
          <div class="stat-icon stat-icon-blue">
            <i class="fas fa-user-shield"></i>
          </div>
          <div class="stat-value"><?= $adminCount ?></div>
          <div class="stat-name">Standard Admins</div>
          <i class="fas fa-user-cog stat-bg-icon"></i>
        </div>
      </div>
    </div>

    <!-- ── ACCOUNTS TABLE ── -->
    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title"><i class="fas fa-users-cog me-2 section-icon-accent"></i>Admin Accounts</div>
        <a href="admin_accounts.php?action=add" class="btn-admin-primary btn-sm">
          <i class="fas fa-user-plus"></i> Add Account
        </a>
      </div>
      <div class="table-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Admin</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admins as $acc): ?>
            <tr>
              <td>
                <div class="account-row">
                  <div class="account-avatar <?= $acc['role']==='superadmin' ? 'account-avatar-superadmin' : 'account-avatar-admin' ?>">
                    <?= strtoupper(substr($acc['full_name'],0,1)) ?>
                  </div>
                  <div>
                    <div class="account-name">
                      <?= htmlspecialchars($acc['full_name']) ?>
                      <?php if ($acc['id'] == $admin['id']): ?>
                        <span class="account-you-badge">You</span>
                      <?php endif; ?>
                    </div>
                    <div class="account-id">#<?= $acc['id'] ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="code-pill">
                  <?= htmlspecialchars($acc['username']) ?>
                </span>
              </td>
              <td class="td-small">
                <?= htmlspecialchars($acc['email']) ?>
              </td>
              <td>
                <span class="role-badge <?= $acc['role'] ?>">
                  <?php if ($acc['role']==='superadmin'): ?>
                    <i class="fas fa-crown"></i>
                  <?php else: ?>
                    <i class="fas fa-user-shield"></i>
                  <?php endif; ?>
                  <?= ucfirst($acc['role']) ?>
                </span>
              </td>
              <td>
                <?php $isActive = isset($acc['is_active']) ? $acc['is_active'] : 1; ?>
                <span class="status-badge <?= $isActive?'active':'inactive' ?>">
                  <?= $isActive?'Active':'Inactive' ?>
                </span>
              </td>
              <td class="role-info-box-text">
                <?= date('M j, Y', strtotime($acc['created_at'])) ?>
              </td>
              <td>
                <div class="table-actions">
                  <a href="admin_accounts.php?action=edit&id=<?= $acc['id'] ?>" class="btn-admin-edit btn-sm">
                    <i class="fas fa-pencil-alt"></i>
                  </a>
                  <?php if ($acc['id'] != $admin['id']): ?>
                    <a href="admin_accounts.php?toggle=<?= $acc['id'] ?>" class="btn-admin-secondary btn-sm"
                       title="<?= $isActive?'Deactivate':'Activate' ?>">
                      <i class="fas fa-<?= $isActive?'ban':'check' ?>"></i>
                    </a>
                    <button onclick="confirmDelete('admin_accounts.php?delete=<?= $acc['id'] ?>','<?= htmlspecialchars($acc['full_name'],ENT_QUOTES) ?>')"
                       class="btn-admin-danger btn-sm">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  <?php else: ?>
                    <span class="your-account-label">Your account</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PERMISSIONS GUIDE -->
    <div class="row g-4 mt-1">
      <div class="col-md-6">
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title"><i class="fas fa-crown me-2 icon-amber"></i>Superadmin Permissions</div>
          </div>
          <div class="perm-list">
            <?php $superPerms = ['Manage Admin Accounts (create, edit, delete)','Activate / Deactivate Admins','Full content management','Manage Listings, Categories, Events','View and reply to Messages','Moderate Reviews','Access all Settings']; ?>
            <?php foreach ($superPerms as $p): ?>
              <div class="perm-row">
                <i class="fas fa-check-circle perm-check"></i>
                <?= $p ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="admin-table-wrap">
          <div class="admin-table-header">
            <div class="admin-table-title"><i class="fas fa-user-shield me-2 icon-green"></i>Admin Permissions</div>
          </div>
          <div class="perm-list">
            <?php $adminPerms = ['Manage Listings (add, edit, delete)','Manage Categories','Manage Events','View and reply to Messages','Moderate Reviews','Access own Settings']; ?>
            <?php $adminNot = ['Cannot manage Admin Accounts','Cannot activate/deactivate Admins']; ?>
            <?php foreach ($adminPerms as $p): ?>
              <div class="perm-row">
                <i class="fas fa-check-circle perm-check"></i> <?= $p ?>
              </div>
            <?php endforeach; ?>
            <?php foreach ($adminNot as $p): ?>
              <div class="perm-row-muted">
                <i class="fas fa-times-circle perm-cross"></i> <?= $p ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'scripts.php'; ?>
</body>
</html>