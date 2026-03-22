<?php
/**
 * api/changes.php  —  Real-time change detection endpoint.
 *
 * Returns a JSON object with the latest modification timestamp AND
 * per-section fingerprints so the JS client knows exactly which
 * sections changed, avoiding unnecessary DOM work or full re-fetches.
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../includes/functions.php';
$db = getDB();

// ── Per-table latest timestamps ───────────────────────────
$ts = [];
foreach (['listings','categories','events','reviews'] as $tbl) {
    $chk = $db->query("SHOW COLUMNS FROM `$tbl` LIKE 'updated_at'");
    $q = ($chk && $chk->num_rows > 0)
        ? "SELECT GREATEST(COALESCE(UNIX_TIMESTAMP(MAX(created_at)),0), COALESCE(UNIX_TIMESTAMP(MAX(updated_at)),0)) as t FROM `$tbl`"
        : "SELECT COALESCE(UNIX_TIMESTAMP(MAX(created_at)),0) as t FROM `$tbl`";
    $r = $db->query($q);
    $ts[$tbl] = $r ? (int)$r->fetch_assoc()['t'] : 0;
}

// ── Section fingerprints ──────────────────────────────────
$fp = [];

$r = $db->query("SELECT id,name,featured_image,is_featured,status FROM listings WHERE is_featured=1 AND status='active' ORDER BY id");
$fp['featured'] = $r ? md5(serialize($r->fetch_all(MYSQLI_ASSOC))) : '0';

$r = $db->query("SELECT id,name,slug,icon,color FROM categories ORDER BY name");
$fp['categories'] = $r ? md5(serialize($r->fetch_all(MYSQLI_ASSOC))) : '0';

$r = $db->query("SELECT id,title,event_date,end_date,location,description FROM events WHERE status='active' AND event_date>=CURDATE() ORDER BY event_date LIMIT 3");
$fp['events'] = $r ? md5(serialize($r->fetch_all(MYSQLI_ASSOC))) : '0';

$r = $db->query("SELECT (SELECT COUNT(*) FROM listings WHERE status='active') l,(SELECT COUNT(*) FROM categories) c,(SELECT COUNT(DISTINCT barangay) FROM listings WHERE barangay IS NOT NULL AND barangay!='') b,(SELECT COUNT(*) FROM events WHERE status='active') e");
$fp['stats'] = $r ? md5(serialize($r->fetch_assoc())) : '0';

$r = $db->query("SELECT id,name,slug,featured_image,category_id FROM listings WHERE status='active' ORDER BY created_at DESC LIMIT 24");
$fp['listings'] = $r ? md5(serialize($r->fetch_all(MYSQLI_ASSOC))) : '0';

$r = $db->query("SELECT id,listing_id,rating,reviewer_name FROM reviews ORDER BY created_at DESC LIMIT 50");
$fp['reviews'] = $r ? md5(serialize($r->fetch_all(MYSQLI_ASSOC))) : '0';

$r = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0");
$fp['admin_msgs'] = $r ? (string)$r->fetch_assoc()['c'] : '0';

echo json_encode([
    'timestamp'    => max(array_values($ts)),
    'time'         => date('Y-m-d H:i:s', max(array_values($ts))),
    'ts'           => $ts,
    'fingerprints' => $fp,
]);
