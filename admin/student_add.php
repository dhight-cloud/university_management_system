//University Management System (student_add.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$editId = (int)($_GET['id'] ?? 0);
$student = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM students WHERE id=?');
    $stmt->execute([$editId]);
    $student = $stmt->fetch();
}

$degrees = $db->query('SELECT * FROM degrees WHERE active=1 ORDER BY level, title')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'first_name'        => trim($_POST['first_name'] ?? ''),
        'last_name'         => trim($_POST['last_name'] ?? ''),
        'date_of_birth'     => trim($_POST['date_of_birth'] ?? ''),
        'email'             => trim($_POST['email'] ?? ''),
        'phone'             => trim($_POST['phone'] ?? ''),
        'address'           => trim($_POST['address'] ?? ''),
        'city'              => trim($_POST['city'] ?? ''),
        'state'             => trim($_POST['state'] ?? 'Tennessee'),
        'zip'               => trim($_POST['zip'] ?? ''),
        'degree_id'         => (int)($_POST['degree_id'] ?? 0) ?: null,
        'gpa'               => (float)($_POST['gpa'] ?? 0),
        'credits_earned'    => (int)($_POST['credits_earned'] ?? 0),
        'enrollment_status' => $_POST['enrollment_status'] ?? 'Active',
        'enrolled_date'     => trim($_POST['enrolled_date'] ?? ''),
    ];

    if (!$fields['first_name'])    $errors[] = 'First name is required.';
    if (!$fields['last_name'])     $errors[] = 'Last name is required.';
    if (!$fields['date_of_birth']) $errors[] = 'Date of birth is required.';
    if (!$fields['email'])         $errors[] = 'Email is required.';

    if (empty($errors)) {
        if ($editId) {
            $sql = 'UPDATE students SET first_name=?,last_name=?,date_of_birth=?,email=?,phone=?,address=?,city=?,state=?,zip=?,degree_id=?,gpa=?,credits_earned=?,enrollment_status=?,enrolled_date=? WHERE id=?';
            $params = array_values($fields);
            $params[] = $editId;
            $db->prepare($sql)->execute($params);
            // Password reset if provided
            if (!empty($_POST['new_password'])) {
                $db->prepare('UPDATE students SET password_hash=? WHERE id=?')->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $editId]);
            }
            flashMsg('success', 'Student profile updated successfully.');
        } else {
            $year = date('Y');
            $count = $db->query("SELECT COUNT(*)+1 FROM students WHERE student_id LIKE 'VS{$year}%'")->fetchColumn();
            $sid = 'VS' . $year . str_pad($count, 3, '0', STR_PAD_LEFT);
            $pass = password_hash($_POST['new_password'] ?? 'password', PASSWORD_DEFAULT);
            $sql = 'INSERT INTO students (student_id,first_name,last_name,date_of_birth,email,phone,address,city,state,zip,degree_id,gpa,credits_earned,enrollment_status,enrolled_date,password_hash) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $params = [$sid, $fields['first_name'], $fields['last_name'], $fields['date_of_birth'], $fields['email'], $fields['phone'], $fields['address'], $fields['city'], $fields['state'], $fields['zip'], $fields['degree_id'], $fields['gpa'], $fields['credits_earned'], $fields['enrollment_status'], $fields['enrolled_date'], $pass];
            $db->prepare($sql)->execute($params);
            $newId = $db->lastInsertId();
            flashMsg('success', "Student created! ID: $sid — Default password: password");
            header('Location: student_view.php?id=' . $newId); exit;
        }
        header('Location: student_view.php?id=' . $editId); exit;
    }
}

$pageTitle = $editId ? 'Edit Student' : 'Add Student';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="students.php">Students</a></li><li class="breadcrumb-item active"><?= $editId ? 'Edit' : 'Add' ?></li></ol></nav>
    <h1><i class="bi bi-person-<?= $editId ? 'gear' : 'plus' ?>-fill me-2 text-warning"></i><?= $editId ? 'Edit Student Profile' : 'Add New Student' ?></h1>
  </div>
