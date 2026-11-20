//University Management System (admin_nav.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
$_navDb = getDB();
$_pendingApps = (int)$_navDb->query('SELECT COUNT(*) FROM students WHERE enrollment_status="Pending"')->fetchColumn();
?>
<nav class="navbar navbar-expand-lg admin-navbar sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= SITE_URL ?>/admin/dashboard.php">
      <i class="bi bi-shield-fill-check me-2"></i><?= SITE_SHORT ?> <span class="badge bg-warning text-dark ms-1">Admin</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>

        <li class="nav-item">
          <a class="nav-link d-flex align-items-center gap-1" href="<?= SITE_URL ?>/admin/applications.php">
            <i class="bi bi-person-check me-1"></i>Applications
            <?php if ($_pendingApps > 0): ?>
              <span class="badge bg-danger rounded-pill"><?= $_pendingApps ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-people me-1"></i>Students</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/students.php">All Students</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/student_add.php">Add Student</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/grades.php">Update Grades</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-book me-1"></i>Courses</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/courses.php">All Courses</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/course_add.php">Add Course</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/sections.php">Class Sections</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/section_add.php">Add Section</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/admin/waitlist.php"><i class="bi bi-list-ol me-1"></i>Waitlist</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/admin/events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/admin/announcements.php"><i class="bi bi-megaphone me-1"></i>Announcements</a></li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item text-light me-3 small"><i class="bi bi-person-circle me-1"></i><?= sanitize($_SESSION['admin_name'] ?? 'Admin') ?></li>
        <li class="nav-item"><a class="nav-link btn btn-outline-warning btn-sm" href="<?= SITE_URL ?>/admin/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
