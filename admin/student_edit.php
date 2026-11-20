//University Management System (student_edit.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
header('Location: student_add.php?id=' . $id); exit;
