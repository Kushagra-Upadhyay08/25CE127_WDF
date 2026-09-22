<?php
/**
 * StudentHub — Secure Login Processor (Practical 10)
 * 
 * Verifies password via password_verify(), prevents session fixation,
 * handles remember-me token, updates last_login timestamp, and performs
 * role-based redirection.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/config/db.php';
initSession();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}
// 1. Verify CSRF Token
if (!validateCSRFToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['login_error'] = 'Invalid or expired security token. Please try again.';
    header('Location: login.php');
    exit;
}
$identity   = trim($_POST['identity'] ?? '');
$password   = $_POST['password'] ?? '';
$rememberMe = !empty($_POST['remember_me']);
if ($identity === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both username/email and password.';
    header('Location: login.php');
    exit;
}
try {
    $pdo = getDBConnection();
    // 2. Fetch User Record by Email OR Username using Prepared Statement
    $stmt = $pdo->prepare("
        SELECT id, student_id, username, email, password_hash, role, status 
        FROM users 
        WHERE (email = ? OR username = ?) 
        LIMIT 1
    ");
    $stmt->execute([$identity, $identity]);

    $user = $stmt->fetch();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    // 3. Verify Account and Password Hash
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Record failed audit log
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, 
'LOGIN_FAILED', ?, ?)");
        $logStmt->execute(["Failed login attempt for identity: {$identity}", $ip]);
        $_SESSION['login_error'] = 'Invalid username/email or password.';
        header('Location: login.php');
        exit;
    }
    // 4. Verify Account Status
    if ($user['status'] !== 'active') {
        $_SESSION['login_error'] = 'This account has been deactivated. Please contact the administrator.';
        header('Location: login.php');
        exit;
    }
    // 5. Authenticate Session (Regenerates Session ID, Updates last_login, Sets remember cookie)
    loginUser($user, $rememberMe);
    // 6. Role-Based Redirection (RBAC)
    if ($user['role'] === 'admin') {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_student.php');
    }
    exit;
} catch (Exception $e) {
    error_log("[Login Error]: " . $e->getMessage());
    $_SESSION['login_error'] = 'Database connection error during authentication: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