</div>
<div class="container py-4" style="max-width:860px;">
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="vs-card p-4">
    <form method="POST">
      <h5 class="fw-bold mb-3 text-navy"><i class="bi bi-person me-2"></i>Personal Information</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" value="<?= sanitize($student['first_name'] ?? $_POST['first_name'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-control" value="<?= sanitize($student['last_name'] ?? $_POST['last_name'] ?? '') ?>" required></div>
        <div class="col-md-4"><label class="form-label">Date of Birth *</label><input type="date" name="date_of_birth" class="form-control" value="<?= sanitize($student['date_of_birth'] ?? $_POST['date_of_birth'] ?? '') ?>" required></div>
        <div class="col-md-4"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="<?= sanitize($student['email'] ?? $_POST['email'] ?? '') ?>" required></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" placeholder="(615) 555-0100" value="<?= sanitize($student['phone'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Street Address</label><input type="text" name="address" class="form-control" value="<?= sanitize($student['address'] ?? '') ?>"></div>
        <div class="col-md-5"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= sanitize($student['city'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?= sanitize($student['state'] ?? 'Tennessee') ?>"></div>
        <div class="col-md-3"><label class="form-label">ZIP</label><input type="text" name="zip" class="form-control" value="<?= sanitize($student['zip'] ?? '') ?>"></div>
      </div>

      <h5 class="fw-bold mb-3 text-navy"><i class="bi bi-mortarboard me-2"></i>Academic Information</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Degree / Major</label>
          <select name="degree_id" class="form-select">
            <option value="">— Undeclared —</option>
            <?php foreach ($degrees as $deg): ?>
              <option value="<?= $deg['id'] ?>" <?= ($student['degree_id'] ?? null) == $deg['id'] ? 'selected' : '' ?>><?= sanitize($deg['level'] . ' — ' . $deg['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">GPA (0.00–4.00)</label><input type="number" name="gpa" class="form-control" step="0.01" min="0" max="4" value="<?= number_format($student['gpa'] ?? 0, 2) ?>"></div>
        <div class="col-md-3"><label class="form-label">Credits Earned</label><input type="number" name="credits_earned" class="form-control" min="0" value="<?= (int)($student['credits_earned'] ?? 0) ?>"></div>
        <div class="col-md-4">
          <label class="form-label">Enrollment Status</label>
          <select name="enrollment_status" class="form-select">
            <?php foreach (['Active','Inactive','Graduated','On Leave'] as $st): ?>
              <option value="<?= $st ?>" <?= ($student['enrollment_status'] ?? 'Active') === $st ? 'selected' : '' ?>><?= $st ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Enrolled Date</label><input type="date" name="enrolled_date" class="form-control" value="<?= sanitize($student['enrolled_date'] ?? date('Y-m-d')) ?>"></div>
      </div>

      <h5 class="fw-bold mb-3 text-navy"><i class="bi bi-lock me-2"></i><?= $editId ? 'Reset Password (leave blank to keep)' : 'Set Password' ?></h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6"><label class="form-label">Password<?= $editId ? '' : ' *' ?></label><input type="password" name="new_password" class="form-control" placeholder="<?= $editId ? 'Leave blank to keep current' : 'Set initial password' ?>" <?= $editId ? '' : 'required' ?>></div>
        <?php if (!$editId): ?><div class="col-md-12"><small class="text-muted">Default: <code>password</code> — Student should change this after first login.</small></div><?php endif; ?>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-vs-gold px-4 py-2"><i class="bi bi-save me-2"></i><?= $editId ? 'Save Changes' : 'Create Student' ?></button>
        <a href="<?= $editId ? 'student_view.php?id='.$editId : 'students.php' ?>" class="btn btn-outline-secondary px-4">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
