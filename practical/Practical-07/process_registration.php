<?php
/**
 * StudentHub — Registration Form Processor (Practical 7)
 * 
 * Handles POST data from the registration form.
 * Validates and sanitizes inputs server-side.
 * Stores records in both CSV and JSON file formats.
 * Hashes passwords before storage.
 */
require_once __DIR__ . '/includes/validate.php';
// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Practical-02/StudentHub/register.html');
    exit;

}
$errors = [];
$data = [];
// ■■ Validate all fields ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$fullname = validate_fullname($_POST['fullname'] ?? '');
if (!$fullname['valid']) $errors['fullname'] = $fullname['message'];
else $data['fullname'] = $fullname['value'];
$email = validate_email($_POST['email'] ?? '');
if (!$email['valid']) $errors['email'] = $email['message'];
else $data['email'] = $email['value'];
$mobile = validate_mobile($_POST['mobile'] ?? '');
if (!$mobile['valid']) $errors['mobile'] = $mobile['message'];
else $data['mobile'] = $mobile['value'];
$gender = validate_gender($_POST['gender'] ?? '');
if (!$gender['valid']) $errors['gender'] = $gender['message'];
else $data['gender'] = $gender['value'];
$course = validate_course($_POST['course'] ?? '');
if (!$course['valid']) $errors['course'] = $course['message'];
else $data['course'] = $course['value'];
$year = validate_year($_POST['year'] ?? '');
if (!$year['valid']) $errors['year'] = $year['message'];
else $data['year'] = $year['value'];
$password = validate_password($_POST['password'] ?? '');
if (!$password['valid']) $errors['password'] = $password['message'];
$confirm = validate_confirm_password($_POST['password'] ?? '', $_POST['confirm_password'] ?? '');
if (!$confirm['valid']) $errors['confirm_password'] = $confirm['message'];
// Terms check
if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
    $errors['terms'] = 'You must agree to the Terms & Conditions.';
}
// ■■ If errors, show error page ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
if (!empty($errors)) {
    showResultPage('Registration Failed', $errors, true);
    exit;
}
// ■■ Hash password ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
$data['registered_at'] = date('Y-m-d H:i:s');
// ■■ Ensure data directory exists ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
// ■■ Store to CSV ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$csvFile = $dataDir . '/registrations.csv';
$csvExists = file_exists($csvFile);
$fp = fopen($csvFile, 'a');
if ($fp) {
    if (!$csvExists) {
        // Write header row
        fputcsv($fp, ['fullname', 'email', 'mobile', 'gender', 'course', 'year', 'registered_at']);
    }
    fputcsv($fp, [
        $data['fullname'],
        $data['email'],
        $data['mobile'],
        $data['gender'],
        $data['course'],
        $data['year'],
        $data['registered_at']
    ]);
    fclose($fp);
}
// ■■ Store to JSON ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
$jsonFile = $dataDir . '/registrations.json';
$records = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $records = json_decode($jsonContent, true) ?? [];
}
// Don't store password hash in JSON for safety (in a real app, use database)
$jsonRecord = [
    'fullname'      => $data['fullname'],
    'email'         => $data['email'],
    'mobile'        => $data['mobile'],
    'gender'        => $data['gender'],
    'course'        => $data['course'],
    'year'          => $data['year'],
    'registered_at' => $data['registered_at']
];
$records[] = $jsonRecord;
file_put_contents($jsonFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
// ■■ Show success ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■

showResultPage('Registration Successful', [
    'Your account has been created successfully!',
    'Name: ' . $data['fullname'],
    'Email: ' . $data['email'],
    'Course: ' . $data['course'],
    'Data stored in CSV and JSON format.'
], false);
// ■■ Result Page Helper ■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■
function showResultPage($title, $messages, $isError) {
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
            max-width: 500px;
            margin: 0 auto;
            background: <?= $bgColor ?>;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .result-card h1 { color: <?= $textColor ?>; font-size: 1.5rem; margin-bottom: 16px; }
        .result-card p, .result-card li { color: #333; font-size: 0.95rem; line-height: 1.6; }
        .result-card ul { text-align: left; padding-left: 20px; }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 24px;
            background: #00bcd4;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-back:hover { background: #0097a7; }
    </style>
</head>
<body>
    <div class="result-card">
        <h1><?= $icon ?> <?= htmlspecialchars($title) ?></h1>
        <?php if ($isError): ?>
            <p>Please fix the following errors and try again:</p>
            <ul>
                <?php foreach ($messages as $field => $msg): ?>
                    <li><strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $field))) ?>:</strong> <?= 
htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="javascript:history.back()" class="btn-back">← Go Back</a>
        <?php else: ?>
            <ul>
                <?php foreach ($messages as $msg): ?>
                    <li><?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="../Practical-02/StudentHub/login.html" class="btn-back">Go to Login →</a>
        <?php endif; ?>
    </div>
</body>
</html>
    <?php
}
?>
