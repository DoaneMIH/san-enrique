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
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* ── Base ────────────────────────────────────────────── */
    body { overflow: hidden; background: #0d2b1e; }

    #fullMap { height: 100vh; width: 100%; }

    /* ── Sidebar ─────────────────────────────────────────── */
    .map-sidebar {
      position: fixed;
      top: 0; left: 0; bottom: 0;
      width: 320px;
      background: rgba(13,43,30,0.97);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      z-index: 200;
      display: flex;
      flex-direction: column;
      box-shadow: 4px 0 32px rgba(0,0,0,0.45);
      border-right: 1px solid rgba(82,183,136,0.18);
      transition: transform 0.3s ease;
    }

    .map-sidebar.collapsed { transform: translateX(-280px); }

    /* Sidebar header */
    .map-sidebar-header {
      background: linear-gradient(135deg, rgba(27,67,50,0.98), rgba(13,43,30,0.98));
      padding: 1.25rem 1.5rem;
      flex-shrink: 0;
      border-bottom: 1px solid rgba(82,183,136,0.20);
    }

    /* Sidebar search */
    .map-sidebar-search {
      padding: 1rem;
      border-bottom: 1px solid rgba(82,183,136,0.12);
      flex-shrink: 0;
      background: rgba(8,22,12,0.4);
    }

    .map-search-input {
      width: 100%;
      background: rgba(255,255,255,0.07);
      border: 1.5px solid rgba(82,183,136,0.25);
      border-radius: 10px;
      padding: 0.6rem 1rem;
      font-size: 0.85rem;
      font-family: 'Nunito', sans-serif;
      outline: none;
      color: #fff;
      transition: var(--transition);
    }
    .map-search-input::placeholder { color: rgba(255,255,255,0.38); }
    .map-search-input:focus {
      border-color: rgba(82,183,136,0.6);
      background: rgba(255,255,255,0.11);
      box-shadow: 0 0 0 3px rgba(82,183,136,0.12);
    }

    /* Listings list */
    .map-listings-list {
      flex: 1;
      overflow-y: auto;
      padding: 0.5rem;
      scrollbar-width: thin;
      scrollbar-color: rgba(82,183,136,0.3) transparent;
    }
    .map-listings-list::-webkit-scrollbar { width: 4px; }
    .map-listings-list::-webkit-scrollbar-thumb {
      background: rgba(82,183,136,0.3);
      border-radius: 4px;
    }

    /* Listing item */
    .map-listing-item {
      display: flex;
      gap: 10px;
      padding: 0.75rem;
      border-radius: 12px;
      cursor: pointer;
      transition: var(--transition);
      border: 1.5px solid transparent;
      margin-bottom: 4px;
      align-items: flex-start;
    }
    .map-listing-item:hover {
      background: rgba(82,183,136,0.12);
      border-color: rgba(82,183,136,0.35);
    }
    .map-listing-item.active {
      background: rgba(82,183,136,0.18);
      border-color: rgba(82,183,136,0.55);
    }

    .map-listing-thumb {
      width: 52px; height: 42px;
      border-radius: 8px;
      object-fit: cover;
      flex-shrink: 0;
      border: 1px solid rgba(82,183,136,0.2);
    }

    .map-listing-name {
      font-weight: 700;
      font-size: 0.85rem;
      color: #fff;
      line-height: 1.3;
      margin-bottom: 2px;
    }

    .map-listing-cat {
      font-size: 0.73rem;
      color: rgba(255,255,255,0.55);
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* View Details link */
    .map-listing-item a {
      color: #52b788 !important;
      font-size: 0.72rem;
      font-weight: 700;
      margin-top: 3px;
      display: inline-block;
      transition: var(--transition);
    }
    .map-listing-item a:hover { color: #95d5b2 !important; }

    /* Address text */
    .map-listing-item > div > div[style*="0.72rem"] {
      color: rgba(255,255,255,0.42) !important;
    }

    /* ── Sidebar toggle button ────────────────────────────── */
    .sidebar-toggle {
      position: fixed;
      left: 320px; top: 50%;
      transform: translateY(-50%);
      width: 24px; height: 56px;
      background: rgba(13,43,30,0.97);
      border: none;
      border-radius: 0 10px 10px 0;
      box-shadow: 4px 0 16px rgba(0,0,0,0.35);
      border-right: 1px solid rgba(82,183,136,0.20);
      border-top: 1px solid rgba(82,183,136,0.20);
      border-bottom: 1px solid rgba(82,183,136,0.20);
      cursor: pointer;
      z-index: 201;
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(82,183,136,0.85);
      transition: left 0.3s ease;
      font-size: 0.7rem;
    }
    .sidebar-toggle:hover { color: #fff; background: rgba(27,67,50,0.99); }
    .sidebar-toggle.collapsed { left: 40px; }

    /* ── Top bar ─────────────────────────────────────────── */
    .map-topbar {
      position: fixed;
      top: 0; left: 320px; right: 0;
      background: rgba(13,43,30,0.97);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      z-index: 100;
      padding: 0.75rem 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 20px rgba(0,0,0,0.35);
      border-bottom: 1px solid rgba(82,183,136,0.18);
      transition: left 0.3s ease;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .map-topbar.expanded { left: 40px; }

    /* Home button inside topbar */
    .map-topbar a[href="index.php"] {
      background: linear-gradient(135deg, #1b4332, #40916c) !important;
      color: white !important;
      border-radius: 8px !important;
      padding: 5px 14px !important;
      font-size: 0.82rem !important;
      font-weight: 700 !important;
      text-decoration: none !important;
      display: flex !important;
      align-items: center !important;
      gap: 5px !important;
      border: 1px solid rgba(82,183,136,0.3) !important;
      transition: var(--transition) !important;
    }
    .map-topbar a[href="index.php"]:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    /* Topbar title */
    .map-topbar > div:first-child > div:last-child {
      color: rgba(255,255,255,0.85) !important;
    }

    /* Location count */
    .map-topbar > div:last-child {
      color: rgba(255,255,255,0.45) !important;
      font-size: 0.8rem;
    }

    /* ── Category filter pills ───────────────────────────── */
    .cat-filter-wrap { display: flex; gap: 0.4rem; flex-wrap: wrap; }

    .cat-pill {
      padding: 4px 12px;
      border-radius: 100px;
      border: 1.5px solid rgba(82,183,136,0.25);
      background: rgba(255,255,255,0.06);
      color: rgba(255,255,255,0.68);
      font-size: 0.78rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      transition: var(--transition);
      backdrop-filter: blur(8px);
    }
    .cat-pill:hover {
      background: rgba(82,183,136,0.22);
      border-color: rgba(82,183,136,0.55);
      color: #fff;
    }
    .cat-pill.active {
      background: linear-gradient(135deg, #1b4332, #40916c);
      border-color: rgba(82,183,136,0.6);
      color: #fff;
      box-shadow: 0 3px 12px rgba(27,67,50,0.45);
    }
    .cat-pill.active i,
    .cat-pill:hover i { color: #fff !important; }

    /* ── Mobile ──────────────────────────────────────────── */
    @media (max-width: 767px) {
      .map-sidebar {
        width: 100%; height: 50vh;
        top: auto; bottom: 0; left: 0;
        transform: translateY(calc(100% - 60px));
        border-right: none;
        border-top: 1px solid rgba(82,183,136,0.22);
      }
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
      <a href="index.php"
        style="background:var(--primary);color:white;border-radius:8px;padding:5px 12px;font-size:0.82rem;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:5px;">
        <i class="fas fa-home"></i> Home
      </a>
      <div style="font-family:'Playfair Display',serif;font-size:0.9rem;color:var(--primary);font-weight:700;">
        Interactive Map
      </div>
    </div>
    <div class="cat-filter-wrap">
      <span class="cat-pill active" data-cat="all"><i class="fas fa-map-marker-alt" style="color:var(--accent);"></i>
        All</span>
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
          <div style="font-family:'Playfair Display',serif;font-weight:700;color:white;font-size:0.95rem;">San Enrique
          </div>
          <div style="font-size:0.68rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.08em;">
            Tourism Destinations</div>
        </div>
      </div>
    </div>
    <div class="map-sidebar-search">
      <input type="text" id="sidebarSearch" class="map-search-input" placeholder="Search destinations...">
    </div>
    <div class="map-listings-list" id="listingsList">
      <?php foreach ($mapListings as $listing): ?>
        <div class="map-listing-item" data-lat="<?= $listing['latitude'] ?>" data-lng="<?= $listing['longitude'] ?>"
          data-id="<?= $listing['id'] ?>" data-slug="<?= htmlspecialchars($listing['slug']) ?>"
          data-cat="<?= htmlspecialchars($listing['cat_slug']) ?>">
          <img
            src="<?= $listing['featured_image'] ?: 'https://placehold.co/52x42/1b4332/ffffff?text=' . urlencode(substr($listing['name'], 0, 3)) ?>"
            class="map-listing-thumb" alt="<?= htmlspecialchars($listing['name']) ?>"
            onerror="this.src='https://placehold.co/52x42/1b4332/ffffff?text=?'">
          <div>
            <div class="map-listing-name"><?= htmlspecialchars($listing['name']) ?></div>
            <div class="map-listing-cat">
              <i class="<?= htmlspecialchars($listing['icon']) ?>"
                style="color:<?= htmlspecialchars($listing['color']) ?>;"></i>
              <?= htmlspecialchars($listing['category_name']) ?>
            </div>
            <?php if ($listing['address']): ?>
              <div style="font-size:0.72rem;color:var(--gray-500);margin-top:2px;">
                <?= htmlspecialchars(substr($listing['address'], 0, 45)) ?></div>
            <?php endif; ?>
            <a href="listing.php?slug=<?= urlencode($listing['slug']) ?>"
              style="font-size:0.72rem;color:var(--primary-mid);font-weight:700;margin-top:3px;display:inline-block;">View
              Details →</a>
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
  <script src="assets/js/main.js"></script>

  <script>
    const mapListings = <?= json_encode($mapListings) ?>;

    // Override initMap to target #fullMap instead of #interactiveMap
    function initMap() {
      const sanEnrique = { lat: 10.9178, lng: 122.8845 };
      markers = [];
      map = new google.maps.Map(document.getElementById('fullMap'), {
        center: sanEnrique,
        zoom: 13,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        styles: mapStyles,
        gestureHandling: 'cooperative'
      });
      infoWindow = new google.maps.InfoWindow();
      if (typeof mapListings !== 'undefined') {
        mapListings.forEach(function (listing) { addMarker(listing); });
      }
    }

    // Sidebar toggle
    $('#sidebarToggle').on('click', function () {
      const sidebar = $('#mapSidebar');
      const topbar = $('#mapTopbar');
      const toggle = $('#sidebarToggle');
      const icon = $('#toggleIcon');
      const collapsed = sidebar.hasClass('collapsed');
      sidebar.toggleClass('collapsed');
      topbar.toggleClass('expanded', !collapsed);
      toggle.toggleClass('collapsed', !collapsed);
      icon.toggleClass('fa-chevron-left', collapsed).toggleClass('fa-chevron-right', !collapsed);
    });

    // Sidebar search
    $('#sidebarSearch').on('input', function () {
      const query = $(this).val().toLowerCase();
      $('.map-listing-item').each(function () {
        const name = $(this).find('.map-listing-name').text().toLowerCase();
        $(this).toggle(name.includes(query));
      });
    });

    // Click listing in sidebar
    $(document).on('click', '.map-listing-item', function () {
      const lat = parseFloat($(this).data('lat'));
      const lng = parseFloat($(this).data('lng'));
      const id = $(this).data('id');
      if (lat && lng && typeof map !== 'undefined') {
        map.panTo({ lat, lng });
        map.setZoom(16);
        // Trigger marker click
        const m = markers.find(mk => mk.listingId === id);
        if (m) google.maps.event.trigger(m, 'click');
      }
      $('.map-listing-item').removeClass('active');
      $(this).addClass('active');
    });

    // Category filter pills
    $('.cat-pill').on('click', function () {
      $('.cat-pill').removeClass('active');
      $(this).addClass('active');
      const cat = $(this).data('cat');
      filterMapMarkers(cat);
      // Filter sidebar list
      if (cat === 'all') {
        $('.map-listing-item').show();
      } else {
        $('.map-listing-item').each(function () {
          $(this).toggle($(this).data('cat') === cat);
        });
      }
    });

    // Override addMarker to store listing id on marker
    const _origAddMarker = addMarker;
    function addMarker(listing) {
      if (!listing.latitude || !listing.longitude) return;
      const color = listing.color || '#2d6a4f';
      const svgIcon = {
        path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
        fillColor: color, fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2, scale: 1.8,
        anchor: new google.maps.Point(12, 22)
      };
      const marker = new google.maps.Marker({
        position: { lat: parseFloat(listing.latitude), lng: parseFloat(listing.longitude) },
        map: map, title: listing.name, icon: svgIcon,
        animation: google.maps.Animation.DROP,
        category: listing.cat_slug, listingId: listing.id
      });
      const img = listing.featured_image || `https://placehold.co/280x120/1b4332/ffffff?text=${encodeURIComponent(listing.name)}`;
      const infoContent = `
    <div style="font-family:'Nunito',sans-serif;max-width:250px;padding:4px;">
      <img src="${img}" style="width:100%;height:100px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
      <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:${color};margin-bottom:3px;">
        <i class="${listing.icon}" style="margin-right:3px;"></i> ${listing.category_name}
      </div>
      <div style="font-family:'Playfair Display',serif;font-size:0.92rem;font-weight:700;color:#1b4332;margin-bottom:4px;">${listing.name}</div>
      <div style="font-size:0.78rem;color:#5a7564;margin-bottom:8px;">${listing.address || ''}</div>
      <a href="listing.php?slug=${listing.slug}" style="display:inline-block;background:#1b4332;color:white;font-size:0.75rem;font-weight:700;padding:5px 12px;border-radius:6px;text-decoration:none;">View Details &rarr;</a>
      &nbsp;
      <a href="https://www.google.com/maps/dir/?api=1&destination=${listing.latitude},${listing.longitude}" target="_blank" style="display:inline-block;background:#2d6a4f;color:white;font-size:0.75rem;font-weight:700;padding:5px 12px;border-radius:6px;text-decoration:none;">Directions</a>
    </div>`;
      marker.addListener('click', function () {
        infoWindow.setContent(infoContent);
        infoWindow.open(map, marker);
        map.panTo(marker.getPosition());
        // Highlight sidebar item
        $('.map-listing-item').removeClass('active');
        $(`.map-listing-item[data-id="${listing.id}"]`).addClass('active');
      });
      markers.push(marker);
    }
  </script>

  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAPS_API_KEY ?>&callback=initMap&libraries=places"></script>
</body>

</html>