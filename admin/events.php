<?php
require_once '../includes/functions.php';
requireLogin();
$admin = currentAdmin();
$db = getDB();

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editEvent = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $event_date  = sanitize($_POST['event_date'] ?? '');
    $end_date    = sanitize($_POST['end_date'] ?? '');
    $location    = sanitize($_POST['location'] ?? '');
    $lat         = (float)($_POST['latitude'] ?? 0);
    $lng         = (float)($_POST['longitude'] ?? 0);
    // ── Event images: multi-image gallery stored as JSON in `image` column ──
    $evtUploadDir = '../uploads/events/';
    if (!is_dir($evtUploadDir)) mkdir($evtUploadDir, 0755, true);

    // Start from existing images
    $evtImgPaths = [];
    $evtImgRaw   = sanitize($_POST['event_image_current'] ?? '');
    if ($evtImgRaw) {
        $decoded = json_decode($evtImgRaw, true);
        if (is_array($decoded)) {
            $evtImgPaths = $decoded;               // already JSON array
        } elseif ($evtImgRaw !== '') {
            $evtImgPaths = [$evtImgRaw];           // legacy single string
        }
    }

    // Remove images the admin deleted
    $toRemove = $_POST['remove_evt_img'] ?? [];
    foreach ($toRemove as $rp) {
        $rp = sanitize($rp);
        if (strpos($rp, '../uploads/events/') === 0 && file_exists($rp)) unlink($rp);
        $evtImgPaths = array_filter($evtImgPaths, fn($p) => $p !== $rp);
    }
    $evtImgPaths = array_values($evtImgPaths);

    // Add URL inputs (Google Drive or direct image URLs)
    foreach ($_POST['evt_img_urls'] ?? [] as $u) {
        $u = trim($u);
        if ($u && filter_var($u, FILTER_VALIDATE_URL)) {
            $evtImgPaths[] = convertGdriveUrl($u);
        }
    }

    // Add uploaded files
    if (!empty($_FILES['evt_img_upload']['name'][0])) {
        $evtAllow = ['jpg','jpeg','png','webp','gif'];
        foreach ($_FILES['evt_img_upload']['tmp_name'] as $k => $tmp) {
            if ($_FILES['evt_img_upload']['error'][$k] !== UPLOAD_ERR_OK) continue;
            $evtExt = strtolower(pathinfo($_FILES['evt_img_upload']['name'][$k], PATHINFO_EXTENSION));
            if (!in_array($evtExt, $evtAllow)) continue;
            if ($_FILES['evt_img_upload']['size'][$k] > 5 * 1024 * 1024) continue;
            $evtNewName = 'event_' . time() . '_' . mt_rand(1000,9999) . '_' . $k . '.' . $evtExt;
            if (move_uploaded_file($tmp, $evtUploadDir . $evtNewName)) {
                $evtImgPaths[] = '../uploads/events/' . $evtNewName;
            }
        }
    }

    // Store as JSON (or empty string if none)
    $image = $db->real_escape_string(json_encode(array_values($evtImgPaths)));
    $status      = sanitize($_POST['status'] ?? 'active');
    $is_pinned   = isset($_POST['is_pinned']) ? 1 : 0;

    if ($_POST['form_action'] === 'add') {
        $sql = "INSERT INTO events (title, description, event_date, end_date, location, latitude, longitude, image, status, is_pinned) VALUES ('$title','$description','$event_date','$end_date','$location',$lat,$lng,'$image','$status',$is_pinned)";
        if ($db->query($sql)) { $message = "Event \"$title\" added successfully!"; $action = 'list'; }
        else { $error = 'Failed to add event: ' . $db->error; }
    } elseif ($_POST['form_action'] === 'edit') {
        $id = (int)$_POST['event_id'];
        $sql = "UPDATE events SET title='$title', description='$description', event_date='$event_date', end_date='$end_date', location='$location', latitude=$lat, longitude=$lng, image='$image', status='$status', is_pinned=$is_pinned WHERE id=$id";
        if ($db->query($sql)) { $message = "Event updated successfully!"; $action = 'list'; }
        else { $error = 'Failed to update: ' . $db->error; }
    }
}

