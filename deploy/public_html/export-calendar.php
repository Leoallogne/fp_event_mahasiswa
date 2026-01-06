<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';

$auth = new Auth();
$auth->requireLogin();

$eventService = new EventService();
$eventId = $_GET['id'] ?? 0;
$event = $eventService->getEventById($eventId);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Generate Google Calendar URL
$startDate = date('Ymd\THis', strtotime($event['tanggal']));
$endDate = date('Ymd\THis', strtotime($event['tanggal'] . ' +2 hours'));
$title = urlencode($event['title']);
$details = urlencode($event['deskripsi']);
$location = urlencode($event['lokasi']);

$googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE" .
    "&text=" . $title .
    "&dates=" . $startDate . "/" . $endDate .
    "&details=" . $details .
    "&location=" . $location .
    "&sf=true&output=xml";

header('Location: ' . $googleCalendarUrl);
exit;

