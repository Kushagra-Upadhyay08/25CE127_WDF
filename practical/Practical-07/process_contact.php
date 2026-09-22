<?php
/**
 * StudentHub — Contact Form Processor (Practical 7)
 * 
 * Handles POST data from the contact form.
 * Validates and sanitizes inputs server-side.
 * Stores messages in JSON file format.
 */
require_once __DIR__ . '/includes/validate.php';
// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Practical-02/StudentHub/contact.html');
    exit;
}
$errors = [];
$data = [];
// ■■ Validate fields ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$name = validate_fullname($_POST['name'] ?? '');
if (!$name['valid']) $errors['name'] = $name['message'];
else $data['name'] = $name['value'];
$email = validate_email($_POST['email'] ?? '');
if (!$email['valid']) $errors['email'] = $email['message'];
else $data['email'] = $email['value'];
$subject = validate_subject($_POST['subject'] ?? '');
if (!$subject['valid']) $errors['subject'] = $subject['message'];
else $data['subject'] = $subject['value'];
$message = validate_message($_POST['message'] ?? '');
if (!$message['valid']) $errors['message'] = $message['message'];
else $data['message'] = $message['value'];
// ■■ If errors, show error page ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
if (!empty($errors)) {
    showContactResult('Message Not Sent', $errors, true);
    exit;
}

// ■■ Store to JSON ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
$jsonFile = $dataDir . '/contacts.json';
$records = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $records = json_decode($jsonContent, true) ?? [];
}
$data['submitted_at'] = date('Y-m-d H:i:s');
$records[] = $data;
file_put_contents($jsonFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
// ■■ Show success ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
showContactResult('Message Sent Successfully', [
    'Thank you for contacting us, ' . $data['name'] . '!',
    'Subject: ' . $data['subject'],
    'We will respond to ' . $data['email'] . ' shortly.',
    'Message stored in JSON format.'
], false);
// ■■ Result Page Helper ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
function showContactResult($title, $messages, $isError) {
    $bgColor = $isError ? '#fff5f5' : '#f0fff4';
    $textColor = $isError ? '#c62828' : '#2e7d32';
    $icon = $isError ? '■' : '■';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — StudentHub</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 40px 20px; }
        .result-card {
            max-width: 500px; margin: 0 auto;
            background: <?= $bgColor ?>; border-radius: 12px;
            padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .result-card h1 { color: <?= $textColor ?>; font-size: 1.5rem; margin-bottom: 16px; }
        .result-card p, .result-card li { color: #333; font-size: 0.95rem; line-height: 1.6; }
        .result-card ul { text-align: left; padding-left: 20px; }
        .btn-back {
            display: inline-block; margin-top: 20px; padding: 10px 24px;
            background: #00bcd4; color: #fff; text-decoration: none;
            border-radius: 8px; font-weight: 600; transition: background 0.2s;
        }
        .btn-back:hover { background: #0097a7; }
    </style>
</head>
<body>
    <div class="result-card">
        <h1><?= $icon ?> <?= htmlspecialchars($title) ?></h1>
        <?php if ($isError): ?>
            <p>Please fix the following errors:</p>
            <ul>
                <?php foreach ($messages as $field => $msg): ?>
                    <li><strong><?= htmlspecialchars(ucfirst($field)) ?>:</strong> <?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="javascript:history.back()" class="btn-back">← Go Back</a>
        <?php else: ?>
            <ul>
                <?php foreach ($messages as $msg): ?>
                    <li><?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="../Practical-02/StudentHub/index.html" class="btn-back">Back to Home →</a>
        <?php endif; ?>
    </div>
</body>
</html>
    <?php
}
?>
