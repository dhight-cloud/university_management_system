//University Management System (courses.php)
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
    $id     = (int)($_POST['id'] ?? 0);
    if ($action === 'toggle' && $id) {
        $db->prepare('UPDATE courses SET active = NOT active WHERE id=?')->execute([$id]);
        flashMsg('success', 'Course status updated.');
    } elseif ($action === 'delete' && $id) {
        $db->prepare('UPDATE courses SET active=0 WHERE id=?')->execute([$id]);
        flashMsg('success', 'Course deactivated.');
    }
    header('Location: courses.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$degreeFilter = (int)($_GET['degree_id'] ?? 0);

$where  = ['1=1'];
$params = [];
if ($search) {
    $where[] = '(c.course_code LIKE ? OR c.title LIKE ?)';
    $params  = array_merge($params, ["%$search%", "%$search%"]);
}
if ($degreeFilter) {
    $where[] = 'c.degree_id=?';
    $params[] = $degreeFilter;
}
$whereStr = implode(' AND ', $where);

$courses = $db->prepare("SELECT c.*, d.title AS degree_title FROM courses c LEFT JOIN degrees d ON c.degree_id=d.id WHERE $whereStr ORDER BY c.course_code");
$courses->execute($params);
$courses = $courses->fetchAll();

$degrees = $db->query('SELECT * FROM degrees WHERE active=1 ORDER BY level, title')->fetchAll();

$pageTitle = 'Course Catalog';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Courses</li></ol></nav>
    <h1><i class="bi bi-journals me-2 text-warning"></i>Course Catalog</h1>
  </div>
</div>
<div class="container py-4">
  <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
    <form class="d-flex gap-2 flex-grow-1" method="GET">
      <input type="text" name="q" class="form-control" placeholder="Search code or title…" value="<?= sanitize($search) ?>" style="max-width:260px;">
      <select name="degree_id" class="form-select" style="max-width:240px;">
        <option value="">— All Programs —</option>
        <?php foreach ($degrees as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $degreeFilter===$d['id']?'selected':'' ?>><?= sanitize($d['title']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-vs px-3">Filter</button>
      <?php if ($search || $degreeFilter): ?><a href="courses.php" class="btn btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <a href="course_add.php" class="btn btn-vs-gold"><i class="bi bi-plus-circle me-1"></i>Add Course</a>
  </div>

  <div class="vs-card overflow-hidden">
    <div class="card-header-vs d-flex justify-content-between">
      <span><i class="bi bi-book me-2"></i>Courses</span>
      <span class="badge bg-secondary"><?= count($courses) ?> courses</span>
    </div>
    <div class="table-responsive">
      <table class="vs-table">
        <thead><tr><th>Code</th><th>Title</th><th>Credits</th><th>Program</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($courses as $c): ?>
          <tr class="searchable-row">
            <td><strong class="text-navy"><?= sanitize($c['course_code']) ?></strong></td>
            <td>
              <?= sanitize($c['title']) ?>
              <?php if ($c['description']): ?><br><small class="text-muted"><?= sanitize(mb_substr($c['description'],0,70)) ?>…</small><?php endif; ?>
            </td>
            <td class="text-center"><?= $c['credits'] ?></td>
            <td class="small text-muted"><?= sanitize($c['degree_title'] ?? 'General Education') ?></td>
            <td><?= $c['active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="course_add.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Edit</a>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-<?= $c['active'] ? 'warning' : 'success' ?> rounded-pill px-2"><?= $c['active'] ? 'Deactivate' : 'Activate' ?></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($courses)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No courses found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
