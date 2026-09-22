<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/audit.php';
requireRole(['admin']);
$stats = ['users' => 0, 'students' => 0, 'events' => 0, 'registrations' => 0]; $registrations = []; $logs = []; 
$error = null;
try {
    $pdo = getDBConnection();
    foreach (array_keys($stats) as $table) $stats[$table] = (int)$pdo->query("SELECT COUNT(*) FROM $table")-
>fetchColumn();
    $registrations = $pdo->query("SELECT s.fullname, s.roll_no, e.title, r.registered_at, r.status FROM registrations 
r JOIN students s ON s.id=r.student_id JOIN events e ON e.id=r.event_id ORDER BY r.registered_at DESC LIMIT 6")-
>fetchAll();
    $logs = $pdo->query('SELECT action, details, ip_address, created_at FROM audit_logs ORDER BY created_at DESC 
LIMIT 6')->fetchAll();
    writeAuditLog($pdo, 'DASHBOARD_VIEWED', 'Administrator opened the Practical 14 dashboard.');
} catch (Throwable $e) { error_log('[Practical-14] '.$e->getMessage()); $error = 'Dashboard data is temporarily 
unavailable. Check the database connection.'; }
$menu = [['label'=>'Dashboard','href'=>'index.php'],['label'=>'Students','href'=>'../Practical-11/index.php'],[
'label'=>'Events','href'=>'../Practical-12/index.php'],['label'=>'API Console','href'=>'../Practical-13/index.html']];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-
scale=1"><title>Admin Dashboard - Practical 14</title><link rel="stylesheet" href="style.css"></head><body>
<aside><div class="brand">Student<span>Hub</span><small>ADMIN CONSOLE · P14</small></div><nav><?php foreach ($menu as 
$item): ?><a class="<?= $item['href']==='index.php'?'active':'' ?>" href="<?= htmlspecialchars($item['href']) ?>"><?= 
htmlspecialchars($item['label']) ?></a><?php endforeach; ?></nav><div class="profile"><b><?= htmlspecialchars(
$_SESSION['username']) ?></b><small>Administrator</small><a href="../Practical-10/logout.php">Sign out</a></div></
aside>
<main><header><div><p class="eyebrow">ROLE-BASED ADMINISTRATION</p><h1>Good day, <?= htmlspecialchars($_SESSION[
'username']) ?>.</h1><p>Live campus activity and operational controls.</p></div><span class="role">● ADMIN ACCESS 
VERIFIED</span></header>
<?php if ($error): ?><p class="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<section class="metrics"><article><small>Total users</small><strong><?= $stats['users'] ?></strong><em>Authenticated 
accounts</em></article><article><small>Students</small><strong><?= $stats['students'] ?></strong><em>Enrollment 
records</em></article><article><small>Events</small><strong><?= $stats['events'] ?></strong><em>Campus calendar</em></
article><article><small>Registrations</small><strong><?= $stats['registrations'] ?></strong><em>Event participation</
em></article></section>
<section class="panels"><article class="panel"><div class="panel-head"><div><p class="eyebrow">LATEST</p><h2>Recent 
registrations</h2></div><a href="../Practical-12/index.php">Manage events →</a></div><table><thead><tr><th>Student</
th><th>Event</th><th>Status</th></tr></thead><tbody><?php foreach ($registrations as $row): ?><tr><td><b><?= 
htmlspecialchars($row['fullname']) ?></b><small><?= htmlspecialchars($row['roll_no']) ?></small></td><td><?= 
htmlspecialchars($row['title']) ?></td><td><span class="status"><?= htmlspecialchars($row['status']) ?></span></td></
tr><?php endforeach; ?><?php if (!$registrations): ?><tr><td colspan="3">No registrations available.</td></tr><?php 
endif; ?></tbody></table></article>
<article class="panel audit"><div class="panel-head"><div><p class="eyebrow">TRACEABILITY</p><h2>Audit log</h2></
div></div><?php foreach ($logs as $log): ?><div class="log"><i></i><div><b><?= htmlspecialchars($log['action']) ?></
b><p><?= htmlspecialchars($log['details'] ?? 'Administrative action') ?></p><small><?= htmlspecialchars($log[
'created_at']) ?> · <?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></div></div><?php endforeach; ?><?php 
if (!$logs): ?><p>No logged administrative actions yet.</p><?php endif; ?></article></section>
</main></body></html>
