//University Management System (applications.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'approve' && $id) {
        $db->prepare('UPDATE students SET enrollment_status="Active" WHERE id=? AND enrollment_status="Pending"')
          ->execute([$id]);
        // Also set enrolled_date to today
        $db->prepare('UPDATE students SET enrolled_date=CURDATE() WHERE id=?')->execute([$id]);
        $s = $db->prepare('SELECT first_name, last_name, student_id FROM students WHERE id=?');
        $s->execute([$id]);
        $s = $s->fetch();
        flashMsg('success', "Approved! {$s['first_name']} {$s['last_name']} ({$s['student_id']}) is now an Active student.");
    } elseif ($action === 'deny' && $id) {
        $reason = trim($_POST['deny_reason'] ?? 'Application denied by admissions.');
        $db->prepare('UPDATE students SET enrollment_status="Inactive" WHERE id=? AND enrollment_status="Pending"')
          ->execute([$id]);
        flashMsg('warning', 'Application denied. Student status set to Inactive.');
    } elseif ($action === 'delete' && $id) {
        $db->prepare('DELETE FROM students WHERE id=? AND enrollment_status IN ("Pending","Inactive")')->execute([$id]);
        flashMsg('success', 'Application removed.');
    }

    header('Location: applications.php'); exit;
}

$statusFilter = $_GET['status'] ?? 'Pending';

$where  = ['1=1'];
$params = [];

if ($statusFilter && $statusFilter !== 'All') {
    $where[] = 's.enrollment_status=?';
    $params[] = $statusFilter;
} else {
    $where[] = 's.enrollment_status IN ("Pending","Inactive")';
}

$search = trim($_GET['q'] ?? '');
if ($search) {
    $where[] = '(s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ? OR s.student_id LIKE ?)';
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}

$whereStr = implode(' AND ', $where);

