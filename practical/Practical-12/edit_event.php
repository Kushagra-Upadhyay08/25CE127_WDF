<?php
/**
 * StudentHub — Edit Event & Update Poster (Practical 12: Update)
 * 
 * Allows modification of event details, replacing or removing posters,
 * and maintains disk cleanliness by deleting obsolete image files.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/validate_event.php';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}
$errors = [];
$event = null;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if (!$event) {
        $_SESSION['flash_error'] = "Event #{$id} was not found.";
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['flash_error'] = "Database error: " . $e->getMessage();
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? null)) {
        $errors['csrf'] = 'Invalid or expired CSRF security token.';
    }
    $formData = [
        'title'            => sanitizeInput($_POST['title'] ?? ''),
        'category'         => sanitizeInput($_POST['category'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'event_date'       => trim($_POST['event_date'] ?? ''),
        'venue'            => sanitizeInput($_POST['venue'] ?? ''),
        'max_participants' => (int)($_POST['max_participants'] ?? 100),
        'status'           => sanitizeInput($_POST['status'] ?? 'upcoming'),
    ];
    // 2. Validate Text Fields
    $fieldErrors = validateEvent($formData);
    $errors = array_merge($errors, $fieldErrors);
    $newPosterUrl = $event['poster_url'];
    $removePoster = !empty($_POST['remove_poster']);
    // 3. Handle File Upload if new file submitted
    $uploadedNewFile = false;
    if (!empty($_FILES['poster']['name'])) {
        $uploadResult = handlePosterUpload($_FILES['poster']);
        if (!$uploadResult['success']) {
            $errors['poster'] = $uploadResult['error'];
        } else {
            $newPosterUrl = $uploadResult['path'];
            $uploadedNewFile = true;
        }
    } elseif ($removePoster) {
        $newPosterUrl = null;
    }
    if (empty($errors)) {
        try {
            $sql = "UPDATE events 
                    SET title = ?, category = ?, description = ?, event_date = ?, venue = ?, max_participants = ?, 
poster_url = ?, status = ?
                    WHERE id = ?";
            $updateStmt = $pdo->prepare($sql);
            $updateStmt->execute([
                $formData['title'],
                $formData['category'],
                $formData['description'],
                $formData['event_date'],
                $formData['venue'],
                $formData['max_participants'],
                $newPosterUrl,
                $formData['status'],
                $id,
            ]);
            // Clean up old file if replaced or removed
            if (($uploadedNewFile || $removePoster) && !empty($event['poster_url'])) {

                deletePosterFile($event['poster_url']);
            }
            // Advanced Extension: Audit Log Entry
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, 
'EVENT_UPDATED', ?, ?)");
            $auditStmt->execute(["Updated event #{$id} '{$formData['title']}'", $ip]);
            $_SESSION['flash_success'] = "Event #{$id} ({$formData['title']}) updated successfully!";
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            if ($uploadedNewFile && $newPosterUrl) {
                deletePosterFile($newPosterUrl);
            }
            $errors['db'] = "Update error: " . $e->getMessage();
        }
    } else {
        if ($uploadedNewFile && $newPosterUrl) {
            deletePosterFile($newPosterUrl);
        }
    }
    $event = array_merge($event, $formData);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event #<?= $id ?> — StudentHub (Practical 12)</title>
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
        textarea { resize: vertical; min-height: 90px; }
        .preview-box {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fafafa;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            padding: 12px;
            margin-top: 6px;
        }
        .preview-img { width: 120px; height: 68px; object-fit: cover; border-radius: 4px; }
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
            <h1>✏■ Edit Event: <?= htmlspecialchars($event['title']) ?></h1>
            <p>Practical 12: Update event details & replace/remove poster</p>
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
            <form method="POST" action="edit_event.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" 
required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="Technical" <?= $event['category'] === 'Technical' ? 'selected' : '' 
?>>Technical</option>
                            <option value="Workshop" <?= $event['category'] === 'Workshop' ? 'selected' : '' 
?>>Workshop</option>
                            <option value="Sports" <?= $event['category'] === 'Sports' ? 'selected' : '' ?>>Sports</
option>
                            <option value="Cultural" <?= $event['category'] === 'Cultural' ? 'selected' : '' 
?>>Cultural</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Event Status *</label>
                        <select id="status" name="status" required>
                            <option value="upcoming" <?= $event['status'] === 'upcoming' ? 'selected' : '' 
?>>Upcoming</option>
                            <option value="completed" <?= $event['status'] === 'completed' ? 'selected' : '' 
?>>Completed</option>
                            <option value="cancelled" <?= $event['status'] === 'cancelled' ? 'selected' : '' 
?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event[
'event_date']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="max_participants">Max Seat Capacity *</label>
                        <input type="number" id="max_participants" name="max_participants" min="1" max="5000" 
                               value="<?= htmlspecialchars((string)$event['max_participants']) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="venue">Campus Venue *</label>
                        <input type="text" id="venue" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" 
required>
                    </div>
                    <div class="form-group full-width">
                        <label for="description">Event Description *</label>
                        <textarea id="description" name="description" required><?= htmlspecialchars($event[
'description']) ?></textarea>
                    </div>
                    <!-- Current Poster & Replacement Upload -->
                    <div class="form-group full-width">
                        <label>Poster Image</label>
                        <?php if (!empty($event['poster_url']) && file_exists(__DIR__ . '/' . $event['poster_url'])): 
?>
                            <div class="preview-box">
                                <img src="<?= htmlspecialchars($event['poster_url']) ?>" alt="Current Poster" 
class="preview-img">
                                <div>
                                    <span style="font-size:0.85rem; font-weight:600; color:#333;">Current Poster</
span><br>
                                    <label style="font-size:0.82rem; color:var(--color-danger); cursor:pointer; 
display:inline-flex; align-items:center; gap:4px; margin-top:4px;">
                                        <input type="checkbox" name="remove_poster" value="1">
                                        Remove this poster image
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
                            <label for="poster" style="font-size:0.82rem; color:#555;">Upload Replacement Poster (Max 
2MB, JPG/PNG/WEBP)</label>
                            <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp" style="margin-
top:4px;">
                        </div>
                        <?php if (isset($errors['poster'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['poster']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group full-width btn-group">

                        <button type="submit" class="btn btn-save">■ Update Event</button>
                        <a href="index.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
