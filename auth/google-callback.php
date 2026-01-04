<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';

$auth = new Auth();
$auth->requireUser();

$env = parse_ini_file(__DIR__ . '/../.env');

if (!isset($_GET['code'])) {
    die("Error: No code returned");
}

$code = $_GET['code'];
$event_id = $_SESSION['sync_event_id'] ?? null;

if (!$event_id) {
    header('Location: ../public/my-events.php?error=no_event_id');
    exit;
}

// 1. Exchange Code for Access Token
$token_url = 'https://oauth2.googleapis.com/token';
$params = [
    'code' => $code,
    'client_id' => $env['GOOGLE_CLIENT_ID'],
    'client_secret' => $env['GOOGLE_CLIENT_SECRET'],
    'redirect_uri' => $env['GOOGLE_REDIRECT_URI'],
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    die("Error getting token: " . print_r($token_data, true));
}

$access_token = $token_data['access_token'];

// 2. Get Event Details
$eventService = new EventService();
$event = $eventService->getEventById($event_id);

if (!$event) {
    die("Event not found");
}

// 3. Create Event in Google Calendar
$calendar_api_url = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';

// Convert date to RFC3339 format
$startDateTime = date('Y-m-d\TH:i:s', strtotime($event['tanggal']));
$endDateTime = date('Y-m-d\TH:i:s', strtotime($event['tanggal'] . ' +2 hours'));

$event_data = [
    'summary' => $event['title'],
    'location' => $event['lokasi'],
    'description' => $event['deskripsi'],
    'start' => [
        'dateTime' => $startDateTime,
        'timeZone' => 'Asia/Jakarta',
    ],
    'end' => [
        'dateTime' => $endDateTime,
        'timeZone' => 'Asia/Jakarta',
    ],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $calendar_api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200 || $http_code == 201) {
    header('Location: ../public/my-events.php?status=synced');
} else {
    // Log error for debug
    error_log("Google Calendar Sync Error: " . $response);
    header('Location: ../public/my-events.php?error=sync_failed');
}
exit();
