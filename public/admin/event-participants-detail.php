<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/events/EventService.php';
require_once __DIR__ . '/../../modules/registrations/RegistrationService.php';

$auth = new Auth();
$auth->requireAdmin();

$eventService = new EventService();
$registrationService = new RegistrationService();

$eventId = $_GET['id'] ?? 0;
$event = $eventService->getEventById($eventId);

if (!$event) {
    header('Location: event-participants.php');
    exit;
}

$participants = $registrationService->getEventRegistrations($eventId);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
        <h2 class="mb-4">
            <i class="bi bi-people"></i> Daftar Peserta: <?= htmlspecialchars($event['title']) ?>
        </h2>

        <div class="card">
            <div class="card-body">
                <p><strong>Total Peserta:</strong> <?= count($participants) ?> / <?= $event['kuota'] ?></p>
                
                <?php if (empty($participants)): ?>
                    <div class="alert alert-info">
                        Belum ada peserta yang terdaftar pada event ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Waktu Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $index => $participant): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($participant['nama']) ?></td>
                                        <td><?= htmlspecialchars($participant['email']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($participant['daftar_waktu'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
