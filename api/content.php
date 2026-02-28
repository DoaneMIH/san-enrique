<?php
/**
 * api/content.php
 * Returns fresh page-section content as JSON for the live-update polling system.
 *
 * Query parameters:
 *   ?type=featured          → 6 featured listings (index.php featured section)
 *   ?type=events            → upcoming events     (index.php events section)
 *   ?type=stats             → site-wide counts    (index.php stats section)
 *   ?type=categories        → all categories      (index.php categories & footer)
 *   ?type=listings          → all listings        (explore.php grid)
 *     &category=SLUG        → optional category filter
 *     &search=TERM          → optional search filter
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../includes/functions.php';

$type = $_GET['type'] ?? '';

switch ($type) {

    case 'featured':
        $listings = getFeaturedListings(6);
        foreach ($listings as &$l) {
            $l['image_url'] = listingImage($l['featured_image'], $l['name'], 600, 400);
        }
        echo json_encode(['success' => true, 'data' => $listings]);
        break;

    case 'events':
        $events = getUpcomingEvents(3);
        echo json_encode(['success' => true, 'data' => $events]);
        break;

    case 'stats':
        $stats = getStats();
        echo json_encode(['success' => true, 'data' => $stats]);
        break;

    case 'categories':
        $categories = getCategories();
        echo json_encode(['success' => true, 'data' => $categories]);
        break;

    case 'listings':
        $category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
        $search   = isset($_GET['search'])   ? sanitize($_GET['search'])   : '';
        $listings = getAllListings($category, $search, 24);
        foreach ($listings as &$l) {
            $l['image_url'] = listingImage($l['featured_image'], $l['name'], 600, 400);
        }
        echo json_encode(['success' => true, 'data' => $listings, 'count' => count($listings)]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown type']);
}
