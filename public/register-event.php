<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/registrations/RegistrationService.php';

$auth = new Auth();
$auth->requireUser();

$registrationService = new RegistrationService();
$currentUser = $auth->getCurrentUser();

$eventId = $_GET['id'] ?? 0;

if (!$eventId) {
    header('Location: index.php');
    exit;
}

// Check if already registered
if ($registrationService->isRegistered($currentUser['id'], $eventId)) {
    header('Location: event-detail.php?id=' . $eventId . '&error=sudah_terdaftar');
    exit;
}

// Register for event
$result = $registrationService->registerForEvent($currentUser['id'], $eventId);

if ($result['success']) {
    header('Location: event-detail.php?id=' . $eventId . '&success=1');
} else {
    header('Location: event-detail.php?id=' . $eventId . '&error=' . urlencode($result['message']));
}
exit;

