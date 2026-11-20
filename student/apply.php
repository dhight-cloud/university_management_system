//University Management System (apply.php)
//Devon Hight
//CST499-Software Engineering Capstone
//Instructor- Charmelia Butler
//04/13/2026

<?php
require_once __DIR__ . '/../includes/config.php';
startSession();

if (isStudentLoggedIn()) {
    header('Location: ' . SITE_URL . '/student/portal.php'); exit;
}

$db = getDB();
$degrees = $db->query('SELECT * FROM degrees WHERE active=1 ORDER BY level, title')->fetchAll();

$errors  = [];
$success = false;
$newSid  = '';

$usStates = ['Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware',
'Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana',
'Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana',
'Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina',
'North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina',
'South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia',
'Wisconsin','Wyoming'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $f = [
        'first_name'    => trim($_POST['first_name'] ?? ''),
        'last_name'     => trim($_POST['last_name'] ?? ''),
        'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
        'gender'        => trim($_POST['gender'] ?? ''),
        'ssn_last4'     => trim($_POST['ssn_last4'] ?? ''),

        'email'         => trim($_POST['email'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'address'       => trim($_POST['address'] ?? ''),
        'city'          => trim($_POST['city'] ?? ''),
        'state'         => trim($_POST['state'] ?? 'Tennessee'),
        'zip'           => trim($_POST['zip'] ?? ''),

        'degree_id'     => (int)($_POST['degree_id'] ?? 0) ?: null,
        'start_term'    => trim($_POST['start_term'] ?? ''),
        'enrollment_type'=> trim($_POST['enrollment_type'] ?? 'Full-Time'),
        'prior_college' => trim($_POST['prior_college'] ?? ''),
        'high_school'   => trim($_POST['high_school'] ?? ''),
        'hs_grad_year'  => trim($_POST['hs_grad_year'] ?? ''),

        'password'      => $_POST['password'] ?? '',
        'password2'     => $_POST['password2'] ?? '',

        'agree_terms'   => isset($_POST['agree_terms']),
        'agree_ferpa'   => isset($_POST['agree_ferpa']),
    ];

    if (!$f['first_name'])          $errors[] = 'First name is required.';
    if (!$f['last_name'])           $errors[] = 'Last name is required.';
    if (!$f['date_of_birth'])       $errors[] = 'Date of birth is required.';
    if (!$f['email'])               $errors[] = 'Email address is required.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!$f['phone'])               $errors[] = 'Phone number is required.';
    if (!$f['address'])             $errors[] = 'Street address is required.';
    if (!$f['city'])                $errors[] = 'City is required.';
    if (!$f['zip'])                 $errors[] = 'ZIP code is required.';
    if (strlen($f['password']) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($f['password'] !== $f['password2']) $errors[] = 'Passwords do not match.';
    if (!$f['agree_terms'])         $errors[] = 'You must agree to the Terms & Conditions.';
    if (!$f['agree_ferpa'])         $errors[] = 'You must acknowledge the FERPA notice.';

    if ($f['date_of_birth']) {
        $age = calcAge($f['date_of_birth']);
        if ($age < 16) $errors[] = 'Applicants must be at least 16 years of age.';
    }

    if (empty($errors)) {
        $dup = $db->prepare('SELECT id FROM students WHERE email=?');
        $dup->execute([$f['email']]);
        if ($dup->fetch()) $errors[] = 'An account with this email address already exists. Please log in or contact the registrar.';
    }

    if (empty($errors)) {
        $year  = date('Y');
        $prefix   = 'VSS' . $year; // e.g. VSS2026  

        $stmt = $db->prepare("SELECT MAX(CAST(SUBSTRING(student_id, 8) AS UNSIGNED)) FROM students WHERE student_id LIKE ?");
        $stmt->execute([$prefix . '%']);
        $lastNum = $stmt->fetchColumn();

        $nextNum = ($lastNum) ? (int)$lastNum + 1 : 1;
        $sid = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }
        
        $hash = password_hash($f['password'], PASSWORD_DEFAULT);
        $db->prepare('
            INSERT INTO students
                (student_id, first_name, last_name, date_of_birth, email, phone,
                address, city, state, zip, degree_id, enrollment_status,
                enrolled_date, password_hash, gpa, credits_earned)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,"Pending",?,?,0,0)
        ')->execute([
            $sid, $f['first_name'], $f['last_name'], $f['date_of_birth'],
            $f['email'], $f['phone'], $f['address'], $f['city'], $f['state'],
            $f['zip'], $f['degree_id'], date('Y-m-d'), $hash
        ]);

        $newStudentDbId = $db->lastInsertId();

        $_SESSION['app_submitted'] = [
            'sid'        => $sid,
            'name'       => $f['first_name'] . ' ' . $f['last_name'],
            'email'      => $f['email'],
            'start_term' => $f['start_term'],
        ];

        $success = true;
        $newSid  = $sid;
    }

if (isset($_SESSION['app_submitted']) && !$success) {
    $success = true;
    $newSid  = $_SESSION['app_submitted']['sid'];
}

$pageTitle = 'Apply to VolState';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> | <?= SITE_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
<style>
    body { background: #f0f2f5; }

    .app-shell {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .app-header {
        background: var(--vs-navy);
        padding: 1rem 0;
        border-bottom: 3px solid var(--vs-gold);
    }
    .app-header .brand { color:#fff; font-family:var(--font-serif); font-size:1.3rem; text-decoration:none; display:flex; align-items:center; gap:.6rem; }
    .app-header .brand i { color:var(--vs-gold); font-size:1.5rem; }

    .step-bar { background:#fff; border-bottom:1px solid #dee2e6; padding:1.25rem 0; }
    .step-bar .step { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; }
    .step-bar .step:not(:last-child)::after {
        content:''; position:absolute; top:18px; left:calc(50% + 20px);
        right:calc(-50% + 20px); height:2px; background:#dee2e6; z-index:0;
    }
    .step-num {
        width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.875rem; z-index:1; position:relative;
        background:#e9ecef; color:#adb5bd; border:2px solid #dee2e6;
        transition: all .3s;
    }
    .step-num.active { background:var(--vs-navy); color:#fff; border-color:var(--vs-navy); }
    .step-num.done   { background:var(--vs-gold); color:var(--vs-navy); border-color:var(--vs-gold); }
    .step-lbl { font-size:.72rem; font-weight:600; color:#adb5bd; margin-top:.4rem; text-transform:uppercase; letter-spacing:.05em; }
    .step-lbl.active { color:var(--vs-navy); }

    .form-section { display:none; }
    .form-section.active { display:block; }
    .section-card { background:#fff; border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); border:1px solid var(--vs-border); padding:2rem; margin-bottom:1.5rem; }
    .section-icon { width:44px; height:44px; border-radius:50%; background:rgba(200,151,58,.12); color:var(--vs-gold); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }

    .btn-next { background:var(--vs-navy); color:#fff; border:none; border-radius:2rem; padding:.65rem 2rem; font-weight:600; transition:all .25s; }
    .btn-next:hover { background:#253f5e; color:#fff; transform:translateY(-1px); }
    .btn-back { background:transparent; color:var(--vs-muted); border:1.5px solid var(--vs-border); border-radius:2rem; padding:.65rem 2rem; font-weight:600; transition:all .25s; }
    .btn-back:hover { border-color:var(--vs-navy); color:var(--vs-navy); }
    .btn-submit { background:var(--vs-gold); color:var(--vs-navy); border:none; border-radius:2rem; padding:.7rem 2.5rem; font-weight:700; font-size:1rem; transition:all .25s; box-shadow:0 4px 16px rgba(200,151,58,.3); }
    .btn-submit:hover { background:var(--vs-gold-lt); transform:translateY(-2px); }

    .strength-bar { height:4px; border-radius:2px; background:#e9ecef; margin-top:.4rem; overflow:hidden; }
    .strength-fill { height:100%; border-radius:2px; transition:all .3s; width:0%; }

    .success-card { background:#fff; border-radius:var(--radius-lg); box-shadow:var(--shadow-md); padding:3rem 2rem; text-align:center; max-width:560px; margin:0 auto; }
    .success-icon { width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,#d4edda,#c3e6cb); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; font-size:2.5rem; color:#155724; }

    .policy-box { background:#f8f9fa; border:1px solid #dee2e6; border-radius:var(--radius-md); padding:1rem 1.25rem; max-height:160px; overflow-y:auto; font-size:.825rem; color:#495057; line-height:1.6; }
</style>
</head>
<body class="app-shell">

<div class="app-header">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="<?= SITE_URL ?>/" class="brand"><i class="bi bi-mortarboard-fill"></i><?= SITE_NAME ?></a>
        <a href="<?= SITE_URL ?>/student/login.php" class="btn btn-outline-warning btn-sm rounded-pill px-3">
        <i class="bi bi-box-arrow-in-right me-1"></i>Already enrolled? Sign In
        </a>
    </div>
</div>

<?php if ($success): ?>

<div class="container py-5">
    <div class="success-card">
        <div class="success-icon"><i class="bi bi-check-lg"></i></div>
        <h2 class="fw-bold mb-2" style="font-family:var(--font-serif);color:var(--vs-navy);">Application Received!</h2>
        <p class="text-muted mb-4">Thank you for applying to Volunteer State College. Your application is now under review by our admissions office.</p>

        <div class="bg-light border rounded p-3 mb-4 text-start">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Your Student ID:</span>
            <strong class="font-monospace text-navy fs-5"><?= sanitize($newSid) ?></strong>
        </div>
        <div class="d-flex justify-content-between">
            <span class="text-muted small">Application Status:</span>
            <span class="badge bg-warning text-dark">Pending Review</span>
        </div>
        </div>

        <div class="alert alert-info text-start small mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>What happens next?</strong><br>
        Our admissions team will review your application within 2–3 business days. Once approved, you will receive a confirmation email and your account will be activated — you can then log in and register for classes.
        </div>

        <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="<?= SITE_URL ?>/student/login.php" class="btn btn-vs rounded-pill px-4">
            <i class="bi bi-box-arrow-in-right me-2"></i>Go to Student Login
        </a>
        <a href="<?= SITE_URL ?>/" class="btn btn-outline-secondary rounded-pill px-4">Back to Homepage</a>
        </div>
    </div>
</div>

<?php else: ?>

<div class="step-bar">
    <div class="container">
        <div class="d-flex" id="stepBar">
        <div class="step"><div class="step-num active" id="sn1">1</div><div class="step-lbl active">Personal</div></div>
        <div class="step"><div class="step-num"         id="sn2">2</div><div class="step-lbl">Contact</div></div>
        <div class="step"><div class="step-num"         id="sn3">3</div><div class="step-lbl">Academic</div></div>
        <div class="step"><div class="step-num"         id="sn4">4</div><div class="step-lbl">Account</div></div>
        <div class="step"><div class="step-num"         id="sn5">5</div><div class="step-lbl">Review</div></div>
        </div>
    </div>
</div>

<div class="container py-4" style="max-width:780px;">

    <?php if ($errors): ?>
        <div class="alert alert-danger mb-3">
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
        </ul>
        </div>
    <?php endif; ?>

    <form method="POST" id="appForm" novalidate>

        <div class="form-section active" id="step1">
        <div class="section-card">
            <div class="d-flex align-items-center gap-3 mb-4">
            <div class="section-icon"><i class="bi bi-person-fill"></i></div>
            <div><h5 class="fw-bold mb-0 text-navy">Personal Information</h5><p class="text-muted small mb-0">Tell us about yourself</p></div>
            </div>
            <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" class="form-control" value="<?= sanitize($_POST['first_name'] ?? '') ?>" placeholder="e.g. Emily" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Last Name *</label>
                <input type="text" name="last_name" class="form-control" value="<?= sanitize($_POST['last_name'] ?? '') ?>" placeholder="e.g. Johnson" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= sanitize($_POST['date_of_birth'] ?? '') ?>" max="<?= date('Y-m-d', strtotime('-16 years')) ?>" required>
                <div class="form-text">Must be at least 16 years old</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                <option value="">— Prefer not to say —</option>
                <option value="Male"   <?= ($_POST['gender']??'')==='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= ($_POST['gender']??'')==='Female'?'selected':'' ?>>Female</option>
                <option value="Non-binary" <?= ($_POST['gender']??'')==='Non-binary'?'selected':'' ?>>Non-binary</option>
                <option value="Other"  <?= ($_POST['gender']??'')==='Other'?'selected':'' ?>>Other / Self-describe</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Last 4 of SSN</label>
                <input type="text" name="ssn_last4" class="form-control" maxlength="4" pattern="\d{4}" placeholder="####" value="<?= sanitize($_POST['ssn_last4'] ?? '') ?>">
                <div class="form-text">Used for identity verification</div>
            </div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn-next" onclick="nextStep(1)">Next: Contact Info <i class="bi bi-arrow-right ms-2"></i></button>
        </div>
        </div>

        <div class="form-section" id="step2">
        <div class="section-card">
            <div class="d-flex align-items-center gap-3 mb-4">
            <div class="section-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <div><h5 class="fw-bold mb-0 text-navy">Contact Information</h5><p class="text-muted small mb-0">Where can we reach you?</p></div>
            </div>
            <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="you@example.com" required>
                <div class="form-text">This will be your login email</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone Number *</label>
                <input type="tel" name="phone" class="form-control" value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="(615) 555-0100" required>
            </div>
            <div class="col-12">
                <label class="form-label">Street Address *</label>
                <input type="text" name="address" class="form-control" value="<?= sanitize($_POST['address'] ?? '') ?>" placeholder="123 Main St" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" value="<?= sanitize($_POST['city'] ?? '') ?>" placeholder="Gallatin" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">State *</label>
                <select name="state" class="form-select" required>
                <?php foreach ($usStates as $st): ?>
                    <option value="<?= $st ?>" <?= ($_POST['state'] ?? 'Tennessee') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ZIP Code *</label>
                <input type="text" name="zip" class="form-control" maxlength="10" value="<?= sanitize($_POST['zip'] ?? '') ?>" placeholder="37066" required>
            </div>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn-back" onclick="prevStep(2)"><i class="bi bi-arrow-left me-2"></i>Back</button>
            <button type="button" class="btn-next" onclick="nextStep(2)">Next: Academic Intent <i class="bi bi-arrow-right ms-2"></i></button>
        </div>
        </div>

        <div class="form-section" id="step3">
        <div class="section-card">
            <div class="d-flex align-items-center gap-3 mb-4">
            <div class="section-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div><h5 class="fw-bold mb-0 text-navy">Academic Intent</h5><p class="text-muted small mb-0">Tell us about your educational goals</p></div>
            </div>
            <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Intended Degree / Major</label>
                <select name="degree_id" class="form-select">
                <option value="">— Undecided / Explore Options —</option>
                <?php foreach ($degrees as $deg): ?>
                    <option value="<?= $deg['id'] ?>" <?= ($_POST['degree_id'] ?? '') == $deg['id'] ? 'selected' : '' ?>>
                    <?= sanitize($deg['level'] . ' — ' . $deg['title']) ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <div class="form-text">You can change this later in your portal</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Intended Start Term *</label>
                <select name="start_term" class="form-select" required>
                <option value="">— Select —</option>
                <?php
                    $yr = (int)date('Y');
                    $terms = [
                        "Summer $yr", "Fall $yr",
                        "Spring " . ($yr+1), "Summer " . ($yr+1), "Fall " . ($yr+1)
                    ];
                    foreach ($terms as $t): ?>
                    <option value="<?= $t ?>" <?= ($_POST['start_term'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Enrollment Type</label>
                <select name="enrollment_type" class="form-select">
                <?php foreach (['Full-Time','Part-Time','Online Only'] as $et): ?>
                    <option value="<?= $et ?>" <?= ($_POST['enrollment_type'] ?? 'Full-Time') === $et ? 'selected' : '' ?>><?= $et ?></option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">High School Name *</label>
                <input type="text" name="high_school" class="form-control" value="<?= sanitize($_POST['high_school'] ?? '') ?>" placeholder="e.g. Gallatin High School" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">HS Graduation Year</label>
                <input type="number" name="hs_grad_year" class="form-control" min="1970" max="<?= date('Y') + 2 ?>" value="<?= sanitize($_POST['hs_grad_year'] ?? '') ?>" placeholder="<?= date('Y') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Prior College / University <span class="text-muted fw-normal">(if any)</span></label>
                <input type="text" name="prior_college" class="form-control" value="<?= sanitize($_POST['prior_college'] ?? '') ?>" placeholder="e.g. Nashville State Community College — transfer student">
                <div class="form-text">Leave blank if this is your first college enrollment</div>
            </div>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn-back" onclick="prevStep(3)"><i class="bi bi-arrow-left me-2"></i>Back</button>
            <button type="button" class="btn-next" onclick="nextStep(3)">Next: Create Account <i class="bi bi-arrow-right ms-2"></i></button>
        </div>
        </div>

        <div class="form-section" id="step4">
        <div class="section-card">
            <div class="d-flex align-items-center gap-3 mb-4">
            <div class="section-icon"><i class="bi bi-lock-fill"></i></div>
            <div><h5 class="fw-bold mb-0 text-navy">Create Your Account</h5><p class="text-muted small mb-0">Set a secure password for your student portal</p></div>
            </div>
            <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Password *</label>
                <div class="input-group">
                <input type="password" name="password" id="pwdField" class="form-control" placeholder="At least 8 characters" required oninput="checkStrength(this.value)">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdField',this)"><i class="bi bi-eye"></i></button>
                </div>
                <div class="strength-bar mt-2"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="form-text" id="strengthText">Enter a password to see strength</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password *</label>
                <div class="input-group">
                <input type="password" name="password2" id="pwd2Field" class="form-control" placeholder="Repeat your password" required oninput="checkMatch()">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwd2Field',this)"><i class="bi bi-eye"></i></button>
                </div>
                <div class="form-text" id="matchText"></div>
            </div>
            </div>

            <div class="mt-4">
            <h6 class="fw-bold mb-3 text-navy"><i class="bi bi-shield-check me-2"></i>Password Requirements</h6>
            <div class="row g-2 small text-muted" id="pwdReqs">
                <div class="col-6"><span id="req-len"><i class="bi bi-circle me-1"></i>At least 8 characters</span></div>
                <div class="col-6"><span id="req-upper"><i class="bi bi-circle me-1"></i>One uppercase letter</span></div>
                <div class="col-6"><span id="req-num"><i class="bi bi-circle me-1"></i>One number</span></div>
                <div class="col-6"><span id="req-special"><i class="bi bi-circle me-1"></i>One special character</span></div>
            </div>
            </div>
        </div>

        <div class="section-card">
            <div class="d-flex align-items-center gap-3 mb-3">
            <div class="section-icon"><i class="bi bi-file-text-fill"></i></div>
            <div><h5 class="fw-bold mb-0 text-navy">Agreements &amp; Acknowledgements</h5></div>
            </div>

            <div class="mb-4">
            <label class="form-label fw-bold">Terms &amp; Conditions</label>
            <div class="policy-box mb-2">
                By submitting this application, you certify that all information provided is accurate and complete. You understand that any falsification of information may result in denial of admission or dismissal from the college. You agree to abide by the academic and conduct policies of Volunteer State College. Enrollment is subject to acceptance by the Office of Admissions. The college reserves the right to verify all information submitted and request supporting documentation. Tuition and fees are due as published in the academic calendar. Failure to pay by the published deadline may result in removal from classes.
            </div>
            <div class="form-check">
                <input type="checkbox" name="agree_terms" id="agreeTerms" class="form-check-input" value="1" <?= !empty($_POST['agree_terms']) ? 'checked' : '' ?> required>
                <label class="form-check-label fw-600" for="agreeTerms">I have read and agree to the Terms &amp; Conditions *</label>
            </div>
            </div>

            <div>
            <label class="form-label fw-bold">FERPA Notice</label>
            <div class="policy-box mb-2">
                The Family Educational Rights and Privacy Act (FERPA) affords students certain rights with respect to their education records. These rights include: (1) The right to inspect and review education records within 45 days of the day the college receives a request for access. (2) The right to request the amendment of education records believed to be inaccurate or misleading. (3) The right to provide written consent before the college discloses personally identifiable information from education records, except to the extent that FERPA authorizes disclosure without consent. (4) The right to file a complaint with the U.S. Department of Education.
            </div>
            <div class="form-check">
                <input type="checkbox" name="agree_ferpa" id="agreeFerpa" class="form-check-input" value="1" <?= !empty($_POST['agree_ferpa']) ? 'checked' : '' ?> required>
                <label class="form-check-label fw-600" for="agreeFerpa">I acknowledge receipt of the FERPA Notice *</label>
            </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" class="btn-back" onclick="prevStep(4)"><i class="bi bi-arrow-left me-2"></i>Back</button>
            <button type="button" class="btn-next" onclick="nextStep(4)">Review Application <i class="bi bi-arrow-right ms-2"></i></button>
        </div>
        </div>

        <div class="form-section" id="step5">
        <div class="section-card">
            <div class="d-flex align-items-center gap-3 mb-4">
            <div class="section-icon"><i class="bi bi-clipboard-check-fill"></i></div>
            <div><h5 class="fw-bold mb-0 text-navy">Review Your Application</h5><p class="text-muted small mb-0">Please verify your information before submitting</p></div>
            </div>

            <!-- Review table — populated by JS -->
            <div id="reviewTable">
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Full Name</div>
                <div class="col-7 fw-600 small" id="rv-name">—</div>
            </div>
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Date of Birth</div>
                <div class="col-7 fw-600 small" id="rv-dob">—</div>
            </div>
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Email</div>
                <div class="col-7 fw-600 small" id="rv-email">—</div>
            </div>
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Phone</div>
                <div class="col-7 fw-600 small" id="rv-phone">—</div>
            </div>
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Address</div>
                <div class="col-7 fw-600 small" id="rv-address">—</div>
            </div>
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Intended Major</div>
                <div class="col-7 fw-600 small" id="rv-major">—</div>
            </div>
            <div class="row g-0 border-bottom py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Start Term</div>
                <div class="col-7 fw-600 small" id="rv-term">—</div>
            </div>
            <div class="row g-0 py-2">
                <div class="col-5 text-muted small fw-500 ps-1">Enrollment Type</div>
                <div class="col-7 fw-600 small" id="rv-etype">—</div>
            </div>
            </div>

            <div class="alert alert-warning mt-4 small mb-0">
            <i class="bi bi-clock me-2"></i><strong>Processing time:</strong> Applications are typically reviewed within 2–3 business days. You will receive an email notification when your account is approved.
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn-back" onclick="prevStep(5)"><i class="bi bi-arrow-left me-2"></i>Back &amp; Edit</button>
            <button type="submit" class="btn-submit">
            <i class="bi bi-send-fill me-2"></i>Submit Application
            </button>
        </div>
        </div>

    </form>

    <p class="text-center text-muted small mt-4">
        Already a student? <a href="<?= SITE_URL ?>/student/login.php">Sign in to your portal</a> &nbsp;|&nbsp;
        <a href="<?= SITE_URL ?>/">Back to Homepage</a>
    </p>

</div>
<?php endif; // end !$success ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentStep = 1;
const totalSteps = 5;

function showStep(n) {
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');

    for (let i = 1; i <= totalSteps; i++) {
        const num = document.getElementById('sn' + i);
        const lbl = num?.nextElementSibling;
        num?.classList.remove('active','done');
        lbl?.classList.remove('active');
        if (i < n)  { num?.classList.add('done'); }
        if (i === n){ num?.classList.add('active'); lbl?.classList.add('active'); }
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
    currentStep = n;
    if (n === 5) populateReview();
}

function nextStep(from) {
    if (!validateStep(from)) return;
    showStep(from + 1);
}
function prevStep(from) { showStep(from - 1); }

function validateStep(step) {
    let ok = true;
    const section = document.getElementById('step' + step);
    section.querySelectorAll('[required]').forEach(el => {
        el.classList.remove('is-invalid');
        if (!el.value.trim()) {
        el.classList.add('is-invalid');
        ok = false;
        }
    });
    if (step === 4) {
        const p1 = document.getElementById('pwdField').value;
        const p2 = document.getElementById('pwd2Field').value;
        if (p1.length < 8) { document.getElementById('pwdField').classList.add('is-invalid'); ok = false; }
        if (p1 !== p2)     { document.getElementById('pwd2Field').classList.add('is-invalid'); ok = false; }
        if (!document.getElementById('agreeTerms').checked ||
            !document.getElementById('agreeFerpa').checked) {
        alert('Please check both agreement boxes before continuing.');
        ok = false;
        }
    }
    if (!ok) {
        const first = section.querySelector('.is-invalid');
        if (first) first.scrollIntoView({ behavior:'smooth', block:'center' });
    }
    return ok;
}

function populateReview() {
    const g = id => document.querySelector('[name="'+id+'"]')?.value || '—';
    const degSel = document.querySelector('[name="degree_id"]');
    const major = degSel?.selectedIndex > 0 ? degSel.options[degSel.selectedIndex].text : 'Undecided';
    document.getElementById('rv-name').textContent    = [g('first_name'), g('last_name')].filter(Boolean).join(' ');
    document.getElementById('rv-dob').textContent     = g('date_of_birth');
    document.getElementById('rv-email').textContent   = g('email');
    document.getElementById('rv-phone').textContent   = g('phone');
    document.getElementById('rv-address').textContent = [g('address'), g('city'), g('state'), g('zip')].filter(v => v && v!=='—').join(', ');
    document.getElementById('rv-major').textContent   = major;
    document.getElementById('rv-term').textContent    = g('start_term');
    document.getElementById('rv-etype').textContent   = g('enrollment_type');
}

function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    const checks = {
        len:     val.length >= 8,
        upper:   /[A-Z]/.test(val),
        num:     /[0-9]/.test(val),
        special: /[^A-Za-z0-9]/.test(val),
    };
    Object.entries(checks).forEach(([k,v]) => {
        score += v ? 1 : 0;
        const el = document.getElementById('req-' + k);
        if (el) {
        el.innerHTML = v
            ? '<i class="bi bi-check-circle-fill text-success me-1"></i>' + el.textContent.trim()
            : '<i class="bi bi-circle me-1"></i>' + el.textContent.trim();
        el.className = v ? 'text-success' : '';
        }
    });
    const pct = (score/4)*100;
    fill.style.width = pct + '%';
    fill.style.background = score <= 1 ? '#dc3545' : score === 2 ? '#fd7e14' : score === 3 ? '#ffc107' : '#198754';
    text.textContent = ['','Weak','Fair','Good','Strong'][score] || '';
    text.className = 'form-text ' + (['','text-danger','text-warning','text-warning','text-success'][score] || '');
}

function checkMatch() {
    const p1 = document.getElementById('pwdField').value;
    const p2 = document.getElementById('pwd2Field').value;
    const mt = document.getElementById('matchText');
    if (!p2) { mt.textContent = ''; return; }
    if (p1 === p2) {
        mt.textContent = '✓ Passwords match';
        mt.className = 'form-text text-success';
    } else {
        mt.textContent = '✗ Passwords do not match';
        mt.className = 'form-text text-danger';
    }
}

function togglePwd(fieldId, btn) {
    const f = document.getElementById(fieldId);
    if (f.type === 'password') { f.type = 'text'; btn.innerHTML = '<i class="bi bi-eye-slash"></i>'; }
    else                       { f.type = 'password'; btn.innerHTML = '<i class="bi bi-eye"></i>'; }
}

<?php if ($errors): ?>
showStep(1);
<?php endif; ?>
</script>
</body>
</html>
