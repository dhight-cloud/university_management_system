//University Management System (nav.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php // includes/nav.php ?>
<nav class="navbar navbar-expand-lg site-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= SITE_URL ?>/">
      <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
      <span class="brand-text"><?= SITE_SHORT ?></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/degrees.php">Programs</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/courses.php">Courses</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/events.php">Events</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isStudentLoggedIn()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/portal.php"><i class="bi bi-person-circle me-1"></i>My Portal</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/student/apply.php"><i class="bi bi-pencil-square me-1"></i>Apply Now</a></li>
          <li class="nav-item"><a class="nav-link btn-nav-outline me-2" href="<?= SITE_URL ?>/student/login.php">Student Login</a></li>
          <li class="nav-item"><a class="nav-link btn-nav-solid" href="<?= SITE_URL ?>/admin/login.php"><i class="bi bi-shield-lock me-1"></i>Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