$apps = $db->prepare("
    SELECT s.*, d.title AS degree_title
    FROM students s
    LEFT JOIN degrees d ON s.degree_id=d.id
    WHERE $whereStr
    ORDER BY s.created_at DESC
");
$apps->execute($params);
$apps = $apps->fetchAll();

$pendingCount = $db->query('SELECT COUNT(*) FROM students WHERE enrollment_status="Pending"')->fetchColumn();

$pageTitle = 'Student Applications';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>

<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Applications</li>
      </ol>
    </nav>
    <div class="d-flex align-items-center gap-3">
      <h1><i class="bi bi-person-check-fill me-2 text-warning"></i>Student Applications</h1>
      <?php if ($pendingCount > 0): ?>
        <span class="badge bg-danger fs-6"><?= $pendingCount ?> pending</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container py-4">

  <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
    <div class="d-flex gap-2">
      <?php foreach (['Pending','Inactive','All'] as $tab): ?>
        <a href="applications.php?status=<?= $tab ?><?= $search ? '&q='.urlencode($search) : '' ?>"
          class="btn btn-sm rounded-pill px-3 <?= $statusFilter === $tab ? 'btn-vs' : 'btn-outline-secondary' ?>">
          <?= $tab ?>
          <?php if ($tab === 'Pending' && $pendingCount > 0): ?>
            <span class="badge bg-danger ms-1"><?= $pendingCount ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
    <form class="d-flex gap-2 ms-auto" method="GET">
      <input type="hidden" name="status" value="<?= sanitize($statusFilter) ?>">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name, email, ID…" value="<?= sanitize($search) ?>" style="max-width:240px;">
      <button type="submit" class="btn btn-vs btn-sm px-3">Search</button>
      <?php if ($search): ?><a href="applications.php?status=<?= sanitize($statusFilter) ?>" class="btn btn-outline-secondary btn-sm">Clear</a><?php endif; ?>
    </form>
  </div>

  <?php if (empty($apps)): ?>
    <div class="vs-card p-5 text-center text-muted">
      <i class="bi bi-inbox fs-1 d-block mb-3"></i>
      <h5>No applications found</h5>
      <p class="mb-0">No <?= strtolower($statusFilter) ?> applications at this time.</p>
    </div>

  <?php else: ?>

  <?php foreach ($apps as $app):
    $age      = calcAge($app['date_of_birth']);
    $isPending = $app['enrollment_status'] === 'Pending';
  ?>
  <div class="vs-card mb-3 overflow-hidden">
    <div class="d-flex flex-wrap gap-0">

      <div style="width:6px;flex-shrink:0;background:<?= $isPending ? 'var(--vs-gold)' : '#6c757d' ?>;"></div>

      <div class="flex-grow-1 p-3 p-md-4">
        <div class="row g-3 align-items-start">

          <div class="col-md-4">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="profile-avatar" style="width:52px;height:52px;font-size:1.1rem;flex-shrink:0;">
                <?= strtoupper(substr($app['first_name'],0,1).substr($app['last_name'],0,1)) ?>
              </div>
              <div>
                <div class="fw-bold"><?= sanitize($app['first_name'].' '.$app['last_name']) ?></div>
                <code class="small text-muted"><?= sanitize($app['student_id']) ?></code>
              </div>
            </div>
            <div class="small text-muted">
              <div class="mb-1"><i class="bi bi-envelope me-1"></i><?= sanitize($app['email']) ?></div>
              <div class="mb-1"><i class="bi bi-telephone me-1"></i><?= sanitize($app['phone'] ?? '—') ?></div>
              <div><i class="bi bi-geo-alt me-1"></i><?= sanitize($app['city'] ?? '—') ?>, <?= sanitize($app['state'] ?? '') ?></div>
            </div>
          </div>

          <div class="col-md-4">
            <table class="table table-sm table-borderless mb-0 small">
              <tr><td class="text-muted pe-2 py-1">Age</td><td class="fw-600 py-1"><?= $age ?> yrs (DOB: <?= date('M j, Y', strtotime($app['date_of_birth'])) ?>)</td></tr>
              <tr><td class="text-muted pe-2 py-1">Major</td><td class="fw-600 py-1"><?= sanitize($app['degree_title'] ?? 'Undecided') ?></td></tr>
              <tr><td class="text-muted pe-2 py-1">Enrolled</td><td class="fw-600 py-1"><?= $app['enrolled_date'] ? date('M j, Y', strtotime($app['enrolled_date'])) : '—' ?></td></tr>
              <tr><td class="text-muted pe-2 py-1">Applied</td><td class="fw-600 py-1"><?= date('M j, Y g:i A', strtotime($app['created_at'])) ?></td></tr>
              <tr><td class="text-muted pe-2 py-1">Status</td>
                <td class="py-1">
                  <span class="badge <?= $isPending ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                    <?= sanitize($app['enrollment_status']) ?>
                  </span>
                </td>
              </tr>
            </table>
          </div>

          <div class="col-md-4 d-flex flex-column gap-2">
            <?php if ($isPending): ?>
              <!-- Approve -->
              <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= $app['id'] ?>">
                <button type="submit" class="btn btn-success w-100 rounded-pill fw-600"
                        data-confirm="Approve <?= sanitize($app['first_name']) ?>'s application and activate their account?">
                  <i class="bi bi-check-circle me-2"></i>Approve &amp; Activate
                </button>
              </form>

              <button type="button" class="btn btn-outline-danger w-100 rounded-pill fw-600"
                      data-bs-toggle="collapse" data-bs-target="#deny-<?= $app['id'] ?>">
                <i class="bi bi-x-circle me-2"></i>Deny Application
              </button>
              <div class="collapse" id="deny-<?= $app['id'] ?>">
                <form method="POST" class="mt-1">
                  <input type="hidden" name="action" value="deny">
                  <input type="hidden" name="id" value="<?= $app['id'] ?>">
                  <textarea name="deny_reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Reason for denial (optional, internal only)"></textarea>
                  <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill">Confirm Denial</button>
                </form>
              </div>

              <a href="student_view.php?id=<?= $app['id'] ?>" class="btn btn-outline-secondary w-100 rounded-pill btn-sm">
                <i class="bi bi-eye me-1"></i>View Full Profile
              </a>

            <?php else: ?>
              <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= $app['id'] ?>">
                <button type="submit" class="btn btn-outline-success w-100 rounded-pill btn-sm mb-1"
                        data-confirm="Re-approve and activate this student?">
                  <i class="bi bi-arrow-counterclockwise me-1"></i>Re-Approve
                </button>
              </form>
              <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $app['id'] ?>">
                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill btn-sm"
                        data-confirm="Permanently delete this application? This cannot be undone.">
                  <i class="bi bi-trash me-1"></i>Delete Application
                </button>
              </form>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div><!-- /card -->
  <?php endforeach; ?>

  <div class="text-muted small mt-2"><?= count($apps) ?> application<?= count($apps) !== 1 ? 's' : '' ?> shown</div>
  <?php endif; ?>

</div>
<?php include '../includes/footer.php'; ?>
