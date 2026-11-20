//University Management System (course_add.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$editId = (int)($_GET['id'] ?? 0);
$course = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM courses WHERE id=?');
    $stmt->execute([$editId]);
    $course = $stmt->fetch();
}
$degrees = $db->query('SELECT * FROM degrees WHERE active=1 ORDER BY level, title')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'course_code' => strtoupper(trim($_POST['course_code'] ?? '')),
        'title'       => trim($_POST['title'] ?? ''),
        'credits'     => (int)($_POST['credits'] ?? 3),
        'degree_id'   => (int)($_POST['degree_id'] ?? 0) ?: null,
        'description' => trim($_POST['description'] ?? ''),
        'active'      => (int)(isset($_POST['active'])),
    ];
    if (!$fields['course_code']) $errors[] = 'Course code is required.';
    if (!$fields['title'])       $errors[] = 'Course title is required.';

    if (empty($errors)) {
        if ($editId) {
            $db->prepare('UPDATE courses SET course_code=?,title=?,credits=?,degree_id=?,description=?,active=? WHERE id=?')
              ->execute(array_merge(array_values($fields), [$editId]));
            flashMsg('success', 'Course updated.');
        } else {
            $db->prepare('INSERT INTO courses (course_code,title,credits,degree_id,description,active) VALUES (?,?,?,?,?,?)')
              ->execute(array_values($fields));
            flashMsg('success', 'Course added to catalog.');
        }
        header('Location: courses.php'); exit;
    }
}

$pageTitle = $editId ? 'Edit Course' : 'Add Course';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="courses.php">Courses</a></li><li class="breadcrumb-item active"><?= $editId ? 'Edit' : 'Add' ?></li></ol></nav>
    <h1><i class="bi bi-book me-2 text-warning"></i><?= $editId ? 'Edit Course' : 'Add New Course' ?></h1>
  </div>
</div>
<div class="container py-4" style="max-width:720px;">
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="vs-card p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Course Code *</label><input type="text" name="course_code" class="form-control text-uppercase" placeholder="BUS100" value="<?= sanitize($course['course_code'] ?? '') ?>" required></div>
        <div class="col-md-5"><label class="form-label">Credits</label>
          <select name="credits" class="form-select">
            <?php foreach ([1,2,3,4,5,6] as $cr): ?><option value="<?= $cr ?>" <?= ($course['credits'] ?? 3)===$cr?'selected':'' ?>><?= $cr ?> credit<?= $cr>1?'s':'' ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check mb-2">
            <input type="checkbox" name="active" id="activeCheck" class="form-check-input" value="1" <?= ($course['active'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label fw-600" for="activeCheck">Active</label>
          </div>
        </div>
        <div class="col-12"><label class="form-label">Course Title *</label><input type="text" name="title" class="form-control" value="<?= sanitize($course['title'] ?? '') ?>" required></div>
        <div class="col-12">
          <label class="form-label">Degree Program</label>
          <select name="degree_id" class="form-select">
            <option value="">— General Education (no specific program) —</option>
            <?php foreach ($degrees as $d): ?><option value="<?= $d['id'] ?>" <?= ($course['degree_id'] ?? null)===$d['id']?'selected':'' ?>><?= sanitize($d['level'].' — '.$d['title']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Brief course description…"><?= sanitize($course['description'] ?? '') ?></textarea></div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-vs-gold px-4"><i class="bi bi-save me-2"></i><?= $editId ? 'Save Changes' : 'Add Course' ?></button>
        <a href="courses.php" class="btn btn-outline-secondary px-4">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
