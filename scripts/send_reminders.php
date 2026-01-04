<?php
// Script to be run via Cron job or manually
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/notifications/NotificationService.php';

echo "Starting Event Reminder Check...\n";

$database = new Database();
$db = $database->getConnection();
$notificationService = new NotificationService();

// Find events happening tomorrow (between 24h and 30h from now to be safe, or just DATE match)
// Let's find events happening roughly 24 hours from now
$tomorrow = date('Y-m-d', strtotime('+1 day'));

echo "Checking for events on: $tomorrow\n";

try {
    $stmt = $db->prepare("SELECT id, title, tanggal FROM events WHERE DATE(tanggal) = ?");
    $stmt->execute([$tomorrow]);
    $events = $stmt->fetchAll();

    $totalSent = 0;

    foreach ($events as $event) {
        echo "Processing event: {$event['title']} (ID: {$event['id']})\n";

        $result = $notificationService->sendEventReminder($event['id']);

        if ($result['success']) {
            echo "  - Sent {$result['sent']} reminders (Total registered: {$result['total']})\n";
            $totalSent += $result['sent'];
        } else {
            echo "  - Failed: {$result['message']}\n";
        }
    }

    echo "Done. Total emails sent: $totalSent\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
