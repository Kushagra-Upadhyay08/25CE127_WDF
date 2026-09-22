<?php
/**
 * StudentHub — Add New Event with Poster Upload (Practical 12: Create)
 * 
 * Validates text fields, performs server-side file type and size validation,
 * renames poster securely, and saves record to MySQL database.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/validate_event.php';
$errors = [];
$old = [
    'title'            => '',
    'category'         => 'Technical',
    'description'      => '',
    'event_date'       => date('Y-m-d', strtotime('+7 days')),
    'venue'            => '',
    'max_participants' => 100,
    'status'           => 'upcoming',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? null)) {
        $errors['csrf'] = 'Invalid or expired CSRF security token.';
    }
    $old = [
        'title'            => sanitizeInput($_POST['title'] ?? ''),
        'category'         => sanitizeInput($_POST['category'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'event_date'       => trim($_POST['event_date'] ?? ''),
        'venue'            => sanitizeInput($_POST['venue'] ?? ''),
        'max_participants' => (int)($_POST['max_participants'] ?? 100),
        'status'           => sanitizeInput($_POST['status'] ?? 'upcoming'),
    ];
    // 2. Validate Text Fields
    $fieldErrors = validateEvent($old);
    $errors = array_merge($errors, $fieldErrors);
    // 3. Handle File Upload (Poster)
    $posterUrl = null;
    if (!empty($_FILES['poster']['name'])) {
        $uploadResult = handlePosterUpload($_FILES['poster']);
        if (!$uploadResult['success']) {
            $errors['poster'] = $uploadResult['error'];
        } else {
            $posterUrl = $uploadResult['path'];
        }
    }
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $sql = "INSERT INTO events (title, category, description, event_date, venue, max_participants, poster_url,
 status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $old['title'],
                $old['category'],
                $old['description'],
                $old['event_date'],
                $old['venue'],
                $old['max_participants'],
                $posterUrl,
                $old['status'],
            ]);
            $eventId = (int)$pdo->lastInsertId();
            // Advanced Extension: Audit Log Entry
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, 
'EVENT_CREATED', ?, ?)");
            $auditStmt->execute(["Created event #{$eventId} '{$old['title']}' with poster: " . ($posterUrl ? 'Yes' : 
'No'), $ip]);
            $_SESSION['flash_success'] = "Event '{$old['title']}' was published successfully!";
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            if ($posterUrl) {
                deletePosterFile($posterUrl); // Clean up uploaded file on failure
            }
            $errors['db'] = "Database error: " . $e->getMessage();
        }
    } else {
        // Clean up uploaded file if validation failed on another field

        if ($posterUrl) {
            deletePosterFile($posterUrl);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event — StudentHub (Practical 12)</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-primary-lt: #16213e;
            --color-accent: #00bcd4;
            --color-accent-dark: #0097a7;
            --color-bg: #f0f2f5;
            --color-card: #ffffff;
            --color-danger: #c62828;
            --color-danger-bg: #ffcdd2;
            --color-border: #dde1e6;
            --radius: 8px;
            --shadow: 0 4px 14px rgba(0,0,0,0.08);
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * { box-sizing: border-box; }
        body { font-family: var(--font-sans); background: var(--color-bg); margin: 0; padding: 24px 16px; }
        .container { max-width: 720px; margin: 0 auto; }
        .card { background: var(--color-card); border-radius: var(--radius); padding: 28px; box-shadow: var(--shadow);
 }
        header { background: var(--color-primary); color: #fff; padding: 20px 24px; border-radius: var(--radius) var(-
-radius) 0 0; }
        header h1 { margin: 0 0 4px; font-size: 1.4rem; }
        header p { margin: 0; color: #b0bec5; font-size: 0.88rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .full-width { grid-column: 1 / -1; }
        .form-group { display: flex; flex-direction: column; gap: 4px; }
        label { font-size: 0.88rem; font-weight: 600; color: var(--color-primary-lt); }
        input[type="text"], input[type="date"], input[type="number"], select, textarea {
            padding: 10px 12px;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            font-size: 0.92rem;
            outline: none;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--color-accent); }
        textarea { resize: vertical; min-height: 90px; }
        .file-upload-box {
            border: 2px dashed var(--color-border);
            border-radius: 6px;
            padding: 18px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
        }
        .file-upload-box:hover { border-color: var(--color-accent); background: #f0fbfc; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: var(--color-danger-bg); 
color: var(--color-danger); font-size: 0.88rem; }
        .error-msg { color: var(--color-danger); font-size: 0.78rem; }
        .btn-group { display: flex; gap: 10px; margin-top: 12px; }
        .btn { padding: 11px 20px; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; border: 
none; text-decoration: none; }
        .btn-save { background: var(--color-accent); color: #fff; flex: 1; }
        .btn-save:hover { background: var(--color-accent-dark); }
        .btn-cancel { background: #e0e0e0; color: #333; text-align: center; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>■ Create New Campus Event</h1>
            <p>Practical 12: Event CRUD with MIME & Size Validated Poster Upload</p>
        </header>
        <div class="card" style="border-radius: 0 0 var(--radius) var(--radius);">
            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <strong>Please correct the errors below:</strong>
                    <ul style="margin: 6px 0 0; padding-left: 18px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="add_event.php" enctype="multipart/form-data" novalidate>
                <?= csrfField() ?>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" placeholder="e.g. AI & Robotics Hackathon 2026" 
                               value="<?= htmlspecialchars($old['title']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>

                        <select id="category" name="category" required>
                            <option value="Technical" <?= $old['category'] === 'Technical' ? 'selected' : '' 
?>>Technical</option>
                            <option value="Workshop" <?= $old['category'] === 'Workshop' ? 'selected' : '' 
?>>Workshop</option>
                            <option value="Sports" <?= $old['category'] === 'Sports' ? 'selected' : '' ?>>Sports</
option>
                            <option value="Cultural" <?= $old['category'] === 'Cultural' ? 'selected' : '' 
?>>Cultural</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Initial Status *</label>
                        <select id="status" name="status" required>
                            <option value="upcoming" <?= $old['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</
option>
                            <option value="completed" <?= $old['status'] === 'completed' ? 'selected' : '' 
?>>Completed</option>
                            <option value="cancelled" <?= $old['status'] === 'cancelled' ? 'selected' : '' 
?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($old[
'event_date']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="max_participants">Max Seat Capacity *</label>
                        <input type="number" id="max_participants" name="max_participants" min="1" max="5000" 
                               value="<?= htmlspecialchars((string)$old['max_participants']) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="venue">Campus Venue *</label>
                        <input type="text" id="venue" name="venue" placeholder="e.g. FTE Auditorium 1, CHARUSAT" 
                               value="<?= htmlspecialchars($old['venue']) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="description">Event Description *</label>
                        <textarea id="description" name="description" placeholder="Comprehensive overview of agenda, 
eligibility, and rules..." required><?= htmlspecialchars($old['description']) ?></textarea>
                    </div>
                    <!-- Poster File Upload (Practical 12 Core Requirement) -->
                    <div class="form-group full-width">
                        <label for="poster">Event Poster Image (Optional, Max 2MB)</label>
                        <div class="file-upload-box">
                            <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                            <p style="margin: 6px 0 0; font-size: 0.8rem; color: #777;">
                                Allowed types: <strong>JPG, JPEG, PNG, WEBP</strong>. Validated via server-side MIME 
detection.
                            </p>
                        </div>
                        <?php if (isset($errors['poster'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['poster']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group full-width btn-group">
                        <button type="submit" class="btn btn-save">■ Publish Event</button>
                        <a href="index.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
