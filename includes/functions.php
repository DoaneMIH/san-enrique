<?php
define('BASE_URL', 'http://localhost/san-enrique');
define('SITE_NAME', 'San Enrique Tourism Hub');
define('SITE_TAGLINE', 'Discover the Hidden Paradise of Iloilo');
define('MUNICIPALITY', 'San Enrique, Iloilo');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function getCategories()
{
    $db = getDB();
    $result = $db->query("SELECT * FROM categories ORDER BY name");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getFeaturedListings($limit = 6)
{
    $db = getDB();
    $result = $db->query("SELECT l.*, c.name as category_name, c.icon, c.color, c.slug as cat_slug FROM listings l JOIN categories c ON l.category_id = c.id WHERE l.is_featured = 1 AND l.status = 'active' LIMIT $limit");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllListings($category_slug = '', $search = '', $limit = 20, $offset = 0)
{
    $db = getDB();
    $where = ["l.status = 'active'"];
    if ($category_slug) {
        $cat = $db->real_escape_string($category_slug);
        $where[] = "c.slug = '$cat'";
    }
    if ($search) {
        $s = $db->real_escape_string($search);
        $where[] = "(l.name LIKE '%$s%' OR l.description LIKE '%$s%' OR l.barangay LIKE '%$s%')";
    }
    $whereStr = implode(' AND ', $where);
    $result = $db->query("SELECT l.*, c.name as category_name, c.icon, c.color, c.slug as cat_slug FROM listings l JOIN categories c ON l.category_id = c.id WHERE $whereStr ORDER BY l.is_featured DESC, l.name ASC LIMIT $limit OFFSET $offset");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getListing($slug)
{
    $db = getDB();
    $slug = $db->real_escape_string($slug);
    $result = $db->query("SELECT l.*, c.name as category_name, c.icon, c.color FROM listings l JOIN categories c ON l.category_id = c.id WHERE l.slug = '$slug' AND l.status = 'active' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $listing = $result->fetch_assoc();
        $db->query("UPDATE listings SET views = views + 1 WHERE slug = '$slug'");
        return $listing;
    }
    return null;
}

function getUpcomingEvents($limit = 3)
{
    $db = getDB();

    // Pinned events always show first regardless of date
    $pinned = [];
    $upcoming = [];

    $pResult = $db->query("SELECT * FROM events WHERE status='active' AND is_pinned=1 ORDER BY event_date ASC");
    if ($pResult) $pinned = $pResult->fetch_all(MYSQLI_ASSOC);

    // Fill remaining slots with upcoming non-pinned events
    $pinnedIds = array_column($pinned, 'id');
    $excludeStr = implode(',', array_map('intval', $pinnedIds)) ?: '0';
    $remaining = max(0, $limit - count($pinned));

    if ($remaining > 0) {
        $uResult = $db->query("SELECT * FROM events WHERE status='active' AND is_pinned=0 AND event_date >= CURDATE() AND id NOT IN ($excludeStr) ORDER BY event_date ASC LIMIT $remaining");
        if ($uResult) $upcoming = $uResult->fetch_all(MYSQLI_ASSOC);
    }

    $events = array_merge($pinned, $upcoming);

    // Fallback: if is_pinned column doesn't exist yet, use original query
    if ($db->errno) {
        $result = $db->query("SELECT * FROM events WHERE status='active' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT $limit");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    return $events ?: [];
}

function getAllListingsForMap()
{
    $db = getDB();
    $result = $db->query("SELECT l.id, l.name, l.latitude, l.longitude, l.address, l.slug, l.featured_image, c.name as category_name, c.icon, c.color, c.slug as cat_slug FROM listings l JOIN categories c ON l.category_id = c.id WHERE l.status = 'active' AND l.latitude IS NOT NULL AND l.longitude IS NOT NULL");
    if (!$result)
        return [];
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    // Resolve image paths to absolute URLs so JS map markers work correctly
    foreach ($rows as &$row) {
        $row['featured_image'] = listingImage($row['featured_image'], $row['name'], 280, 120);
    }
    return $rows;
}

function getStats()
{
    $db = getDB();
    $stats = [];
    $stats['listings'] = $db->query("SELECT COUNT(*) as c FROM listings WHERE status='active'")->fetch_assoc()['c'];
    $stats['categories'] = $db->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
    $stats['events'] = $db->query("SELECT COUNT(*) as c FROM events WHERE status='active'")->fetch_assoc()['c'];
    // Count listings in the 'barangays' category (matches admin panel display)
    $brgyResult = $db->query("SELECT COUNT(l.id) as c FROM listings l JOIN categories c ON l.category_id=c.id WHERE c.slug='barangays' AND l.status='active'");
    $stats['barangays'] = $brgyResult ? (int)$brgyResult->fetch_assoc()['c'] : 0;
    return $stats;
}

function placeholderImage($name = 'Place', $w = 600, $h = 400)
{
    return "https://placehold.co/{$w}x{$h}/2d6a4f/ffffff?text=" . urlencode($name);
}

/**
 * Converts a rich-text (HTML) field like `listings.description` into a
 * clean, plain-text excerpt safe to echo with htmlspecialchars() on
 * listing cards, meta tags, and API responses.
 *
 * Strips all HTML tags/entities first, THEN truncates on a word boundary,
 * so raw tags such as <p>, <div>, <span style="..."> never leak into the
 * output and words are never cut in half.
 */
function richExcerpt($html, $length = 150)
{
    $html = (string) ($html ?? '');
    // Turn block-level tags into spaces first so words don't get jammed
    // together (e.g. "</p><p>" would otherwise become "wordword").
    $html = preg_replace('#</(p|div|li|h[1-6]|br)\s*>#i', ' ', $html);
    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text);

    if ($length > 0 && mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length);
        // Trim back to the last full word so we don't cut mid-word
        $text = preg_replace('/\s+?(\S+)?$/u', '', $text) . '...';
    }
    return $text;
}

/**
 * Returns the correct absolute URL for a listing image.
 * Normalises paths like:
 *   ../uploads/listings/file.jpg   (saved by admin panel)
 *   uploads/listings/file.jpg      (alternative form)
 *   https://external.com/img.jpg   (external URL - left as-is)
 */
function listingImage($img, $name = 'Place', $w = 600, $h = 400)
{
    if (!$img)
        return placeholderImage($name, $w, $h);
    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0)
        return $img;
    // Strip leading ../ so we always get a clean root-relative path
    $clean = preg_replace('#^(\.\./)+#', '', $img);
    $clean = ltrim($clean, '/');
    return BASE_URL . '/' . $clean;
}
/**
 * Converts Google Drive share URLs to direct-image URLs.
 * Supports formats:
 *   https://drive.google.com/file/d/FILE_ID/view?usp=sharing
 *   https://drive.google.com/open?id=FILE_ID
 *   https://drive.google.com/uc?id=FILE_ID&export=view  (already direct)
 *   https://lh3.googleusercontent.com/d/FILE_ID         (already direct)
 * Non-Drive URLs are returned unchanged.
 */
function convertGdriveUrl($url)
{
    $url = trim($url);
    if (empty($url)) return $url;

    // Not a Drive link at all — return as-is
    if (strpos($url, 'drive.google.com') === false &&
        strpos($url, 'googleusercontent.com') === false) {
        return $url;
    }

    // Extract file ID from all known Drive URL formats
    $fileId = '';

    // /file/d/FILE_ID/
    if (preg_match('#/file/d/([a-zA-Z0-9_-]{10,})#i', $url, $m)) {
        $fileId = $m[1];
    // ?id=FILE_ID or &id=FILE_ID (uc?export=view style)
    } elseif (preg_match('#[?&]id=([a-zA-Z0-9_-]{10,})#i', $url, $m)) {
        $fileId = $m[1];
    // open?id=FILE_ID
    } elseif (preg_match('#open\?id=([a-zA-Z0-9_-]{10,})#i', $url, $m)) {
        $fileId = $m[1];
    }

    if ($fileId) {
        // thumbnail endpoint works reliably for ALL public Drive images
        return 'https://drive.google.com/thumbnail?id=' . $fileId . '&sz=w1200';
    }

    return $url;
}
?>