if (isset($_GET['delete'])) {
    $db->query("DELETE FROM events WHERE id=" . (int)$_GET['delete']);
    $message = 'Event deleted.'; $action = 'list';
}

// Toggle pin
if (isset($_GET['pin'])) {
    $pinId = (int)$_GET['pin'];
    $current = $db->query("SELECT is_pinned FROM events WHERE id=$pinId")->fetch_assoc();
    if ($current) {
        $newPin = $current['is_pinned'] ? 0 : 1;
        $db->query("UPDATE events SET is_pinned=$newPin WHERE id=$pinId");
        $message = $newPin ? 'Event pinned — it will always show on the homepage.' : 'Event unpinned.';
    }
}

if ($action === 'edit' && isset($_GET['id'])) {
    $r = $db->query("SELECT * FROM events WHERE id=" . (int)$_GET['id']);
    $editEvent = $r ? $r->fetch_assoc() : null;
}

$events = $db->query("SELECT * FROM events ORDER BY event_date DESC")->fetch_all(MYSQLI_ASSOC);
$unreadMsgs = $db->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events - Admin Panel</title>
  <link rel="shortcut icon" type="x-icon" href="../assets/images/san-enrique-logo.jpg">

<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php require_once 'sidebar.php'; ?>

<div class="admin-content">
  <div class="admin-topbar">
    <div>
      <button class="d-lg-none topbar-menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <span class="topbar-title"><?= $action === 'list' ? 'Events Management' : ($action === 'add' ? 'Add New Event' : 'Edit Event') ?></span>
    </div>
    <div class="topbar-actions">
      <?php if ($action === 'list'): ?>
      <a href="?action=add" class="btn-admin-primary"><i class="fas fa-plus me-1"></i> Add Event</a>
      <?php else: ?>
      <a href="events.php" class="btn-admin-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="admin-main">
    <?php if ($message): ?>
    <div class="admin-alert success">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="admin-alert error">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
    <!-- EVENTS TABLE -->
    <div class="admin-table-wrap">
      <div class="admin-table-header">
        <div class="admin-table-title">All Events (<?= count($events) ?>)</div>
        <div class="admin-search">
          <i class="fas fa-search"></i>
          <input type="text" id="tableSearch" placeholder="Search events...">
        </div>
      </div>
      <?php if ($events): ?>
      <div class="table-scroll">
        <table class="admin-table" id="eventsTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Event</th>
              <th>Date</th>
              <th>Location</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($events as $i => $ev): 
              $isPast = strtotime($ev['event_date']) < time();
            ?>
            <tr>
              <td class="td-muted"><?= $i+1 ?></td>
              <td>
                <div class="listing-info">
                  <?php
                    // Decode JSON array or handle legacy single string
                    $evImgs = [];
                    if ($ev['image']) {
                      $decoded = json_decode($ev['image'], true);
                      $evImgs  = is_array($decoded) ? $decoded : [$ev['image']];
                    }
                    // Use first image as thumbnail
                    $evThumbSrc = '';
                    if (!empty($evImgs[0])) {
                      $first = $evImgs[0];
                      if (strpos($first, 'http') === 0) {
                        if (preg_match('#drive\.google\.com/thumbnail\?id=([a-zA-Z0-9_-]+)#', $first, $_tm)) {
                          $evThumbSrc = 'https://lh3.googleusercontent.com/d/' . $_tm[1];
                        } else {
                          $evThumbSrc = $first;
                        }
                      } else {
                        $evClean    = preg_replace('#^(\.\./)+#', '', $first);
                        $evThumbSrc = BASE_URL . '/' . ltrim($evClean, '/');
                      }
                    }
                  ?>
                  <?php if ($evThumbSrc): ?>
                  <img src="<?= htmlspecialchars($evThumbSrc) ?>" class="listing-thumb" alt=""
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                  <div class="event-thumb-placeholder" style="display:none;">
                    <i class="fas fa-calendar"></i>
                  </div>
                  <?php else: ?>
                  <div class="event-thumb-placeholder">
                    <i class="fas fa-calendar"></i>
                  </div>
                  <?php endif; ?>
                  <div>
                    <div class="event-title-text">
                      <?= htmlspecialchars($ev['title']) ?>
                      <?php if ($ev['is_pinned'] ?? 0): ?>
                        <span style="display:inline-flex;align-items:center;gap:3px;background:#fef3c7;color:#d97706;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:100px;border:1px solid #fde68a;margin-left:6px;">
                          <i class="fas fa-thumbtack" style="font-size:0.6rem;"></i> PINNED
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="event-desc-text"><?= htmlspecialchars($ev['description']) ?></div>
                  </div>
                </div>
              </td>
              <td>
                <div class="<?= $isPast ? 'event-date-past' : 'event-date-active' ?>">
                  <?= date('M j, Y', strtotime($ev['event_date'])) ?>
                </div>
                <?php if ($ev['end_date'] && $ev['end_date'] !== $ev['event_date']): ?>
                <div class="event-date-end">– <?= date('M j', strtotime($ev['end_date'])) ?></div>
                <?php endif; ?>
                <?php if ($isPast): ?>
                <span class="event-past-label">Past event</span>
                <?php endif; ?>
              </td>
              <td class="event-loc-text">
                <?= htmlspecialchars($ev['location'] ?: '—') ?>
              </td>
              <td><span class="status-badge <?= $ev['status'] ?>"><?= ucfirst($ev['status']) ?></span></td>
              <td>
                <div class="table-actions">
                  <a href="?action=edit&id=<?= $ev['id'] ?>" class="btn-admin-edit">
                    <i class="fas fa-pencil-alt"></i> Edit
                  </a>
                  <a href="?pin=<?= $ev['id'] ?>"
                     class="btn-admin-edit"
                     style="background:<?= ($ev['is_pinned'] ?? 0) ? 'rgba(217,119,6,0.15)' : '' ?>;border-color:<?= ($ev['is_pinned'] ?? 0) ? 'rgba(217,119,6,0.4)' : '' ?>;color:<?= ($ev['is_pinned'] ?? 0) ? '#d97706' : '' ?>;"
                     title="<?= ($ev['is_pinned'] ?? 0) ? 'Unpin from homepage' : 'Pin to homepage' ?>">
                    <i class="fas fa-thumbtack<?= ($ev['is_pinned'] ?? 0) ? '' : '' ?>"></i>
                    <?= ($ev['is_pinned'] ?? 0) ? 'Pinned' : 'Pin' ?>
                  </a>
                  <button onclick="confirmDeleteEvent(<?= $ev['id'] ?>, '<?= addslashes($ev['title']) ?>')" class="btn-admin-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times empty-icon-lg"></i>
        <div class="empty-title">No events yet</div>
        <a href="?action=add" class="btn-admin-primary mt-3">
          <i class="fas fa-plus me-1"></i> Add First Event
        </a>
      </div>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- EVENT FORM -->
    <div class="admin-form-card">
      <div class="admin-form-header">
        <div class="form-header-title">
          <?= $action === 'add' ? 'Add New Event' : 'Edit: '.htmlspecialchars($editEvent['title'] ?? '') ?>
        </div>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="form_action" value="<?= $action ?>">
        <?php if ($action === 'edit' && $editEvent): ?>
        <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>">
        <?php endif; ?>
        <div class="admin-form-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="admin-label">Event Title *</label>
              <input type="text" name="title" class="admin-input" required
                     value="<?= htmlspecialchars($editEvent['title'] ?? '') ?>"
                     placeholder="e.g. San Enrique Fiesta Festival">
            </div>

            <div class="col-12">
              <label class="admin-label">Description</label>
              <textarea name="description" class="admin-input" rows="4"
                        placeholder="Describe the event..."><?= htmlspecialchars($editEvent['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="admin-label">Start Date *</label>
              <input type="date" name="event_date" class="admin-input" required
                     value="<?= htmlspecialchars($editEvent['event_date'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="admin-label">End Date (optional)</label>
              <input type="date" name="end_date" class="admin-input"
                     value="<?= htmlspecialchars($editEvent['end_date'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="admin-label">Location / Venue</label>
              <input type="text" name="location" class="admin-input"
                     value="<?= htmlspecialchars($editEvent['location'] ?? '') ?>"
                     placeholder="e.g. Poblacion Plaza, San Enrique">
            </div>

            <!-- <div class="col-md-6">
              <label class="admin-label">Latitude (GPS)</label>
              <input type="number" name="latitude" class="admin-input" step="any"
                     value="<?= htmlspecialchars($editEvent['latitude'] ?? '10.9178') ?>">
            </div>
            <div class="col-md-6">
              <label class="admin-label">Longitude (GPS)</label>
              <input type="number" name="longitude" class="admin-input" step="any"
                     value="<?= htmlspecialchars($editEvent['longitude'] ?? '122.8845') ?>">
            </div> -->

            <!-- Event Images — multi-image gallery (same pattern as listings gallery) -->
            <div class="col-12">
              <label class="admin-label">
                <i class="fas fa-images me-1"></i> Event Photos
                <span class="label-sub">(first photo shows as thumbnail in table &amp; homepage)</span>
              </label>
              <?php
                // Decode existing images JSON or handle legacy single string
                $evtImgRaw   = $editEvent['image'] ?? '';
                $evtExisting = [];
                if ($evtImgRaw) {
                  $decoded = json_decode($evtImgRaw, true);
                  $evtExisting = is_array($decoded) ? $decoded : [$evtImgRaw];
                }
              ?>
              <!-- Hidden: pass current image JSON so PHP can merge on save -->
              <input type="hidden" name="event_image_current" id="evtCurrentImg"
                     value="<?= htmlspecialchars($evtImgRaw) ?>">
              <div id="evtRemovedInputs"></div>

              <!-- Existing images with remove buttons -->
              <?php if (!empty($evtExisting)): ?>
              <div id="evtExistingWrap" class="gallery-existing-wrap">
                <?php foreach ($evtExisting as $gi => $gImg): ?>
                <?php
                  if (strpos($gImg, 'http') === 0) {
                    if (preg_match('#drive\.google\.com/thumbnail\?id=([a-zA-Z0-9_-]+)#', $gImg, $_gm)) {
                      $gDisp = 'https://lh3.googleusercontent.com/d/' . $_gm[1];
                    } else {
                      $gDisp = $gImg;
                    }
                    $gType = 'url';
                  } else {
                    $gClean = preg_replace('#^(\.\./)+#', '', $gImg);
                    $gDisp  = BASE_URL . '/' . ltrim($gClean, '/');
                    $gType  = 'file';
                  }
                ?>
                <div class="gallery-thumb-wrap<?= $gi === 0 ? ' evt-thumb-first' : '' ?>">
                  <img src="<?= htmlspecialchars($gDisp) ?>" onerror="this.src='https://placehold.co/100x80/1b4332/fff?text=IMG'">
                  <button type="button" onclick="evtRemoveExisting(this,'<?= htmlspecialchars($gImg, ENT_QUOTES) ?>')" class="gallery-thumb-remove" title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                  <span class="gallery-thumb-type <?= $gType ?>">
                    <?= $gi === 0 ? '<i class="fas fa-star" title="First photo = thumbnail"></i>' : ($gType === 'url' ? '<i class="fas fa-link"></i>' : '<i class="fas fa-hdd"></i>') ?>
                  </span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <!-- Upload from device -->
              <div class="gallery-method-block">
                <div class="gallery-method-label"><i class="fas fa-upload me-1"></i> Upload from Device</div>
                <div id="evtUploadZone" class="upload-zone upload-zone-sm">
                  <input type="file" name="evt_img_upload[]" id="evtFileInput" multiple
                         accept="image/jpeg,image/png,image/webp,image/gif"
                         onchange="evtHandleMultiFile(this)">
                  <i class="fas fa-images upload-zone-icon upload-zone-icon-sm"></i>
                  <div class="upload-zone-title">Click to select one or more photos</div>
                  <div class="upload-zone-hint">JPG, PNG, WEBP or GIF &mdash; max 5 MB each</div>
                </div>
                <div id="evtNewFilePreview" class="gallery-preview-row"></div>
              </div>

              <!-- Divider -->
              <div class="gallery-or-divider"><span>AND / OR</span></div>

              <!-- Paste URLs -->
              <div class="gallery-method-block">
                <div class="gallery-method-label"><i class="fas fa-link me-1"></i> Paste Image URLs</div>
                <div id="evtUrlInputs" class="gallery-url-inputs-wrap"></div>
                <button type="button" onclick="evtAddUrlRow()" class="btn-add-gallery-url">
                  <i class="fas fa-plus me-1"></i> Add Image URL
                </button>
                <div class="form-hint form-hint-gdrive">
                  <i class="fab fa-google-drive me-1"></i>
                  <strong>Google Drive supported!</strong> Auto-converted. Set files to <em>"Anyone with the link"</em>.
                </div>
              </div>

              <div class="form-hint"><i class="fas fa-info-circle me-1"></i>
                Add multiple photos — the <strong>first photo</strong> is used as the thumbnail in the events table and homepage.
              </div>
            </div>

            <div class="col-md-6">
              <label class="admin-label">Status</label>
              <select name="status" class="admin-input">
                <option value="active" <?= ($editEvent['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($editEvent['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="p-3 w-100" style="background:var(--g7);border-radius:10px;border:1px solid var(--border-solid);">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" name="is_pinned" id="isPinned"
                    <?= ($editEvent['is_pinned'] ?? 0) ? 'checked' : '' ?> value="1">
                  <label class="form-check-label" for="isPinned" style="color:var(--g1);font-weight:600;font-size:0.88rem;">
                    <i class="fas fa-thumbtack me-1" style="color:var(--warning);"></i>
                    Pin to Homepage
                  </label>
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">
                  Pinned events always show on the homepage regardless of date.
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="admin-form-footer">
          <a href="events.php" class="btn-admin-secondary"><i class="fas fa-times me-1"></i> Cancel</a>
          <button type="submit" class="btn-admin-primary">
            <i class="fas fa-save me-1"></i> <?= $action === 'add' ? 'Save Event' : 'Update Event' ?>
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once 'scripts.php'; ?>
<script>
function toggleSidebar() {
  document.getElementById('adminSidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('d-none');
}

// Table search
const ts = document.getElementById('tableSearch');
if (ts) ts.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#eventsTable tbody tr').forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});

// ── Event gallery: Google Drive URL converter ───────────────
function evtExtractGdriveId(url) {
  if (!url) return null;
  var m = url.match(/[?&]id=([a-zA-Z0-9_-]{10,})/); if (m) return m[1];
  m = url.match(/\/file\/d\/([a-zA-Z0-9_-]{10,})/); if (m) return m[1];
  m = url.match(/open\?id=([a-zA-Z0-9_-]{10,})/); if (m) return m[1];
  return null;
}
function evtConvertGdriveUrl(url) {
  if (!url) return url;
  if (url.indexOf('drive.google.com') === -1 && url.indexOf('googleusercontent.com') === -1) return url;
  var id = evtExtractGdriveId(url);
  return id ? 'https://drive.google.com/thumbnail?id=' + id + '&sz=w1200' : url;
}
function evtLh3Url(url) {
  // lh3 for browser <img> preview of Drive links
  var id = evtExtractGdriveId(url);
  return id ? 'https://lh3.googleusercontent.com/d/' + id : url;
}

// ── Remove existing saved image ──────────────────────────────
function evtRemoveExisting(btn, path) {
  var wrap = btn.closest('.gallery-thumb-wrap');
  var inp = document.createElement('input');
  inp.type = 'hidden'; inp.name = 'remove_evt_img[]'; inp.value = path;
  document.getElementById('evtRemovedInputs').appendChild(inp);
  wrap.remove();
  evtUpdateFirstBadge();
}

function evtUpdateFirstBadge() {
  // Re-badge: first remaining thumb gets the star
  var thumbs = document.querySelectorAll('#evtExistingWrap .gallery-thumb-wrap');
  thumbs.forEach(function(t, i) {
    var badge = t.querySelector('.gallery-thumb-type');
    if (!badge) return;
    t.classList.toggle('evt-thumb-first', i === 0);
    if (i === 0) badge.innerHTML = '<i class="fas fa-star" title="First photo = thumbnail"></i>';
    else if (badge.innerHTML.includes('fa-star')) badge.innerHTML = '<i class="fas fa-hdd"></i>';
  });
}

// ── Multi-file upload preview ────────────────────────────────
function evtHandleMultiFile(input) {
  var allowed = ['image/jpeg','image/png','image/webp','image/gif'];
  var preview = document.getElementById('evtNewFilePreview');
  Array.from(input.files).forEach(function(file) {
    if (!allowed.includes(file.type)) return;
    if (file.size > 5 * 1024 * 1024) {
      Swal.fire({ icon:'error', title:'Too Large', text: file.name + ' exceeds 5 MB.', confirmButtonColor:'#1b4332' });
      return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
      var wrap = document.createElement('div');
      wrap.className = 'gallery-thumb-wrap gallery-thumb-new';
      wrap.innerHTML = '<img src="' + e.target.result + '"><span class="gallery-thumb-type file"><i class="fas fa-hdd"></i></span>';
      preview.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
}

// ── URL row helpers ──────────────────────────────────────────
var _evtUrlIdx = 0;
var _evtUrlTimers = {};

function evtAddUrlRow() {
  var container = document.getElementById('evtUrlInputs');
  var idx = _evtUrlIdx++;
  var row = document.createElement('div');
  row.className = 'gallery-url-row';
  row.id = 'evtUrlRow_' + idx;
  row.innerHTML = '<div class="url-input-wrap">'
    + '<i class="fas fa-link url-input-icon"></i>'
    + '<input type="url" name="evt_img_urls[]" class="admin-input url-input-field" '
    + 'placeholder="https://drive.google.com/file/d/... or any image URL" '
    + 'oninput="evtPreviewUrlRow(' + idx + ', this.value)">'
    + '<button type="button" class="gallery-url-row-remove" onclick="evtRemoveUrlRow(' + idx + ')" title="Remove">'
    + '<i class="fas fa-times"></i></button>'
    + '</div>'
    + '<div class="gallery-url-row-thumb" id="evtUrlThumb_' + idx + '" style="display:none;">'
    + '<img id="evtUrlThumbImg_' + idx + '" src="" alt="" onerror="this.src=\'https://placehold.co/60x45/dc2626/fff?text=Error\'">'
    + '<span class="url-thumb-status"><i class="fas fa-check-circle"></i></span>'
    + '</div>';
  container.appendChild(row);
  row.querySelector('input[type="url"]').focus();
}

function evtRemoveUrlRow(idx) {
  var row = document.getElementById('evtUrlRow_' + idx);
  if (row) row.remove();
}

function evtPreviewUrlRow(idx, rawUrl) {
  clearTimeout(_evtUrlTimers[idx]);
  var wrap = document.getElementById('evtUrlThumb_' + idx);
  var img  = document.getElementById('evtUrlThumbImg_' + idx);
  if (!wrap || !img) return;
  if (!rawUrl || !rawUrl.match(/^https?:\/\/.+/i)) { wrap.style.display = 'none'; return; }
  _evtUrlTimers[idx] = setTimeout(function() {
    var id = evtExtractGdriveId(rawUrl);
    var url = id ? 'https://lh3.googleusercontent.com/d/' + id : rawUrl;
    img.onload = function() {
      if (img.naturalWidth === 0) { img.src = 'https://placehold.co/60x45/dc2626/fff?text=Not+Public'; }
      wrap.style.display = '';
    };
    img.onerror = function() { img.src = 'https://placehold.co/60x45/dc2626/fff?text=Error'; wrap.style.display = ''; };
    img.src = url;
  }, 500);
}

function confirmDeleteEvent(id, name) {
  Swal.fire({
    title: 'Delete Event?',
    html: `Are you sure you want to delete <strong>${name}</strong>?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b8c73',
    confirmButtonText: 'Yes, Delete'
  }).then(r => {
    if (r.isConfirmed) window.location.href = 'events.php?delete=' + id;
  });
}
</script>

</body>