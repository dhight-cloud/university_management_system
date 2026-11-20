//University Management System (config.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026


<?php


define('DB_HOST', 'localhost');
define('DB_USER', 'root');         
define('DB_PASS', '');             
define('DB_NAME', 'volunteer_state');

define('SITE_NAME',  'Volunteer State College');
define('SITE_SHORT', 'VolState');
define('SITE_URL',   'http://localhost/volunteer_state');
define('CURRENT_YEAR', date('Y'));

define('GRADE_POINTS', [
    'A'  => 4.00, 'A-' => 3.70,
    'B+' => 3.30, 'B'  => 3.00, 'B-' => 2.70,
    'C+' => 2.30, 'C'  => 2.00, 'C-' => 1.70,
    'D+' => 1.30, 'D'  => 1.00, 'D-' => 0.70,
    'F'  => 0.00, 'W'  => null, 'I'  => null, 'P'  => null,
]);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div class="alert alert-danger m-4">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }
    return $pdo;
}

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isAdminLoggedIn(): bool {
    startSession();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function isStudentLoggedIn(): bool {
    startSession();
    return isset($_SESSION['student_id']) && !empty($_SESSION['student_id']);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function requireStudent(): void {
    if (!isStudentLoggedIn()) {
        header('Location: ' . SITE_URL . '/student/login.php');
        exit;
    }
}

function calcAge(string $dob): int {
    $birth = new DateTime($dob);
    $now   = new DateTime();
    return (int)$birth->diff($now)->y;
}

function formatGrade(?string $grade): string {
    if ($grade === null) return '<span class="text-muted">—</span>';
    $class = match(true) {
        in_array($grade, ['A','A-'])          => 'text-success fw-bold',
        in_array($grade, ['B+','B','B-'])     => 'text-primary',
        in_array($grade, ['C+','C','C-'])     => 'text-warning',
        in_array($grade, ['D+','D','D-','F']) => 'text-danger',
        default                               => 'text-muted',
    };
    return "<span class=\"$class\">$grade</span>";
}

function sanitize(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function flashMsg(string $type, string $msg): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    startSession();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function addToWaitlist(int $studentId, int $sectionId): bool {
    $db = getDB();
    // Check if already on waitlist
    $check = $db->prepare('SELECT id FROM waitlist WHERE student_id=? AND section_id=? AND status="Waiting"');
    $check->execute([$studentId, $sectionId]);
    if ($check->fetch()) return false;
    // Get next position
    $pos = $db->prepare('SELECT COALESCE(MAX(position),0)+1 AS pos FROM waitlist WHERE section_id=? AND status="Waiting"');
    $pos->execute([$sectionId]);
    $nextPos = (int)$pos->fetchColumn();
    $ins = $db->prepare('INSERT INTO waitlist (student_id, section_id, position, status) VALUES (?,?,?,"Waiting")');
    $ins->execute([$studentId, $sectionId, $nextPos]);
    $db->prepare('UPDATE class_sections SET waitlist_count = waitlist_count + 1 WHERE id = ?')->execute([$sectionId]);
    return true;
}

function promoteFromWaitlist(int $sectionId): void {
    $db = getDB();
    // Get first in queue (FIFO)
    $first = $db->prepare('SELECT * FROM waitlist WHERE section_id=? AND status="Waiting" ORDER BY position ASC LIMIT 1');
    $first->execute([$sectionId]);
    $entry = $first->fetch();
    if (!$entry) return;
    // Enroll them
    $ins = $db->prepare('INSERT IGNORE INTO enrollments (student_id, section_id, status) VALUES (?,?,"Enrolled")');
    $ins->execute([$entry['student_id'], $sectionId]);
    // Update waitlist record
    $db->prepare('UPDATE waitlist SET status="Enrolled" WHERE id=?')->execute([$entry['id']]);
    $db->prepare('UPDATE class_sections SET enrolled=enrolled+1, waitlist_count=waitlist_count-1 WHERE id=?')->execute([$sectionId]);
}
