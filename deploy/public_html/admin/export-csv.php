<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/analytics/AnalyticsService.php';

$auth = new Auth();
$auth->requireAdmin(); // Only admin can export

$analytics = new AnalyticsService();
$data = $analytics->getExportData();

$filename = "laporan_event_" . date('Y-m-d_H-i-s') . ".csv";

// Set Headers for Download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Check if data is empty
if (empty($data)) {
    fputcsv($output, ['Belum ada data pendaftaran']);
    fclose($output);
    exit;
}

// Add Column Headers
$headers = array_keys($data[0]);
// Optional: Prettify headers
$prettyHeaders = array_map(function ($header) {
    return ucwords(str_replace('_', ' ', $header));
}, $headers);

fputcsv($output, $prettyHeaders);

// Add Rows
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
