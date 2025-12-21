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

$result = $registrationService->cancelRegistration($currentUser['id'], $eventId);

header('Location: event-detail.php?id=' . $eventId);
exit;

