<?php
/**
 * StudentHub — Database Connection Diagnostic & Test (Practical 8)
 * 
 * Verifies both PDO and MySQLi connections, displays schema statistics,
 * and tests prepared statement execution. Works via Web and CLI.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/db.php';
$isCli = (php_sapi_name() === 'cli');
$pdoStatus = false;
$pdoMsg = '';
$pdoVersion = '';
$mysqliStatus = false;
$mysqliMsg = '';
$mysqliVersion = '';
$tables = [];
$sampleStudents = [];
// 1. Test PDO
try {
    $pdo = getPDOConnection();
    $pdoStatus = true;
    $pdoVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    $pdoMsg = "Connected successfully via PDO to MySQL (" . $pdoVersion . ")";
    // Fetch tables & counts
    $stmt = $pdo->query("SHOW TABLES");
    $rawTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($rawTables as $t) {
        $cStmt = $pdo->query("SELECT COUNT(*) FROM `{$t}`");
        $tables[$t] = (int)$cStmt->fetchColumn();
    }
    // Test Prepared Statement (Intermediate Extension)
    $prepStmt = $pdo->prepare("SELECT id, roll_no, fullname, email, course, year, status FROM students ORDER BY id 
ASC LIMIT 5");
    $prepStmt->execute();
    $sampleStudents = $prepStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $pdoStatus = false;
    $pdoMsg = $e->getMessage();
}
// 2. Test MySQLi
try {
    $mysqli = getMySQLiConnection();
    $mysqliStatus = true;
    $mysqliVersion = $mysqli->server_info;
    $mysqliMsg = "Connected successfully via MySQLi (" . $mysqliVersion . ")";
} catch (Exception $e) {
    $mysqliStatus = false;
    $mysqliMsg = $e->getMessage();
}
// CLI Output Mode
if ($isCli) {
    echo "========================================================\n";
    echo " StudentHub Database Connection Test (Practical 8)\n";
    echo "========================================================\n";
    echo "Host: " . DB_HOST . ":" . DB_PORT . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "User: " . DB_USER . "\n\n";
    echo "[PDO Connection]:    " . ($pdoStatus ? "PASS" : "FAIL") . "\n";
    echo "  Details: " . $pdoMsg . "\n\n";
    echo "[MySQLi Connection]: " . ($mysqliStatus ? "PASS" : "FAIL") . "\n";
    echo "  Details: " . $mysqliMsg . "\n\n";
    if (!empty($tables)) {
        echo "Tables and Record Counts:\n";
        foreach ($tables as $tbl => $cnt) {
            echo "  - {$tbl}: {$cnt} rows\n";
        }
        echo "\n";

    }
    if (!empty($sampleStudents)) {
        echo "Sample Prepared Statement Query Result (First 5 Students):\n";
        foreach ($sampleStudents as $s) {
            echo "  [#{$s['id']}] {$s['roll_no']} - {$s['fullname']} ({$s['course']} Year {$s['year']}) - {$s[
'status']}\n";
        }
    }
    echo "========================================================\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Diagnostic — Practical 8</title>
    <style>
        :root {
            --color-primary: #1a1a2e;
            --color-accent: #00bcd4;
            --color-bg: #f0f2f5;
            --color-success: #2e7d32;
            --color-danger: #c62828;
            --color-card: #ffffff;
            --radius: 8px;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--color-bg);
            color: #222;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
        }
        header {
            background: var(--color-primary);
            color: #fff;
            padding: 24px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        header h1 { margin: 0 0 6px; font-size: 1.6rem; }
        header p { margin: 0; color: #b0bec5; font-size: 0.95rem; }
        .meta-bar {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 14px;
            font-size: 0.85rem;
            color: #eceff1;
        }
        .meta-tag {
            background: rgba(255,255,255,0.12);
            padding: 4px 10px;
            border-radius: 4px;
        }
        .card {
            background: var(--color-card);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
        }
        h2 {
            margin-top: 0;
            color: var(--color-primary);
            font-size: 1.25rem;
            border-bottom: 2px solid var(--color-accent);
            display: inline-block;
            padding-bottom: 4px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .status-box {
            border: 1px solid #e0e0e0;
            border-radius: var(--radius);
            padding: 16px;
            background: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .badge-success { background: #c8e6c9; color: var(--color-success); }
        .badge-danger { background: #ffcdd2; color: var(--color-danger); }
        .table-custom {

            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 0.9rem;
        }
        .table-custom th {
            background: var(--color-primary);
            color: #fff;
            padding: 10px 14px;
            text-align: left;
        }
        .table-custom td {
            padding: 9px 14px;
            border-bottom: 1px solid #eee;
        }
        .table-custom tr:hover { background: #f8fafc; }
        .instructions {
            background: #e0f7fa;
            border-left: 4px solid var(--color-accent);
            padding: 14px 18px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 16px;
        }
        .code-box {
            background: #263238;
            color: #eceff1;
            padding: 12px 16px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.85rem;
            overflow-x: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>■ StudentHub — Database Connection Test</h1>
            <p>Practical 8: MySQL Schema Design, ER Model, PDO Connectivity, and Prepared Statements</p>
            <div class="meta-bar">
                <span class="meta-tag">Student: <strong>Kushagra Upadhyay (25CE127)</strong></span>
                <span class="meta-tag">Database: <strong><?= htmlspecialchars(DB_NAME) ?></strong></span>
                <span class="meta-tag">Host: <strong><?= htmlspecialchars(DB_HOST . ':' . DB_PORT) ?></strong></span>
                <span class="meta-tag">User: <strong><?= htmlspecialchars(DB_USER) ?></strong></span>
            </div>
        </header>
        <!-- Driver Test Results -->
        <div class="card">
            <h2>Driver Connectivity Status</h2>
            <div class="status-grid">
                <div class="status-box">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <strong>PHP Data Objects (PDO)</strong>
                        <span class="badge <?= $pdoStatus ? 'badge-success' : 'badge-danger' ?>">
                            <?= $pdoStatus ? 'CONNECTED' : 'FAILED' ?>
                        </span>
                    </div>
                    <p style="margin:4px 0; font-size:0.85rem; color:#555;">
                        <?= htmlspecialchars($pdoMsg) ?>
                    </p>
                    <small style="color:#777;">Mode: PDO with Emulated Prepares Disabled</small>
                </div>
                <div class="status-box">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <strong>MySQL Improved (MySQLi)</strong>
                        <span class="badge <?= $mysqliStatus ? 'badge-success' : 'badge-danger' ?>">
                            <?= $mysqliStatus ? 'CONNECTED' : 'FAILED' ?>
                        </span>
                    </div>
                    <p style="margin:4px 0; font-size:0.85rem; color:#555;">
                        <?= htmlspecialchars($mysqliMsg) ?>
                    </p>
                    <small style="color:#777;">Mode: Object-Oriented MySQLi Driver</small>
                </div>
            </div>
            <?php if (!$pdoStatus || !$mysqliStatus): ?>
            <div class="instructions">
                <strong>■ Tip to start MySQL daemon in XAMPP:</strong>
                <p style="margin:6px 0 0;">Ensure MySQL is running in your XAMPP Control Panel or terminal:</p>
                <div class="code-box">sudo /opt/lampp/lampp startmysql<br>mysql -u root &lt; studenthub.sql</div>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($tables)): ?>
        <!-- Schema Tables -->
        <div class="card">
            <h2>Database Tables & Row Counts</h2>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Row Count</th>
                        <th>Classification</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($tables as $tbl => $cnt): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($tbl) ?></strong></td>
                        <td><?= htmlspecialchars((string)$cnt) ?> records</td>
                        <td>
                            <?php 
                                if (in_array($tbl, ['students', 'events', 'users'])) echo 'Core Entity';
                                elseif ($tbl === 'registrations') echo 'Associative (M:N)';
                                else echo 'Security / System';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php if (!empty($sampleStudents)): ?>
        <!-- Prepared Statement Demo -->
        <div class="card">
            <h2>Prepared Statement Query Demo (First 5 Students)</h2>
            <p style="font-size:0.85rem; color:#666;">Executed via: <code>$pdo-&gt;prepare("SELECT ... FROM students 
LIMIT 5")</code></p>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Roll No</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sampleStudents as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$s['id']) ?></td>
                        <td><strong><?= htmlspecialchars($s['roll_no']) ?></strong></td>
                        <td><?= htmlspecialchars($s['fullname']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars(strtoupper($s['course'])) ?></td>
                        <td>Year <?= htmlspecialchars((string)$s['year']) ?></td>
                        <td>
                            <span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= htmlspecialchars(ucfirst($s['status'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

Practical 09
