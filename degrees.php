//University Management System (degrees.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

$level  = $_GET['level'] ?? '';
$area   = $_GET['area'] ?? '';

$where = ['active=1'];
$params = [];
if ($level) { $where[] = 'level=?'; $params[] = $level; }
if ($area)  { $where[] = 'area=?';  $params[] = $area; }
$whereStr = implode(' AND ', $where);

$degrees = $db->prepare("SELECT * FROM degrees WHERE $whereStr ORDER BY level, area, title");
$degrees->execute($params);
$degrees = $degrees->fetchAll();

$levels = ['Associate','Bachelor','Master','Doctoral'];
$areas  = ['Business','Criminal Justice','Education','Health Care','Information Technology','Liberal Arts','Social & Behavioral Science'];

$areaIcons = ['Business'=>'bi-briefcase-fill','Information Technology'=>'bi-cpu-fill','Criminal Justice'=>'bi-shield-fill','Health Care'=>'bi-heart-pulse-fill','Education'=>'bi-book-fill','Liberal Arts'=>'bi-palette-fill','Social & Behavioral Science'=>'bi-people-fill'];
$levelColors = ['Associate'=>'bg-secondary','Bachelor'=>'bg-primary','Master'=>'bg-success','Doctoral'=>'bg-danger'];

$pageTitle = 'Degrees & Programs';
include 'includes/header.php';
include 'includes/nav.php';
?>
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active">Programs</li></ol></nav>
        <h1><i class="bi bi-mortarboard me-2 text-warning"></i>Degrees &amp; Programs</h1>
        <p class="text-white-50 mb-0">Discover your academic path at Volunteer State College</p>
    </div>
</div>
<div class="container py-4">
    
    <div class="row g-2 mb-4 align-items-end">
        <div class="col-auto">
            <a href="degrees.php" class="btn <?= (!$level&&!$area)?'btn-vs':'btn-outline-secondary' ?> rounded-pill px-3 btn-sm">All</a>
        </div>
        <?php foreach ($levels as $l): ?>
        <div class="col-auto">
            <a href="degrees.php?level=<?= urlencode($l) ?><?= $area?'&area='.urlencode($area):'' ?>" class="btn <?= $level===$l?'btn-vs':'btn-outline-secondary' ?> rounded-pill px-3 btn-sm"><?= $l ?></a>
        </div>
        <?php endforeach; ?>
        <div class="col-md-3 ms-auto">
            <select onchange="window.location='degrees.php?area='+this.value+'<?= $level?'&level='.urlencode($level):'' ?>'" class="form-select form-select-sm">
            <option value="">— All Areas —</option>
            <?php foreach ($areas as $a): ?><option value="<?= $a ?>" <?= $area===$a?'selected':'' ?>><?= $a ?></option><?php endforeach; ?>
            </select>
        </div>
</div>

<div class="row g-3">
    <?php foreach ($degrees as $deg): ?>
    <?php $icon = $areaIcons[$deg['area']] ?? 'bi-mortarboard-fill'; $lc = $levelColors[$deg['level']] ?? 'bg-secondary'; ?>
    <div class="col-md-6 col-xl-4">
        <div class="vs-card program-card p-4 h-100">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="program-icon flex-shrink-0"><i class="bi <?= $icon ?>"></i></div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge <?= $lc ?>"><?= $deg['level'] ?></span>
                    <code class="text-muted small"><?= sanitize($deg['code']) ?></code>
                </div>
                <h6 class="fw-bold mb-0"><?= sanitize($deg['title']) ?></h6>
            </div>
            </div>
        <p class="small text-muted mb-3"><?= sanitize($deg['description']) ?></p>
        <div class="d-flex align-items-center justify-content-between">
            <span class="small text-muted"><i class="bi bi-bookmark me-1"></i><?= $deg['credits_required'] ?> credits</span>
            <span class="badge bg-light text-dark small"><?= sanitize($deg['area']) ?></span>
        </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($degrees)): ?>
    <div class="col-12 text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-2"></i>No programs match your filters.</div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

