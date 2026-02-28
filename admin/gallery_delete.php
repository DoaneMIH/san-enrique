<?php
require_once '../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listings.php'); exit;
}

$db         = getDB();
$listingId  = (int)($_POST['listing_id'] ?? 0);
$photoPath  = sanitize($_POST['photo_path'] ?? '');

if (!$listingId || !$photoPath) {
    header('Location: listings.php'); exit;
}

// Fetch current gallery
$row = $db->query("SELECT gallery, slug FROM listings WHERE id = $listingId")->fetch_assoc();
if (!$row) { header('Location: listings.php'); exit; }

$gallery = json_decode($row['gallery'] ?? '[]', true) ?: [];

// Remove the photo from the array
$gallery = array_values(array_filter($gallery, fn($p) => $p !== $photoPath));

// Delete the file from disk if it's an uploaded one
if (strpos($photoPath, '../uploads/listings/') === 0 && file_exists($photoPath)) {
    unlink($photoPath);
}

// Save updated gallery back
$galleryJson = $db->real_escape_string(json_encode($gallery));
$db->query("UPDATE listings SET gallery = '$galleryJson' WHERE id = $listingId");

header('Location: listing_view.php?slug=' . urlencode($row['slug']));
exit;