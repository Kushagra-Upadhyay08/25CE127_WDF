<?php
/**
 * StudentHub — Secure User Registration Form (Practical 9)
 * 
 * Features:
 * - Real-time client-side validation hints
 * - Server-side validation feedback & sticky form fields
 * - CSRF token protection
 * - Password strength meter
 * - Duplicate checks for Email and Username
 * 
 * @author Kushagra Upadhyay (25CE127)

 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/includes/csrf.php';
$errors = $_SESSION['reg_errors'] ?? [];
$old = $_SESSION['reg_old'] ?? [];
$successMessage = $_SESSION['reg_success'] ?? null;
// Clear flash session data
unset($_SESSION['reg_errors'], $_SESSION['reg_old'], $_SESSION['reg_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Student Registration — StudentHub (Practical 9)</title>
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
            padding: 24px 16px;
            line-height: 1.5;
        }
        .container {
            max-width: 760px;
            margin: 0 auto;
        }
        .header-card {
            background: var(--color-primary);
            color: #fff;
            padding: 24px 28px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        .header-card h1 { margin: 0 0 6px; font-size: 1.5rem; }
        .header-card p { margin: 0; color: #b0bec5; font-size: 0.9rem; }
        .meta-tags {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
            font-size: 0.8rem;
        }
        .meta-tag {
            background: rgba(255,255,255,0.12);
            padding: 3px 8px;
            border-radius: 4px;
        }
        .card {
            background: var(--color-card);
            border-radius: var(--radius);
            padding: 28px;
            box-shadow: var(--shadow);
        }
        .alert {
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.92rem;
        }
        .alert-success { background: var(--color-success-bg); color: var(--color-success); border-left: 4px solid var(
--color-success); }
        .alert-danger { background: var(--color-danger-bg); color: var(--color-danger); border-left: 4px solid var(--
color-danger); }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .full-width { grid-column: 1 / -1; }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        label {
            font-weight: 600;
            font-size: 0.88rem;

            color: var(--color-primary-lt);
        }
        label .req { color: var(--color-danger); }
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus, select:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.15);
        }
        .is-invalid {
            border-color: var(--color-danger) !important;
            background: #fff8f8;
        }
        .error-msg {
            color: var(--color-danger);
            font-size: 0.8rem;
            margin-top: 2px;
        }
        .strength-container {
            margin-top: 4px;
        }
        .strength-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }
        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .strength-text {
            font-size: 0.78rem;
            font-weight: 600;
            margin-top: 4px;
            display: block;
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 0.88rem;
        }
        .checkbox-group input { margin-top: 3px; }
        .btn-submit {
            background: var(--color-accent);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: var(--color-accent-dark);
        }
        .helper-text {
            font-size: 0.78rem;
            color: var(--color-muted);
        }
        .footer-links {
            margin-top: 18px;
            text-align: center;
            font-size: 0.88rem;
            color: var(--color-muted);
        }
        .footer-links a {
            color: var(--color-accent-dark);
            text-decoration: none;
            font-weight: 600;
        }
        .footer-links a:hover { text-decoration: underline; }
        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header-card">
            <h1>■ Student Registration Module</h1>
            <p>Practical 9: Secure User Registration with Database Insert, Duplicate Email Check & Password Hashing</
p>
            <div class="meta-tags">
                <span class="meta-tag">Student: <strong>Kushagra Upadhyay (25CE127)</strong></span>
                <span class="meta-tag">Security: <strong>Bcrypt / CSRF / Parameterized Prepared Statements</strong></
span>
            </div>
        </header>
        <div class="card">

            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <strong>■ Success!</strong> <?= htmlspecialchars($successMessage) ?><br>
                    <small>Your account is registered in MySQL with a securely salted Bcrypt password hash.</small>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>■■ Please correct the errors below before submitting:</strong>
                    <ul style="margin: 8px 0 0; padding-left: 20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form action="process_register.php" method="POST" novalidate id="regForm">
                <?= csrfField() ?>
                <div class="form-grid">
                    <!-- Roll Number -->
                    <div class="form-group">
                        <label for="roll_no">Roll Number <span class="req">*</span></label>
                        <input type="text" id="roll_no" name="roll_no" placeholder="e.g. 25CE127" 
                               value="<?= htmlspecialchars($old['roll_no'] ?? '') ?>"
                               class="<?= isset($errors['roll_no']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['roll_no'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['roll_no']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Username <span class="req">*</span></label>
                        <input type="text" id="username" name="username" placeholder="e.g. kushagra" 
                               value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                               class="<?= isset($errors['username']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['username']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Full Name -->
                    <div class="form-group full-width">
                        <label for="fullname">Full Name <span class="req">*</span></label>
                        <input type="text" id="fullname" name="fullname" placeholder="e.g. Kushagra Upadhyay" 
                               value="<?= htmlspecialchars($old['fullname'] ?? '') ?>"
                               class="<?= isset($errors['fullname']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['fullname'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['fullname']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address <span class="req">*</span></label>
                        <input type="email" id="email" name="email" placeholder="e.g. megh@charusat.edu.in" 
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Mobile -->
                    <div class="form-group">
                        <label for="mobile">Mobile Number <span class="req">*</span></label>
                        <input type="text" id="mobile" name="mobile" placeholder="10 digits starting with 6-9" 
                               value="<?= htmlspecialchars($old['mobile'] ?? '') ?>"
                               class="<?= isset($errors['mobile']) ? 'is-invalid' : '' ?>" required maxlength="10">
                        <?php if (isset($errors['mobile'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['mobile']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Gender -->
                    <div class="form-group">
                        <label for="gender">Gender <span class="req">*</span></label>
                        <select id="gender" name="gender" class="<?= isset($errors['gender']) ? 'is-invalid' : '' ?>" 
required>
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</
option>
                            <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' 
?>>Female</option>
                            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</
option>
                        </select>
                        <?php if (isset($errors['gender'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['gender']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Course -->
                    <div class="form-group">
                        <label for="course">Engineering Branch <span class="req">*</span></label>
                        <select id="course" name="course" class="<?= isset($errors['course']) ? 'is-invalid' : '' ?>" 
required>
                            <option value="">Select Branch</option>
                            <option value="btech-ce" <?= ($old['course'] ?? '') === 'btech-ce' ? 'selected' : '' ?>>B.

Tech Computer Engineering (CE)</option>
                            <option value="btech-cs" <?= ($old['course'] ?? '') === 'btech-cs' ? 'selected' : '' ?>>B.
Tech Computer Science & Eng (CSE)</option>
                            <option value="btech-it" <?= ($old['course'] ?? '') === 'btech-it' ? 'selected' : '' ?>>B.
Tech Information Technology (IT)</option>
                            <option value="btech-ec" <?= ($old['course'] ?? '') === 'btech-ec' ? 'selected' : '' ?>>B.
Tech Electronics & Comm (EC)</option>
                        </select>
                        <?php if (isset($errors['course'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['course']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Academic Year -->
                    <div class="form-group">
                        <label for="year">Academic Year <span class="req">*</span></label>
                        <select id="year" name="year" class="<?= isset($errors['year']) ? 'is-invalid' : '' ?>" 
required>
                            <option value="">Select Year</option>
                            <option value="1" <?= ($old['year'] ?? '') === '1' ? 'selected' : '' ?>>1st Year</option>
                            <option value="2" <?= ($old['year'] ?? '') === '2' ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3" <?= ($old['year'] ?? '') === '3' ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4" <?= ($old['year'] ?? '') === '4' ? 'selected' : '' ?>>4th Year</option>
                        </select>
                        <?php if (isset($errors['year'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['year']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password <span class="req">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 
digit, 1 symbol" 
                               class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                        <div class="strength-container">
                            <div class="strength-bar"><div id="strengthFill" class="strength-bar-fill"></div></div>
                            <span id="strengthText" class="strength-text" style="color:#777;">Password strength</span>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="req">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-type 
password" 
                               class="<?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['confirm_password']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Terms & Conditions -->
                    <div class="form-group full-width">
                        <label class="checkbox-group">
                            <input type="checkbox" name="terms" value="1" <?= !empty($old['terms']) ? 'checked' : '' 
?> required>
                            <span>I certify that all details submitted are accurate and I agree to the <a href="../
Practical-02/StudentHub/faq.html" target="_blank">StudentHub Code of Conduct</a>. <span class="req">*</span></span>
                        </label>
                        <?php if (isset($errors['terms'])): ?>
                            <span class="error-msg"><?= htmlspecialchars($errors['terms']) ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- Submit Button -->
                    <div class="form-group full-width" style="margin-top: 10px;">
                        <button type="submit" class="btn-submit">Register Account</button>
                    </div>
                </div>
            </form>
            <div class="footer-links">
                Already registered? <a href="../Practical-10/login.php">Log In to Portal (Practical 10)</a> &bull;
                <a href="../Practical-08/test_connection.php">Test DB Connection (Practical 8)</a>
            </div>
        </div>
    </div>
    <script>
        const passInput = document.getElementById('password');
        const fill = document.getElementById('strengthFill');
        const txt = document.getElementById('strengthText');
        passInput.addEventListener('input', () => {
            const val = passInput.value;
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[a-z]/.test(val)) score++;
            if (/\d/.test(val)) score++;
            if (/[@$!%*?&]/.test(val)) score++;
            if (val.length === 0) {
                fill.style.width = '0%';
                txt.textContent = 'Password strength';
                txt.style.color = '#777';
            } else if (score <= 2) {
                fill.style.width = '33%';

                fill.style.backgroundColor = '#c62828';
                txt.textContent = 'Weak password';
                txt.style.color = '#c62828';
            } else if (score <= 4) {
                fill.style.width = '66%';
                fill.style.backgroundColor = '#f57c00';
                txt.textContent = 'Fair / Medium password';
                txt.style.color = '#f57c00';
            } else {
                fill.style.width = '100%';
                fill.style.backgroundColor = '#2e7d32';
                txt.textContent = 'Strong password';
                txt.style.color = '#2e7d32';
            }
        });
    </script>
</body>
</html>

Practical 10
