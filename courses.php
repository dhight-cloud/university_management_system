//University Management System (courses.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

$search = trim($_GET['q'] ?? '');
$semFilter = (int)($_GET['semester_id'] ?? 0);

$semesters = $db->query('SELECT * FROM semesters ORDER BY start_date')->fetchAll();

if ($semFilter) {
    $stmt = $db->prepare('
        SELECT cs.*, c.course_code, c.title AS course_title, c.credits, c.description AS course_desc,
            d.title AS degree_title, sem.name AS semester_name
        FROM class_sections cs
        JOIN courses c ON cs.course_id=c.id
        LEFT JOIN degrees d ON c.degree_id=d.id
        JOIN semesters sem ON cs.semester_id=sem.id
        WHERE cs.semester_id=? AND cs.active=1' . ($search ? ' AND (c.course_code LIKE ? OR c.title LIKE ?)' : '') . '
        ORDER BY c.course_code, cs.section_number
    ');
    $params = [$semFilter];
    if ($search) $params = array_merge($params, ["%$search%", "%$search%"]);
    $stmt->execute($params);
    $sections = $stmt->fetchAll();
    $courses = [];
} else {
    $params = [];
    $where = ['c.active=1'];
    if ($search) { $where[] = '(c.course_code LIKE ? OR c.title LIKE ?)'; $params = ["%$search%", "%$search%"]; }
    $stmt = $db->prepare('SELECT c.*, d.title AS degree_title FROM courses c LEFT JOIN degrees d ON c.degree_id=d.id WHERE ' . implode(' AND ',$where) . ' ORDER BY c.course_code');
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    $sections = [];
}

$pageTitle = 'Course Catalog';
include 'includes/header.php';
include 'includes/nav.php';
?>
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active">Courses</li></ol></nav>
        <h1><i class="bi bi-journals me-2 text-warning"></i>Course Catalog</h1>
    </div>
</div>
<div class="container py-4">
    <form method="GET" class="row g-2 mb-4 align-items-end">
        <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Search by code or title…" value="<?= sanitize($search) ?>"></div>
        <div class="col-md-4">
        <select name="semester_id" class="form-select">
            <option value="">— Full Catalog —</option>
            <?php foreach ($semesters as $sem): ?><option value="<?= $sem['id'] ?>" <?= $semFilter===(int)$sem['id']?'selected':'' ?>><?= sanitize($sem['name']) ?> <?= $sem['is_current']?'(Current)':($sem['is_upcoming']?'(Upcoming)':'') ?></option><?php endforeach; ?>
        </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-vs px-4">Search</button></div>
        <?php if ($search || $semFilter): ?><div class="col-auto"><a href="courses.php" class="btn btn-outline-secondary">Clear</a></div><?php endif; ?>
    </form>

    <?php if ($sections): ?>
    <!-- Semester sections view -->
    <h5 class="fw-bold mb-3 text-navy">Sections — <?= sanitize($sections[0]['semester_name'] ?? '') ?></h5>
    <div class="vs-card overflow-hidden">
        <div class="table-responsive">
        <table class="vs-table">
            <thead><tr><th>Code</th><th>Title</th><th>Instructor</th><th>Schedule</th><th>Delivery</th><th>Credits</th><th>Seats</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($sections as $s): $full = $s['enrolled'] >= $s['capacity']; ?>
            <tr>
                <td><strong class="text-navy"><?= sanitize($s['course_code']) ?></strong><br><small class="text-muted"><?= sanitize($s['section_number']) ?></small></td>
                <td><?= sanitize($s['course_title']) ?><br><small class="text-muted"><?= sanitize(mb_substr($s['course_desc']??'',0,55)) ?></small></td>
                <td class="small"><?= sanitize($s['instructor_name']??'TBA') ?></td>
                <td class="small"><?= sanitize($s['schedule_days'].' '.$s['schedule_time']) ?></td>
                <td><span class="badge <?= $s['delivery']==='Online'?'badge-delivery-online':($s['delivery']==='Hybrid'?'badge-delivery-hybrid':'badge-delivery-person') ?>"><?= $s['delivery'] ?></span></td>
                <td class="text-center"><?= $s['credits'] ?></td>
                <td data-enrolled="<?= $s['enrolled'] ?>" data-capacity="<?= $s['capacity'] ?>">
                <span class="<?= $full?'text-danger fw-bold':'' ?>"><?= $s['enrolled'] ?>/<?= $s['capacity'] ?></span>
                <?php if ($full): ?> <span class="badge bg-danger small">Full</span><?php endif; ?>
                <div class="capacity-bar mt-1"><div class="capacity-fill"></div></div>
                </td>
                <td><a href="student/login.php" class="btn btn-sm btn-vs rounded-pill px-3"><?= $full?'Waitlist':'Enroll' ?></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php elseif ($courses): ?>
    <!-- Full catalog view -->
    <div class="vs-card overflow-hidden">
        <div class="card-header-vs d-flex justify-content-between"><span><i class="bi bi-book me-2"></i>All Courses</span><span class="badge bg-secondary"><?= count($courses) ?></span></div>
        <div class="table-responsive">
        <table class="vs-table">
            <thead><tr><th>Code</th><th>Title</th><th>Credits</th><th>Program</th><th>Description</th></tr></thead>
            <tbody>
            <?php foreach ($courses as $c): ?>
            <tr class="searchable-row">
                <td><strong class="text-navy"><?= sanitize($c['course_code']) ?></strong></td>
                <td class="fw-600"><?= sanitize($c['title']) ?></td>
                <td class="text-center"><?= $c['credits'] ?></td>
                <td class="small text-muted"><?= sanitize($c['degree_title'] ?? 'General Education') ?></td>
                <td class="small text-muted"><?= sanitize(mb_substr($c['description']??'',0,80)) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php else: ?>
        <div class="text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-2"></i>No courses found matching your search.</div>
    <?php endif; ?>
    </div>
<?php include 'includes/footer.php'; ?>
