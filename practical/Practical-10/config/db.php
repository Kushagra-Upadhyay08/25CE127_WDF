<?php
/**
 * StudentHub — Database Configuration & Connection (Practical 10)
 * 
 * Provides secure PDO connection for authentication and session management.
 * 
 * @author Kushagra Upadhyay (25CE127)
 * @subject ITUE203 Web Development Frameworks
 */
defined('DB_HOST')    or define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
defined('DB_PORT')    or define('DB_PORT', getenv('DB_PORT') ?: 3306);
defined('DB_NAME')    or define('DB_NAME', getenv('DB_NAME') ?: 'studenthub');
defined('DB_USER')    or define('DB_USER', getenv('DB_USER') ?: 'root');
defined('DB_PASS')    or define('DB_PASS', getenv('DB_PASS') ?: '');
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8mb4');
/**
 * Returns a configured PDO database connection.
 * 
 * @throws PDOException
 * @return PDO
 */
function getDBConnection(): PDO {
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
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("[Practical-10 DB Error]: " . $e->getMessage());
        throw $e;
    }
}
