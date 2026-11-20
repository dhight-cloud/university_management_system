//University Management System (waitlist.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $sectionId = (int)($_POST['section_id'] ?? 0);

    if ($action === 'promote' && $id) {
        $entry = $db->prepare('SELECT * FROM waitlist WHERE id=?');
        $entry->execute([$id]);
        $entry = $entry->fetch();
        if ($entry) {
            $ins = $db->prepare('INSERT IGNORE INTO enrollments (student_id, section_id, status) VALUES (?,?,"Enrolled")');
            $ins->execute([$entry['student_id'], $entry['section_id']]);
            $db->prepare('UPDATE waitlist SET status="Enrolled" WHERE id=?')->execute([$id]);
            $db->prepare('UPDATE class_sections SET enrolled=enrolled+1, waitlist_count=GREATEST(waitlist_count-1,0) WHERE id=?')->execute([$entry['section_id']]);
            flashMsg('success', 'Student promoted from waitlist and enrolled.');
        }
    } elseif ($action === 'remove' && $id) {
        $wl = $db->prepare('SELECT * FROM waitlist WHERE id=?');
        $wl->execute([$id]);
        $wl = $wl->fetch();
        if ($wl) {
            $db->prepare('UPDATE waitlist SET status="Cancelled" WHERE id=?')->execute([$id]);
            $db->prepare('UPDATE class_sections SET waitlist_count=GREATEST(waitlist_count-1,0) WHERE id=?')->execute([$wl['section_id']]);
            flashMsg('success', 'Removed from waitlist.');
        }
    } elseif ($action === 'promote_next' && $sectionId) {
        promoteFromWaitlist($sectionId);
        flashMsg('success', 'Next student in queue (FIFO) has been promoted.');
    }
    header('Location: waitlist.php'); exit;
}

$waitlist = $db->query('
    SELECT w.*, s.student_id AS student_sid, s.first_name, s.last_name, s.email,
          c.course_code, c.title AS course_title,
          cs.section_number, cs.capacity, cs.enrolled,
          sem.name AS semester_name
    FROM waitlist w
    JOIN students s ON w.student_id=s.id
    JOIN class_sections cs ON w.section_id=cs.id
    JOIN courses c ON cs.course_id=c.id
    JOIN semesters sem ON cs.semester_id=sem.id
    WHERE w.status="Waiting"
    ORDER BY w.section_id ASC, w.position ASC
')->fetchAll();

$bySection = [];
foreach ($waitlist as $entry) {
    $key = $entry['section_id'] ?? 0;
    $bySection[$key][] = $entry;
}

$pageTitle = 'Waitlist Management';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Waitlist</li></ol></nav>
    <h1><i class="bi bi-list-ol me-2 text-warning"></i>Waitlist Management (FIFO)</h1>
  </div>
</div>
<div class="container py-4">
  <div class="alert alert-info d-flex align-items-start gap-2">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>The waitlist uses <strong>First In, First Out (FIFO)</strong> ordering. Students are enrolled in the order they joined the waitlist. Use "Promote Next" to automatically enroll the next student in queue, or use "Promote" to manually advance a specific student.</div>
  </div>

  <?php if (empty($bySection)): ?>
    <div class="vs-card p-4 text-center text-muted"><i class="bi bi-check-circle-fill fs-2 text-success d-block mb-2"></i>No students are currently on any waitlist.</div>
  <?php else: ?>
    <?php foreach ($bySection as $sectionId => $entries): ?>
      <?php $first = $entries[0]; ?>
      <div class="vs-card overflow-hidden mb-4">
        <div class="card-header-vs d-flex align-items-center justify-content-between">
          <div>
            <strong><?= sanitize($first['course_code']) ?></strong> — <?= sanitize($first['course_title']) ?>
            &nbsp;<span class="badge bg-secondary">Sec <?= sanitize($first['section_number']) ?></span>
            &nbsp;<small class="opacity-75"><?= sanitize($first['semester_name']) ?></small>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark"><?= count($entries) ?> waiting</span>
            <span class="badge bg-light text-dark"><?= $first['enrolled'] ?>/<?= $first['capacity'] ?> enrolled</span>
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="promote_next">
              <input type="hidden" name="section_id" value="<?= $sectionId ?>">
              <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" <?= $first['enrolled'] >= $first['capacity'] ? '' : '' ?>><i class="bi bi-arrow-up-circle me-1"></i>Promote Next (FIFO)</button>
            </form>
          </div>
        </div>
        <div class="table-responsive">
          <table class="vs-table">
            <thead><tr><th>Position</th><th>Student</th><th>Email</th><th>Requested</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($entries as $e): ?>
              <tr>
                <td><div class="waitlist-pos"><?= $e['position'] ?></div></td>
                <td>
                  <div class="fw-600"><?= sanitize($e['first_name'].' '.$e['last_name']) ?></div>
                  <small class="text-muted"><?= sanitize($e['student_sid']) ?></small>
                </td>
                <td class="small"><?= sanitize($e['email']) ?></td>
                <td class="small text-muted"><?= date('M j, Y g:i A', strtotime($e['requested_at'])) ?></td>
                <td>
                  <div class="d-flex gap-1">
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="promote">
                      <input type="hidden" name="id" value="<?= $e['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2">Promote</button>
                    </form>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="id" value="<?= $e['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-confirm="Remove <?= sanitize($e['first_name']) ?> from the waitlist?">Remove</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
