<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/users/Auth.php';

$auth = new Auth();
$auth->requireUser();

// Load .env
$env = parse_ini_file(__DIR__ . '/../.env');

$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    die("Event ID required");
}

// Store event ID in session to recall after callback
$_SESSION['sync_event_id'] = $eventId;

// Google OAuth Params
$client_id = $env['GOOGLE_CLIENT_ID'];
$redirect_uri = $env['GOOGLE_REDIRECT_URI'];
$scope = 'https://www.googleapis.com/auth/calendar.events';

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'offline',
    'prompt' => 'consent' // Force prompts to get refresh_token if needed
]);

header('Location: ' . $auth_url);
exit();
