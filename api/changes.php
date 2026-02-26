<?php
/**
 * api/changes.php
 * Returns a JSON object with the latest content modification timestamp.
 * Used by the live-update polling system on public pages.
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../includes/functions.php';
$db = getDB();

// Get the most recent updated_at from listings, categories, and events
$ts = 0;

$tables = ['listings', 'categories', 'events'];
foreach ($tables as $table) {
    $r = $db->query("SELECT UNIX_TIMESTAMP(MAX(updated_at)) as t FROM `$table`");
    if ($r) {
        $val = (int)$r->fetch_assoc()['t'];
        if ($val > $ts) $ts = $val;
    }
}

// Also check created_at as a fallback (in case updated_at is null)
foreach ($tables as $table) {
    $r = $db->query("SELECT UNIX_TIMESTAMP(MAX(created_at)) as t FROM `$table`");
    if ($r) {
        $val = (int)$r->fetch_assoc()['t'];
        if ($val > $ts) $ts = $val;
    }
}

echo json_encode(['timestamp' => $ts, 'time' => date('Y-m-d H:i:s', $ts)]);
