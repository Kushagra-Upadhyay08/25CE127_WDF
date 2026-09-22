<?php
/**
 * StudentHub — Administrator Dashboard (Practical 10)
 * 
 * Protected by Role-Based Access Control (Admin Role Required).
 * Demonstrates role restriction against student accounts.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
// ENFORCE STRICT ADMIN ROLE
requireRole(['admin']);
$username = $_SESSION['username'];
$email    = $_SESSION['email'];
$totalStudents = 0;
$totalEvents   = 0;
$totalRegs     = 0;
$recentLogs    = [];
try {
    $pdo = getDBConnection();
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalEvents   = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalRegs     = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $stmtLogs = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 5");
    $recentLogs = $stmtLogs->fetchAll();
} catch (Exception $e) {
    error_log("[Dashboard Admin Error]: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard — StudentHub (Practical 10)</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-primary-lt: #16213e;
            --color-accent: #00bcd4;
            --color-bg: #f0f2f5;
            --color-card: #ffffff;
            --color-text: #222222;
            --color-muted: #666666;
            --radius: 8px;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            font-family: var(--font-sans);
            background: var(--color-bg);
            color: var(--color-text);
            margin: 0;
            padding: 0;
        }
        header {
            background: var(--color-primary);
            color: #fff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand { font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; }
        .user-nav { display: flex; align-items: center; gap: 16px; font-size: 0.88rem; }
        .admin-badge { background: #ffd54f; color: #3e2723; padding: 4px 10px; border-radius: 20px; font-weight: 700; 
font-size: 0.75rem; }
        .btn-logout { background: #c62828; color: #fff; text-decoration: none; padding: 6px 14px; border-radius: 4px; 
font-weight: 600; }
        .container { max-width: 1040px; margin: 28px auto; padding: 0 16px; }
        .admin-banner {
            background: #fff3e0;
            border-left: 4px solid #f57c00;
            padding: 12px 18px;
            border-radius: 4px;
            margin-bottom: 24px;
            font-size: 0.88rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--color-card);
            border-radius: var(--radius);
            padding: 22px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--color-accent);
        }
        .stat-card .num { font-size: 2rem; font-weight: 700; color: var(--color-primary); margin: 6px 0 0; }
        .stat-card .lbl { font-size: 0.85rem; color: var(--color-muted); text-transform: uppercase; letter-spacing: 0.
5px; font-weight: 600; }
        .card {
            background: var(--color-card);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        h2 { margin-top: 0; font-size: 1.2rem; color: var(--color-primary-lt); border-bottom: 2px solid var(--color-
accent); padding-bottom: 6px; display: inline-block; }
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 0.88rem; }
        .table-custom th { background: var(--color-primary); color: #fff; text-align: left; padding: 10px 12px; }
        .table-custom td { padding: 9px 12px; border-bottom: 1px solid #eee; }
        .btn-action { display: inline-block; padding: 8px 16px; background: var(--color-accent); color: #fff; text-
decoration: none; border-radius: 4px; font-weight: 600; font-size: 0.9rem; }
        .btn-action:hover { background: #0097a7; }
    </style>
</head>
<body>
    <header>
        <a href="../Practical-02/StudentHub/index.html" class="brand">■■ StudentHub Admin Center</a>
        <div class="user-nav">
            <span>Logged in as <strong><?= htmlspecialchars($username) ?></strong></span>
            <span class="admin-badge">ADMINISTRATOR</span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </header>
    <div class="container">
        <div class="admin-banner">
            <span>■ <strong>Privileged Session:</strong> Role-Based Access Control verified. Student users are 
blocked from this view.</span>
            <span>■■ Inactivity Timeout in: <strong id="adminTimer">15:00</strong></span>
        </div>
        <div class="stats-grid">
            <div class="stat-card">

                <div class="lbl">Total Enrolled Students</div>
                <div class="num"><?= htmlspecialchars((string)$totalStudents) ?></div>
            </div>
            <div class="stat-card">
                <div class="lbl">Campus Events</div>
                <div class="num"><?= htmlspecialchars((string)$totalEvents) ?></div>
            </div>
            <div class="stat-card">
                <div class="lbl">Event Registrations</div>
                <div class="num"><?= htmlspecialchars((string)$totalRegs) ?></div>
            </div>
        </div>
        <!-- Management Shortcuts -->
        <div class="card">
            <h2>Administrative Operations</h2>
            <p style="color:var(--color-muted); font-size:0.9rem; margin: 6px 0 16px;">
                Direct navigation to StudentHub administrative modules:
            </p>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="../Practical-11/index.php" class="btn-action">
                    ■ Launch Student Management CRUD (Practical 11)
                </a>
                <a href="../Practical-08/test_connection.php" class="btn-action" style="background:#455a64;">
                    ■ Database Diagnostics (Practical 8)
                </a>
                <a href="../Practical-09/register.php" class="btn-action" style="background:#455a64;">
                    ■ Register New Student (Practical 9)
                </a>
            </div>
        </div>
        <!-- Recent Audit Logs -->
        <div class="card">
            <h2>Recent Security & Audit Logs</h2>
            <p style="color:var(--color-muted); font-size:0.85rem; margin-top:4px;">
                Maintained dynamically from authentication and registration operations.
            </p>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentLogs)): ?>
                        <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td>#<?= htmlspecialchars((string)$log['id']) ?></td>
                            <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                            <td><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:16px;">No audit logs recorded yet.</
td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        let secondsLeft = 15 * 60;
        const display = document.getElementById('adminTimer');
        const interval = setInterval(() => {
            secondsLeft--;
            if (secondsLeft <= 0) {
                clearInterval(interval);
                window.location.href = 'login.php?msg=timeout';
                return;
            }
            const mins = Math.floor(secondsLeft / 60);
            const secs = secondsLeft % 60;
            display.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }, 1000);
    </script>
</body>
</html>
