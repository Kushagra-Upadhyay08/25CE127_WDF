<?php
/**
 * StudentHub — Edit Student (Practical 11: Update)
 * 
 * Form and server-side processor for updating an existing student record.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validate.php';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}
$errors = [];
$student = null;
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    if (!$student) {
        $_SESSION['flash_error'] = "Student with ID #{$id} was not found.";
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
        $errors['csrf'] = 'Invalid or expired CSRF token.';
    }
    $formData = [
        'roll_no'  => sanitizeInput($_POST['roll_no'] ?? ''),
        'fullname' => sanitizeInput($_POST['fullname'] ?? ''),
        'email'    => sanitizeInput($_POST['email'] ?? ''),
        'mobile'   => sanitizeInput($_POST['mobile'] ?? ''),
        'gender'   => sanitizeInput($_POST['gender'] ?? ''),
        'course'   => sanitizeInput($_POST['course'] ?? ''),
        'year'     => sanitizeInput($_POST['year'] ?? ''),
        'status'   => sanitizeInput($_POST['status'] ?? 'active'),
    ];
    // 2. Validate fields
    $fieldErrors = validateStudent($formData);
    $errors = array_merge($errors, $fieldErrors);
    if (empty($errors)) {
        try {
            // 3. Unique checks excluding current student ID
            $checkRoll = $pdo->prepare("SELECT id FROM students WHERE roll_no = ? AND id != ? LIMIT 1");
            $checkRoll->execute([$formData['roll_no'], $id]);
            if ($checkRoll->fetch()) {
                $errors['roll_no'] = 'Another student already has this Roll Number.';
            }

            $checkEmail = $pdo->prepare("SELECT id FROM students WHERE email = ? AND id != ? LIMIT 1");
            $checkEmail->execute([$formData['email'], $id]);
            if ($checkEmail->fetch()) {
                $errors['email'] = 'Another student is already using this Email address.';
            }
            if (empty($errors)) {
                // 4. Prepared Statement Update
                $updateSql = "UPDATE students 
                              SET roll_no = ?, fullname = ?, email = ?, mobile = ?, gender = ?, course = ?, year = ?, 
status = ?
                              WHERE id = ?";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    $formData['roll_no'],
                    $formData['fullname'],
                    $formData['email'],
                    $formData['mobile'],
                    $formData['gender'],
                    $formData['course'],
                    (int)$formData['year'],
                    $formData['status'],
                    $id,
                ]);
                $_SESSION['flash_success'] = "Student #{$id} ({$formData['fullname']}) updated successfully!";
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors['db'] = "Update error: " . $e->getMessage();
        }
    }
    // Retain form data on error
    $student = array_merge($student, $formData);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student #<?= $id ?> — StudentHub (Practical 11)</title>
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
        .container { max-width: 680px; margin: 0 auto; }
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
        input[type="text"], input[type="email"], select { padding: 10px 12px; border: 1px solid var(--color-border); 
border-radius: 6px; font-size: 0.92rem; outline: none; }
        input:focus, select:focus { border-color: var(--color-accent); }
        .is-invalid { border-color: var(--color-danger) !important; background: #fff8f8; }
        .error-msg { color: var(--color-danger); font-size: 0.78rem; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; background: var(--color-danger-bg); 
color: var(--color-danger); font-size: 0.88rem; }
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
            <h1>✏■ Edit Student Record (ID #<?= $id ?>)</h1>
            <p>Practical 11: Update operation using parameterized MySQL UPDATE statement</p>
        </header>
        <div class="card" style="border-radius: 0 0 var(--radius) var(--radius);">
            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <strong>Please fix the errors below:</strong>
                    <ul style="margin: 6px 0 0; padding-left: 18px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="edit_student.php?id=<?= $id ?>" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="roll_no">Roll Number *</label>
                        <input type="text" id="roll_no" name="roll_no" 
                               value="<?= htmlspecialchars($student['roll_no'] ?? '') ?>"
                               class="<?= isset($errors['roll_no']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['roll_no'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['roll_no']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" 
                               value="<?= htmlspecialchars($student['fullname'] ?? '') ?>"
                               class="<?= isset($errors['fullname']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['fullname'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['fullname']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($student['email'] ?? '') ?>"
                               class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile Number *</label>
                        <input type="text" id="mobile" name="mobile" 
                               value="<?= htmlspecialchars($student['mobile'] ?? '') ?>"
                               class="<?= isset($errors['mobile']) ? 'is-invalid' : '' ?>" required maxlength="10">
                        <?php if (isset($errors['mobile'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['mobile']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="male" <?= ($student['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</
option>
                            <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' 
?>>Female</option>
                            <option value="other" <?= ($student['gender'] ?? '') === 'other' ? 'selected' : '' 
?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course">Branch *</label>
                        <select id="course" name="course" required>
                            <option value="btech-ce" <?= ($student['course'] ?? '') === 'btech-ce' ? 'selected' : '' 
?>>B.Tech CE</option>
                            <option value="btech-cs" <?= ($student['course'] ?? '') === 'btech-cs' ? 'selected' : '' 
?>>B.Tech CSE</option>
                            <option value="btech-it" <?= ($student['course'] ?? '') === 'btech-it' ? 'selected' : '' 
?>>B.Tech IT</option>
                            <option value="btech-ec" <?= ($student['course'] ?? '') === 'btech-ec' ? 'selected' : '' 
?>>B.Tech EC</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Academic Year *</label>
                        <select id="year" name="year" required>
                            <option value="1" <?= (string)($student['year'] ?? '') === '1' ? 'selected' : '' ?>>Year 
1</option>
                            <option value="2" <?= (string)($student['year'] ?? '') === '2' ? 'selected' : '' ?>>Year 
2</option>
                            <option value="3" <?= (string)($student['year'] ?? '') === '3' ? 'selected' : '' ?>>Year 
3</option>
                            <option value="4" <?= (string)($student['year'] ?? '') === '4' ? 'selected' : '' ?>>Year 
4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="active" <?= ($student['status'] ?? '') === 'active' ? 'selected' : '' 
?>>Active</option>
                            <option value="inactive" <?= ($student['status'] ?? '') === 'inactive' ? 'selected' : '' 
?>>Inactive</option>
                        </select>
                    </div>
                    <div class="form-group full-width btn-group">
                        <button type="submit" class="btn btn-save">■ Update Changes</button>
                        <a href="index.php" class="btn btn-cancel">Cancel</a>

                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
