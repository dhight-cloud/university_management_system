//University Management System (register.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/../includes/config.php';
requireStudent();
$db = getDB();

$studentId = $_SESSION['student_id'];
$msg = '';

// Code for handling add/drop
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $sectionId = (int)($_POST['section_id'] ?? 0);

    if ($action === 'enroll' && $sectionId) {
        // Checks if student already enrolled
        $existing = $db->prepare('SELECT id,status FROM enrollments WHERE student_id=? AND section_id=?');
        $existing->execute([$studentId, $sectionId]);
        $exist = $existing->fetch();

        if ($exist && $exist['status'] === 'Enrolled') {
            flashMsg('warning', 'You are already enrolled in this section.');
        } elseif ($exist && $exist['status'] === 'Dropped') {
            // For re-enrolling
            $sec = $db->prepare('SELECT * FROM class_sections WHERE id=?');
            $sec->execute([$sectionId]);
            $sec = $sec->fetch();
            if ($sec['enrolled'] < $sec['capacity']) {
                $db->prepare('UPDATE enrollments SET status="Enrolled" WHERE id=?')->execute([$exist['id']]);
                $db->prepare('UPDATE class_sections SET enrolled=enrolled+1 WHERE id=?')->execute([$sectionId]);
                flashMsg('success', 'You have re-enrolled in this course.');
            } else {
                addToWaitlist($studentId, $sectionId);
                flashMsg('info', 'Section is full. You have been added to the waitlist (FIFO).');
            }
        } else {
            // For student New enrollment
            $sec = $db->prepare('SELECT * FROM class_sections WHERE id=?');
            $sec->execute([$sectionId]);
            $sec = $sec->fetch();
            if (!$sec) {
                flashMsg('danger', 'Invalid section.');
            } elseif ($sec['enrolled'] >= $sec['capacity']) {
                addToWaitlist($studentId, $sectionId);
                flashMsg('info', 'Section is full. You have been added to the FIFO waitlist.');
            } else {
                $ins = $db->prepare('INSERT INTO enrollments (student_id, section_id, status) VALUES (?,?,"Enrolled")');
                $ins->execute([$studentId, $sectionId]);
                $db->prepare('UPDATE class_sections SET enrolled=enrolled+1 WHERE id=?')->execute([$sectionId]);
                flashMsg('success', 'Successfully enrolled! See you in class.');
            }
        }
    } elseif ($action === 'drop' && $sectionId) {
        $upd = $db->prepare('UPDATE enrollments SET status="Dropped" WHERE student_id=? AND section_id=? AND status="Enrolled"');
        $upd->execute([$studentId, $sectionId]);
        if ($upd->rowCount()) {
            $db->prepare('UPDATE class_sections SET enrolled=enrolled-1 WHERE id=?')->execute([$sectionId]);
            promoteFromWaitlist($sectionId);
            flashMsg('success', 'Course dropped. If a waitlist existed, the next student has been notified.');
        }
    } elseif ($action === 'waitlist_cancel' && $sectionId) {
        $del = $db->prepare('UPDATE waitlist SET status="Cancelled" WHERE student_id=? AND section_id=? AND status="Waiting"');
        $del->execute([$studentId, $sectionId]);
        $db->prepare('UPDATE class_sections SET waitlist_count=waitlist_count-1 WHERE id=?')->execute([$sectionId]);
        flashMsg('success', 'Removed from waitlist.');
    }
    header('Location: ' . SITE_URL . '/student/register.php'); exit;
}

// Query to get upcoming semester
$semester = $db->query('SELECT * FROM semesters WHERE is_upcoming=1 ORDER BY start_date ASC LIMIT 1')->fetch();
if (!$semester) $semester = $db->query('SELECT * FROM semesters WHERE is_current=1 LIMIT 1')->fetch();

