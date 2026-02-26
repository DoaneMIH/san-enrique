<?php
require_once '../includes/functions.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = sanitize($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (login($user, $pass)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="login-page">
  <!-- Left Side: Login Form -->
  <div class="login-left">
    <div class="login-card">
      <div class="login-logo">
        <div class="logo-icon">🌿</div>
        <h2 class="login-title">Admin Portal</h2>
        <p class="login-sub">San Enrique Tourism Hub</p>
      </div>

      <?php if ($error): ?>
      <div style="background:#fee2e2;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:0.87rem;font-weight:600;margin-bottom:1.25rem;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div style="margin-bottom:1.1rem;">
          <label class="admin-label">Username or Email</label>
          <div style="position:relative;">
            <i class="fas fa-user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.85rem;"></i>
            <input type="text" name="username" class="admin-input" style="padding-left:36px;" placeholder="admin" required autofocus>
          </div>
        </div>

        <div style="margin-bottom:1.5rem;">
          <label class="admin-label">Password</label>
          <div style="position:relative;">
            <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.85rem;"></i>
            <input type="password" name="password" id="passInput" class="admin-input" style="padding-left:36px;padding-right:44px;" placeholder="••••••••" required>
            <button type="button" onclick="togglePass()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:0.85rem;">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-admin-primary w-100" style="width:100%;justify-content:center;padding:0.85rem;font-size:0.95rem;">
          <i class="fas fa-sign-in-alt me-2"></i> Sign In to Admin Panel
        </button>

        <div style="text-align:center;margin-top:1.5rem;">
          <a href="../index.php" style="color:var(--text-muted);font-size:0.82rem;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
            <i class="fas fa-globe"></i> Back to Tourism Hub
          </a>
        </div>
      </form>

      <div style="margin-top:1.5rem;padding:1rem;background:var(--content-bg);border-radius:10px;font-size:0.78rem;color:var(--text-muted);border:1px dashed var(--border);">
        <strong>Demo Credentials:</strong><br>
        Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>password</code>
      </div>
    </div>
  </div>

  <!-- Right Side: Municipal Hall Tourism Showcase -->
  <div class="login-right">
    <!-- Decorative floating shapes -->
    <div class="login-right-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <!-- Main image showcase -->
    <div class="login-showcase">
      <div class="showcase-image-wrapper">
        <img src="../assets/images/San_Enrique_Municipal_Hall.jpg" alt="San Enrique Municipal Hall" class="showcase-image">
        <div class="showcase-image-border"></div>
      </div>

      <div class="showcase-content">
        <div class="showcase-badge">
          <i class="fas fa-landmark"></i> Heart of Governance
        </div>
        <h2 class="showcase-title">San Enrique<br>Municipal Hall</h2>
        <p class="showcase-desc">The seat of local governance and community service in the Municipality of San Enrique, Iloilo — proudly serving the community.</p>

        <div class="showcase-features">
          <div class="showcase-feature">
            <div class="feature-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="feature-text">
              <span class="feature-label">Location</span>
              <span class="feature-value">San Enrique, Iloilo</span>
            </div>
          </div>
          <div class="showcase-feature">
            <div class="feature-icon"><i class="fas fa-mountain-sun"></i></div>
            <div class="feature-text">
              <span class="feature-label">Province</span>
              <span class="feature-value">Iloilo, Western Visayas</span>
            </div>
          </div>
          <div class="showcase-feature">
            <div class="feature-icon"><i class="fas fa-compass"></i></div>
            <div class="feature-text">
              <span class="feature-label">Tourism</span>
              <span class="feature-value">Nature &amp; Heritage</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom tagline -->
    <div class="showcase-tagline">
      <span class="tagline-line"></span>
      <span class="tagline-text"><i class="fas fa-leaf"></i> Discover the Beauty of San Enrique</span>
      <span class="tagline-line"></span>
    </div>
  </div>
</div>

<script>
function togglePass() {
  const input = document.getElementById('passInput');
  const icon = document.getElementById('eyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>
</body>
</html>
