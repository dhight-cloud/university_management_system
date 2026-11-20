//University Management System (announcements.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$db = getDB();

$editId = (int)($_GET['id'] ?? 0);
$ann = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM announcements WHERE id=?');
    $stmt->execute([$editId]);
    $ann = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'delete') {
        $db->prepare('DELETE FROM announcements WHERE id=?')->execute([(int)$_POST['id']]);
        flashMsg('success', 'Announcement deleted.');
        header('Location: announcements.php'); exit;
    }
    $fields = [
        'title'      => trim($_POST['title'] ?? ''),
        'body'       => trim($_POST['body'] ?? ''),
        'priority'   => $_POST['priority'] ?? 'normal',
        'expires_at' => trim($_POST['expires_at'] ?? '') ?: null,
        'active'     => (int)isset($_POST['active']),
        'posted_by'  => $_SESSION['admin_id'],
    ];
    if ($fields['title'] && $fields['body']) {
        if ($editId) {
            $db->prepare('UPDATE announcements SET title=?,body=?,priority=?,expires_at=?,active=? WHERE id=?')
              ->execute([$fields['title'],$fields['body'],$fields['priority'],$fields['expires_at'],$fields['active'],$editId]);
            flashMsg('success', 'Announcement updated.');
        } else {
            $db->prepare('INSERT INTO announcements (title,body,priority,expires_at,active,posted_by) VALUES (?,?,?,?,?,?)')
              ->execute([$fields['title'],$fields['body'],$fields['priority'],$fields['expires_at'],$fields['active'],$fields['posted_by']]);
            flashMsg('success', 'Announcement posted.');
        }
        header('Location: announcements.php'); exit;
    }
}

$announcements = $db->query('SELECT a.*, u.full_name FROM announcements a LEFT JOIN users u ON a.posted_by=u.id ORDER BY a.posted_at DESC')->fetchAll();

$pageTitle = 'Announcements';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Announcements</li></ol></nav>
    <h1><i class="bi bi-megaphone me-2 text-warning"></i>Announcements</h1>
  </div>
</div>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="vs-card p-4">
        <h5 class="fw-bold mb-3"><?= $editId ? 'Edit Announcement' : 'Post Announcement' ?></h5>
        <form method="POST" action="announcements.php<?= $editId?'?id='.$editId:'' ?>">
          <div class="mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?= sanitize($ann['title']??'') ?>" required></div>
          <div class="mb-3"><label class="form-label">Body *</label><textarea name="body" class="form-control" rows="4" required><?= sanitize($ann['body']??'') ?></textarea></div>
          <div class="row g-2 mb-3">
            <div class="col-6"><label class="form-label">Priority</label>
              <select name="priority" class="form-select">
                <?php foreach (['low','normal','high'] as $p): ?><option value="<?= $p ?>" <?= ($ann['priority']??'normal')===$p?'selected':'' ?>><?= ucfirst($p) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-6"><label class="form-label">Expires</label><input type="date" name="expires_at" class="form-control" value="<?= sanitize($ann['expires_at']??'') ?>"></div>
          </div>
          <div class="form-check mb-3"><input type="checkbox" name="active" class="form-check-input" id="annActive" value="1" <?= ($ann['active']??1)?'checked':'' ?>><label class="form-check-label" for="annActive">Publish (visible to students)</label></div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-vs-gold px-4"><?= $editId?'Update':'Post' ?></button>
            <?php if ($editId): ?><a href="announcements.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
          </div>
        </form>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="vs-card overflow-hidden">
        <div class="card-header-vs"><i class="bi bi-chat-left-text me-2"></i>All Announcements</div>
        <div class="p-3">
          <?php foreach ($announcements as $a): ?>
          <div class="announcement-card <?= sanitize($a['priority']) ?> vs-card p-3 mb-3">
            <div class="d-flex align-items-start justify-content-between gap-2">
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <?php if ($a['priority']==='high'): ?><span class="badge bg-danger">Important</span><?php endif; ?>
                  <h6 class="mb-0 fw-bold"><?= sanitize($a['title']) ?></h6>
                </div>
                <p class="small mb-1"><?= sanitize(mb_substr($a['body'],0,100)) ?>…</p>
                <small class="text-muted"><i class="bi bi-person me-1"></i><?= sanitize($a['full_name']??'Admin') ?> &bull; <?= date('M j, Y', strtotime($a['posted_at'])) ?><?= $a['expires_at'] ? ' &bull; Expires: '.date('M j, Y', strtotime($a['expires_at'])) : '' ?> &bull; <?= $a['active']?'<span class="text-success">Published</span>':'<span class="text-muted">Draft</span>' ?></small>
              </div>
              <div class="d-flex gap-1 flex-shrink-0">
                <a href="announcements.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2">Edit</a>
                <form method="POST" class="d-inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-confirm="Delete this announcement?">✕</button></form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($announcements)): ?><p class="text-center text-muted py-3">No announcements yet.</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
