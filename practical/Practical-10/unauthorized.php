<?php
/**
 * StudentHub — Unauthorized Access (403 Forbidden) (Practical 10)
 */
require_once __DIR__ . '/includes/auth.php';
initSession();
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied — StudentHub (Practical 10)</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-accent: #00bcd4;
            --color-danger: #c62828;
            --color-card: #ffffff;
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            font-family: var(--font-sans);
            background: #f0f2f5;
            color: #222;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: var(--color-card);
            border-radius: 8px;
            padding: 36px;
            max-width: 520px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .icon {
            font-size: 3.5rem;
            margin-bottom: 12px;
        }
        h1 {
            color: var(--color-danger);
            margin: 0 0 10px;
            font-size: 1.6rem;
        }
        p {
            color: #555;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .badge {
            background: #ffcdd2;

            color: var(--color-danger);
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 16px;
        }
        .btn {
            background: var(--color-accent);
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn:hover { background: #0097a7; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">■</div>
        <span class="badge">HTTP 403 &bull; ROLE-BASED ACCESS CONTROL</span>
        <h1>Access Denied / Forbidden</h1>
        <p>You do not have administrative privileges to view this section. Your current session role (<strong><?= 
htmlspecialchars($_SESSION['role'] ?? 'Guest') ?></strong>) is restricted by StudentHub security policies.</p>
        <div>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="<?= ($_SESSION['role'] === 'admin') ? 'dashboard_admin.php' : 'dashboard_student.php' ?>" 
class="btn">
                    Return to Your Dashboard
                </a>
            <?php else: ?>
                <a href="login.php" class="btn">Go to Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

Practical 11
