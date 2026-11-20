//University Management System (index.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/includes/config.php';
startSession();

$db = getDB();

$events = $db->query('SELECT * FROM events WHERE active=1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 15')->fetchAll();

$announcements = $db->query('SELECT a.*, u.full_name FROM announcements a LEFT JOIN users u ON a.posted_by=u.id WHERE a.active=1 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE()) ORDER BY a.priority DESC, a.posted_at DESC LIMIT 5')->fetchAll();

$totalStudents = $db->query('SELECT COUNT(*) FROM students WHERE enrollment_status="Active"')->fetchColumn();
$totalPrograms = $db->query('SELECT COUNT(*) FROM degrees WHERE active=1')->fetchColumn();
$totalCourses  = $db->query('SELECT COUNT(*) FROM courses WHERE active=1')->fetchColumn();

$programs = $db->query('SELECT * FROM degrees WHERE active=1 ORDER BY level ASC LIMIT 6')->fetchAll();

$pageTitle = 'Welcome';
include 'includes/header.php';
?>
<?php include 'includes/nav.php'; ?>

<section class="hero-section">
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge"><i class="bi bi-geo-alt-fill me-1"></i> Gallatin, Tennessee</div>
        <h1 class="hero-title">Your Future <span class="gold">Starts Here</span> at VolState</h1>
        <p class="hero-subtitle">Accredited programs, flexible scheduling, and a community dedicated to your success — right here in Middle Tennessee.</p>
        <div class="hero-cta">
          <a href="student/apply.php" class="btn-hero-primary"><i class="bi bi-pencil-square me-2"></i>Apply Now</a>
          <a href="student/login.php" class="btn-hero-outline"><i class="bi bi-door-open me-1"></i>Student Login</a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-num"><?= number_format($totalStudents) ?>+</div>
            <div class="hero-stat-lbl">Active Students</div>
          </div>
          <div>
            <div class="hero-stat-num"><?= $totalPrograms ?></div>
            <div class="hero-stat-lbl">Degree Programs</div>
          </div>
          <div>
            <div class="hero-stat-num"><?= $totalCourses ?>+</div>
            <div class="hero-stat-lbl">Courses Offered</div>
          </div>
        </div>
      </div>
      <div class="col-lg-5 offset-lg-1 d-none d-lg-block">
        <div class="hero-card-float">
          <p class="mb-2 small opacity-75 text-uppercase letter-spacing-1">Summer 2026 Registration</p>
          <h4 class="mb-3" style="font-family:var(--font-serif);color:#fff;">Now Open</h4>
          <ul class="list-unstyled mb-3" style="color:rgba(255,255,255,.8);font-size:.9rem;">
            <li class="mb-1"><i class="bi bi-check-circle-fill text-warning me-2"></i>Online &amp; in-person options</li>
            <li class="mb-1"><i class="bi bi-check-circle-fill text-warning me-2"></i>FIFO waitlist available</li>
            <li class="mb-1"><i class="bi bi-check-circle-fill text-warning me-2"></i>Financial aid accepted</li>
          </ul>
          <a href="student/login.php" class="btn btn-warning btn-sm fw-bold rounded-pill px-3">Register Now &rarr;</a>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="event-ticker-wrap">
  <div class="container-fluid d-flex align-items-center" style="gap:0;padding:0;">
    <div class="ticker-label"><i class="bi bi-broadcast me-1"></i>Campus News</div>
    <div class="ticker-scroll" style="padding:0 1rem;">
      <div class="ticker-inner">
        <?php foreach ($events as $ev): ?>
          <span class="ticker-item">
            <strong><?= sanitize($ev['event_date']) ?></strong>&nbsp;&mdash;&nbsp;<?= sanitize($ev['title']) ?>
            <?php if ($ev['location']): ?> &nbsp;<span class="opacity-75 fw-normal">(<?= sanitize($ev['location']) ?>)</span><?php endif; ?>
          </span>
        <?php endforeach; ?>
        <?php if (empty($events)): ?>
          <span class="ticker-item">No upcoming events scheduled at this time. Check back soon!</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($announcements): ?>
