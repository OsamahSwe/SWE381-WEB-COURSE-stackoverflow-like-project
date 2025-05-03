<?php
// config.php — include at the very top of every script

// 1. Start session exactly once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Database credentials (only define if not already)
if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'stackoverflow_clone');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// 3. Create PDO instance
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    exit('DB connection failed: ' . htmlspecialchars($e->getMessage()));
}
