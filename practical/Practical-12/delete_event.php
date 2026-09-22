<?php
/**
 * StudentHub — Delete Event (Practical 12: Delete)
 * 
 * Removes event record, cleans up uploaded poster image from disk,
 * logs deletion in audit logs, and cascades registration cleanup.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';
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
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid event ID.';
    header('Location: index.php');
    exit;
}
try {
    $pdo = getDBConnection();
    // Fetch Event to retrieve poster path
    $stmt = $pdo->prepare("SELECT id, title, poster_url FROM events WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if (!$event) {
        $_SESSION['flash_error'] = "Event #{$id} was not found.";
        header('Location: index.php');
        exit;
    }
    // 2. Delete Poster File from Disk
    if (!empty($event['poster_url'])) {
        deletePosterFile($event['poster_url']);
    }
    // 3. Delete Event Record from MySQL
    $delStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $delStmt->execute([$id]);
    // 4. Advanced Extension: Audit Log Entry
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, 
'EVENT_DELETED', ?, ?)");
    $auditStmt->execute(["Deleted event #{$id} '{$event['title']}' and poster", $ip]);
    $_SESSION['flash_success'] = "Event '{$event['title']}' was deleted successfully.";
} catch (Exception $e) {
    error_log("[Delete Event Error]: " . $e->getMessage());
    $_SESSION['flash_error'] = "Database error during event deletion: " . $e->getMessage();
}
header('Location: index.php');
exit;
