<?php
require_once 'includes/functions.php';
$categories = getCategories();
$mapListings = getAllListingsForMap();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Interactive Map - <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  body { overflow: hidden; }
  #fullMap { height: 100vh; width: 100%; }
  .map-sidebar {
    position: fixed; top: 0; left: 0; bottom: 0;
    width: 320px; background: white; z-index: 200;
    display: flex; flex-direction: column;
    box-shadow: 4px 0 20px rgba(27,67,50,0.12);
    transition: transform 0.3s ease;
  }
  .map-sidebar.collapsed { transform: translateX(-280px); }
  .map-sidebar-header {
    background: var(--primary);
    padding: 1.25rem 1.5rem;
    flex-shrink: 0;
  }
  .map-sidebar-search {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-100);
    flex-shrink: 0;
  }
  .map-search-input {
    width: 100%;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    font-family: 'Nunito', sans-serif;
    outline: none;
    color: var(--text);
  }
  .map-search-input:focus { border-color: var(--accent); }
  .map-listings-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
  }
  .map-listing-item {
    display: flex;
    gap: 10px;
    padding: 0.75rem;
    border-radius: 10px;
    cursor: pointer;
    transition: var(--transition);
    border: 1.5px solid transparent;
    margin-bottom: 4px;
    align-items: flex-start;
  }
  .map-listing-item:hover, .map-listing-item.active {
    background: var(--gray-50);
    border-color: var(--accent);
  }
  .map-listing-thumb {
    width: 52px; height: 42px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
  }
  .map-listing-name {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--primary);
    line-height: 1.3;
    margin-bottom: 2px;
  }
  .map-listing-cat {
    font-size: 0.73rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .sidebar-toggle {
    position: fixed;
    left: 320px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px; height: 48px;
    background: white;
    border: none;
    border-radius: 0 8px 8px 0;
    box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    cursor: pointer;
    z-index: 201;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-mid);
    transition: left 0.3s ease;
    font-size: 0.7rem;
  }
  .sidebar-toggle.collapsed { left: 40px; }
  .map-topbar {
    position: fixed; top: 0; left: 320px; right: 0;
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(20px);
    z-index: 100;
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 10px rgba(27,67,50,0.08);
    transition: left 0.3s ease;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .map-topbar.expanded { left: 40px; }
  .cat-filter-wrap { display: flex; gap: 0.4rem; flex-wrap: wrap; }
  .cat-pill {
    padding: 4px 12px;
    border-radius: 100px;
    border: 1.5px solid var(--gray-200);
    background: white;
    color: var(--text-muted);
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: var(--transition);
  }
  .cat-pill.active, .cat-pill:hover { border-color: var(--primary); background: var(--primary); color: white; }
  .cat-pill.active i, .cat-pill:hover i { color: white !important; }
  @media (max-width: 767px) {
    .map-sidebar { width: 100%; height: 50vh; top: auto; bottom: 0; left: 0; transform: translateY(calc(100% - 60px)); }
    .map-sidebar.open { transform: translateY(0); }
    #fullMap { height: 60vh; margin-top: 50px; }
    .map-topbar { left: 0 !important; top: 50px; }
    .sidebar-toggle { display: none; }
  }
</style>
</head>
<body>

<!-- FULL MAP -->
<div id="fullMap"></div>

<!-- CATEGORY TOPBAR -->
<div class="map-topbar" id="mapTopbar">
  <div class="d-flex align-items-center gap-3">
    <a href="index.php" style="background:var(--primary);color:white;border-radius:8px;padding:5px 12px;font-size:0.82rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:5px;">
      <i class="fas fa-home"></i> Home
    </a>
    <div style="font-family:'Playfair Display',serif;font-size:0.9rem;color:var(--primary);font-weight:700;">
      Interactive Map
    </div>
  </div>
  <div class="cat-filter-wrap">
    <span class="cat-pill active" data-cat="all"><i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> All</span>
    <?php foreach ($categories as $cat): ?>
    <span class="cat-pill" data-cat="<?= htmlspecialchars($cat['slug']) ?>">
      <i class="<?= htmlspecialchars($cat['icon']) ?>" style="color:<?= htmlspecialchars($cat['color']) ?>;"></i>
      <?= htmlspecialchars($cat['name']) ?>
    </span>
    <?php endforeach; ?>
  </div>
  <div style="font-size:0.8rem;color:var(--text-muted);"><?= count($mapListings) ?> locations</div>
</div>

<!-- SIDEBAR -->
<div class="map-sidebar" id="mapSidebar">
  <div class="map-sidebar-header">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:1.5rem;">🌿</span>
      <div>
        <div style="font-family:'Playfair Display',serif;font-weight:700;color:white;font-size:0.95rem;">San Enrique</div>
        <div style="font-size:0.68rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.08em;">Tourism Destinations</div>
      </div>
    </div>
  </div>
  <div class="map-sidebar-search">
    <input type="text" id="sidebarSearch" class="map-search-input" placeholder="Search destinations...">
  </div>
  <div class="map-listings-list" id="listingsList">
    <?php foreach ($mapListings as $listing): ?>
    <div class="map-listing-item"
         data-lat="<?= $listing['latitude'] ?>"
         data-lng="<?= $listing['longitude'] ?>"
         data-id="<?= $listing['id'] ?>"
         data-slug="<?= htmlspecialchars($listing['slug']) ?>"
         data-cat="<?= htmlspecialchars($listing['cat_slug']) ?>">
      <img src="<?= htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 52, 42)) ?>"
           class="map-listing-thumb"
           alt="<?= htmlspecialchars($listing['name']) ?>"
           onerror="this.src='https://placehold.co/52x42/1b4332/ffffff?text=?'">
      <div>
        <div class="map-listing-name"><?= htmlspecialchars($listing['name']) ?></div>
        <div class="map-listing-cat">
          <i class="<?= htmlspecialchars($listing['icon']) ?>" style="color:<?= htmlspecialchars($listing['color']) ?>;"></i>
          <?= htmlspecialchars($listing['category_name']) ?>
        </div>
        <?php if ($listing['address']): ?>
        <div style="font-size:0.72rem;color:var(--gray-500);margin-top:2px;"><?= htmlspecialchars(substr($listing['address'],0,45)) ?></div>
        <?php endif; ?>
        <a href="listing.php?slug=<?= urlencode($listing['slug']) ?>" style="font-size:0.72rem;color:var(--primary-mid);font-weight:700;margin-top:3px;display:inline-block;">View Details →</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Sidebar Toggle -->
<button class="sidebar-toggle" id="sidebarToggle">
  <i class="fas fa-chevron-left" id="toggleIcon"></i>
</button>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Map listings data must be defined BEFORE main.js loads -->
<script>
const mapListings = <?= json_encode($mapListings) ?>;

// Tell main.js initMap to use the #fullMap div instead of #interactiveMap
const fullMapPage = true;
</script>

<script src="assets/js/main.js"></script>

<script>
// ---- Sidebar toggle ----
$('#sidebarToggle').on('click', function() {
  const collapsed = $('#mapSidebar').hasClass('collapsed');
  $('#mapSidebar').toggleClass('collapsed');
  $('#mapTopbar').toggleClass('expanded', !collapsed);
  $('#sidebarToggle').toggleClass('collapsed', !collapsed);
  $('#toggleIcon').toggleClass('fa-chevron-left', collapsed).toggleClass('fa-chevron-right', !collapsed);
  // Trigger resize so map tiles fill correctly after sidebar slides
  if (typeof map !== 'undefined') {
    setTimeout(() => google.maps.event.trigger(map, 'resize'), 350);
  }
});

// ---- Sidebar search ----
$('#sidebarSearch').on('input', function() {
  const query = $(this).val().toLowerCase();
  $('.map-listing-item').each(function() {
    $(this).toggle($(this).find('.map-listing-name').text().toLowerCase().includes(query));
  });
});

// ---- Click listing in sidebar → pan map ----
$(document).on('click', '.map-listing-item', function() {
  const lat = parseFloat($(this).data('lat'));
  const lng = parseFloat($(this).data('lng'));
  const id  = parseInt($(this).data('id'));
  if (lat && lng && typeof map !== 'undefined') {
    map.panTo({ lat, lng });
    map.setZoom(16);
    const m = markers.find(mk => mk.listingId === id);
    if (m) google.maps.event.trigger(m, 'click');
  }
  $('.map-listing-item').removeClass('active');
  $(this).addClass('active');
});

// ---- Category filter pills ----
$('.cat-pill').on('click', function() {
  $('.cat-pill').removeClass('active');
  $(this).addClass('active');
  const cat = $(this).data('cat');
  filterMapMarkers(cat);
  if (cat === 'all') {
    $('.map-listing-item').show();
  } else {
    $('.map-listing-item').each(function() {
      $(this).toggle($(this).data('cat') === cat);
    });
  }
});
</script>

<!-- Google Maps - defer only, no async to preserve callback order -->
<script
  src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap&libraries=places"
  defer>
</script>
</body>
</html>