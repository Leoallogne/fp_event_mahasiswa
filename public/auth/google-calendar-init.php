<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/users/Auth.php';

$auth = new Auth();
$auth->requireUser();

// Load .env
$envFile = __DIR__ . '/../../.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    die("Event ID required");
}

// Store event ID in session to recall after callback
$_SESSION['sync_event_id'] = $eventId;

// Google OAuth Params
$client_id = $env['GOOGLE_CLIENT_ID'];
// We use the SAME callback URI but handle the logic there or use a distinct one if configured.
// Since User defined ONE callback URI in env, we MUST use that one and dispatch logic inside it,
// OR ask user to add another.
// To keep it simple for User who might strictly follow .env, let's try to use a NEW callback if they can add it, 
// BUT they likely only added one.
// Let's use the `state` parameter or just check session in the generic callback? 
// No, the generic callback `google-callback.php` is logic-heavy for LOGIN.
// Safest: Use a new callback file and ask user to add it? 
// OR: Temporarily override redirect_uri here if Google Console allows loose matching (it doesn't).
// USER REQUESTED: "google_redirect_uri" in env is fixed. 
// If I change it here, I get "redirect_uri_mismatch".
// So I MUST use `public/auth/google-callback.php`.
// I will MODIFY `public/auth/google-callback.php` to handle this scenario.

// Wait, strictly speaking, I should modify `google-callback.php`.
// So this file just redirects to Google.

$redirect_uri = $env['GOOGLE_REDIRECT_URI'];
$scope = 'https://www.googleapis.com/auth/calendar.events';

// Is user already logged in with Google and has token?
// We don't know if the token has calendar scope. Assuming it doesn't.
// We force PROMPT to ensure we get consent.

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'offline',
    'prompt' => 'consent',
    'state' => 'calendar_sync' // Marker to tell callback this is for calendar
]);

header('Location: ' . $auth_url);
exit();
