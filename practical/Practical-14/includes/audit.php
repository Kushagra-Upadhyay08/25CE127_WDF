<?php
declare(strict_types=1);
function writeAuditLog(PDO $pdo, string $action, string $details): void {
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_SESSION['user_id'] ?? null, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
}
