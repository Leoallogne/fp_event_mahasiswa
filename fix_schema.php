<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Modify event_id to be nullable
    $sql = "ALTER TABLE notifications MODIFY event_id INT NULL";
    $conn->exec($sql);

    echo "Database schema updated successfully: notifications.event_id is now NULLABLE.\n";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
