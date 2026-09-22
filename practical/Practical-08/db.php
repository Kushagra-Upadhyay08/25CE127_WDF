<?php
/**
 * StudentHub — Database Connection (Practical 8)
 * 
 * Provides both PDO and MySQLi connections with prepared statements,
 * comprehensive error handling, and security hardening.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
// Database Configuration (Default XAMPP parameters)
defined('DB_HOST')    or define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
defined('DB_PORT')    or define('DB_PORT', getenv('DB_PORT') ?: 3306);
defined('DB_NAME')    or define('DB_NAME', getenv('DB_NAME') ?: 'studenthub');
defined('DB_USER')    or define('DB_USER', getenv('DB_USER') ?: 'root');
defined('DB_PASS')    or define('DB_PASS', getenv('DB_PASS') ?: '');
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8mb4');
/**
 * Returns a secure PDO instance.
 * 
 * Configured with:
 * - ERRMODE_EXCEPTION for reliable error catching
 * - FETCH_ASSOC as default fetch mode
 * - EMULATE_PREPARES = false for genuine server-side prepared statements
 * 
 * @throws PDOException
 * @return PDO
 */
function getPDOConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $dsn = sprintf(
        "mysql:host=%s;port=%d;dbname=%s;charset=%s",
        DB_HOST,
        (int)DB_PORT,
        DB_NAME,
        DB_CHARSET
    );
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log detailed error internally, throw generic or structured error
        error_log("[StudentHub DB Error] PDO Connection failed: " . $e->getMessage());
        throw $e;
    }
}
/**
 * Returns a MySQLi connection instance.
 * 
 * @throws mysqli_sql_exception
 * @return mysqli
 */
function getMySQLiConnection(): mysqli {
    static $mysqli = null;
    if ($mysqli !== null) {
        return $mysqli;
    }
    // Enable mysqli exception reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
        $mysqli->set_charset(DB_CHARSET);
        return $mysqli;
    } catch (mysqli_sql_exception $e) {
        error_log("[StudentHub DB Error] MySQLi Connection failed: " . $e->getMessage());
        throw $e;
    }
}
/**

 * Helper: Test database connectivity safely and return diagnostic array.
 * 
 * @return array
 */
function testDatabaseConnection(): array {
    $results = [
        'pdo' => ['status' => false, 'message' => '', 'version' => ''],
        'mysqli' => ['status' => false, 'message' => '', 'version' => ''],
        'tables' => [],
    ];
    // 1. Test PDO
    try {
        $pdo = getPDOConnection();
        $results['pdo']['status'] = true;
        $results['pdo']['version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $results['pdo']['message'] = "PDO connected successfully to database '" . DB_NAME . "'.";
        // Query tables and record counts
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $count = (int)$countStmt->fetchColumn();
            $results['tables'][$table] = $count;
        }
    } catch (Exception $e) {
        $results['pdo']['status'] = false;
        $results['pdo']['message'] = $e->getMessage();
    }
    // 2. Test MySQLi
    try {
        $mysqli = getMySQLiConnection();
        $results['mysqli']['status'] = true;
        $results['mysqli']['version'] = $mysqli->server_info;
        $results['mysqli']['message'] = "MySQLi connected successfully to database '" . DB_NAME . "'.";
    } catch (Exception $e) {
        $results['mysqli']['status'] = false;
        $results['mysqli']['message'] = $e->getMessage();
    }
    return $results;
}
