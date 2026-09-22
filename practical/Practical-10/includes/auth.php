<?php
/**
 * StudentHub — Authentication & Session Management Middleware (Practical 10)
 * 
 * Implements:
 * - Secure session initiation with HttpOnly & SameSite cookies
 * - Session fixation prevention via session_regenerate_id(true)
 * - Session inactivity timeout (15 minutes)
 * - Role-Based Access Control (RBAC)
 * - Cryptographically secure Remember-Me persistent authentication (hash in DB)
 * - Last login timestamp tracking
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/../config/db.php';
// Inactivity timeout limit: 15 minutes (900 seconds)
defined('SESSION_TIMEOUT_SECONDS') or define('SESSION_TIMEOUT_SECONDS', 900);
/**
 * Initializes secure PHP session settings.
 */
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Enforce cookie security parameters
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,          // Cleared on browser close (unless remember-me is active)
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,       // Prevents JavaScript access (mitigates XSS cookie theft)
            'samesite' => 'Strict',   // Mitigates CSRF
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }
}
/**
 * Logs in a verified user and initializes session state.
 * 
 * @param array $user User record from MySQL
 * @param bool $rememberMe Whether to generate persistent remember-me cookie
 */
function loginUser(array $user, bool $rememberMe = false): void {

    initSession();
    // 1. Prevent Session Fixation by regenerating session ID
    session_regenerate_id(true);
    // 2. Set Session state
    $_SESSION['user_id']        = (int)$user['id'];
    $_SESSION['student_id']     = $user['student_id'] ? (int)$user['student_id'] : null;
    $_SESSION['username']       = $user['username'];
    $_SESSION['email']          = $user['email'];
    $_SESSION['role']           = $user['role']; // 'student' or 'admin'
    $_SESSION['logged_in_time'] = time();
    $_SESSION['last_activity']  = time();
    // 3. Intermediate Extension: Update last_login timestamp
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        // Audit log entry
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $logStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 
'LOGIN_SUCCESS', 'User authenticated successfully', ?)");
        $logStmt->execute([$user['id'], $ip]);
    } catch (Exception $e) {
        error_log("[Auth] Failed to update last_login: " . $e->getMessage());
    }
    // 4. Advanced Extension: Remember-Me Token Generation
    if ($rememberMe) {
        createRememberToken($user['id']);
    }
}
/**
 * Creates a cryptographically secure Remember-Me token in database and cookie.
 * 
 * Architecture:
 * - Selector: 16 random bytes (hex 32 chars) — looked up in DB
 * - Validator: 32 random bytes (hex 64 chars) — validated via SHA-256 hash in DB
 * 
 * @param int $userId
 */
function createRememberToken(int $userId): void {
    try {
        $pdo = getDBConnection();
        $selector  = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 86400)); // 30 days
        // Remove any old tokens for this user
        $delStmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $delStmt->execute([$userId]);
        // Insert new hashed token
        $insStmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, 
?, ?, ?)");
        $insStmt->execute([$userId, $selector, $tokenHash, $expiresAt]);
        // Set HttpOnly cookie
        $cookieValue = $selector . ':' . $validator;
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('studenthub_remember', $cookieValue, [
            'expires'  => time() + (30 * 86400),
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    } catch (Exception $e) {
        error_log("[Auth] Failed to create remember token: " . $e->getMessage());
    }
}
/**
 * Attempts to authenticate user from Remember-Me cookie if session is expired.
 * 
 * @return bool
 */
function tryRememberMeLogin(): bool {
    if (empty($_COOKIE['studenthub_remember'])) {
        return false;
    }
    $parts = explode(':', $_COOKIE['studenthub_remember']);
    if (count($parts) !== 2) {
        return false;
    }
    [$selector, $validator] = $parts;
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT rt.*, u.username, u.email, u.role, u.student_id, u.status 
            FROM remember_tokens rt
            JOIN users u ON rt.user_id = u.id
            WHERE rt.selector = ? AND rt.expires_at > NOW() AND u.status = 'active'
            LIMIT 1

        ");
        $stmt->execute([$selector]);
        $row = $stmt->fetch();
        if ($row && hash_equals($row['token_hash'], hash('sha256', $validator))) {
            // Valid token! Re-login user without regenerating remember token
            loginUser([
                'id'         => $row['user_id'],
                'student_id' => $row['student_id'],
                'username'   => $row['username'],
                'email'      => $row['email'],
                'role'       => $row['role'],
            ], false);
            return true;
        }
    } catch (Exception $e) {
        error_log("[Auth] Remember-me validation error: " . $e->getMessage());
    }
    return false;
}
/**
 * Checks for session inactivity timeout.
 */
function checkSessionTimeout(): void {
    initSession();
    if (!empty($_SESSION['user_id'])) {
        $lastActivity = $_SESSION['last_activity'] ?? time();
        if ((time() - $lastActivity) > SESSION_TIMEOUT_SECONDS) {
            // Session expired
            logoutUser(false);
            header('Location: login.php?msg=timeout');
            exit;
        }
        // Update activity timestamp
        $_SESSION['last_activity'] = time();
    }
}
/**
 * Middleware: Requires the user to be authenticated.
 */
function requireLogin(): void {
    initSession();
    checkSessionTimeout();
    if (empty($_SESSION['user_id'])) {
        // Attempt remember-me login
        if (tryRememberMeLogin()) {
            return;
        }
        header('Location: login.php?msg=unauthorized');
        exit;
    }
}
/**
 * Middleware: Enforces Role-Based Access Control (RBAC).
 * 
 * @param string|array $allowedRoles Single role or array of allowed roles (e.g. ['admin'])
 */
function requireRole($allowedRoles): void {
    requireLogin();
    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, $roles, true)) {
        header('Location: unauthorized.php');
        exit;
    }
}
/**
 * Destroys session, unsets cookies, and cleans DB remember tokens.
 * 
 * @param bool $clearFlash Whether to clear flash messages
 */
function logoutUser(bool $clearFlash = true): void {
    initSession();
    // Remove DB remember token
    if (!empty($_SESSION['user_id'])) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $logStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 
'LOGOUT', 'User logged out', ?)");
            $logStmt->execute([$_SESSION['user_id'], $ip]);
        } catch (Exception $e) {
            // Ignore DB error during logout
        }
    }
    // Clear Remember-Me cookie
    if (isset($_COOKIE['studenthub_remember'])) {
        setcookie('studenthub_remember', '', [

            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
        ]);
        unset($_COOKIE['studenthub_remember']);
    }
    // Save flash message if needed
    $msg = $_SESSION['flash_msg'] ?? null;
    // Destroy session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    if (!$clearFlash && $msg) {
        session_start();
        $_SESSION['flash_msg'] = $msg;
    }
}
