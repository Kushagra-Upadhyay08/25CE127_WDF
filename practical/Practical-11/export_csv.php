<?php
/**
 * StudentHub — Export Student Records to CSV (Practical 11: Intermediate Extension)
 * 
 * Streams filtered student dataset as a downloadable RFC 4180 CSV file.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
require_once __DIR__ . '/config/db.php';
$search = trim($_GET['search'] ?? '');
$course = trim($_GET['course'] ?? '');
$year   = trim($_GET['year'] ?? '');
$status = trim($_GET['status'] ?? '');
try {
    $pdo = getDBConnection();
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
    $sql = "SELECT id, roll_no, fullname, email, mobile, gender, course, year, status, created_at 
            FROM students 
            {$whereSql} 
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    // Set download headers
    $filename = "studenthub_students_" . date('Y-m-d_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $output = fopen('php://output', 'w');
    // Add UTF-8 BOM for Microsoft Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    // CSV Header row
    fputcsv($output, [
        'ID',
        'Roll Number',
        'Full Name',
        'Email Address',
        'Mobile Number',
        'Gender',
        'Branch / Course',
        'Academic Year',
        'Enrollment Status',
        'Registration Date'
    ]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['roll_no'],
            $row['fullname'],
            $row['email'],
            $row['mobile'],
            ucfirst($row['gender']),
            strtoupper(str_replace('btech-', 'B.Tech ', $row['course'])),
            'Year ' . $row['year'],
            ucfirst($row['status']),

            $row['created_at'],
        ]);
    }
    fclose($output);
    exit;
} catch (Exception $e) {
    die("Error exporting student dataset: " . htmlspecialchars($e->getMessage()));
}
