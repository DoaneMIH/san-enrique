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
    $image       = sanitize($_POST['image'] ?? '');
    $status      = sanitize($_POST['status'] ?? 'active');

    if ($_POST['form_action'] === 'add') {
        $sql = "INSERT INTO events (title, description, event_date, end_date, location, latitude, longitude, image, status) VALUES ('$title','$description','$event_date','$end_date','$location',$lat,$lng,'$image','$status')";
        if ($db->query($sql)) { $message = "Event \"$title\" added successfully!"; $action = 'list'; }
        else { $error = 'Failed to add event: ' . $db->error; }
    } elseif ($_POST['form_action'] === 'edit') {
        $id = (int)$_POST['event_id'];
        $sql = "UPDATE events SET title='$title', description='$description', event_date='$event_date', end_date='$end_date', location='$location', latitude=$lat, longitude=$lng, image='$image', status='$status' WHERE id=$id";
        if ($db->query($sql)) { $message = "Event updated successfully!"; $action = 'list'; }
        else { $error = 'Failed to update: ' . $db->error; }
    }
}

if (isset($_GET['delete'])) {
    $db->query("DELETE FROM events WHERE id=" . (int)$_GET['delete']);
    $message = 'Event deleted.'; $action = 'list';
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
                  <?php if ($ev['image']): ?>
                  <img src="<?= htmlspecialchars($ev['image']) ?>" class="listing-thumb" alt="" onerror="this.style.display='none'">
                  <?php else: ?>
                  <div class="event-thumb-placeholder">
                    <i class="fas fa-calendar"></i>
                  </div>
                  <?php endif; ?>
                  <div>
                    <div class="event-title-text"><?= htmlspecialchars($ev['title']) ?></div>
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
      <form method="POST" action="">
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

            <div class="col-md-6">
              <label class="admin-label">Latitude (GPS)</label>
              <input type="number" name="latitude" class="admin-input" step="any"
                     value="<?= htmlspecialchars($editEvent['latitude'] ?? '10.9178') ?>">
            </div>
            <div class="col-md-6">
              <label class="admin-label">Longitude (GPS)</label>
              <input type="number" name="longitude" class="admin-input" step="any"
                     value="<?= htmlspecialchars($editEvent['longitude'] ?? '122.8845') ?>">
            </div>

            <div class="col-12">
              <label class="admin-label">Event Image URL (optional)</label>
              <input type="url" name="image" class="admin-input" id="evtImgUrl"
                     value="<?= htmlspecialchars($editEvent['image'] ?? '') ?>"
                     placeholder="https://example.com/event-image.jpg">
              <img src="<?= htmlspecialchars($editEvent['image'] ?? '') ?>" id="evtImgPreview"
                   style="margin-top:8px;height:80px;border-radius:8px;object-fit:cover;<?= ($editEvent['image'] ?? '') ? '' : 'display:none;' ?>" alt="Preview">
            </div>

            <div class="col-md-6">
              <label class="admin-label">Status</label>
              <select name="status" class="admin-input">
                <option value="active" <?= ($editEvent['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($editEvent['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
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

// Image preview
const evtImg = document.getElementById('evtImgUrl');
if (evtImg) evtImg.addEventListener('input', function() {
  const p = document.getElementById('evtImgPreview');
  if (this.value) { p.src = this.value; p.style.display = 'block'; }
  else p.style.display = 'none';
});

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