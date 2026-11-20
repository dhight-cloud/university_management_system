//University Management System (login.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
startSession();

if (isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username=?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            header('Location: ' . SITE_URL . '/admin/dashboard.php'); exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter your username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login | <?= SITE_NAME ?></title>
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
          <div class="icon"><i class="bi bi-shield-lock-fill"></i></div>
          <h2><?= SITE_NAME ?></h2>
          <p class="text-muted small">Faculty &amp; Staff Admin Portal</p>
        </div>
        <?php if ($error): ?>
          <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
              <input type="text" name="username" class="form-control" placeholder="admin" value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus>
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
            <i class="bi bi-shield-check me-2"></i>Admin Sign In
          </button>
        </form>
        <hr class="my-3">
        <div class="text-center small text-muted">
          <p class="mb-1"><strong>Demo Credentials:</strong></p>
          <p class="mb-0">Username: <code>admin</code> &nbsp; Password: <code>password</code></p>
        </div>
        <div class="text-center mt-3">
          <a href="<?= SITE_URL ?>/" class="small text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Homepage</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
