<?php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/GoogleAuth.php';

$googleAuth = new GoogleAuth();

// Auto-migrate database if needed
try {
    $database = new Database();
    $db = $database->getConnection();
    // Check if google_id exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email");
    }
} catch (Exception $e) {
    // Silently continue if column already exists or other DB error
}

if (isset($_GET['code'])) {
    // Check if this is a Calendar Sync request
    if (isset($_GET['state']) && $_GET['state'] === 'calendar_sync') {
        require_once __DIR__ . '/../../modules/events/EventService.php';

        $code = $_GET['code'];
        $event_id = $_SESSION['sync_event_id'] ?? null;

        if (!$event_id) {
            header('Location: ../my-events.php?error=no_event_id');
            exit;
        }

        // 1. Exchange Code for Access Token manually (since GoogleAuth might be tailored for login)
        // We use cURL to keep it isolated and simple
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
            // Log error but redirect to avoid dead end
            error_log("Google Sync Token Error: " . print_r($token_data, true));
            header('Location: ../my-events.php?error=auth_failed');
            exit;
        }

        $access_token = $token_data['access_token'];

        // 2. Get Event Details
        $eventService = new EventService();
        $event = $eventService->getEventById($event_id);

        if ($event) {
            // 3. Create Event in Google Calendar
            $calendar_api_url = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';

            $startDateTime = date('Y-m-d\TH:i:s', strtotime($event['tanggal']));
            $endDateTime = date('Y-m-d\TH:i:s', strtotime($event['tanggal'] . ' +2 hours')); // Default 2 hours

            $event_data = [
                'summary' => $event['title'],
                'location' => $event['lokasi'],
                'description' => $event['deskripsi'],
                'start' => ['dateTime' => $startDateTime, 'timeZone' => 'Asia/Jakarta'],
                'end' => ['dateTime' => $endDateTime, 'timeZone' => 'Asia/Jakarta'],
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

            $calResponse = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200 || $http_code == 201) {
                header('Location: ../my-events.php?status=synced');
            } else {
                header('Location: ../my-events.php?error=sync_failed');
            }
        } else {
            header('Location: ../my-events.php?error=event_not_found');
        }
        exit;
    }

    // Standard Login flow
    $googleUser = $googleAuth->authenticate($_GET['code']);

    if ($googleUser) {
        $user = $googleAuth->findOrCreateUser($googleUser);

        if ($user) {
            Session::regenerate();
            Session::set('user_id', $user['id']);
            Session::set('user_nama', $user['nama']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);
            Session::set('logged_in', true);

            header('Location: ../dashboard.php');
            exit;
        }
    }
}

// If something goes wrong
header('Location: ../login.php?error=google_failed');
exit;
