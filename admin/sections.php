//University Management System (sections.php)
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
    if ($action === 'toggle' && $id) {
        $db->prepare('UPDATE class_sections SET active = NOT active WHERE id=?')->execute([$id]);
        flashMsg('success', 'Section status updated.');
    }
    header('Location: sections.php' . (isset($_GET['semester_id']) ? '?semester_id='.(int)$_GET['semester_id'] : '')); exit;
}

$semesterFilter = (int)($_GET['semester_id'] ?? 0);
$semesters = $db->query('SELECT * FROM semesters ORDER BY start_date DESC')->fetchAll();

$where = ['1=1'];
$params = [];
if ($semesterFilter) { $where[] = 'cs.semester_id=?'; $params[] = $semesterFilter; }
$whereStr = implode(' AND ', $where);

$sections = $db->prepare("
    SELECT cs.*, c.course_code, c.title AS course_title, c.credits,
          sem.name AS semester_name
    FROM class_sections cs
    JOIN courses c ON cs.course_id=c.id
    JOIN semesters sem ON cs.semester_id=sem.id
    WHERE $whereStr ORDER BY sem.start_date DESC, c.course_code, cs.section_number
");
$sections->execute($params);
$sections = $sections->fetchAll();

$pageTitle = 'Class Sections';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Sections</li></ol></nav>
    <h1><i class="bi bi-calendar-week me-2 text-warning"></i>Class Sections</h1>
  </div>
</div>
<div class="container py-4">
  <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <form class="d-flex gap-2" method="GET">
      <select name="semester_id" class="form-select" style="max-width:220px;">
        <option value="">— All Semesters —</option>
        <?php foreach ($semesters as $sem): ?><option value="<?= $sem['id'] ?>" <?= $semesterFilter===(int)$sem['id']?'selected':'' ?>><?= sanitize($sem['name']) ?></option><?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-vs px-3">Filter</button>
    </form>
    <a href="section_add.php" class="btn btn-vs-gold ms-auto"><i class="bi bi-plus-circle me-1"></i>Add Section</a>
  </div>

  <div class="vs-card overflow-hidden">
    <div class="card-header-vs d-flex justify-content-between">
      <span><i class="bi bi-table me-2"></i>Sections</span>
      <span class="badge bg-secondary"><?= count($sections) ?></span>
    </div>
    <div class="table-responsive">
      <table class="vs-table">
        <thead><tr><th>Course</th><th>Sec</th><th>Semester</th><th>Instructor</th><th>Schedule</th><th>Delivery</th><th>Capacity</th><th>Waitlist</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($sections as $s):
            $pct = $s['capacity'] > 0 ? ($s['enrolled']/$s['capacity']*100) : 0;
          ?>
          <tr>
            <td><strong><?= sanitize($s['course_code']) ?></strong><br><small class="text-muted"><?= sanitize(mb_substr($s['course_title'],0,28)) ?></small></td>
            <td><?= sanitize($s['section_number']) ?></td>
            <td class="small"><?= sanitize($s['semester_name']) ?></td>
            <td class="small"><?= sanitize($s['instructor_name'] ?? 'TBA') ?></td>
            <td class="small"><?= sanitize($s['schedule_days'].' '.$s['schedule_time']) ?></td>
            <td><span class="badge <?= $s['delivery']==='Online'?'badge-delivery-online':($s['delivery']==='Hybrid'?'badge-delivery-hybrid':'badge-delivery-person') ?>"><?= $s['delivery'] ?></span></td>
            <td style="min-width:100px;" data-enrolled="<?= $s['enrolled'] ?>" data-capacity="<?= $s['capacity'] ?>">
              <?= $s['enrolled'] ?>/<?= $s['capacity'] ?>
              <div class="capacity-bar mt-1"><div class="capacity-fill"></div></div>
            </td>
            <td class="text-center">
              <?php if ($s['waitlist_count'] > 0): ?>
                <span class="badge bg-warning text-dark"><?= $s['waitlist_count'] ?></span>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </td>
            <td><?= $s['active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="section_add.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Edit</a>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <input type="hidden" name="action" value="toggle">
                  <button type="submit" class="btn btn-sm btn-outline-<?= $s['active']?'warning':'success' ?> rounded-pill px-2"><?= $s['active']?'Hide':'Show' ?></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($sections)): ?><tr><td colspan="10" class="text-center py-4 text-muted">No sections found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
