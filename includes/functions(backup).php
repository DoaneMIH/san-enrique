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
    $result = $db->query("SELECT * FROM events WHERE status = 'active' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT $limit");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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
    $stats['barangays'] = $db->query("SELECT COUNT(DISTINCT barangay) as c FROM listings WHERE status='active'")->fetch_assoc()['c'];
    return $stats;
}

function placeholderImage($name = 'Place', $w = 600, $h = 400)
{
    return "https://placehold.co/{$w}x{$h}/2d6a4f/ffffff?text=" . urlencode($name);
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

    // Already a direct link
    if (strpos($url, 'lh3.googleusercontent.com') !== false) return $url;
    if (preg_match('#drive\.google\.com/uc\?.*id=#i', $url)) return $url;

    // Extract file ID from various Drive URL formats
    $fileId = '';
    if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#i', $url, $m)) {
        $fileId = $m[1];
    } elseif (preg_match('#drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)#i', $url, $m)) {
        $fileId = $m[1];
    }

    if ($fileId) {
        // Use lh3.googleusercontent.com — most reliable for direct image display
        return 'https://lh3.googleusercontent.com/d/' . $fileId;
    }

    return $url;
}
?>