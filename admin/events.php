//University Management System (events.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$editId = (int)($_GET['id'] ?? 0);
$event = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM events WHERE id=?');
    $stmt->execute([$editId]);
    $event = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'delete') {
        $db->prepare('DELETE FROM events WHERE id=?')->execute([(int)$_POST['id']]);
        flashMsg('success', 'Event deleted.');
        header('Location: events.php'); exit;
    } elseif ($action === 'toggle') {
        $db->prepare('UPDATE events SET active=NOT active WHERE id=?')->execute([(int)$_POST['id']]);
        flashMsg('success', 'Event visibility toggled.');
        header('Location: events.php'); exit;
    }

    $fields = [
        'title'       => trim($_POST['title'] ?? ''),
        'event_date'  => trim($_POST['event_date'] ?? ''),
        'event_time'  => trim($_POST['event_time'] ?? ''),
        'location'    => trim($_POST['location'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category'    => $_POST['category'] ?? 'Academic',
        'active'      => (int)isset($_POST['active']),
    ];
    if ($fields['title'] && $fields['event_date']) {
        if ($editId) {
            $db->prepare('UPDATE events SET title=?,event_date=?,event_time=?,location=?,description=?,category=?,active=? WHERE id=?')
              ->execute(array_merge(array_values($fields), [$editId]));
            flashMsg('success', 'Event updated.');
        } else {
            $db->prepare('INSERT INTO events (title,event_date,event_time,location,description,category,active) VALUES (?,?,?,?,?,?,?)')
              ->execute(array_values($fields));
            flashMsg('success', 'Event added.');
        }
        header('Location: events.php'); exit;
    }
}

$events = $db->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();
$categories = ['Academic','Student Life','Sports','Career','Administrative'];

$pageTitle = 'Manage Events';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Events</li></ol></nav>
    <h1><i class="bi bi-calendar-event me-2 text-warning"></i>Event Management</h1>
  </div>
</div>
<div class="container py-4">
  <div class="row g-4">
    <!-- Form -->
    <div class="col-lg-5">
      <div class="vs-card p-4">
        <h5 class="fw-bold mb-3"><?= $editId ? 'Edit Event' : 'Add New Event' ?></h5>
        <form method="POST" action="events.php<?= $editId ? '?id='.$editId : '' ?>">
          <div class="mb-3"><label class="form-label">Event Title *</label><input type="text" name="title" class="form-control" value="<?= sanitize($event['title'] ?? '') ?>" required></div>
          <div class="row g-2 mb-3">
            <div class="col-6"><label class="form-label">Date *</label><input type="date" name="event_date" class="form-control" value="<?= sanitize($event['event_date'] ?? '') ?>" required></div>
            <div class="col-6"><label class="form-label">Time</label><input type="text" name="event_time" class="form-control" placeholder="9:00 AM" value="<?= sanitize($event['event_time'] ?? '') ?>"></div>
          </div>
          <div class="mb-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= sanitize($event['location'] ?? '') ?>"></div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <?php foreach ($categories as $cat): ?><option value="<?= $cat ?>" <?= ($event['category']??'Academic')===$cat?'selected':'' ?>><?= $cat ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= sanitize($event['description'] ?? '') ?></textarea></div>
          <div class="form-check mb-3"><input type="checkbox" name="active" class="form-check-input" id="evtActive" value="1" <?= ($event['active']??1)?'checked':'' ?>><label class="form-check-label fw-600" for="evtActive">Show in ticker &amp; events page</label></div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-vs-gold px-4"><?= $editId?'Update':'Add Event' ?></button>
            <?php if ($editId): ?><a href="events.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- List -->
    <div class="col-lg-7">
      <div class="vs-card overflow-hidden">
        <div class="card-header-vs"><i class="bi bi-calendar3 me-2"></i>All Events</div>
        <div class="table-responsive">
          <table class="vs-table">
            <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Visible</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($events as $ev): ?>
              <tr>
                <td class="small"><?= date('M j, Y', strtotime($ev['event_date'])) ?></td>
                <td class="fw-600 small"><?= sanitize(mb_substr($ev['title'],0,40)) ?></td>
                <td><span class="badge bg-secondary small"><?= sanitize($ev['category']) ?></span></td>
                <td><?= $ev['active'] ? '<span class="text-success"><i class="bi bi-eye-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-eye-slash"></i></span>' ?></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="events.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Edit</a>
                    <form method="POST" class="d-inline"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $ev['id'] ?>"><button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2"><?= $ev['active']?'Hide':'Show' ?></button></form>
                    <form method="POST" class="d-inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $ev['id'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-confirm="Delete this event?">✕</button></form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
