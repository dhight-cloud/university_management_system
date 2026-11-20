//University Management System (students.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    $db->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
    flashMsg('success', 'Student record deleted.');
    header('Location: students.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

$where = ['1=1'];
$params = [];
if ($search) {
    $where[] = '(s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)';
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($status) {
    $where[] = 's.enrollment_status = ?';
    $params[] = $status;
}
$whereStr = implode(' AND ', $where);

$students = $db->prepare("SELECT s.*, d.title AS degree_title FROM students s LEFT JOIN degrees d ON s.degree_id=d.id WHERE $whereStr ORDER BY s.last_name, s.first_name");
$students->execute($params);
$students = $students->fetchAll();

$pageTitle = 'All Students';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Students</li></ol></nav>
    <h1><i class="bi bi-people-fill me-2 text-warning"></i>Student Directory</h1>
  </div>
</div>
<div class="container py-4">
  <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <form class="d-flex gap-2 flex-grow-1" method="GET">
      <input type="text" name="q" class="form-control" placeholder="Search name, ID, email…" value="<?= sanitize($search) ?>">
      <select name="status" class="form-select" style="max-width:160px;">
        <option value="">All Status</option>
        <option value="Active" <?= $status==='Active'?'selected':'' ?>>Active</option>
        <option value="Graduated" <?= $status==='Graduated'?'selected':'' ?>>Graduated</option>
        <option value="Inactive" <?= $status==='Inactive'?'selected':'' ?>>Inactive</option>
        <option value="On Leave" <?= $status==='On Leave'?'selected':'' ?>>On Leave</option>
      </select>
      <button type="submit" class="btn btn-vs px-3">Search</button>
      <?php if ($search || $status): ?><a href="students.php" class="btn btn-outline-secondary px-3">Clear</a><?php endif; ?>
    </form>
    <a href="student_add.php" class="btn btn-vs-gold px-3"><i class="bi bi-person-plus me-1"></i>Add Student</a>
  </div>

  <div class="vs-card overflow-hidden">
    <div class="card-header-vs d-flex justify-content-between align-items-center">
      <span><i class="bi bi-table me-2"></i>Students</span>
      <span class="badge bg-secondary"><?= count($students) ?> records</span>
    </div>
    <div class="table-responsive">
      <table class="vs-table">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Age</th><th>Major</th><th>GPA</th><th>Credits</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
          <tr class="searchable-row">
            <td><code><?= sanitize($s['student_id']) ?></code></td>
            <td class="fw-600"><?= sanitize($s['first_name'] . ' ' . $s['last_name']) ?></td>
            <td><?= calcAge($s['date_of_birth']) ?></td>
            <td class="small text-muted"><?= sanitize(mb_substr($s['degree_title'] ?? 'Undeclared', 0, 28)) ?></td>
            <td><?= number_format($s['gpa'], 2) ?></td>
            <td><?= $s['credits_earned'] ?></td>
            <td>
              <span class="badge <?= $s['enrollment_status']==='Active'?'bg-success':($s['enrollment_status']==='Graduated'?'bg-primary':'bg-secondary') ?>">
                <?= sanitize($s['enrollment_status']) ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="student_view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2">View</a>
                <a href="student_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Edit</a>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-confirm="Delete <?= sanitize($s['first_name']) ?>'s record? This cannot be undone.">Del</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($students)): ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No students found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
