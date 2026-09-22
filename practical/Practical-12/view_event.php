<?php
/**
 * StudentHub — View Event Details (Practical 12: Read)
 * 
 * Displays full event overview, poster image, and list of enrolled attendees.
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
$event = null;
$attendees = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if (!$event) {
        $_SESSION['flash_error'] = "Event #{$id} does not exist.";
        header('Location: index.php');
        exit;
    }
    // Fetch Enrolled Attendees
    $stmtAtt = $pdo->prepare("
        SELECT s.roll_no, s.fullname, s.email, s.course, s.year, r.registered_at, r.status AS reg_status
        FROM registrations r
        JOIN students s ON r.student_id = s.id
        WHERE r.event_id = ?
        ORDER BY r.registered_at ASC
    ");
    $stmtAtt->execute([$id]);
    $attendees = $stmtAtt->fetchAll();
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
    <title><?= htmlspecialchars($event['title']) ?> — StudentHub (Practical 12)</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-primary-lt: #16213e;
            --color-accent: #00bcd4;
            --color-bg: #f0f2f5;
            --color-card: #ffffff;
            --color-border: #dde1e6;
            --radius: 8px;
            --shadow: 0 4px 14px rgba(0,0,0,0.08);
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * { box-sizing: border-box; }
        body { font-family: var(--font-sans); background: var(--color-bg); margin: 0; padding: 24px 16px; line-height:
 1.5; }
        .container { max-width: 820px; margin: 0 auto; }
        .card { background: var(--color-card); border-radius: var(--radius); box-shadow: var(--shadow); overflow: 
hidden; margin-bottom: 24px; }
        .poster-hero {
            width: 100%;
            max-height: 340px;
            background: #263238;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .poster-hero img { width: 100%; height: auto; object-fit: cover; }
        .content-box { padding: 28px; }
        .meta-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-cat { background: #e0f7fa; color: #00838f; }
        .badge-upcoming { background: #c8e6c9; color: #2e7d32; }
        .badge-completed { background: #e3f2fd; color: #1565c0; }
        .badge-cancelled { background: #ffcdd2; color: #c62828; }
        h1 { margin: 0 0 16px; color: var(--color-primary-lt); font-size: 1.6rem; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid var(--color-border);
        }
        .info-item span:first-child { display: block; font-size: 0.78rem; color: #777; text-transform: uppercase; 
font-weight: 600; }
        .info-item strong { font-size: 0.95rem; color: var(--color-primary-lt); }
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 0.88rem; }
        .table-custom th { background: var(--color-primary); color: #fff; text-align: left; padding: 10px 12px; font-
size: 0.8rem; text-transform: uppercase; }
        .table-custom td { padding: 9px 12px; border-bottom: 1px solid #eee; }
        .btn-group { display: flex; gap: 10px; margin-top: 24px; }
        .btn { padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; 
display: inline-block; }
        .btn-back { background: #e0e0e0; color: #333; }
        .btn-edit { background: var(--color-accent); color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if (!empty($event['poster_url']) && file_exists(__DIR__ . '/' . $event['poster_url'])): ?>
                <div class="poster-hero">
                    <img src="<?= htmlspecialchars($event['poster_url']) ?>" alt="<?= htmlspecialchars($event[
'title']) ?>">
                </div>
            <?php endif; ?>
            <div class="content-box">
                <div class="meta-strip">
                    <span class="badge badge-cat"><?= htmlspecialchars($event['category']) ?></span>
                    <span class="badge badge-<?= htmlspecialchars($event['status']) ?>">
                        <?= htmlspecialchars(ucfirst($event['status'])) ?>
                    </span>
                </div>
                <h1><?= htmlspecialchars($event['title']) ?></h1>

                <div class="info-grid">
                    <div class="info-item">
                        <span>Event Date</span>
                        <strong>■ <?= htmlspecialchars(date('F d, Y', strtotime($event['event_date']))) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Campus Venue</span>
                        <strong>■ <?= htmlspecialchars($event['venue']) ?></strong>
                    </div>
                    <div class="info-item">
                        <span>Seat Capacity</span>
                        <strong>■ <?= count($attendees) ?> / <?= htmlspecialchars((string)$event['max_participants']) 
?> Seats</strong>
                    </div>
                </div>
                <h3 style="color:var(--color-primary-lt); margin-bottom:8px;">About the Event</h3>
                <p style="color:#444; line-height:1.6;"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                <h3 style="color:var(--color-primary-lt); margin-top:28px; margin-bottom:8px;">
                    Enrolled Students (<?= count($attendees) ?> Total)
                </h3>
                <?php if (!empty($attendees)): ?>
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Branch</th>
                                <th>Year</th>
                                <th>Registered At</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendees as $a): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($a['roll_no']) ?></strong></td>
                                <td><?= htmlspecialchars($a['fullname']) ?></td>
                                <td><?= htmlspecialchars(strtoupper(str_replace('btech-', '', $a['course']))) ?></td>
                                <td>Yr <?= htmlspecialchars((string)$a['year']) ?></td>
                                <td><?= htmlspecialchars(date('M d, H:i', strtotime($a['registered_at']))) ?></td>
                                <td><span style="color:#2e7d32; font-weight:600;"><?= htmlspecialchars(ucfirst($a[
'reg_status'])) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color:#777; font-size:0.88rem;">No students have registered for this event yet.</p>
                <?php endif; ?>
                <div class="btn-group">
                    <a href="index.php" class="btn btn-back">&larr; Back to Events Console</a>
                    <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-edit">✏■ Edit Event</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

Practical 13
