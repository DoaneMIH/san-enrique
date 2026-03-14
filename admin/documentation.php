<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$unreadMsgs = getDB()->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Documentation — Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php require_once 'sidebar.php'; ?>

<div class="admin-content">
  <div class="admin-topbar">
    <div class="topbar-left">
      <button class="d-lg-none topbar-icon-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <div>
        <div class="topbar-title">Documentation</div>
        <div class="topbar-breadcrumb">Admin Panel Guide</div>
      </div>
    </div>
    <div class="topbar-actions">
      <a href="dashboard.php" class="btn-admin-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
  </div>

  <div class="admin-main">
    <div class="row g-4">
      <!-- TABLE OF CONTENTS -->
      <div class="col-lg-3">
        <div class="admin-table-wrap doc-toc">
          <div class="admin-table-header"><div class="admin-table-title">Contents</div></div>
          <div class="doc-toc-inner">
            <?php $sections = [
              ['id'=>'overview','icon'=>'fas fa-home','label'=>'Overview'],
              ['id'=>'navigation','icon'=>'fas fa-compass','label'=>'Navigation Guide'],
              ['id'=>'listings','icon'=>'fas fa-map-marker-alt','label'=>'Managing Listings'],
              ['id'=>'categories','icon'=>'fas fa-th-large','label'=>'Categories'],
              ['id'=>'events','icon'=>'fas fa-calendar-alt','label'=>'Events'],
              ['id'=>'messages','icon'=>'fas fa-envelope','label'=>'Messages'],
              ['id'=>'reviews','icon'=>'fas fa-star','label'=>'Reviews'],
              ['id'=>'roles','icon'=>'fas fa-users-shield','label'=>'Role System'],
              ['id'=>'admin-accounts','icon'=>'fas fa-users-cog','label'=>'Admin Accounts'],
              ['id'=>'settings','icon'=>'fas fa-cog','label'=>'Settings'],
              ['id'=>'future','icon'=>'fas fa-rocket','label'=>'Future Improvements'],
            ]; ?>
            <?php foreach ($sections as $s): ?>
            <a href="#<?= $s['id'] ?>" class="doc-toc-link">
              <i class="<?= $s['icon'] ?>" ></i>
              <?= $s['label'] ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- CONTENT -->
      <div class="col-lg-9">
        <div class="doc-sections">

          <!-- OVERVIEW -->
          <section id="overview">
            <div class="admin-table-wrap">
              <div class="admin-table-header">
                <div class="admin-table-title"><i class="fas fa-home me-2 section-icon-accent"></i>System Overview</div>
              </div>
              <div class="doc-body">
                <p>The <strong>San Enrique Tourism Hub Admin Panel</strong> is a full-featured content management system built for the Municipality of San Enrique, Iloilo. It allows authorized administrators to manage all tourism-related content displayed on the public website.</p>
                <div class="row g-3">
                  <?php $tech = [
                    ['PHP 8+', 'fas fa-code', 'Server-side scripting'],
                    ['MySQL / MariaDB', 'fas fa-database', 'Relational database'],
                    ['Bootstrap 5', 'fab fa-bootstrap', 'Responsive layout'],
                    ['Font Awesome 6', 'fas fa-icons', 'Icon library'],
                    ['Google Maps API', 'fas fa-map', 'Map picker for listings'],
                    ['SweetAlert2', 'fas fa-bell', 'Confirmation dialogs'],
                  ]; ?>
                  <?php foreach ($tech as [$name, $icon, $desc]): ?>
                  <div class="col-sm-6">
                    <div class="doc-feature-item">
                      <i class="<?= $icon ?> doc-feature-icon"></i>
                      <div>
                        <div class="doc-feature-name"><?= $name ?></div>
                        <div class="doc-feature-desc"><?= $desc ?></div>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </section>

          <!-- NAVIGATION -->
          <section id="navigation">
            <div class="admin-table-wrap">
              <div class="admin-table-header"><div class="admin-table-title"><i class="fas fa-compass me-2 section-icon-accent"></i>Navigation Guide</div></div>
              <div class="doc-body">
                <p>The sidebar is divided into sections for easy navigation:</p>
                <?php $navSections = [
                  ['Main', [
                    ['Dashboard', 'fas fa-home', 'Overview of all statistics, recent listings, and quick actions.'],
                  ]],
                  ['Website Management', [
                    ['Listings', 'fas fa-map-marker-alt', 'Add, edit, delete, and manage all tourism listings.'],
                    ['Categories', 'fas fa-th-large', 'Manage the listing categories (Resorts, Barangays, etc.).'],
                    ['Events', 'fas fa-calendar-alt', 'Create and manage upcoming tourism events.'],
                  ]],
                  ['Communication', [
                    ['Messages', 'fas fa-envelope', 'View and reply to contact form submissions.'],
                    ['Reviews', 'fas fa-star', 'Moderate visitor reviews on listings.'],
                  ]],
                  ['User Management (Superadmin)', [
                    ['Admin Accounts', 'fas fa-users-cog', 'Create, edit, activate/deactivate, and delete admin accounts.'],
                  ]],
                  ['System', [
                    ['View Website', 'fas fa-external-link-alt', 'Opens the public website in a new tab.'],
                    ['View Map', 'fas fa-map', 'Opens the interactive map page.'],
                    ['Settings', 'fas fa-cog', 'Update your profile and change password.'],
                  ]],
                ]; ?>
                <?php foreach ($navSections as [$sectionName, $items]): ?>
                <div class="doc-nav-section-group">
                  <div class="doc-nav-sub-label"><?= $sectionName ?></div>
                  <?php foreach ($items as [$name, $icon, $desc]): ?>
                  <div class="doc-nav-item">
                    <i class="<?= $icon ?> doc-nav-item-icon"></i>
                    <div>
                      <div class="doc-nav-item-name"><?= $name ?></div>
                      <div class="doc-feature-desc"><?= $desc ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </section>

          <!-- LISTINGS -->
          <section id="listings">
            <div class="admin-table-wrap">
              <div class="admin-table-header"><div class="admin-table-title"><i class="fas fa-map-marker-alt me-2 section-icon-accent"></i>Managing Listings</div></div>
              <div class="doc-body">
                <p>Listings are the core content of the tourism website — they represent resorts, barangays, cultural sites, restaurants, farms, and nature spots.</p>
                <?php $steps = [
                  ['Add a Listing', 'Go to Listings → Add Listing. Fill in Name, Category, Description, Address, Barangay, GPS Coordinates (use the map picker), Operating Hours, Entrance Fee, and upload a Featured Image. Mark "Featured" to show it on the homepage. Click Save.'],
                  ['Edit a Listing', 'From the Listings table, click the Edit button next to any listing. All fields can be updated. You can also add gallery photos and a video (YouTube URL or upload MP4).'],
                  ['Change Status', 'Set status to Active (visible on website), Inactive (hidden), or Pending (awaiting review).'],
                  ['Delete a Listing', 'Click the Delete button and confirm. This also removes the listing\'s uploaded images from the server.'],
                  ['Preview a Listing', 'Click the Eye icon to view the listing exactly as visitors see it (admin-preview mode).'],
                ]; ?>
                <ol class="doc-steps">
                  <?php foreach ($steps as $i => [$title,$desc]): ?>
                  <li class="doc-step">
                    <div class="doc-step-num"><?= $i+1 ?></div>
                    <div class="doc-step-body"><div class="doc-step-title"><?= $title ?></div><div class="doc-step-desc"><?= $desc ?></div></div>
                  </li>
                  <?php endforeach; ?>
                </ol>
              </div>
            </div>
          </section>

          <!-- ROLES -->
          <section id="roles">
            <div class="admin-table-wrap">
              <div class="admin-table-header"><div class="admin-table-title"><i class="fas fa-users-shield me-2 section-icon-accent"></i>Role &amp; Permission System</div></div>
              <div class="doc-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="role-card-super">
                      <div class="role-card-header">
                        <i class="fas fa-crown icon-amber"></i>
                        <span class="role-card-title-super">Superadmin</span>
                        <span class="role-badge superadmin ms-auto">Highest Level</span>
                      </div>
                      <?php $sp = ['Manage all website content','Full listings CRUD','Categories management','Events management','Messages &amp; Reviews','Create Admin accounts','Edit Admin accounts','Delete Admin accounts','Activate/Deactivate Admins','Access all Settings']; ?>
                      <?php foreach ($sp as $p): ?>
                      <div class="role-card-perm role-card-perm-super">
                        <i class="fas fa-check perm-icon-super"></i><?= $p ?>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="role-card-admin">
                      <div class="role-card-header">
                        <i class="fas fa-user-shield icon-green"></i>
                        <span class="role-card-title-admin">Admin</span>
                        <span class="role-badge admin ms-auto">Standard</span>
                      </div>
                      <?php $ap = ['Manage all website content','Full listings CRUD','Categories management','Events management','Messages &amp; Reviews','Own Settings (password, profile)']; ?>
                      <?php $an = ['Cannot manage Admin accounts','Cannot activate/deactivate Admins']; ?>
                      <?php foreach ($ap as $p): ?>
                      <div class="role-card-perm role-card-perm-admin">
                        <i class="fas fa-check perm-icon-admin"></i><?= $p ?>
                      </div>
                      <?php endforeach; ?>
                      <?php foreach ($an as $p): ?>
                      <div class="role-card-perm role-card-perm-denied">
                        <i class="fas fa-times perm-cross"></i><?= $p ?>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- ADMIN ACCOUNTS -->
          <section id="admin-accounts">
            <div class="admin-table-wrap">
              <div class="admin-table-header"><div class="admin-table-title"><i class="fas fa-users-cog me-2 section-icon-accent"></i>How to Add New Admins</div></div>
              <div class="doc-body">
                <?php if ($admin['role']==='superadmin'): ?>
                <div class="admin-alert success mb-3"><i class="fas fa-check-circle"></i> You have Superadmin access. You can manage admin accounts.</div>
                <?php else: ?>
                <div class="admin-alert warning mb-3"><i class="fas fa-lock"></i> Only Superadmins can manage admin accounts. Contact your system administrator.</div>
                <?php endif; ?>
                <ol class="doc-steps">
                  <?php $steps = [
                    ['Navigate to Admin Accounts', 'In the sidebar, under "User Management", click "Admin Accounts". (Only visible to Superadmins.)'],
                    ['Click "Add Admin"', 'Click the green "Add Admin" button in the top-right of the page.'],
                    ['Fill in the details', 'Enter Full Name, Username, Email Address, and choose a Role (Admin or Superadmin). Set a strong password.'],
                    ['Save the account', 'Click "Create Account". The new admin can now log in with their credentials.'],
                    ['Manage existing accounts', 'From the accounts table, you can Edit details, Activate/Deactivate accounts, or Delete them (except your own).'],
                  ]; ?>
                  <?php foreach ($steps as $i => [$title,$desc]): ?>
                  <li class="doc-step">
                    <div class="doc-step-num"><?= $i+1 ?></div>
                    <div class="doc-step-body"><div class="doc-step-title"><?= $title ?></div><div class="doc-step-desc"><?= $desc ?></div></div>
                  </li>
                  <?php endforeach; ?>
                </ol>
              </div>
            </div>
          </section>

          <!-- FUTURE -->
          <section id="future">
            <div class="admin-table-wrap">
              <div class="admin-table-header"><div class="admin-table-title"><i class="fas fa-rocket me-2 section-icon-accent"></i>Future Improvements</div></div>
              <div class="doc-body">
                <div class="row g-3">
                  <?php $improvements = [
                    ['Activity Log / Audit Trail', 'fas fa-history', 'Track who changed what and when — full audit log for accountability.'],
                    ['Email Notifications', 'fas fa-envelope', 'Auto-email admins when new messages or reviews are submitted.'],
                    ['Media Manager', 'fas fa-images', 'Centralized image library with bulk upload and delete.'],
                    ['Analytics Dashboard', 'fas fa-chart-bar', 'Visual charts showing listing views, popular categories, and traffic trends.'],
                    ['Two-Factor Authentication', 'fas fa-shield-alt', 'OTP-based 2FA for enhanced security on admin logins.'],
                    ['Export to PDF/Excel', 'fas fa-file-export', 'Export listings, events, and message reports to downloadable files.'],
                    ['SEO Management', 'fas fa-search', 'Per-listing meta tags, og:image, and sitemap generator.'],
                    ['Public Review Moderation', 'fas fa-star', 'Approve/reject reviews before they appear publicly.'],
                    ['Booking/Reservation System', 'fas fa-calendar-check', 'Allow tourists to book resorts directly through the website.'],
                    ['Multi-language Support', 'fas fa-language', 'Filipino/Ilonggo language option for the public website.'],
                  ]; ?>
                  <?php foreach ($improvements as [$title,$icon,$desc]): ?>
                  <div class="col-sm-6">
                    <div class="doc-nav-item h-100">
                      <div class="doc-step-num doc-step-num-lg"><i class="<?= $icon ?>"></i></div>
                      <div><div class="doc-nav-item-name"><?= $title ?></div><div class="doc-step-desc"><?= $desc ?></div></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'scripts.php'; ?>
</body>
</html>