//University Management System (logout.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php
require_once __DIR__ . '/../includes/config.php';
startSession();
session_destroy();
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
