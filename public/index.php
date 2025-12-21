<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';
require_once __DIR__ . '/../modules/registrations/RegistrationService.php';

$auth = new Auth();
$eventService = new EventService();
$registrationService = new RegistrationService();

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    header('Location: landing.php');
    exit;
}

// Redirect if admin
if ($auth->isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$events = $eventService->getAllEvents();
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Event - Event Management System</title>
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
        <h2 class="mb-4"><i class="bi bi-calendar3"></i> Daftar Event</h2>

        <?php if (empty($events)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada event yang tersedia.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($events as $event): 
                    $isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $event['id']) : false;
                    $availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-tag"></i> <?= htmlspecialchars($event['kategori']) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar"></i> <?= date('d/m/Y H:i', strtotime($event['tanggal'])) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['lokasi']) ?>
                                </p>
                                <p class="card-text small text-muted">
                                    <?= htmlspecialchars(substr($event['deskripsi'], 0, 100)) ?>...
                                </p>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        Peserta: <?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="event-detail.php?id=<?= $event['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <?php if ($auth->isLoggedIn() && !$isRegistered && $availableQuota > 0): ?>
                                    <a href="register-event.php?id=<?= $event['id'] ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-plus-circle"></i> Daftar
                                    </a>
                                <?php elseif ($isRegistered): ?>
                                    <span class="badge bg-success">Terdaftar</span>
                                <?php elseif ($availableQuota <= 0): ?>
                                    <span class="badge bg-danger">Penuh</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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

