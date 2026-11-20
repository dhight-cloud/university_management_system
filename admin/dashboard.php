//University Management System (dashboard.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$totalStudents  = $db->query('SELECT COUNT(*) FROM students WHERE enrollment_status="Active"')->fetchColumn();
$totalSections  = $db->query('SELECT COUNT(*) FROM class_sections WHERE active=1')->fetchColumn();
$totalWaitlist  = $db->query('SELECT COUNT(*) FROM waitlist WHERE status="Waiting"')->fetchColumn();
$pendingApps    = $db->query("SELECT COUNT(*) FROM students WHERE enrollment_status=\"Pending\"")->fetchColumn();
$fullSections   = $db->query('SELECT COUNT(*) FROM class_sections WHERE enrolled>=capacity AND active=1')->fetchColumn();

$recentStudents = $db->query('SELECT s.*, d.title AS degree_title FROM students s LEFT JOIN degrees d ON s.degree_id=d.id ORDER BY s.created_at DESC LIMIT 5')->fetchAll();

$hotSections = $db->query('
    SELECT cs.*, c.course_code, c.title AS course_title, sem.name AS semester_name
    FROM class_sections cs JOIN courses c ON cs.course_id=c.id JOIN semesters sem ON cs.semester_id=sem.id
    WHERE cs.waitlist_count>0 AND cs.active=1 ORDER BY cs.waitlist_count DESC LIMIT 5
')->fetchAll();

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-header">
  <div class="container">
    <h1><i class="bi bi-speedometer2 me-2 text-warning"></i>Admin Dashboard</h1>
    <p class="text-white-50 mb-0">Welcome back, <strong class="text-white"><?= sanitize($_SESSION['admin_name']) ?></strong> &mdash; <?= date('l, F j, Y') ?></p>
  </div>
</div>

<div class="container py-4">
  <!-- Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-widget">
        <div class="stat-icon stat-icon-blue"><i class="bi bi-people-fill"></i></div>
        <div><div class="stat-num"><?= $totalStudents ?></div><div class="stat-lbl">Active Students</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-widget">
        <div class="stat-icon stat-icon-green"><i class="bi bi-journal-bookmark-fill"></i></div>
        <div><div class="stat-num"><?= $totalSections ?></div><div class="stat-lbl">Active Sections</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-widget">
        <div class="stat-icon stat-icon-gold"><i class="bi bi-list-ol"></i></div>
        <div><div class="stat-num"><?= $totalWaitlist ?></div><div class="stat-lbl">Waitlisted Students</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-widget">
        <div class="stat-icon stat-icon-red"><i class="bi bi-door-closed-fill"></i></div>
        <div><div class="stat-num"><?= $fullSections ?></div><div class="stat-lbl">Full Sections</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <a href="applications.php" class="text-decoration-none">
        <div class="stat-widget" style="border:2px solid <?php echo $pendingApps > 0 ? 'var(--vs-gold)' : 'var(--vs-border)'; ?>">
          <div class="stat-icon stat-icon-gold"><i class="bi bi-person-check-fill"></i></div>
          <div>
            <div class="stat-num" style="color:<?php echo $pendingApps > 0 ? 'var(--vs-gold)' : 'inherit'; ?>"><?= $pendingApps ?></div>
            <div class="stat-lbl">Pending Applications</div>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="vs-card overflow-hidden">
        <div class="card-header-vs d-flex align-items-center justify-content-between">
          <span><i class="bi bi-person-lines-fill me-2"></i>Recently Added Students</span>
          <a href="students.php" class="btn btn-sm btn-outline-warning rounded-pill px-3">View All</a>
        </div>
        <div class="table-responsive">
          <table class="vs-table">
            <thead><tr><th>Student ID</th><th>Name</th><th>Major</th><th>GPA</th><th>Status</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($recentStudents as $s): ?>
              <tr>
                <td><code class="text-navy"><?= sanitize($s['student_id']) ?></code></td>
                <td class="fw-600"><?= sanitize($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td class="small text-muted"><?= sanitize(mb_substr($s['degree_title'] ?? 'Undeclared', 0, 30)) ?></td>
                <td><?= number_format($s['gpa'], 2) ?></td>
                <td><span class="badge bg-success"><?= sanitize($s['enrollment_status']) ?></span></td>
                <td><a href="student_view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2">View</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="vs-card p-3 mb-3">
        <h6 class="fw-bold mb-3"><i class="bi bi-lightning-fill text-gold me-2"></i>Quick Actions</h6>
        <div class="d-grid gap-2">
          <?php if ($pendingApps > 0): ?>
          <a href="applications.php" class="btn btn-warning text-dark fw-bold text-start"><i class="bi bi-person-check me-2"></i>Review Applications <span class="badge bg-dark ms-1"><?= $pendingApps ?></span></a>
          <?php endif; ?>
          <a href="student_add.php"  class="btn btn-vs text-start"><i class="bi bi-person-plus me-2"></i>Add New Student</a>
          <a href="course_add.php"   class="btn btn-vs text-start"><i class="bi bi-plus-circle me-2"></i>Add New Course</a>
          <a href="section_add.php"  class="btn btn-vs text-start"><i class="bi bi-calendar-plus me-2"></i>Add Class Section</a>
          <a href="grades.php"       class="btn btn-vs text-start"><i class="bi bi-pencil-fill me-2"></i>Update Grades</a>
          <a href="events.php"       class="btn btn-vs text-start"><i class="bi bi-calendar-event me-2"></i>Manage Events</a>
        </div>
      </div>

      <?php if ($hotSections): ?>
      <div class="vs-card overflow-hidden">
        <div class="card-header-vs"><i class="bi bi-fire me-2"></i>High Demand Sections</div>
        <div class="p-3">
          <?php foreach ($hotSections as $hs): ?>
          <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
            <div class="small">
              <div class="fw-600"><?= sanitize($hs['course_code']) ?> — <?= sanitize(mb_substr($hs['course_title'],0,25)) ?>…</div>
              <div class="text-muted"><?= sanitize($hs['semester_name']) ?></div>
            </div>
            <span class="badge bg-warning text-dark"><?= $hs['waitlist_count'] ?> waiting</span>
          </div>
          <?php endforeach; ?>
          <a href="waitlist.php" class="btn btn-vs-outline btn-sm w-100 mt-1">Manage Waitlist</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
