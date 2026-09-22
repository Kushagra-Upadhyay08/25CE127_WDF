<?php
/**
 * StudentHub — Student Management CRUD Module (Practical 11)
 * 
 * Features:
 * - Search by Roll No, Full Name, Email
 * - Multi-criteria Filters: Course, Academic Year, Status
 * - Pagination with dynamic page links preserving filter state
 * - Action controls: View, Add, Edit, Soft Delete / Restore, Hard Delete
 * - Intermediate Extension: Export filtered dataset to CSV
 * - Advanced Extension: Soft delete via status toggle ('active' / 'inactive')
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
// Query parameters
$search = trim($_GET['search'] ?? '');
$course = trim($_GET['course'] ?? '');
$year   = trim($_GET['year'] ?? '');
$status = trim($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 8;
$offset = ($page - 1) * $limit;
$students = [];
$totalRows = 0;
$totalPages = 1;
try {
    $pdo = getDBConnection();
    // Build dynamic parameterized WHERE conditions
    $conditions = [];
    $params = [];
    if ($search !== '') {
        $conditions[] = "(roll_no LIKE ? OR fullname LIKE ? OR email LIKE ?)";
        $wildcard = "%{$search}%";
        $params[] = $wildcard;
        $params[] = $wildcard;
        $params[] = $wildcard;
    }
    if ($course !== '') {
        $conditions[] = "course = ?";
        $params[] = $course;
    }
    if ($year !== '') {
        $conditions[] = "year = ?";
        $params[] = (int)$year;
    }
    if ($status !== '') {
        $conditions[] = "status = ?";
        $params[] = $status;
    }
    $whereSql = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    // Count Total Matching Records
    $countSql = "SELECT COUNT(*) FROM students {$whereSql}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);

    $totalRows = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalRows / $limit));
    // Ensure valid page range
    if ($page > $totalPages && $totalPages > 0) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }
    // Query Paginated Results
    $sql = "SELECT id, roll_no, fullname, email, mobile, gender, course, year, status, created_at 
            FROM students 
            {$whereSql} 
            ORDER BY id ASC 
            LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("[Practical-11 CRUD Error]: " . $e->getMessage());
    $dbError = $e->getMessage();
}
// Helper to preserve filter query params in pagination and links
function buildQueryString(array $override = []): string {
    $current = $_GET;
    foreach ($override as $k => $v) {
        if ($v === null) {
            unset($current[$k]);
        } else {
            $current[$k] = $v;
        }
    }
    return http_build_query($current);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management CRUD — Practical 11</title>
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
            --color-warning: #e65100;
            --color-warning-bg: #ffe0b2;
            --color-danger: #c62828;
            --color-danger-bg: #ffcdd2;
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
        .header-actions { display: flex; gap: 10px; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
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
        .btn-export { background: #2e7d32; color: #fff; }
        .btn-export:hover { background: #1b5e20; }
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
        /* Filter Bar */
        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto auto;
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
        input[type="text"]:focus, select:focus {
            border-color: var(--color-accent);
        }
        .btn-filter { background: var(--color-primary-lt); color: #fff; height: 38px; }
        .btn-reset { background: #e0e0e0; color: #333; height: 38px; }
        .btn-reset:hover { background: #d5d5d5; }
        /* Table */
        .table-responsive { overflow-x: auto; margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        thead { background: var(--color-primary); color: #fff; }
        th { padding: 11px 12px; text-align: left; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.
4px; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tbody tr:hover { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-active { background: var(--color-success-bg); color: var(--color-success); }
        .badge-inactive { background: #eceff1; color: #546e7a; }
        .row-actions { display: flex; gap: 6px; align-items: center; }
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.78rem;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view { background: #e0f7fa; color: #00838f; border: 1px solid #b2ebf2; }
        .btn-view:hover { background: #b2ebf2; }
        .btn-edit { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
        .btn-edit:hover { background: #ffe0b2; }
        .btn-toggle { background: #eceff1; color: #37474f; border: 1px solid #cfd8dc; }
        .btn-toggle:hover { background: #cfd8dc; }
        .btn-del { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; cursor: pointer; }
        .btn-del:hover { background: #ffcdd2; }
        /* Pagination */
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 0.88rem;
        }
        .page-links { display: flex; gap: 4px; }
        .page-link {
            padding: 6px 12px;
            border: 1px solid var(--color-border);
            border-radius: 4px;
            color: var(--color-text);
            text-decoration: none;
            font-weight: 500;
        }
        .page-link.active {
            background: var(--color-accent);
            color: #fff;

            border-color: var(--color-accent);
            font-weight: 700;
        }
        .page-link:hover:not(.active) { background: #f0f0f0; }
        @media (max-width: 900px) {
            .filter-form { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1>■ Student Management Console</h1>
                <p>Practical 11: Data-Driven CRUD with Dynamic Search, Filter & Prepared Statements</p>
            </div>
            <div class="header-actions">
                <a href="export_csv.php?<?= htmlspecialchars(buildQueryString()) ?>" class="btn btn-export" 
title="Export current filtered view to CSV">
                    ■ Export CSV
                </a>
                <a href="add_student.php" class="btn btn-primary">
                    ■ Add Student
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
        <?php if (!empty($dbError)): ?>
            <div class="alert alert-danger">
                <strong>Database Error:</strong> <?= htmlspecialchars($dbError) ?>
            </div>
        <?php endif; ?>
        <!-- Search & Filter Controls -->
        <div class="card">
            <form method="GET" action="index.php" class="filter-form">
                <div class="form-group">
                    <label for="search">Search Keywords</label>
                    <input type="text" id="search" name="search" placeholder="Name, Roll No, or Email..." value="<?= 
htmlspecialchars($search) ?>">
                </div>
                <div class="form-group">
                    <label for="course">Branch / Course</label>
                    <select id="course" name="course">
                        <option value="">All Branches</option>
                        <option value="btech-ce" <?= $course === 'btech-ce' ? 'selected' : '' ?>>B.Tech CE</option>
                        <option value="btech-cs" <?= $course === 'btech-cs' ? 'selected' : '' ?>>B.Tech CS</option>
                        <option value="btech-it" <?= $course === 'btech-it' ? 'selected' : '' ?>>B.Tech IT</option>
                        <option value="btech-ec" <?= $course === 'btech-ec' ? 'selected' : '' ?>>B.Tech EC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <select id="year" name="year">
                        <option value="">All Years</option>
                        <option value="1" <?= $year === '1' ? 'selected' : '' ?>>Year 1</option>
                        <option value="2" <?= $year === '2' ? 'selected' : '' ?>>Year 2</option>
                        <option value="3" <?= $year === '3' ? 'selected' : '' ?>>Year 3</option>
                        <option value="4" <?= $year === '4' ? 'selected' : '' ?>>Year 4</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-filter">■ Filter</button>
                <a href="index.php" class="btn btn-reset">Reset</a>
            </form>
        </div>
        <!-- Students Table -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h3 style="margin:0; font-size:1.1rem; color:var(--color-primary-lt);">
                    Enrolled Students Record (<?= $totalRows ?> Total)
                </h3>
                <span style="font-size:0.85rem; color:var(--color-muted);">
                    Showing Page <?= $page ?> of <?= $totalPages ?>
                </span>
            </div>
            <div class="table-responsive">

                <table>
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Branch</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['roll_no']) ?></strong></td>
                                <td><?= htmlspecialchars($s['fullname']) ?></td>
                                <td><?= htmlspecialchars($s['email']) ?></td>
                                <td><?= htmlspecialchars($s['mobile']) ?></td>
                                <td><?= htmlspecialchars(strtoupper(str_replace('btech-', '', $s['course']))) ?></td>
                                <td>Yr <?= htmlspecialchars((string)$s['year']) ?></td>
                                <td>
                                    <span class="badge <?= $s['status'] === 'active' ? 'badge-active' : 'badge-
inactive' ?>">
                                        <?= htmlspecialchars(ucfirst($s['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content: flex-end;">
                                        <a href="view_student.php?id=<?= $s['id'] ?>" class="btn-sm btn-view" 
title="View Student Profile">
                                            ■■
                                        </a>
                                        <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn-sm btn-edit" 
title="Edit Student">
                                            ✏■
                                        </a>
                                        <!-- Soft Delete / Restore Toggle (Advanced Extension) -->
                                        <?php if ($s['status'] === 'active'): ?>
                                            <form action="delete_student.php" method="POST" style="display:inline;" 
onsubmit="return confirm('Soft-delete this student (status will become Inactive)?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <input type="hidden" name="action" value="soft_delete">
                                                <button type="submit" class="btn-sm btn-toggle" title="Soft Delete (
Toggle to Inactive)">
                                                    Archive
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="delete_student.php" method="POST" style="display:inline;" 
onsubmit="return confirm('Restore this student back to Active status?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <input type="hidden" name="action" value="restore">
                                                <button type="submit" class="btn-sm btn-toggle" style="background:
#e8f5e9; color:#2e7d32;" title="Restore to Active">
                                                    Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <!-- Permanent Hard Delete -->
                                        <form action="delete_student.php" method="POST" style="display:inline;" 
onsubmit="return confirm('WARNING: Permanently delete student <?= htmlspecialchars($s['roll_no']) ?>? This cannot be 
undone.');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="action" value="hard_delete">
                                            <button type="submit" class="btn-sm btn-del" title="Permanent Delete">
                                                ■■
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:32px; color:var(--color-muted);">
                                    No student records found matching the applied filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-bar">
                <span>Displaying <?= count($students) ?> of <?= $totalRows ?> students</span>
                <div class="page-links">
                    <?php if ($page > 1): ?>
                        <a href="index.php?<?= htmlspecialchars(buildQueryString(['page' => $page - 1])) ?>" 
class="page-link">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                        <a href="index.php?<?= htmlspecialchars(buildQueryString(['page' => $i])) ?>" 
                           class="page-link <?= ($i === $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?<?= htmlspecialchars(buildQueryString(['page' => $page + 1])) ?>" 
class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div style="text-align:center; margin-top:16px; font-size:0.85rem; color:var(--color-muted);">
            <a href="../Practical-10/dashboard_admin.php" style="color:var(--color-accent-dark); text-decoration:none;
 font-weight:600;">
                &larr; Admin Dashboard (Practical 10)
            </a> &bull;
            <a href="../Practical-08/test_connection.php" style="color:var(--color-accent-dark); text-decoration:none;
 font-weight:600;">
                Database Diagnostics (Practical 8)
            </a>
        </div>
    </div>
</body>
</html>
