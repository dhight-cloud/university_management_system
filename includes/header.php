//University Management System (header.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
if (!isset($pageTitle)) $pageTitle = SITE_NAME;
if (!isset($bodyClass)) $bodyClass = '';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($pageTitle) ?> | <?= SITE_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body class="<?= sanitize($bodyClass) ?>">
<?php if ($flash): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
  <div class="toast show align-items-center text-bg-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body"><?= sanitize($flash['msg']) ?></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
<?php endif; ?>
