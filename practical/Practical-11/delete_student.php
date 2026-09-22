<?php
/**
 * StudentHub — Delete Student (Practical 11: Delete / Soft Delete)
 * 
 * Handles both permanent deletion and soft delete (Advanced Extension).
 * Protected against CSRF and requires POST method.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = 'Invalid request method. Deletion must use POST.';
    header('Location: index.php');
    exit;
}
// 1. Verify CSRF Token
if (!validateCSRFToken($_POST['csrf_token'] ?? null)) {
    $_SESSION['flash_error'] = 'Security verification failed (invalid CSRF token).';
    header('Location: index.php');
    exit;
}
$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? 'soft_delete';
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid student ID specified.';
    header('Location: index.php');
    exit;
}
try {
    $pdo = getDBConnection();
    // Verify student exists
    $checkStmt = $pdo->prepare("SELECT id, roll_no, fullname FROM students WHERE id = ? LIMIT 1");
    $checkStmt->execute([$id]);
    $student = $checkStmt->fetch();
    if (!$student) {
        $_SESSION['flash_error'] = "Student record #{$id} not found.";
        header('Location: index.php');
        exit;
    }
    if ($action === 'soft_delete') {
        // Advanced Extension: Soft delete by setting status = 'inactive'

        $stmt = $pdo->prepare("UPDATE students SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Student {$student['fullname']} ({$student['roll_no']}) has been archived (soft 
deleted).";
    } elseif ($action === 'restore') {
        // Restore soft-deleted student
        $stmt = $pdo->prepare("UPDATE students SET status = 'active' WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Student {$student['fullname']} ({$student['roll_no']}) has been restored to 
Active status.";
    } elseif ($action === 'hard_delete') {
        // Permanent deletion from database
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = "Student {$student['fullname']} ({$student['roll_no']}) was permanently deleted 
from the database.";
    } else {
        $_SESSION['flash_error'] = "Unknown deletion action: {$action}";
    }
} catch (Exception $e) {
    error_log("[Delete Student Error]: " . $e->getMessage());
    $_SESSION['flash_error'] = "Database error during deletion: " . $e->getMessage();
}
header('Location: index.php');
exit;