<section class="py-4 bg-white border-bottom">
  <div class="container">
    <div class="row g-3">
      <?php foreach ($announcements as $ann): ?>
      <div class="col-md-6 col-lg-4">
        <div class="vs-card announcement-card <?= sanitize($ann['priority']) ?> p-3 h-100">
          <?php if ($ann['priority'] === 'high'): ?>
            <span class="badge bg-danger mb-2">Important</span>
          <?php endif; ?>
          <h6 class="fw-bold mb-1"><?= sanitize($ann['title']) ?></h6>
          <p class="small text-muted mb-1"><?= sanitize(mb_substr($ann['body'], 0, 100)) ?>…</p>
          <small class="text-muted"><i class="bi bi-person me-1"></i><?= sanitize($ann['full_name'] ?? 'Admin') ?></small>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5 bg-cream">
  <div class="container">
    <div class="text-center mb-4">
      <div class="section-label">Academic Programs</div>
      <h2 class="section-title">Degrees &amp; Certificates</h2>
      <div class="section-divider mx-auto"></div>
      <p class="text-muted">Choose from a wide range of accredited programs designed for working adults.</p>
    </div>
    <div class="row g-3">
      <?php foreach ($programs as $prog): ?>
      <?php
        $areaIcons = ['Business'=>'bi-briefcase-fill','Information Technology'=>'bi-cpu-fill','Criminal Justice'=>'bi-shield-fill','Health Care'=>'bi-heart-pulse-fill','Education'=>'bi-book-fill','Liberal Arts'=>'bi-palette-fill','Social & Behavioral Science'=>'bi-people-fill'];
        $icon = $areaIcons[$prog['area']] ?? 'bi-mortarboard-fill';
        $levelColors = ['Associate'=>'bg-secondary','Bachelor'=>'bg-primary','Master'=>'bg-success','Doctoral'=>'bg-danger'];
        $levelClass = $levelColors[$prog['level']] ?? 'bg-secondary';
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="vs-card program-card p-3 h-100">
          <div class="d-flex align-items-start gap-3">
            <div class="program-icon"><i class="bi <?= $icon ?>"></i></div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="program-level <?= $levelClass ?> text-white"><?= $prog['level'] ?></span>
                <small class="text-muted"><?= $prog['credits_required'] ?> credits</small>
              </div>
              <h6 class="fw-bold mb-1"><?= sanitize($prog['title']) ?></h6>
              <p class="small text-muted mb-0"><?= sanitize(mb_substr($prog['description'], 0, 80)) ?>…</p>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="degrees.php" class="btn-vs-gold btn px-4 py-2">View All Programs <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-lg-3">
        <a href="student/apply.php" class="vs-card d-block p-4 text-decoration-none" style="border:2px solid var(--vs-gold);">
          <i class="bi bi-pencil-square fs-1 text-gold mb-3 d-block"></i>
          <h6 class="fw-bold mb-1">Apply Now</h6>
          <p class="small text-muted mb-0">New students — submit your application online in minutes</p>
        </a>
      </div>
      <div class="col-6 col-lg-3">
        <a href="courses.php" class="vs-card d-block p-4 text-decoration-none">
          <i class="bi bi-journals fs-1 text-gold mb-3 d-block"></i>
          <h6 class="fw-bold mb-1">Course Catalog</h6>
          <p class="small text-muted mb-0">Browse all available courses and sections</p>
        </a>
      </div>
      <div class="col-6 col-lg-3">
        <a href="events.php" class="vs-card d-block p-4 text-decoration-none">
          <i class="bi bi-calendar-event fs-1 text-gold mb-3 d-block"></i>
          <h6 class="fw-bold mb-1">Events</h6>
          <p class="small text-muted mb-0">Campus events, deadlines, and activities</p>
        </a>
      </div>
      <div class="col-6 col-lg-3">
        <a href="student/login.php" class="vs-card d-block p-4 text-decoration-none">
          <i class="bi bi-person-circle fs-1 text-gold mb-3 d-block"></i>
          <h6 class="fw-bold mb-1">Student Login</h6>
          <p class="small text-muted mb-0">Access your portal, register for classes, view grades</p>
        </a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
