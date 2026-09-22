<?php
/**
 * StudentHub — View Student Profile Details (Practical 11: Read)
 * 
 * Displays student records along with associated event registrations.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}
$student = null;
$events = [];
try {
    $pdo = getDBConnection();
    // Fetch Student
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    if (!$student) {
        $_SESSION['flash_error'] = "Student #{$id} does not exist.";
        header('Location: index.php');
        exit;
    }
    // Fetch Associated Registrations
    $stmtEvents = $pdo->prepare("
        SELECT e.title, e.category, e.event_date, e.venue, r.registered_at, r.status AS reg_status
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.student_id = ?
        ORDER BY e.event_date DESC
    ");
    $stmtEvents->execute([$id]);
    $events = $stmtEvents->fetchAll();
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Database error: " . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile: <?= htmlspecialchars($student['fullname']) ?> — Practical 11</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-primary-lt: #16213e;
            --color-accent: #00bcd4;
            --color-bg: #f0f2f5;
            --color-card: #ffffff;
            --color-success: #2e7d32;
            --color-success-bg: #c8e6c9;
            --color-border: #dde1e6;
            --radius: 8px;
            --shadow: 0 4px 14px rgba(0,0,0,0.08);
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * { box-sizing: border-box; }
        body { font-family: var(--font-sans); background: var(--color-bg); margin: 0; padding: 24px 16px; }
        .container { max-width: 760px; margin: 0 auto; }
        header {
            background: var(--color-primary);
            color: #fff;
            padding: 22px 26px;
            border-radius: var(--radius) var(--radius) 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { margin: 0 0 4px; font-size: 1.4rem; }
        header p { margin: 0; color: #b0bec5; font-size: 0.88rem; }
        .card { background: var(--color-card); border-radius: 0 0 var(--radius) var(--radius); padding: 28px; box-
shadow: var(--shadow); margin-bottom: 20px; }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 12px;
        }
        .detail-item {
            padding: 10px 14px;
            background: #fafafa;
            border: 1px solid var(--color-border);
            border-radius: 6px;
        }
        .detail-item .lbl { font-size: 0.78rem; font-weight: 600; color: #777; text-transform: uppercase; }
        .detail-item .val { font-size: 0.95rem; font-weight: 600; color: var(--color-primary-lt); margin-top: 2px; }
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 0.88rem; }
        .table-custom th { background: var(--color-primary); color: #fff; text-align: left; padding: 9px 12px; }
        .table-custom td { padding: 9px 12px; border-bottom: 1px solid #eee; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn {
            padding: 9px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
        }
        .btn-back { background: #e0e0e0; color: #333; }

        .btn-edit { background: var(--color-accent); color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1>■ <?= htmlspecialchars($student['fullname']) ?></h1>
                <p>Roll No: <strong><?= htmlspecialchars($student['roll_no']) ?></strong></p>
            </div>
            <span style="background:<?= $student['status'] === 'active' ? '#c8e6c9' : '#eceff1' ?>; color:<?= 
$student['status'] === 'active' ? '#2e7d32' : '#546e7a' ?>; padding: 4px 12px; border-radius: 20px; font-weight: 700; 
font-size: 0.8rem; text-transform: uppercase;">
                <?= htmlspecialchars(ucfirst($student['status'])) ?>
            </span>
        </header>
        <div class="card">
            <h3 style="margin-top:0; color:var(--color-primary-lt);">Academic & Contact Details</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="lbl">University Roll Number</div>
                    <div class="val"><?= htmlspecialchars($student['roll_no']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Full Name</div>
                    <div class="val"><?= htmlspecialchars($student['fullname']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Email Address</div>
                    <div class="val"><?= htmlspecialchars($student['email']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Mobile Number</div>
                    <div class="val"><?= htmlspecialchars($student['mobile']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Branch / Department</div>
                    <div class="val"><?= htmlspecialchars(strtoupper(str_replace('btech-', '', $student['course']))) 
?> (B.Tech)</div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Academic Year</div>
                    <div class="val">Year <?= htmlspecialchars((string)$student['year']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Gender</div>
                    <div class="val"><?= htmlspecialchars(ucfirst($student['gender'])) ?></div>
                </div>
                <div class="detail-item">
                    <div class="lbl">Record Created</div>
                    <div class="val"><?= htmlspecialchars($student['created_at']) ?></div>
                </div>
            </div>
            <!-- Associated Event Registrations -->
            <h3 style="margin: 24px 0 6px; color:var(--color-primary-lt);">Enrolled Campus Events</h3>
            <?php if (!empty($events)): ?>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Registration Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $ev): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                            <td><?= htmlspecialchars($ev['category']) ?></td>
                            <td><?= htmlspecialchars($ev['event_date']) ?></td>
                            <td><?= htmlspecialchars($ev['venue']) ?></td>
                            <td><span style="font-weight:600; color:var(--color-success);"><?= htmlspecialchars(
ucfirst($ev['reg_status'])) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#777; font-size:0.88rem; margin:8px 0;">This student has not enrolled in any campus 
events yet.</p>
            <?php endif; ?>
            <div class="btn-group">
                <a href="index.php" class="btn btn-back">&larr; Back to Student List</a>
                <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-edit">✏■ Edit Student</a>
            </div>
        </div>
    </div>
</body>
</html>

Practical 12
