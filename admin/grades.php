//University Management System (grades.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_grade') {
    $enrollId   = (int)$_POST['enrollment_id'];
    $grade      = strtoupper(trim($_POST['grade'] ?? ''));
    $gradeMap   = constant('GRADE_POINTS');
    $gradePoints = array_key_exists($grade, $gradeMap) ? $gradeMap[$grade] : null;
    $status     = in_array($grade, ['W','I']) ? 'Enrolled' : 'Completed';

    $db->prepare('UPDATE enrollments SET grade=?, grade_points=?, status=? WHERE id=?')
      ->execute([$grade ?: null, $gradePoints, $grade ? $status : 'Enrolled', $enrollId]);

    $sidStmt = $db->prepare('SELECT student_id FROM enrollments WHERE id=?');
    $sidStmt->execute([$enrollId]);
    $sid = $sidStmt->fetchColumn();
    $gpaStmt = $db->prepare('SELECT AVG(grade_points) FROM enrollments WHERE student_id=? AND grade_points IS NOT NULL AND status="Completed"');
    $gpaStmt->execute([$sid]);
    $newGpa = round((float)$gpaStmt->fetchColumn(), 2);

    $credStmt = $db->prepare('SELECT SUM(c.credits) FROM enrollments e JOIN class_sections cs ON e.section_id=cs.id JOIN courses c ON cs.course_id=c.id WHERE e.student_id=? AND e.status="Completed"');
    $credStmt->execute([$sid]);
    $totalCredits = (int)$credStmt->fetchColumn();

    $db->prepare('UPDATE students SET gpa=?, credits_earned=? WHERE id=?')->execute([$newGpa, $totalCredits, $sid]);
    flashMsg('success', 'Grade updated and GPA recalculated.');
    header('Location: grades.php' . (isset($_GET['student_id']) ? '?student_id='.(int)$_GET['student_id'] : '')); exit;
}

$filterStudentId = (int)($_GET['student_id'] ?? 0);
$filterSemester  = (int)($_GET['semester_id'] ?? 0);

$semesters = $db->query('SELECT * FROM semesters ORDER BY start_date DESC')->fetchAll();
$students  = $db->query('SELECT id, student_id, first_name, last_name FROM students WHERE enrollment_status="Active" ORDER BY last_name')->fetchAll();

$where  = ["e.status IN ('Enrolled','Completed')"];
$params = [];
if ($filterStudentId) { $where[] = 'e.student_id=?'; $params[] = $filterStudentId; }
if ($filterSemester)  { $where[] = 'cs.semester_id=?'; $params[] = $filterSemester; }
$whereStr = implode(' AND ', $where);

$enrollments = $db->prepare("
    SELECT e.id AS enrollment_id, e.status, e.grade,
          s.id AS student_db_id, s.student_id AS student_sid, s.first_name, s.last_name,
          c.course_code, c.title AS course_title, c.credits,
          sem.name AS semester_name, sem.id AS semester_id
    FROM enrollments e
    JOIN students s ON e.student_id=s.id
    JOIN class_sections cs ON e.section_id=cs.id
    JOIN courses c ON cs.course_id=c.id
    JOIN semesters sem ON cs.semester_id=sem.id
    WHERE $whereStr
    ORDER BY s.last_name, sem.start_date DESC, c.course_code
");
$enrollments->execute($params);
$enrollments = $enrollments->fetchAll();

$validGrades = ['A','A-','B+','B','B-','C+','C','C-','D+','D','D-','F','W','I','P'];

$pageTitle = 'Update Grades';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Grades</li></ol></nav>
    <h1><i class="bi bi-pencil-fill me-2 text-warning"></i>Grade Management</h1>
  </div>
</div>
<div class="container py-4">
  <div class="vs-card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Filter by Student</label>
        <select name="student_id" class="form-select">
          <option value="">— All Students —</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $filterStudentId === $s['id'] ? 'selected' : '' ?>><?= sanitize($s['student_id'] . ' — ' . $s['last_name'] . ', ' . $s['first_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Filter by Semester</label>
        <select name="semester_id" class="form-select">
          <option value="">— All Semesters —</option>
          <?php foreach ($semesters as $sem): ?>
            <option value="<?= $sem['id'] ?>" <?= $filterSemester === (int)$sem['id'] ? 'selected' : '' ?>><?= sanitize($sem['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-vs px-4">Apply Filters</button>
        <a href="grades.php" class="btn btn-outline-secondary">Clear</a>
      </div>
    </form>
  </div>

  <div class="vs-card overflow-hidden">
    <div class="card-header-vs d-flex justify-content-between">
      <span><i class="bi bi-table me-2"></i>Enrollment Records</span>
      <span class="badge bg-secondary"><?= count($enrollments) ?> records</span>
    </div>
    <div class="table-responsive">
      <table class="vs-table">
        <thead>
          <tr><th>Student</th><th>Course</th><th>Semester</th><th>Credits</th><th>Status</th><th>Grade</th><th>Update</th></tr>
        </thead>
        <tbody>
          <?php foreach ($enrollments as $en): ?>
          <tr>
            <td>
              <div class="fw-600"><?= sanitize($en['first_name'].' '.$en['last_name']) ?></div>
              <small class="text-muted"><?= sanitize($en['student_sid']) ?></small>
            </td>
            <td>
              <div class="fw-600"><?= sanitize($en['course_code']) ?></div>
              <small class="text-muted"><?= sanitize($en['course_title']) ?></small>
            </td>
            <td class="small"><?= sanitize($en['semester_name']) ?></td>
            <td class="text-center"><?= $en['credits'] ?></td>
            <td><span class="badge badge-status-<?= strtolower($en['status']) ?>"><?= sanitize($en['status']) ?></span></td>
            <td class="text-center fw-bold"><?= formatGrade($en['grade']) ?></td>
            <td>
              <form method="POST" class="d-flex gap-1 align-items-center">
                <input type="hidden" name="action" value="update_grade">
                <input type="hidden" name="enrollment_id" value="<?= $en['enrollment_id'] ?>">
                <select name="grade" id="gradeSelect" class="form-select form-select-sm" style="width:85px;">
                  <option value="">—</option>
                  <?php foreach ($validGrades as $g): ?>
                    <option value="<?= $g ?>" <?= $en['grade'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-vs-gold rounded-pill px-2">Save</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($enrollments)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted">No enrollment records match the selected filters.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="mt-2 small text-muted">
    <strong>Grade key:</strong> A=4.0 | A-=3.7 | B+=3.3 | B=3.0 | B-=2.7 | C+=2.3 | C=2.0 | C-=1.7 | D=1.0 | F=0.0 | W=Withdrawn | I=Incomplete | P=Pass &mdash; GPA recalculates automatically on save.
  </div>
</div>
<?php include '../includes/footer.php'; ?>
