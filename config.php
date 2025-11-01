<?php
date_default_timezone_set('Asia/Bangkok');

// config.php — สร้าง/เชื่อมต่อ SQLite + เปิด error สำหรับ debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $dbDir  = __DIR__ . '/db';
    $dbFile = $dbDir . '/database.sqlite';
    $sqlFile = __DIR__ . '/db/create_tables.sql';

    if (!is_dir($dbDir)) mkdir($dbDir, 0777, true);

    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $exists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='problems'")->fetchColumn();
    if (!$exists) {
        if (file_exists($sqlFile)) $pdo->exec(file_get_contents($sqlFile));
        else {
            $pdo->exec("
                CREATE TABLE problems (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    type TEXT,
                    input_value TEXT,
                    input_base INTEGER,
                    bit_width INTEGER,
                    op TEXT,
                    second_value TEXT,
                    target_base INTEGER,
                    result_value TEXT,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                );
            ");
        }
        $status = "Database & table created successfully.";
    } else {
        $status = "Database OK — table 'problems' already exists.";
    }

    if (basename($_SERVER['SCRIPT_NAME']) === 'config.php') {
        echo $status . "<br>DB path: " . htmlspecialchars($dbFile);
    }
} catch (Throwable $e) {
    if (basename($_SERVER['SCRIPT_NAME']) === 'config.php') {
        echo "ERROR: " . htmlspecialchars($e->getMessage());
    } else {
        throw $e;
    }
}