// Grabs sections for that semester with enrollment info
$sections = $db->prepare('
    SELECT cs.*, c.course_code, c.title AS course_title, c.credits, c.description AS course_desc,
        d.title AS degree_title,
        e.status AS my_enrollment,
        w.position AS my_waitlist_pos
    FROM class_sections cs
    JOIN courses c ON cs.course_id=c.id
    LEFT JOIN degrees d ON c.degree_id=d.id
    LEFT JOIN enrollments e ON e.section_id=cs.id AND e.student_id=?
    LEFT JOIN waitlist w ON w.section_id=cs.id AND w.student_id=? AND w.status="Waiting"
    WHERE cs.semester_id=? AND cs.active=1
    ORDER BY c.course_code, cs.section_number
');
$sections->execute([$studentId, $studentId, $semester['id'] ?? 0]);
$sections = $sections->fetchAll();

$pageTitle = 'Course Registration';
include '../includes/header.php';
?>
<nav class="navbar navbar-expand-lg site-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>/"><span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span><span class="brand-text"><?= SITE_SHORT ?></span></a>
        <div class="collapse navbar-collapse"><ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/portal.php"><i class="bi bi-house me-1"></i>My Portal</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/logout.php">Logout</a></li>
        </ul></div>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item"><a href="portal.php">Portal</a></li><li class="breadcrumb-item active">Registration</li></ol></nav>
        <h1><i class="bi bi-pencil-square me-2 text-warning"></i>Course Registration</h1>
        <?php if ($semester): ?>
        <p class="text-white-50 mb-0">Registering for: <strong class="text-warning"><?= sanitize($semester['name']) ?></strong>
        &nbsp;(<?= date('M j', strtotime($semester['start_date'])) ?> – <?= date('M j, Y', strtotime($semester['end_date'])) ?>)</p>
        <?php endif; ?>
    </div>
</div>

<div class="container py-4">
    //Legend
    <div class="d-flex flex-wrap gap-3 mb-4 small">
        <span><span class="badge badge-status-enrolled">Enrolled</span> You are registered</span>
        <span><span class="badge badge-status-waitlisted">Waitlisted</span> FIFO queue position</span>
        <span><span class="badge bg-danger text-white">Full</span> No seats available</span>
        <span><i class="bi bi-info-circle text-primary me-1"></i>Hover over section for more details</span>
    </div>

    <div class="mb-3">
        <input type="text" id="tableSearch" class="form-control" placeholder="Search by course code, title, instructor…" style="max-width:400px;">
    </div>

    <?php if (!$semester): ?>
        <div class="alert alert-info">No upcoming semester registration is currently open.</div>
    <?php elseif (empty($sections)): ?>
        <div class="alert alert-warning">No course sections have been scheduled for this semester yet.</div>
    <?php else: ?>
    <div class="vs-card overflow-hidden">
        <div class="card-header-vs">
        <i class="bi bi-table me-2"></i>Available Sections — <?= sanitize($semester['name']) ?>
        </div>
        <div class="table-responsive">
        <table class="vs-table">
            <thead>
            <tr>
                <th>Code</th><th>Course Title</th><th>Sec</th><th>Instructor</th>
                <th>Days / Time</th><th>Location</th><th>Credits</th>
                <th>Seats</th><th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sections as $s):
                $full = $s['enrolled'] >= $s['capacity'];
                $pct  = $s['capacity'] > 0 ? ($s['enrolled']/$s['capacity']*100) : 0;
                $myStatus = $s['my_enrollment'];
                $myWaitPos = $s['my_waitlist_pos'];
            ?>
            <tr class="searchable-row" title="<?= sanitize($s['course_desc'] ?? '') ?>">
                <td><strong class="text-navy"><?= sanitize($s['course_code']) ?></strong></td>
                <td>
                <?= sanitize($s['course_title']) ?>
                <?php if ($s['degree_title']): ?><br><small class="text-muted"><?= sanitize($s['degree_title']) ?></small><?php endif; ?>
                </td>
                <td><?= sanitize($s['section_number']) ?></td>
                <td class="small"><?= sanitize($s['instructor_name'] ?? 'TBA') ?></td>
                <td class="small">
                <?= sanitize($s['schedule_days'] ?? '') ?><br>
                <span class="text-muted"><?= sanitize($s['schedule_time'] ?? '') ?></span>
                </td>
                <td class="small">
                <?php if ($s['delivery'] === 'Online'): ?>
                    <span class="badge badge-delivery-online">Online</span>
                <?php elseif ($s['delivery'] === 'Hybrid'): ?>
                    <span class="badge badge-delivery-hybrid">Hybrid</span>
                <?php else: ?>
                    <?= sanitize($s['location']) ?>
                <?php endif; ?>
                </td>
                <td class="text-center"><?= $s['credits'] ?></td>
                <td style="min-width:90px;">
                <div data-enrolled="<?= $s['enrolled'] ?>" data-capacity="<?= $s['capacity'] ?>">
                    <div class="small mb-1"><?= $s['enrolled'] ?>/<?= $s['capacity'] ?>
                    <?php if ($s['waitlist_count'] > 0): ?>
                        <span class="text-warning ms-1">(+<?= $s['waitlist_count'] ?> waitlist)</span>
                    <?php endif; ?>
                    </div>
                    <div class="capacity-bar"><div class="capacity-fill"></div></div>
                </div>
                </td>
                <td>
                <?php if ($myStatus === 'Enrolled'): ?>
                    <span class="badge badge-status-enrolled me-1">Enrolled</span>
                    <form method="POST" class="d-inline">
                    <input type="hidden" name="section_id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="action" value="drop">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-confirm="Drop <?= sanitize($s['course_code']) ?>?">Drop</button>
                    </form>
                <?php elseif ($myWaitPos): ?>
                    <span class="badge badge-status-waitlisted">Waitlist #<?= $myWaitPos ?></span>
                    <form method="POST" class="d-inline ms-1">
                    <input type="hidden" name="section_id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="action" value="waitlist_cancel">
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2" data-confirm="Cancel waitlist spot?">✕</button>
                    </form>
                <?php elseif ($full): ?>
                    <form method="POST" class="d-inline">
                    <input type="hidden" name="section_id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="action" value="enroll">
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-2">Join Waitlist</button>
                    </form>
                <?php else: ?>
                    <form method="POST" class="d-inline">
                    <input type="hidden" name="section_id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="action" value="enroll">
                    <button type="submit" class="btn btn-sm btn-vs rounded-pill px-3">Enroll</button>
                    </form>
                <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
