<?php
// Admin Sidebar Include
// Usage: require_once 'sidebar.php'; (after $admin and $unreadMsgs are set)
$currentPage  = basename($_SERVER['PHP_SELF']);
$isSuperAdmin = ($admin['role'] === 'superadmin');

// Determine if we're on any listings-related page
// $onListings = in_array($currentPage, ['listings.php', 'listing_view.php']);
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <!-- <img src="../assets/images/logo-tourism.svg" alt="San Enrique" class="sidebar-brand-logo-img"> -->
     <div class="brand-logo">🌿</div>
    <div>
      <div class="brand-text">San Enrique</div>
      <div class="brand-sub">Tourism Hub Admin</div>
    </div>
  </div>

  <nav class="sidebar-nav">

    <!-- ── MAIN ─────────────────────────────── -->
    <div class="nav-section-label">Main</div>
    <a href="dashboard.php" class="admin-nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
      <i class="fas fa-home"></i> Dashboard
    </a>

    <!-- ── WEBSITE MANAGEMENT ────────────────── -->
    <div class="nav-section-label">Website Management</div>

    <!-- Listings parent row -->
    <a href="listings.php" class="admin-nav-link <?= $currentPage === 'listings.php' ? 'active' : '' ?>">
      <i class="fas fa-map-marker-alt"></i> Listings
    </a>

    <!-- Sub-items: always visible, slightly indented -->
    <!-- <div class="nav-sub-group">
      <a href="listings.php"
         class="admin-nav-link nav-sub <?= ($currentPage === 'listings.php' && (!isset($_GET['action']) || $_GET['action'] === 'list')) ? 'active' : '' ?>">
        <i class="fas fa-list"></i> All Listings
      </a>
      <a href="listings.php?action=add"
         class="admin-nav-link nav-sub <?= ($currentPage === 'listings.php' && isset($_GET['action']) && $_GET['action'] === 'add') ? 'active' : '' ?>">
        <i class="fas fa-plus"></i> Add Listing
      </a>
    </div> -->

    <a href="categories.php" class="admin-nav-link <?= $currentPage === 'categories.php' ? 'active' : '' ?>">
      <i class="fas fa-th-large"></i> Categories
    </a>
    <a href="events.php" class="admin-nav-link <?= $currentPage === 'events.php' ? 'active' : '' ?>">
      <i class="fas fa-calendar-alt"></i> Events
    </a>

    <!-- ── COMMUNICATION ─────────────────────── -->
    <div class="nav-section-label">Communication</div>
    <a href="messages.php" class="admin-nav-link <?= $currentPage === 'messages.php' ? 'active' : '' ?>">

      <i class="fas fa-envelope"></i> Messages
      <?php if (!empty($unreadMsgs) && $unreadMsgs > 0): ?>
        <span class="sidebar-badge"><?= (int)$unreadMsgs ?></span>
      <?php endif; ?>
    </a>
    
    <a href="reviews.php" class="admin-nav-link <?= $currentPage === 'reviews.php' ? 'active' : '' ?>">
      <i class="fas fa-star"></i> Reviews
    </a>

    <!-- ── USER MANAGEMENT (Superadmin only) ─── -->
    <?php if ($isSuperAdmin): ?>
    <div class="nav-section-label">User Management</div>
    <a href="admin_accounts.php" class="admin-nav-link <?= $currentPage === 'admin_accounts.php' ? 'active' : '' ?>">
      <i class="fas fa-users-cog"></i> Admin Accounts
    </a>
    <?php endif; ?>

    <!-- ── SYSTEM ─────────────────────────────── -->
    <div class="nav-section-label">System</div>
    <!-- <a href="../index.php" class="admin-nav-link" target="_blank">
      <i class="fas fa-external-link-alt"></i> View Website
    </a>
    <a href="../map.php" class="admin-nav-link" target="_blank">
      <i class="fas fa-map"></i> View Map
    </a> -->
    <a href="settings.php" class="admin-nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
      <i class="fas fa-cog"></i> Settings
    </a>
    <a href="documentation.php" class="admin-nav-link <?= $currentPage === 'documentation.php' ? 'active' : '' ?>">
      <i class="fas fa-book"></i> Documentation
    </a>

  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
      <div class="sidebar-user-info">
        <div class="user-name"><?= htmlspecialchars($admin['name']) ?></div>
        <div class="user-role"><?= ucfirst($admin['role']) ?></div>
      </div>
      <a href="logout.php" class="btn-logout" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
  </div>
</aside>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="closeSidebar()"></div>