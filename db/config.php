<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'store');
define('DB_USER', 'root');
define('DB_PASS', '');

// إنشاء اتصال PDO
function getPDOConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die(json_encode([
            'error' => true,
            'message' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
        ]));
    }
}
?>