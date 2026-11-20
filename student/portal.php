//University Management System (portal.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/../includes/config.php';
requireStudent();
$db = getDB();

$student = $db->prepare('SELECT s.*, d.title AS degree_title, d.level AS degree_level, d.credits_required FROM students s LEFT JOIN degrees d ON s.degree_id=d.id WHERE s.id=?');
$student->execute([$_SESSION['student_id']]);
$student = $student->fetch();

$age = calcAge($student['date_of_birth']);

$current = $db->prepare('
    SELECT e.*, cs.schedule_days, cs.schedule_time, cs.location, cs.delivery,
        c.course_code, c.title AS course_title, c.credits,
        sem.name AS semester_name
    FROM enrollments e
    JOIN class_sections cs ON e.section_id=cs.id
    JOIN courses c ON cs.course_id=c.id
    JOIN semesters sem ON cs.semester_id=sem.id
    WHERE e.student_id=? AND e.status="Enrolled"
    ORDER BY sem.start_date DESC, c.course_code
');
$current->execute([$_SESSION['student_id']]);
$currentClasses = $current->fetchAll();

$completed = $db->prepare('
    SELECT e.grade, e.grade_points, c.course_code, c.title AS course_title, c.credits,
        sem.name AS semester_name
    FROM enrollments e
    JOIN class_sections cs ON e.section_id=cs.id
    JOIN courses c ON cs.course_id=c.id
    JOIN semesters sem ON cs.semester_id=sem.id
    WHERE e.student_id=? AND e.status="Completed"
    ORDER BY sem.start_date DESC
');
$completed->execute([$_SESSION['student_id']]);
$completedClasses = $completed->fetchAll();

$waitlisted = $db->prepare('
    SELECT w.position, w.requested_at, c.course_code, c.title AS course_title,
        cs.schedule_days, cs.schedule_time, sem.name AS semester_name
    FROM waitlist w
    JOIN class_sections cs ON w.section_id=cs.id
    JOIN courses c ON cs.course_id=c.id
    JOIN semesters sem ON cs.semester_id=sem.id
    WHERE w.student_id=? AND w.status="Waiting"
    ORDER BY w.position
');
$waitlisted->execute([$_SESSION['student_id']]);
$waitlistItems = $waitlisted->fetchAll();

$pageTitle = 'My Portal';
include '../includes/header.php';
?>
<nav class="navbar navbar-expand-lg site-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>/">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="brand-text"><?= SITE_SHORT ?></span>
        </a>
        <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item me-2 text-white-50 small"><i class="bi bi-person-circle me-1"></i><?= sanitize($student['first_name']) ?></li>
            <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/register.php"><i class="bi bi-pencil-square me-1"></i>Register</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/logout.php">Logout</a></li>
        </ul>
        </div>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active">My Portal</li></ol></nav>
        <h1><i class="bi bi-person-circle me-2 text-warning"></i>Student Portal</h1>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">

        <div class="col-lg-4">
        <div class="vs-card p-0 overflow-hidden mb-4">
            <div style="background:linear-gradient(135deg,var(--vs-navy),#2d4a6e);padding:2rem;text-align:center;">
            <div class="profile-avatar mx-auto mb-3">
                <?= strtoupper(substr($student['first_name'],0,1) . substr($student['last_name'],0,1)) ?>
            </div>
            <h4 class="text-white mb-0" style="font-family:var(--font-serif);"><?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?></h4>
            <p class="text-warning small mb-0"><?= sanitize($student['student_id']) ?></p>
            </div>
            <div class="p-3">
            <div class="row g-3 mb-3">
                <div class="col-6 text-center border-end">
                <div class="gpa-ring mx-auto mb-1" data-gpa="<?= $student['gpa'] ?>">
                    <div class="gpa-val"><?= number_format($student['gpa'],2) ?></div>
                </div>
                <small class="text-muted d-block">GPA</small>
                </div>
                <div class="col-6 text-center">
                <div style="font-family:var(--font-serif);font-size:1.8rem;font-weight:700;color:var(--vs-navy);line-height:1;"><?= $student['credits_earned'] ?></div>
                <small class="text-muted d-block">Credits Earned</small>
                </div>
            </div>
            <table class="table table-sm table-borderless mb-0 small">
                <tr><td class="text-muted fw-500 pe-2">Age</td><td class="fw-600"><?= $age ?> yrs</td></tr>
                <tr><td class="text-muted fw-500 pe-2">DOB</td><td class="fw-600"><?= date('M j, Y', strtotime($student['date_of_birth'])) ?></td></tr>
                <tr><td class="text-muted fw-500 pe-2">Major</td><td class="fw-600"><?= sanitize($student['degree_title'] ?? 'Undeclared') ?></td></tr>
                <tr><td class="text-muted fw-500 pe-2">Level</td><td class="fw-600"><?= sanitize($student['degree_level'] ?? '—') ?></td></tr>
                <tr><td class="text-muted fw-500 pe-2">Status</td>
                <td><span class="badge bg-success"><?= sanitize($student['enrollment_status']) ?></span></td>
                </tr>
                <tr><td class="text-muted fw-500 pe-2">Progress</td>
                <td>
                    <?php $pct = $student['credits_required'] > 0 ? round($student['credits_earned']/$student['credits_required']*100) : 0; ?>
                    <div style="font-size:.8rem;"><?= $student['credits_earned'] ?>/<?= $student['credits_required'] ?> credits</div>
                    <div class="capacity-bar mt-1"><div class="capacity-fill" style="width:<?= $pct ?>%;background:var(--vs-gold);"></div></div>
                </td>
                </tr>
            </table>
            </div>
        </div>

        <div class="vs-card p-3 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-lines-fill me-2 text-gold"></i>Contact Information</h6>
            <div class="small">
            <p class="mb-1"><i class="bi bi-envelope me-2 text-muted"></i><?= sanitize($student['email']) ?></p>
            <p class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i><?= sanitize($student['phone'] ?? 'Not provided') ?></p>
            <p class="mb-0"><i class="bi bi-geo-alt me-2 text-muted"></i>
                <?= sanitize($student['address'] ?? '') ?>
                <?php if ($student['city']): ?>, <?= sanitize($student['city']) ?><?php endif; ?>
                <?php if ($student['state']): ?>, <?= sanitize($student['state']) ?><?php endif; ?>
                <?php if ($student['zip']): ?> <?= sanitize($student['zip']) ?><?php endif; ?>
            </p>
            </div>
        </div>

        <?php if ($waitlistItems): ?>
        <div class="vs-card p-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-list-ol me-2 text-gold"></i>Waitlist Positions</h6>
            <?php foreach ($waitlistItems as $w): ?>
            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
            <div class="waitlist-pos"><?= $w['position'] ?></div>
            <div class="small">
                <div class="fw-600"><?= sanitize($w['course_code']) ?> — <?= sanitize($w['course_title']) ?></div>
                <div class="text-muted"><?= sanitize($w['semester_name']) ?></div>
            </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mt-3 text-center">
            <a href="<?= SITE_URL ?>/student/register.php" class="btn btn-vs-gold w-100 py-2">
            <i class="bi bi-plus-circle me-2"></i>Register for Classes
            </a>
        </div>
        </div>

        <div class="col-lg-8">

        <div class="vs-card mb-4 overflow-hidden">
            <div class="card-header-vs d-flex align-items-center justify-content-between">
            <span><i class="bi bi-calendar3 me-2"></i>Currently Enrolled</span>
            <span class="badge bg-warning text-dark"><?= count($currentClasses) ?> classes</span>
            </div>
            <div class="p-3">
            <?php if ($currentClasses): ?>
            <div class="table-responsive">
                <table class="vs-table">
                <thead><tr><th>Course</th><th>Title</th><th>Schedule</th><th>Location</th><th>Credits</th></tr></thead>
                <tbody>
                    <?php foreach ($currentClasses as $cls): ?>
                    <tr>
                    <td><span class="fw-bold text-navy"><?= sanitize($cls['course_code']) ?></span></td>
                    <td><?= sanitize($cls['course_title']) ?></td>
                    <td class="small"><?= sanitize($cls['schedule_days'] ?? '') ?> <?= sanitize($cls['schedule_time'] ?? '') ?></td>
                    <td class="small">
                        <?php if ($cls['delivery'] === 'Online'): ?>
                        <span class="badge badge-delivery-online">Online</span>
                        <?php elseif ($cls['delivery'] === 'Hybrid'): ?>
                        <span class="badge badge-delivery-hybrid">Hybrid</span>
                        <?php else: ?>
                        <?= sanitize($cls['location']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $cls['credits'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                No current enrollments. <a href="<?= SITE_URL ?>/student/register.php">Register for classes</a>
            </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="vs-card overflow-hidden">
            <div class="card-header-vs d-flex align-items-center justify-content-between">
            <span><i class="bi bi-patch-check me-2"></i>Completed Courses</span>
            <span class="badge bg-success"><?= count($completedClasses) ?> completed</span>
            </div>
            <div class="p-3">
            <?php if ($completedClasses): ?>
            <div class="table-responsive">
                <table class="vs-table">
                <thead><tr><th>Course</th><th>Title</th><th>Semester</th><th>Credits</th><th>Grade</th></tr></thead>
                <tbody>
                    <?php foreach ($completedClasses as $cls): ?>
                    <tr>
                    <td><span class="fw-bold text-navy"><?= sanitize($cls['course_code']) ?></span></td>
                    <td><?= sanitize($cls['course_title']) ?></td>
                    <td class="small text-muted"><?= sanitize($cls['semester_name']) ?></td>
                    <td class="text-center"><?= $cls['credits'] ?></td>
                    <td class="text-center fw-bold"><?= formatGrade($cls['grade']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-hourglass fs-2 d-block mb-2"></i>No completed courses yet.
            </div>
            <?php endif; ?>
            </div>
        </div>

        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
