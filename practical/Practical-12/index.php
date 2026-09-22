<?php
/**
 * StudentHub — Event Management Console (Practical 12)
 * 
 * Features:
 * - Event catalog grid & table view
 * - Poster image display & thumbnail rendering
 * - Search by Title, Venue, and Description
 * - Intermediate Extension: Multi-criteria filtering by Category and Status (Open/Closed)
 * - Complete CRUD action links (Create, Read, Update, Delete)
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
// Filters & Search
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status'] ?? '');
$events = [];
$totalEvents = 0;
try {
    $pdo = getDBConnection();
    $conditions = [];
    $params = [];
    if ($search !== '') {
        $conditions[] = "(title LIKE ? OR venue LIKE ? OR description LIKE ?)";
        $wildcard = "%{$search}%";
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }
    if ($category !== '') {
        $conditions[] = "category = ?";
        $params[] = $category;
    }
    if ($status !== '') {
        $conditions[] = "status = ?";
        $params[] = $status;
    }
    $whereSql = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    $sql = "SELECT id, title, category, description, event_date, venue, max_participants, poster_url, status, 
created_at 
            FROM events 
            {$whereSql} 
            ORDER BY event_date ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    $totalEvents = count($events);
} catch (Exception $e) {
    error_log("[Practical-12 CRUD Error]: " . $e->getMessage());
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Console — Practical 12</title>
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
            --color-danger: #c62828;
            --color-danger-bg: #ffcdd2;
            --color-warning: #e65100;
            --color-warning-bg: #ffe0b2;
            --color-border: #dde1e6;
            --radius: 8px;
            --shadow: 0 4px 14px rgba(0,0,0,0.08);
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            font-family: var(--font-sans);
            background: var(--color-bg);
            color: var(--color-text);
            margin: 0;
            padding: 24px 16px;
            line-height: 1.5;
        }
        .container { max-width: 1140px; margin: 0 auto; }
        header {
            background: var(--color-primary);
            color: #fff;
            padding: 22px 28px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        header h1 { margin: 0 0 6px; font-size: 1.5rem; }
        header p { margin: 0; color: #b0bec5; font-size: 0.9rem; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }
        .btn-primary { background: var(--color-accent); color: #fff; }
        .btn-primary:hover { background: var(--color-accent-dark); }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-success { background: var(--color-success-bg); color: var(--color-success); border-left: 4px solid var(
--color-success); }
        .alert-danger { background: var(--color-danger-bg); color: var(--color-danger); border-left: 4px solid var(--
color-danger); }
        .card {
            background: var(--color-card);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        /* Filter Form */
        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto auto;
            gap: 12px;
            align-items: end;
        }
        .form-group { display: flex; flex-direction: column; gap: 4px; }
        .form-group label { font-size: 0.8rem; font-weight: 600; color: var(--color-muted); text-transform: uppercase;
 }
        input[type="text"], select {
            padding: 9px 12px;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            font-size: 0.9rem;
            outline: none;
        }
        .btn-filter { background: var(--color-primary-lt); color: #fff; height: 38px; }
        .btn-reset { background: #e0e0e0; color: #333; height: 38px; }
        /* Event Cards Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 22px;
            margin-top: 16px;
        }
        .event-card {
            background: #fff;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;

        }
        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .poster-container {
            width: 100%;
            height: 180px;
            background: #263238;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .poster-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .poster-placeholder {
            color: #90a4ae;
            font-size: 0.88rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .card-body {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-cat { background: #e0f7fa; color: #00838f; }
        .badge-upcoming { background: var(--color-success-bg); color: var(--color-success); }
        .badge-completed { background: #e3f2fd; color: #1565c0; }
        .badge-cancelled { background: var(--color-danger-bg); color: var(--color-danger); }
        .event-title {
            margin: 0 0 8px;
            font-size: 1.15rem;
            color: var(--color-primary-lt);
            font-weight: 700;
        }
        .event-info {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .event-desc {
            font-size: 0.86rem;
            color: #666;
            line-height: 1.4;
            margin: 8px 0 16px;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
            padding-top: 12px;
            margin-top: auto;
        }
        .btn-action-group { display: flex; gap: 6px; }
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-view { background: #e0f7fa; color: #00838f; border-color: #b2ebf2; }
        .btn-edit { background: #fff3e0; color: #e65100; border-color: #ffe0b2; }
        .btn-del { background: #ffebee; color: #c62828; border-color: #ffcdd2; }
        .btn-del:hover { background: #ffcdd2; }
        @media (max-width: 800px) {
            .filter-form { grid-template-columns: 1fr; }
        }

    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1>■ Campus Event Management Console</h1>
                <p>Practical 12: Admin CRUD with Validated Poster Uploads & Category Filters</p>
            </div>
            <div>
                <a href="add_event.php" class="btn btn-primary">
                    ■ Create New Event
                </a>
            </div>
        </header>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?= htmlspecialchars($flashSuccess) ?>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>
        <!-- Search and Filter Bar -->
        <div class="card">
            <form method="GET" action="index.php" class="filter-form">
                <div class="form-group">
                    <label for="search">Search Events</label>
                    <input type="text" id="search" name="search" placeholder="Title, venue, or keywords..." 
value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="Technical" <?= $category === 'Technical' ? 'selected' : '' ?>>Technical</
option>
                        <option value="Workshop" <?= $category === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                        <option value="Sports" <?= $category === 'Sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="Cultural" <?= $category === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Event Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="upcoming" <?= $status === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-filter">■ Filter</button>
                <a href="index.php" class="btn btn-reset">Reset</a>
            </form>
        </div>
        <!-- Event Cards Grid -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.15rem; color:var(--color-primary-lt);">
                    Registered Campus Events (<?= $totalEvents ?> Records Found)
                </h3>
            </div>
            <?php if (!empty($events)): ?>
                <div class="events-grid">
                    <?php foreach ($events as $ev): ?>
                    <div class="event-card">
                        <!-- Poster Image Thumbnail -->
                        <div class="poster-container">
                            <?php if (!empty($ev['poster_url']) && file_exists(__DIR__ . '/' . $ev['poster_url'])): ?>
                                <img src="<?= htmlspecialchars($ev['poster_url']) ?>" alt="<?= htmlspecialchars($ev[
'title']) ?>" class="poster-img">
                            <?php else: ?>
                                <div class="poster-placeholder">
                                    <span style="font-size:1.8rem;">■■</span>
                                    <span>No Poster Uploaded</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="badge badge-cat"><?= htmlspecialchars($ev['category']) ?></span>
                                <span class="badge badge-<?= htmlspecialchars($ev['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($ev['status'])) ?>
                                </span>
                            </div>
                            <h4 class="event-title"><?= htmlspecialchars($ev['title']) ?></h4>
                            <div class="event-info">
                                <span>■</span>
                                <strong><?= htmlspecialchars(date('M d, Y', strtotime($ev['event_date']))) ?></strong>

                            </div>
                            <div class="event-info">
                                <span>■</span>
                                <span><?= htmlspecialchars($ev['venue']) ?></span>
                            </div>
                            <div class="event-info">
                                <span>■</span>
                                <span>Capacity: <strong><?= htmlspecialchars((string)$ev['max_participants']) ?></
strong> seats</span>
                            </div>
                            <p class="event-desc"><?= htmlspecialchars($ev['description']) ?></p>
                            <div class="card-actions">
                                <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn-sm btn-view">View</a>
                                <div class="btn-action-group">
                                    <a href="edit_event.php?id=<?= $ev['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                    <form action="delete_event.php" method="POST" style="display:inline;" 
onsubmit="return confirm('Are you sure you want to delete this event? This will also remove the uploaded poster file.
');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                        <button type="submit" class="btn-sm btn-del">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="padding: 36px; text-align: center; color: var(--color-muted);">
                    No events found matching your filter criteria.
                </div>
            <?php endif; ?>
        </div>
        <div style="text-align:center; margin-top:20px; font-size:0.85rem; color:var(--color-muted);">
            <a href="../Practical-10/dashboard_admin.php" style="color:var(--color-accent-dark); text-decoration:none;
 font-weight:600;">
                &larr; Admin Dashboard (Practical 10)
            </a> &bull;
            <a href="../Practical-11/index.php" style="color:var(--color-accent-dark); text-decoration:none; font-
weight:600;">
                Student Management CRUD (Practical 11)
            </a>
        </div>
    </div>
</body>
</html>
