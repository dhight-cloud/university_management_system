//University Management System (login.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/../includes/config.php';
startSession();

if (isStudentLoggedIn()) {
    header('Location: ' . SITE_URL . '/student/portal.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid   = trim($_POST['student_id'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($sid && $pass) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM students WHERE student_id=? AND enrollment_status NOT IN ("Inactive","Pending")');
        $stmt->execute([$sid]);
        $student = $stmt->fetch();

        if ($student && password_verify($pass, $student['password_hash'])) {
            $_SESSION['student_id']   = $student['id'];
            $_SESSION['student_sid']  = $student['student_id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            header('Location: ' . SITE_URL . '/student/portal.php'); exit;
        } else {
            $pendingChk = $db->prepare('SELECT enrollment_status FROM students WHERE student_id=?');
            $pendingChk->execute([$sid]);
            $pendingRow = $pendingChk->fetch();
            if ($pendingRow && $pendingRow['enrollment_status'] === 'Pending') {
                $error = 'Your application is still under review. You will be notified by email once your account is approved (typically 2–3 business days).';
            } else {
                $error = 'Invalid Student ID or password. Please try again.';
            }
        }
    } else {
        $error = 'Please enter your Student ID and password.';
    }
}
$pageTitle = 'Student Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Login | <?= SITE_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-4">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        <div class="login-card">
            <div class="login-logo">
            <div class="icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h2><?= SITE_NAME ?></h2>
            <p class="text-muted small">Student Portal Login</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= sanitize($error) ?></div>
            <?php endif; ?>
            <form method="POST">
            <div class="mb-3">
                <label class="form-label">Student ID</label>
                <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="student_id" class="form-control" placeholder="e.g. VSS2026001" value="<?= sanitize($_POST['student_id'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Your password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-vs w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Portal
            </button>
            </form>
            <hr class="my-3">
            <div class="text-center small text-muted">
            <p class="mb-1"><strong>Demo Credentials:</strong></p>
            <p class="mb-0">Student ID: <code>VSS2026001</code> &nbsp; Password: <code>password</code></p>
            </div>
            <div class="alert alert-light border text-center mt-3 mb-0 py-3">
            <p class="mb-2 small fw-600">New to Volunteer State College?</p>
            <a href="<?= SITE_URL ?>/student/apply.php" class="btn btn-vs-gold btn-sm rounded-pill px-4">
                <i class="bi bi-pencil-square me-1"></i>Apply Now — It's Free
            </a>
            </div>
            <div class="text-center mt-3">
            <a href="<?= SITE_URL ?>/" class="small text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Homepage</a>
            </div>
        </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
