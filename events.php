//University Management System (events.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

$catFilter = $_GET['category'] ?? '';
$where = ['active=1'];
$params = [];
if ($catFilter) { $where[] = 'category=?'; $params[] = $catFilter; }
$whereStr = implode(' AND ', $where);

$events = $db->prepare("SELECT * FROM events WHERE $whereStr ORDER BY event_date ASC");
$events->execute($params);
$events = $events->fetchAll();

$categories = ['Academic','Student Life','Sports','Career','Administrative'];
$catColors = ['Academic'=>'primary','Student Life'=>'success','Sports'=>'danger','Career'=>'warning','Administrative'=>'secondary'];
$catIcons  = ['Academic'=>'bi-mortarboard','Student Life'=>'bi-people','Sports'=>'bi-trophy','Career'=>'bi-briefcase','Administrative'=>'bi-building'];

$pageTitle = 'Campus Events';
include 'includes/header.php';
include 'includes/nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active">Events</li></ol></nav>
    <h1><i class="bi bi-calendar-event me-2 text-warning"></i>Campus Events</h1>
  </div>
</div>
<div class="container py-4">

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="events.php" class="btn <?= !$catFilter?'btn-vs':'btn-outline-secondary' ?> rounded-pill px-3 btn-sm">All Events</a>
    <?php foreach ($categories as $cat): ?>
    <a href="events.php?category=<?= urlencode($cat) ?>" class="btn <?= $catFilter===$cat?'btn-vs':'btn-outline-secondary' ?> rounded-pill px-3 btn-sm">
      <i class="bi <?= $catIcons[$cat] ?> me-1"></i><?= $cat ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($events)): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>No events in this category.</div>
  <?php else: ?>
  <div class="row g-3">
    <?php foreach ($events as $ev):
      $past = strtotime($ev['event_date']) < strtotime('today');
      $color = $catColors[$ev['category']] ?? 'secondary';
      $icon  = $catIcons[$ev['category']] ?? 'bi-calendar';
    ?>
    <div class="col-md-6 col-xl-4">
      <div class="vs-card p-0 overflow-hidden h-100 <?= $past?'opacity-75':'' ?>">
        <div class="d-flex">
          <div style="background:var(--vs-navy);width:80px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:.75rem .5rem;text-align:center;">
            <div style="color:var(--vs-gold);font-size:1.4rem;font-weight:800;font-family:var(--font-serif);line-height:1;"><?= date('d', strtotime($ev['event_date'])) ?></div>
            <div style="color:rgba(255,255,255,.7);font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;"><?= date('M', strtotime($ev['event_date'])) ?></div>
            <div style="color:rgba(255,255,255,.5);font-size:.65rem;"><?= date('Y', strtotime($ev['event_date'])) ?></div>
          </div>
          <div class="p-3 flex-grow-1">
            <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
              <span class="badge bg-<?= $color ?> small"><i class="bi <?= $icon ?> me-1"></i><?= sanitize($ev['category']) ?></span>
              <?php if ($past): ?><span class="badge bg-light text-muted small">Past</span><?php endif; ?>
            </div>
            <h6 class="fw-bold mb-1"><?= sanitize($ev['title']) ?></h6>
            <?php if ($ev['event_time']): ?><p class="small text-muted mb-1"><i class="bi bi-clock me-1"></i><?= sanitize($ev['event_time']) ?></p><?php endif; ?>
            <?php if ($ev['location']): ?><p class="small text-muted mb-1"><i class="bi bi-geo-alt me-1"></i><?= sanitize($ev['location']) ?></p><?php endif; ?>
            <?php if ($ev['description']): ?><p class="small text-muted mb-0"><?= sanitize(mb_substr($ev['description'],0,90)) ?><?= strlen($ev['description'])>90?'…':'' ?></p><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
