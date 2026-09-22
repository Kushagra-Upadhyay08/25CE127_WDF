<?php
/**
 * StudentHub — CSRF Protection Helper (Practical 9)
 * 
 * Generates and validates Cross-Site Request Forgery (CSRF) tokens
 * using cryptographically secure random bytes and timing-attack-safe comparisons.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Generates or retrieves the active session CSRF token.
 * 
 * @return string
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
/**
 * Validates the submitted token against the stored session token.
 * 
 * @param string|null $token
 * @return bool
 */
function validateCSRFToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
/**
 * Returns an HTML hidden input tag containing the CSRF token.
 * 
 * @return string

 */
function csrfField(): string {
    $token = htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
