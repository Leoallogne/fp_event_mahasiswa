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

$eventId = $_GET['id'] ?? 0;
$event = $eventService->getEventById($eventId);

if (!$event) {
    header('Location: index.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $eventId) : false;
$availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php if ($event['latitude'] && $event['longitude']): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <?php endif; ?>
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

        #map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> Pendaftaran berhasil!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title"><?= htmlspecialchars($event['title']) ?></h2>

                            <div class="mb-3">
                                <span class="badge bg-primary"><?= htmlspecialchars($event['kategori']) ?></span>
                            </div>

                            <div class="mb-3">
                                <p><strong><i class="bi bi-calendar"></i> Tanggal & Waktu:</strong><br>
                                    <?= date('d F Y, H:i', strtotime($event['tanggal'])) ?></p>
                            </div>

                            <div class="mb-3">
                                <p><strong><i class="bi bi-geo-alt"></i> Lokasi:</strong><br>
                                    <?= htmlspecialchars($event['lokasi']) ?></p>
                            </div>

                            <div class="mb-3">
                                <p><strong><i class="bi bi-people"></i> Kuota:</strong><br>
                                    <?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?> peserta</p>
                            </div>

                            <div class="mb-3">
                                <h5>Deskripsi</h5>
                                <p><?= nl2br(htmlspecialchars($event['deskripsi'])) ?></p>
                            </div>

                            <?php if ($event['latitude'] && $event['longitude']): ?>
                                <div class="mb-3">
                                    <h5>Lokasi di Peta</h5>
                                    <div id="map"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Aksi</h5>

                            <?php if (!$auth->isLoggedIn()): ?>
                                <p>Silakan login untuk mendaftar event ini.</p>
                                <a href="login.php" class="btn btn-primary w-100">Login</a>
                            <?php elseif ($isRegistered): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> Anda sudah terdaftar pada event ini.
                                </div>
                                <a href="cancel-registration.php?id=<?= $eventId ?>" class="btn btn-danger w-100"
                                    onclick="return confirm('Yakin ingin membatalkan pendaftaran?')">
                                    <i class="bi bi-x-circle"></i> Batalkan Pendaftaran
                                </a>
                            <?php elseif ($availableQuota <= 0): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Kuota sudah penuh.
                                </div>
                            <?php else: ?>
                                <a href="register-event.php?id=<?= $eventId ?>" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Daftar Event
                                </a>
                            <?php endif; ?>

                            <hr>

                            <a href="export-calendar.php?id=<?= $eventId ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-calendar-plus"></i> Export ke Google Calendar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <?php if ($event['latitude'] && $event['longitude']): ?>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const lat = <?= $event['latitude'] ?>;
                    const lng = <?= $event['longitude'] ?>;

                    const map = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker([lat, lng]).addTo(map)
                        .bindPopup("<?= htmlspecialchars($event['title']) ?>")
                        .openPopup();
                });
            </script>
        <?php endif; ?>
        <script>
            function toggleSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('active');
            }
        </script>
</body>

</html>