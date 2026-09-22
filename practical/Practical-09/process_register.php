<?php
/**
 * StudentHub — Secure Registration Processor (Practical 9)
 * 
 * Handles POST submission, validates CSRF token, verifies uniqueness
 * of email/username/roll_no, securely hashes password, and executes
 * transactional INSERT with parameterized prepared statements.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validate.php';
require_once __DIR__ . '/config/db.php';
// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}
// 1. CSRF Token Validation (Advanced Extension)
if (!validateCSRFToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['reg_errors'] = ['Security verification failed (invalid or expired CSRF token). Please try again.'];
    $_SESSION['reg_old'] = $_POST;
    header('Location: register.php');
    exit;
}
// 2. Validate Input Fields
$errors = validateRegistration($_POST);
// Retain submitted values (excluding passwords)
$oldData = [
    'roll_no'   => sanitizeInput($_POST['roll_no'] ?? ''),
    'username'  => sanitizeInput($_POST['username'] ?? ''),
    'fullname'  => sanitizeInput($_POST['fullname'] ?? ''),
    'email'     => sanitizeInput($_POST['email'] ?? ''),
    'mobile'    => sanitizeInput($_POST['mobile'] ?? ''),
    'gender'    => sanitizeInput($_POST['gender'] ?? ''),
    'course'    => sanitizeInput($_POST['course'] ?? ''),
    'year'      => sanitizeInput($_POST['year'] ?? ''),
    'terms'     => !empty($_POST['terms']),
];
if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old'] = $oldData;
    header('Location: register.php');
    exit;
}
// 3. Database Operations
try {
    $pdo = getDBConnection();
    // Check duplicate Roll Number in students table
    $stmtRoll = $pdo->prepare("SELECT id FROM students WHERE roll_no = ? LIMIT 1");
    $stmtRoll->execute([$oldData['roll_no']]);
    if ($stmtRoll->fetch()) {
        $errors['roll_no'] = 'This Roll Number is already registered.';
    }
    // Check duplicate Email in both students and users tables
    $stmtEmail = $pdo->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    $stmtEmail->execute([$oldData['email']]);
    if ($stmtEmail->fetch()) {
        $errors['email'] = 'This Email address is already in use by another student.';
    } else {
        $stmtUserEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmtUserEmail->execute([$oldData['email']]);

        if ($stmtUserEmail->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        }
    }
    // Check duplicate Username in users table
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmtUser->execute([$oldData['username']]);
    if ($stmtUser->fetch()) {
        $errors['username'] = 'This username is already taken. Please choose another.';
    }
    // If duplicate check failed, return back with errors
    if (!empty($errors)) {
        $_SESSION['reg_errors'] = $errors;
        $_SESSION['reg_old'] = $oldData;
        header('Location: register.php');
        exit;
    }
    // 4. Secure Password Hashing
    // Uses PASSWORD_BCRYPT with standard salt and cost factor
    $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    // 5. Transactional Insert
    $pdo->beginTransaction();
    // Insert into students table
    $sqlStudent = "INSERT INTO students (roll_no, fullname, email, mobile, gender, course, year, status)
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
    $stmtInsertStudent = $pdo->prepare($sqlStudent);
    $stmtInsertStudent->execute([
        $oldData['roll_no'],
        $oldData['fullname'],
        $oldData['email'],
        $oldData['mobile'],
        $oldData['gender'],
        $oldData['course'],
        (int)$oldData['year'],
    ]);
    $studentId = (int)$pdo->lastInsertId();
    // Insert into users table
    $sqlUser = "INSERT INTO users (student_id, username, email, password_hash, role, status)
                VALUES (?, ?, ?, ?, 'student', 'active')";
    $stmtInsertUser = $pdo->prepare($sqlUser);
    $stmtInsertUser->execute([
        $studentId,
        $oldData['username'],
        $oldData['email'],
        $passwordHash,
    ]);
    $userId = (int)$pdo->lastInsertId();
    // Advanced Extension: Security Audit Log Entry
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $details = sprintf(
        "Student registered: %s (Roll: %s, UserID: %d)",
        $oldData['fullname'],
        $oldData['roll_no'],
        $userId
    );
    $sqlAudit = "INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'REGISTRATION_SUCCESS', ?, 
?)";
    $stmtAudit = $pdo->prepare($sqlAudit);
    $stmtAudit->execute([$userId, $details, $clientIp]);
    // Commit Transaction
    $pdo->commit();
    // Flash success notification
    $_SESSION['reg_success'] = "Account successfully registered for '{$oldData['fullname']}' ({$oldData['roll_no']}).
";
    header('Location: register.php');
    exit;
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[Registration Error]: " . $e->getMessage());
    $_SESSION['reg_errors'] = ['An unexpected error occurred while processing registration. Please try again. (
Details: ' . $e->getMessage() . ')'];
    $_SESSION['reg_old'] = $oldData;
    header('Location: register.php');
    exit;
}
