//University Management System (section_add.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$editId = (int)($_GET['id'] ?? 0);
$section = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM class_sections WHERE id=?');
    $stmt->execute([$editId]);
    $section = $stmt->fetch();
}

$courses   = $db->query('SELECT * FROM courses WHERE active=1 ORDER BY course_code')->fetchAll();
$semesters = $db->query('SELECT * FROM semesters ORDER BY start_date DESC')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'course_id'       => (int)$_POST['course_id'],
        'semester_id'     => (int)$_POST['semester_id'],
        'section_number'  => trim($_POST['section_number'] ?? '001'),
        'instructor_name' => trim($_POST['instructor_name'] ?? ''),
        'capacity'        => (int)($_POST['capacity'] ?? 30),
        'schedule_days'   => trim($_POST['schedule_days'] ?? ''),
        'schedule_time'   => trim($_POST['schedule_time'] ?? ''),
        'location'        => trim($_POST['location'] ?? ''),
        'delivery'        => $_POST['delivery'] ?? 'In-Person',
        'active'          => (int)(isset($_POST['active'])),
    ];
    if (!$fields['course_id'])   $errors[] = 'Please select a course.';
    if (!$fields['semester_id']) $errors[] = 'Please select a semester.';
    if ($fields['capacity'] < 1) $errors[] = 'Capacity must be at least 1.';

    if (empty($errors)) {
        if ($editId) {
            $db->prepare('UPDATE class_sections SET course_id=?,semester_id=?,section_number=?,instructor_name=?,capacity=?,schedule_days=?,schedule_time=?,location=?,delivery=?,active=? WHERE id=?')
              ->execute(array_merge(array_values($fields), [$editId]));
            flashMsg('success', 'Section updated.');
        } else {
            $db->prepare('INSERT INTO class_sections (course_id,semester_id,section_number,instructor_name,capacity,schedule_days,schedule_time,location,delivery,active) VALUES (?,?,?,?,?,?,?,?,?,?)')
              ->execute(array_values($fields));
            flashMsg('success', 'Class section created.');
        }
        header('Location: sections.php'); exit;
    }
}

$pageTitle = $editId ? 'Edit Section' : 'Add Section';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="sections.php">Sections</a></li><li class="breadcrumb-item active"><?= $editId?'Edit':'Add' ?></li></ol></nav>
    <h1><i class="bi bi-calendar-plus me-2 text-warning"></i><?= $editId?'Edit Class Section':'Add Class Section' ?></h1>
  </div>
</div>
<div class="container py-4" style="max-width:760px;">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="vs-card p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Course *</label>
          <select name="course_id" class="form-select" required>
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= ($section['course_id']??null)==$c['id']?'selected':'' ?>><?= sanitize($c['course_code'].' — '.$c['title']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Section #</label>
          <input type="text" name="section_number" class="form-control" value="<?= sanitize($section['section_number']??'001') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Semester *</label>
          <select name="semester_id" class="form-select" required>
            <option value="">— Select Semester —</option>
            <?php foreach ($semesters as $sem): ?><option value="<?= $sem['id'] ?>" <?= ($section['semester_id']??null)==$sem['id']?'selected':'' ?>><?= sanitize($sem['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Capacity</label>
          <input type="number" name="capacity" class="form-control" min="1" max="200" value="<?= (int)($section['capacity']??30) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Delivery</label>
          <select name="delivery" class="form-select">
            <?php foreach (['In-Person','Online','Hybrid'] as $d): ?><option value="<?= $d ?>" <?= ($section['delivery']??'In-Person')===$d?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label">Instructor</label><input type="text" name="instructor_name" class="form-control" value="<?= sanitize($section['instructor_name']??'') ?>" placeholder="Dr. Jane Smith"></div>
        <div class="col-md-4"><label class="form-label">Schedule Days</label><input type="text" name="schedule_days" class="form-control" placeholder="MWF or TR or MTWR" value="<?= sanitize($section['schedule_days']??'') ?>"></div>
        <div class="col-md-4"><label class="form-label">Time</label><input type="text" name="schedule_time" class="form-control" placeholder="9:00 AM – 9:50 AM" value="<?= sanitize($section['schedule_time']??'') ?>"></div>
        <div class="col-md-4"><label class="form-label">Location / Room</label><input type="text" name="location" class="form-control" placeholder="Roane Hall 101" value="<?= sanitize($section['location']??'') ?>"></div>
        <div class="col-12">
          <div class="form-check">
            <input type="checkbox" name="active" id="activeChk" class="form-check-input" value="1" <?= ($section['active']??1)?'checked':'' ?>>
            <label class="form-check-label fw-600" for="activeChk">Section is active / visible to students</label>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-vs-gold px-4"><i class="bi bi-save me-2"></i><?= $editId?'Save Changes':'Create Section' ?></button>
        <a href="sections.php" class="btn btn-outline-secondary px-4">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
