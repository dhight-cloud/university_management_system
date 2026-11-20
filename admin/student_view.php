//University Management System (student_view.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$student = $db->prepare('SELECT s.*, d.title AS degree_title, d.level AS degree_level, d.credits_required FROM students s LEFT JOIN degrees d ON s.degree_id=d.id WHERE s.id=?');
$student->execute([$id]);
$student = $student->fetch();
if (!$student) { echo '<p class="m-4 alert alert-danger">Student not found.</p>'; exit; }

$current = $db->prepare('SELECT e.*, c.course_code, c.title AS course_title, c.credits, cs.schedule_days, cs.schedule_time, cs.location, cs.delivery, sem.name AS semester_name FROM enrollments e JOIN class_sections cs ON e.section_id=cs.id JOIN courses c ON cs.course_id=c.id JOIN semesters sem ON cs.semester_id=sem.id WHERE e.student_id=? AND e.status="Enrolled" ORDER BY sem.start_date DESC');
$current->execute([$id]);
$currentClasses = $current->fetchAll();

$completed = $db->prepare('SELECT e.grade, e.grade_points, c.course_code, c.title AS course_title, c.credits, sem.name AS semester_name FROM enrollments e JOIN class_sections cs ON e.section_id=cs.id JOIN courses c ON cs.course_id=c.id JOIN semesters sem ON cs.semester_id=sem.id WHERE e.student_id=? AND e.status="Completed" ORDER BY sem.start_date DESC');
$completed->execute([$id]);
$completedClasses = $completed->fetchAll();

$pageTitle = $student['first_name'] . ' ' . $student['last_name'];
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="students.php">Students</a></li><li class="breadcrumb-item active"><?= sanitize($student['first_name']) ?></li></ol></nav>
    <div class="d-flex align-items-center gap-3">
      <h1><?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?></h1>
      <code class="fs-6 text-warning"><?= sanitize($student['student_id']) ?></code>
    </div>
  </div>
</div>

<div class="container py-4">
  <div class="d-flex gap-2 mb-4">
    <a href="student_edit.php?id=<?= $id ?>" class="btn btn-vs-gold"><i class="bi bi-pencil me-1"></i>Edit Profile</a>
    <a href="grades.php?student_id=<?= $id ?>" class="btn btn-vs"><i class="bi bi-pencil-fill me-1"></i>Update Grades</a>
    <a href="students.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="vs-card p-0 overflow-hidden mb-4">
        <div style="background:linear-gradient(135deg,var(--vs-navy),#2d4a6e);padding:2rem;text-align:center;">
          <div class="profile-avatar mx-auto mb-3"><?= strtoupper(substr($student['first_name'],0,1).substr($student['last_name'],0,1)) ?></div>
          <h4 class="text-white mb-0" style="font-family:var(--font-serif);"><?= sanitize($student['first_name'].' '.$student['last_name']) ?></h4>
          <span class="badge bg-<?= $student['enrollment_status']==='Active'?'success':'secondary' ?> mt-2"><?= sanitize($student['enrollment_status']) ?></span>
        </div>
        <div class="p-3">
          <div class="row g-3 mb-3">
            <div class="col-6 text-center border-end">
              <div class="gpa-ring mx-auto mb-1" data-gpa="<?= $student['gpa'] ?>"><div class="gpa-val"><?= number_format($student['gpa'],2) ?></div></div>
              <small class="text-muted">GPA</small>
            </div>
            <div class="col-6 text-center">
              <div style="font-family:var(--font-serif);font-size:1.8rem;font-weight:700;color:var(--vs-navy);line-height:1;"><?= $student['credits_earned'] ?></div>
              <small class="text-muted">Credits</small>
            </div>
          </div>
          <table class="table table-sm table-borderless mb-0 small">
            <tr><td class="text-muted">Age</td><td class="fw-600"><?= calcAge($student['date_of_birth']) ?> yrs (DOB: <?= date('M j, Y', strtotime($student['date_of_birth'])) ?>)</td></tr>
            <tr><td class="text-muted">Major</td><td class="fw-600"><?= sanitize($student['degree_title'] ?? 'Undeclared') ?></td></tr>
            <tr><td class="text-muted">Level</td><td class="fw-600"><?= sanitize($student['degree_level'] ?? '—') ?></td></tr>
            <tr><td class="text-muted">Email</td><td class="fw-600 text-break"><?= sanitize($student['email']) ?></td></tr>
            <tr><td class="text-muted">Phone</td><td class="fw-600"><?= sanitize($student['phone'] ?? '—') ?></td></tr>
            <tr><td class="text-muted">Address</td><td class="fw-600"><?= sanitize($student['address'] ?? '') ?>, <?= sanitize($student['city'] ?? '') ?>, <?= sanitize($student['state'] ?? '') ?> <?= sanitize($student['zip'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Since</td><td><?= $student['enrolled_date'] ? date('M j, Y', strtotime($student['enrolled_date'])) : '—' ?></td></tr>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="vs-card overflow-hidden mb-4">
        <div class="card-header-vs"><i class="bi bi-calendar3 me-2"></i>Current Enrollments</div>
        <div class="p-3">
          <?php if ($currentClasses): ?>
          <div class="table-responsive">
            <table class="vs-table">
              <thead><tr><th>Code</th><th>Course</th><th>Schedule</th><th>Credits</th></tr></thead>
              <tbody>
                <?php foreach ($currentClasses as $cls): ?>
                <tr>
                  <td class="fw-bold"><?= sanitize($cls['course_code']) ?></td>
                  <td><?= sanitize($cls['course_title']) ?></td>
                  <td class="small"><?= sanitize($cls['schedule_days'].' '.$cls['schedule_time']) ?></td>
                  <td class="text-center"><?= $cls['credits'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?><p class="text-muted small py-2">No current enrollments.</p><?php endif; ?>
        </div>
      </div>

      <div class="vs-card overflow-hidden">
        <div class="card-header-vs"><i class="bi bi-patch-check me-2"></i>Completed Courses</div>
        <div class="p-3">
          <?php if ($completedClasses): ?>
          <div class="table-responsive">
            <table class="vs-table">
              <thead><tr><th>Code</th><th>Course</th><th>Semester</th><th>Credits</th><th>Grade</th></tr></thead>
              <tbody>
                <?php foreach ($completedClasses as $cls): ?>
                <tr>
                  <td class="fw-bold"><?= sanitize($cls['course_code']) ?></td>
                  <td><?= sanitize($cls['course_title']) ?></td>
                  <td class="small text-muted"><?= sanitize($cls['semester_name']) ?></td>
                  <td class="text-center"><?= $cls['credits'] ?></td>
                  <td class="text-center fw-bold"><?= formatGrade($cls['grade']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?><p class="text-muted small py-2">No completed courses on record.</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
