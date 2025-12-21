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

$registrations = $registrationService->getUserRegistrations($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Saya - Event Management System</title>
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
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-calendar-check"></i> Event Saya</h2>

        <?php if (empty($registrations)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Anda belum terdaftar pada event manapun.
                <a href="index.php">Lihat daftar event</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Waktu Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td><?= htmlspecialchars($reg['title']) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($reg['kategori']) ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($reg['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($reg['lokasi']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($reg['daftar_waktu'])) ?></td>
                                <td>
                                    <a href="event-detail.php?id=<?= $reg['event_id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>

