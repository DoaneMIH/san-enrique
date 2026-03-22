<?php
/**
 * api/content.php  —  Targeted section content delivery for live updates.
 *
 * Returns HTML fragments (not full pages) for each live-updateable section.
 * The JS client surgically swaps only changed sections — no flicker, no
 * full DOM reload, no FOUC.
 *
 * ?type=featured     → Featured listings grid HTML
 * ?type=events       → Events grid HTML
 * ?type=stats        → Stats numbers JSON (client animates)
 * ?type=categories   → Categories grid HTML
 * ?type=listings     → Explore page listings grid HTML
 *   &category=SLUG   → optional filter
 *   &search=TERM     → optional search
 * ?type=gallery      → Gallery slides + thumbs HTML for listing page
 *   &slug=SLUG       → required listing slug
 * ?type=reviews      → Reviews list HTML for listing page
 *   &slug=SLUG       → required listing slug
 * ?type=admin_stats  → Admin dashboard stat numbers JSON
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

require_once '../includes/functions.php';

$type = $_GET['type'] ?? '';
$db   = getDB();

// ── Helper: build a listing card HTML string ──────────────
function listingCardHtml($listing, $idx = 0) {
    $img   = htmlspecialchars(listingImage($listing['featured_image'], $listing['name'], 600, 400));
    $name  = htmlspecialchars($listing['name']);
    $slug  = htmlspecialchars(urlencode($listing['slug']));
    $desc  = htmlspecialchars($listing['description'] ?? '');
    $color = htmlspecialchars($listing['color'] ?? '#1b4332');
    $icon  = htmlspecialchars($listing['icon'] ?? 'fas fa-map-marker-alt');
    $cat   = htmlspecialchars($listing['category_name'] ?? '');
    $bar   = $listing['barangay'] ? '<span><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($listing['barangay']) . '</span>' : '';
    $fee   = $listing['entrance_fee'] ? '<span><i class="fas fa-ticket-alt"></i> ' . htmlspecialchars($listing['entrance_fee']) . '</span>' : '';
    $feat  = $listing['is_featured'] ? '<div class="featured-badge">★ Featured</div>' : '';
    $delay = ($idx % 4) + 1;
    return '<div class="col-md-6 col-lg-4 animate-on-scroll delay-' . $delay . '" data-listing-id="' . (int)$listing['id'] . '">
      <div class="listing-card">
        <div class="listing-card-img">
          <img src="' . $img . '" alt="' . $name . '" loading="lazy"
               onerror="this.src=\'https://placehold.co/600x400/1b4332/ffffff?text=' . rawurlencode($listing['name']) . '\'">
          <div class="listing-badge" style="color:' . $color . '">
            <i class="' . $icon . '"></i> ' . $cat . '
          </div>' . $feat . '
        </div>
        <div class="listing-card-body">
          <h3 class="listing-card-title">' . $name . '</h3>
          <p class="listing-card-desc">' . $desc . '</p>
          <div class="listing-card-meta">' . $bar . $fee . '</div>
          <a href="listing.php?slug=' . $slug . '" class="btn-card">View Details <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>';
}

switch ($type) {

    // ── FEATURED LISTINGS ─────────────────────────────────
    case 'featured':
        $listings = getFeaturedListings(6);
        if (empty($listings)) {
            echo json_encode(['success' => true, 'html' => '<div class="col-12 text-center text-muted py-4"><i class="fas fa-star fa-2x mb-2 d-block"></i>No featured listings yet.</div>']);
            break;
        }
        $html = '';
        foreach ($listings as $i => $l) {
            $html .= listingCardHtml($l, $i);
        }
        echo json_encode(['success' => true, 'html' => $html, 'count' => count($listings)]);
        break;

    // ── EXPLORE LISTINGS ──────────────────────────────────
    case 'listings':
        $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
        $search   = isset($_GET['search'])   ? sanitize($_GET['search'])   : '';
        $listings = getAllListings($category, $search, 24);
        if (empty($listings)) {
            $html = '<div class="empty-state"><i class="fas fa-search"></i><h4 style="color:var(--primary);margin-bottom:0.5rem;">No destinations found</h4><p>Try adjusting your search or filter criteria.</p><a href="explore.php" class="btn-primary-main mt-3">Clear Filters</a></div>';
        } else {
            $html = '<div class="row g-4" id="listingsGrid">';
            foreach ($listings as $i => $l) {
                $html .= listingCardHtml($l, $i);
            }
            $html .= '</div>';
        }
        echo json_encode(['success' => true, 'html' => $html, 'count' => count($listings)]);
        break;

    // ── EVENTS ───────────────────────────────────────────
    case 'events':
        $events = getUpcomingEvents(3);
        if (empty($events)) {
            $html = '<div class="col-12"><div class="empty-state"><i class="fas fa-calendar-times"></i><p>No upcoming events at the moment. Check back soon!</p></div></div>';
        } else {
            $html = '';
            foreach ($events as $i => $ev) {
                $title   = htmlspecialchars($ev['title']);
                $desc    = htmlspecialchars(substr($ev['description'], 0, 120));
                $dateStr = date('F j, Y', strtotime($ev['event_date']));
                $endStr  = ($ev['end_date'] && $ev['end_date'] !== $ev['event_date']) ? ' – ' . date('F j', strtotime($ev['end_date'])) : '';
                $loc     = $ev['location'] ? '<div class="event-location"><i class="fas fa-map-pin"></i> ' . htmlspecialchars($ev['location']) . '</div>' : '';
                $delay   = $i + 1;
                $html   .= '<div class="col-md-4 animate-on-scroll delay-' . $delay . '" data-event-id="' . (int)$ev['id'] . '">
                  <div class="event-card">
                    <div class="event-date-badge"><i class="fas fa-calendar-alt"></i> ' . $dateStr . $endStr . '</div>
                    <h4 class="event-title">' . $title . '</h4>
                    <p class="event-desc">' . $desc . '...</p>' . $loc . '
                  </div>
                </div>';
            }
        }
        echo json_encode(['success' => true, 'html' => $html]);
        break;

    // ── STATS (JSON numbers — client animates) ────────────
    case 'stats':
        $stats = getStats();
        echo json_encode(['success' => true, 'data' => $stats]);
        break;

    // ── CATEGORIES ────────────────────────────────────────
    case 'categories':
        $cats = getCategories();
        $html = '';
        foreach ($cats as $i => $cat) {
            $name  = htmlspecialchars($cat['name']);
            $slug  = htmlspecialchars($cat['slug']);
            $icon  = htmlspecialchars($cat['icon']);
            $color = htmlspecialchars($cat['color']);
            $delay = ($i % 4) + 1;
            $html .= '<div class="col-6 col-md-4 col-lg-2 animate-on-scroll delay-' . $delay . '" data-cat-id="' . (int)$cat['id'] . '">
              <div class="category-card" data-slug="' . $slug . '" role="button"
                   style="height:100%;min-height:170px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
                <div class="cat-icon" style="background:linear-gradient(135deg,' . $color . ',' . $color . '88);flex-shrink:0;">
                  <i class="' . $icon . '"></i>
                </div>
                <div class="cat-name" style="min-height:2.8em;display:flex;align-items:center;justify-content:center;">' . $name . '</div>
                <div class="cat-count">Explore &rarr;</div>
              </div>
            </div>';
        }
        echo json_encode(['success' => true, 'html' => $html]);
        break;

    // ── GALLERY (listing detail page) ─────────────────────
    case 'gallery':
        $slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
        if (!$slug) { echo json_encode(['success' => false, 'error' => 'No slug']); break; }
        $listing = getListing($slug);
        if (!$listing) { echo json_encode(['success' => false, 'error' => 'Not found']); break; }

        $galleryPhotos = json_decode($listing['gallery'] ?? '[]', true) ?: [];
        $featured = $listing['featured_image'] ?? '';
        // Build all slides: featured first (if not already in gallery), then gallery
        $all = [];
        if ($featured && !in_array($featured, $galleryPhotos)) {
            array_unshift($galleryPhotos, $featured);
        }
        $galleryUrls = array_map(fn($p) => listingImage($p, $listing['name'], 900, 600), $galleryPhotos);
        $galleryFull = array_map(fn($p) => listingImage($p, $listing['name'], 1400, 900), $galleryPhotos);
        $total = count($galleryUrls);

        // Build slides HTML
        $slidesHtml = '';
        foreach ($galleryUrls as $i => $url) {
            $slidesHtml .= '<div class="gc-slide"><img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($listing['name']) . ' photo ' . ($i+1) . '" loading="' . ($i===0?'eager':'lazy') . '" onerror="this.src=\'https://placehold.co/900x600/1b4332/fff?text=Photo\'"></div>';
        }

        // Thumbs HTML
        $thumbsHtml = '';
        if ($total > 1) {
            foreach ($galleryUrls as $i => $url) {
                $thumbsHtml .= '<div class="gc-thumb' . ($i===0?' active':'') . '" id="gcThumb' . $i . '" onclick="gcGo(' . $i . ')"><img src="' . htmlspecialchars($url) . '" alt="thumb ' . ($i+1) . '" loading="lazy" onerror="this.src=\'https://placehold.co/80x58/1b4332/fff?text=' . ($i+1) . '\'"></div>';
            }
        }

        echo json_encode([
            'success'     => true,
            'total'       => $total,
            'slidesHtml'  => $slidesHtml,
            'thumbsHtml'  => $thumbsHtml,
            'photos'      => $galleryFull,
            'name'        => $listing['name'],
        ]);
        break;

    // ── REVIEWS (listing detail page) ─────────────────────
    case 'reviews':
        $slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
        if (!$slug) { echo json_encode(['success' => false, 'error' => 'No slug']); break; }
        $listing = getListing($slug);
        if (!$listing) { echo json_encode(['success' => false, 'error' => 'Not found']); break; }

        $reviews = $db->query("SELECT * FROM reviews WHERE listing_id = {$listing['id']} ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'reviews' => $reviews, 'count' => count($reviews)]);
        break;

    // ── ADMIN STATS (dashboard) ───────────────────────────
    case 'admin_stats':
        try {
            require_once '../includes/auth.php';
            if (!isLoggedIn()) { echo json_encode(['success' => false]); break; }
        } catch (Exception $e) { echo json_encode(['success' => false]); break; }
        $stats = getStats();
        $unread = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];
        $pending = $db->query("SELECT COUNT(*) as c FROM listings WHERE status='pending'")->fetch_assoc()['c'];
        $stats['unread_messages'] = (int)$unread;
        $stats['pending_listings'] = (int)$pending;
        echo json_encode(['success' => true, 'data' => $stats]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown type']);
}
