<?php
/**
 * api/admin-updates.php
 * Returns fresh messages or reviews for admin panel live-update polling
 * Query parameters: ?page=messages or ?page=reviews
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../includes/functions.php';
requireLogin();

$db = getDB();
$page = $_GET['page'] ?? 'messages';

if ($page === 'messages') {
    // Get all messages
    $messages = $db->query("
        SELECT id, name, email, subject, message, is_read, created_at
        FROM messages
        ORDER BY created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $unreadCount = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'unreadCount' => $unreadCount,
        'count' => count($messages),
        'timestamp' => time()
    ]);

} elseif ($page === 'reviews') {
    // Get all reviews with listing info
    $reviews = $db->query("
        SELECT rv.id, rv.reviewer_name, rv.rating, rv.comment, rv.created_at,
               l.name as listing_name, l.slug as listing_slug
        FROM reviews rv
        JOIN listings l ON rv.listing_id = l.id
        ORDER BY rv.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $avgRating = 0;
    if (count($reviews) > 0) {
        $avgRating = array_sum(array_column($reviews, 'rating')) / count($reviews);
    }

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'count' => count($reviews),
        'avgRating' => round($avgRating, 1),
        'timestamp' => time()
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid page']);
}
?>