<?php
/**
 * StudentHub — Secure Login Interface (Practical 10)
 * 
 * Features:
 * - CSRF token verification
 * - Remember-Me persistent authentication option
 * - Flash messages (Logout, Session Timeout, Unauthorized Access, Errors)
 * - Quick-fill demo credentials for Student & Admin testing
 * - Auto-redirect if already authenticated
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
initSession();
// If user already authenticated, redirect to appropriate role dashboard
if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_student.php');
    }
    exit;
}
// Notice messages
$msgType = $_GET['msg'] ?? '';
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Portal Login — StudentHub (Practical 10)</title>
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
            --shadow: 0 4px 16px rgba(0,0,0,0.08);
            --font-sans: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            font-family: var(--font-sans);
            background: var(--color-bg);
            color: var(--color-text);
            margin: 0;
            padding: 30px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-wrapper {
            width: 100%;
            max-width: 480px;
        }
        .header-box {
            background: var(--color-primary);
            color: #fff;
            padding: 22px 24px;
            border-radius: var(--radius) var(--radius) 0 0;
            text-align: center;
        }
        .header-box h1 { margin: 0 0 6px; font-size: 1.4rem; }
        .header-box p { margin: 0; color: #b0bec5; font-size: 0.88rem; }
        .login-card {
            background: var(--color-card);
            border-radius: 0 0 var(--radius) var(--radius);
            padding: 28px;
            box-shadow: var(--shadow);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            line-height: 1.4;
        }
        .alert-success { background: var(--color-success-bg); color: var(--color-success); border-left: 4px solid var(
--color-success); }
        .alert-danger { background: var(--color-danger-bg); color: var(--color-danger); border-left: 4px solid var(--
color-danger); }
        .alert-warning { background: var(--color-warning-bg); color: var(--color-warning); border-left: 4px solid var(
--color-warning); }
        .form-group {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label {
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--color-primary-lt);
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.15);
        }
        .row-remember {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
        .btn-login {
            background: var(--color-accent);
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;

            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-login:hover { background: var(--color-accent-dark); }
        .demo-box {
            margin-top: 24px;
            padding: 16px;
            background: #f8fafc;
            border: 1px dashed var(--color-border);
            border-radius: 6px;
            font-size: 0.82rem;
        }
        .demo-box strong { color: var(--color-primary); }
        .demo-btns {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-demo {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid var(--color-accent);
            background: #fff;
            color: var(--color-accent-dark);
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-demo:hover {
            background: var(--color-accent);
            color: #fff;
        }
        .nav-links {
            text-align: center;
            margin-top: 18px;
            font-size: 0.85rem;
            color: var(--color-muted);
        }
        .nav-links a { color: var(--color-accent-dark); text-decoration: none; font-weight: 600; }
        .nav-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="header-box">
            <h1>■ StudentHub Authentication</h1>
            <p>Practical 10: Secure Login/Logout, Sessions, RBAC & Timeout</p>
        </div>
        <div class="login-card">
            <!-- Flash Alerts -->
            <?php if ($msgType === 'logged_out'): ?>
                <div class="alert alert-success">
                    <strong>Logged Out:</strong> You have safely terminated your session.
                </div>
            <?php elseif ($msgType === 'timeout'): ?>
                <div class="alert alert-warning">
                    <strong>Session Expired:</strong> For security, your session timed out after 15 minutes of 
inactivity. Please log in again.
                </div>
            <?php elseif ($msgType === 'unauthorized'): ?>
                <div class="alert alert-danger">
                    <strong>Access Restricted:</strong> You must log in before accessing that dashboard.
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Login Failed:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form action="process_login.php" method="POST" id="loginForm">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="identity">Username or Email Address</label>
                    <input type="text" id="identity" name="identity" placeholder="Enter username or email" required 
autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                <div class="row-remember">
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                        <input type="checkbox" name="remember_me" value="1" id="rememberMe">
                        <span>Remember me (30 days)</span>
                    </label>
                    <span style="color:#777; font-size:0.8rem;">AES/SHA256 Token</span>
                </div>
                <button type="submit" class="btn-login">Sign In to Dashboard</button>
            </form>
            <!-- Quick Demo Credentials Filler -->
            <div class="demo-box">

                <strong>■ Quick Test Credentials (CHARUSAT Seed):</strong>
                <div class="demo-btns">
                    <button type="button" class="btn-demo" onclick="fillCreds('ukushagra8@gmail.com', 
'Student@123')">
                        ■ Student (Kushagra)
                    </button>
                    <button type="button" class="btn-demo" onclick="fillCreds('admin@charusat.edu.in', 'Admin@123')">
                        ■ Administrator
                    </button>
                </div>
            </div>
            <div class="nav-links">
                Need an account? <a href="../Practical-09/register.php">Register Here (Practical 9)</a><br>
                <a href="../Practical-08/test_connection.php">&larr; Return to DB Diagnostics (Practical 8)</a>
            </div>
        </div>
    </div>
    <script>
        function fillCreds(ident, pass) {
            document.getElementById('identity').value = ident;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
