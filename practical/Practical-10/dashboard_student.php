<?php
/**
 * StudentHub — Student Portal Dashboard (Practical 10)
 * 
 * Protected by Role-Based Access Control (RBAC) and Session Timeout.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
// Enforce authentication & session timeout

requireRole(['student', 'admin']);
$userId    = $_SESSION['user_id'];
$username  = $_SESSION['username'];
$email     = $_SESSION['email'];
$role      = $_SESSION['role'];
$studentId = $_SESSION['student_id'] ?? null;
$studentData = null;
$enrolledEvents = [];
try {
    $pdo = getDBConnection();
    // Fetch Student Profile
    if ($studentId) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
        $stmt->execute([$studentId]);
        $studentData = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $studentData = $stmt->fetch();
    }
    // Fetch Enrolled Events
    if ($studentData) {
        $stmtEvents = $pdo->prepare("
            SELECT e.title, e.category, e.event_date, e.venue, r.registered_at, r.status AS reg_status
            FROM registrations r
            JOIN events e ON r.event_id = e.id
            WHERE r.student_id = ?
            ORDER BY e.event_date ASC
        ");
        $stmtEvents->execute([$studentData['id']]);
        $enrolledEvents = $stmtEvents->fetchAll();
    }
} catch (Exception $e) {
    error_log("[Dashboard Student Error]: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard — StudentHub (Practical 10)</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-primary-lt: #16213e;
            --color-accent: #00bcd4;
            --color-accent-dark: #0097a7;
            --color-bg: #f0f2f5;
            --color-card: #ffffff;
            --color-text: #222222;
            --color-muted: #666666;
            --color-success: #2e7d32;
            --color-success-bg: #c8e6c9;
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
            line-height: 1.5;
        }
        header {
            background: var(--color-primary);
            color: #fff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .brand { font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; display: flex; align-items: 
center; gap: 8px; }
        .user-nav { display: flex; align-items: center; gap: 16px; font-size: 0.88rem; }
        .user-badge { background: rgba(0, 188, 212, 0.2); color: var(--color-accent); padding: 4px 10px; border-
radius: 20px; font-weight: 600; }
        .btn-logout { background: #c62828; color: #fff; text-decoration: none; padding: 6px 14px; border-radius: 4px; 
font-weight: 600; }
        .btn-logout:hover { background: #b71c1c; }
        .container { max-width: 1040px; margin: 28px auto; padding: 0 16px; }
        .session-timer-bar {
            background: #e0f7fa;
            border-left: 4px solid var(--color-accent);
            padding: 10px 16px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .grid-dashboard {

            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
        }
        .card {
            background: var(--color-card);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        h2 { margin-top: 0; font-size: 1.2rem; color: var(--color-primary-lt); border-bottom: 2px solid var(--color-
accent); padding-bottom: 6px; display: inline-block; }
        .profile-list { list-style: none; padding: 0; margin: 16px 0 0; }
        .profile-list li { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid 
#eee; font-size: 0.9rem; }
        .profile-list li span:first-child { color: var(--color-muted); font-weight: 500; }
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 0.88rem; }
        .table-custom th { background: var(--color-primary); color: #fff; text-align: left; padding: 10px 12px; }
        .table-custom td { padding: 9px 12px; border-bottom: 1px solid #eee; }
        .table-custom tr:hover { background: #f8fafc; }
        .badge-reg { background: var(--color-success-bg); color: var(--color-success); padding: 3px 8px; border-
radius: 4px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        @media (max-width: 768px) {
            .grid-dashboard { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <a href="../Practical-02/StudentHub/index.html" class="brand">■ StudentHub Portal</a>
        <div class="user-nav">
            <span>Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
            <span class="user-badge"><?= strtoupper(htmlspecialchars($role)) ?></span>
            <?php if ($role === 'admin'): ?>
                <a href="dashboard_admin.php" style="color:var(--color-accent); font-weight:600; text-decoration:none;
">Admin View</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout">Sign Out</a>
        </div>
    </header>
    <div class="container">
        <!-- Security & Session Activity Bar -->
        <div class="session-timer-bar">
            <span>■■ <strong>Session Active:</strong> Authenticated as <code><?= htmlspecialchars($email) ?></code></
span>
            <span>■■ Inactivity Timeout in: <strong id="timerDisplay">15:00</strong></span>
        </div>
        <div class="grid-dashboard">
            <!-- Student Profile Card -->
            <div class="card">
                <h2>Student Profile</h2>
                <ul class="profile-list">
                    <li>
                        <span>Roll Number:</span>
                        <strong><?= htmlspecialchars($studentData['roll_no'] ?? '25CE127') ?></strong>
                    </li>
                    <li>
                        <span>Full Name:</span>
                        <strong><?= htmlspecialchars($studentData['fullname'] ?? 'Kushagra Upadhyay') ?></strong>
                    </li>
                    <li>
                        <span>Branch:</span>
                        <span><?= htmlspecialchars(strtoupper($studentData['course'] ?? 'B.Tech CE')) ?></span>
                    </li>
                    <li>
                        <span>Academic Year:</span>
                        <span>Year <?= htmlspecialchars((string)($studentData['year'] ?? 3)) ?></span>
                    </li>
                    <li>
                        <span>Email:</span>
                        <span><?= htmlspecialchars($email) ?></span>
                    </li>
                    <li>
                        <span>Mobile:</span>
                        <span><?= htmlspecialchars($studentData['mobile'] ?? '9876543210') ?></span>
                    </li>
                    <li>
                        <span>Enrollment Status:</span>
                        <strong style="color:var(--color-success);">ACTIVE</strong>
                    </li>
                </ul>
                <div style="margin-top: 20px;">
                    <a href="../Practical-11/index.php" style="color:var(--color-accent-dark); text-decoration:none; 
font-size:0.88rem; font-weight:600;">
                        &rarr; Go to Student Management CRUD (Practical 11)
                    </a>
                </div>
            </div>
            <!-- Enrolled Events Card -->
            <div class="card">
                <h2>My Registered Campus Events</h2>
                <p style="color:var(--color-muted); font-size:0.85rem; margin-top:4px;">
                    Events pulled directly from MySQL <code>registrations</code> table.
                </p>
                <?php if (!empty($enrolledEvents)): ?>
                    <table class="table-custom">

                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrolledEvents as $ev): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                                <td><?= htmlspecialchars($ev['category']) ?></td>
                                <td><?= htmlspecialchars($ev['event_date']) ?></td>
                                <td><?= htmlspecialchars($ev['venue']) ?></td>
                                <td><span class="badge-reg"><?= htmlspecialchars(ucfirst($ev['reg_status'])) ?></
span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 24px; text-align: center; color: #888; background: #fafafa; border-radius: 
6px;">
                        No event registrations found yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Client-side visual mirror of server 15-minute inactivity timeout
        let secondsLeft = 15 * 60;
        const display = document.getElementById('timerDisplay');
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
