<?php
// api/review.php
require_once '../includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$listing_id = (int)($_POST['listing_id'] ?? 0);
$reviewer_name = sanitize($_POST['reviewer_name'] ?? 'Anonymous');
$rating = (int)($_POST['rating'] ?? 0);
$comment = sanitize($_POST['comment'] ?? '');

if (!$listing_id || $rating < 1 || $rating > 5 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Invalid review data. Please fill all fields.']);
    exit;
}

$db = getDB();
$sql = "INSERT INTO reviews (listing_id, reviewer_name, rating, comment) VALUES ($listing_id, '$reviewer_name', $rating, '$comment')";

if ($db->query($sql)) {
    // Update listing average rating
    $db->query("UPDATE listings SET rating = (SELECT AVG(rating) FROM reviews WHERE listing_id = $listing_id) WHERE id = $listing_id");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review.']);
}
