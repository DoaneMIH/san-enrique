<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? $_GET['q'] : '';
$category = isset($_GET['c']) ? $_GET['c'] : '';

$listings = getAllListings($category, $query, 50);

// Prepare data for JSON response
$response = [];
foreach ($listings as $listing) {
    $img = listingImage($listing['featured_image'], $listing['name'], 600, 400);

    $response[] = [
        'id' => $listing['id'],
        'name' => htmlspecialchars($listing['name']),
        'slug' => urlencode($listing['slug']),
        'description' => htmlspecialchars(richExcerpt($listing['description'], 100)),
        'image' => $img,
        'category_name' => htmlspecialchars($listing['category_name']),
        'icon' => htmlspecialchars($listing['icon']),
        'color' => htmlspecialchars($listing['color']),
        'barangay' => htmlspecialchars($listing['barangay']),
        'entrance_fee' => htmlspecialchars($listing['entrance_fee']),
        'is_featured' => (bool) $listing['is_featured']
    ];
}

echo json_encode(['success' => true, 'data' => $response]);
