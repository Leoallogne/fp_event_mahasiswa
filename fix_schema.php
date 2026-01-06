<?php
require_once __DIR__ . '/config/database.php';

// Manual .env loading if not handled by Database class
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Modify event_id to be nullable
    $sql = "ALTER TABLE notifications MODIFY event_id INT NULL";
    $conn->exec($sql);

    echo "Database schema updated successfully: notifications.event_id is now NULLABLE.\n";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
