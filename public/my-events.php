<?php

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
    <title>Event Saya - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1">

    <link rel="stylesheet" href="assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/app.css?v=<?= time() ?>">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Event Saya</h1>
            <p class="text-muted mb-0">Riwayat event yang Anda daftarkan</p>
        </div>

        <div class="content-card">
            <?php if (empty($registrations)): ?>
                <div class="empty-state">
                    <i class="bi bi-calendar-x empty-icon"></i>
                    <h5 class="fw-bold text-gray-800 mb-2">Belum ada event</h5>
                    <p class="text-muted mb-4">Anda belum mendaftar ke event manapun.</p>
                    <a href="index.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-search me-2"></i>Jelajahi Event
                    </a>
                </div>
            <?php else: ?>
                <div class="ticket-grid">
                    <?php foreach ($registrations as $reg): ?>
                        <div class="ticket-card">
                            <div class="ticket-header">
                                <div class="ticket-date">
                                    <span class="day"><?= date('d', strtotime($reg['tanggal'])) ?></span>
                                    <span class="month-year"><?= date('M Y', strtotime($reg['tanggal'])) ?></span>
                                </div>
                                <div class="ticket-status">
                                    <?php if ($reg['status'] === 'confirmed'): ?>
                                        <span class="text-white"><i class="bi bi-check-circle-fill me-1"></i> Confirmed</span>
                                    <?php else: ?>
                                        <span class="text-white"><i class="bi bi-clock me-1"></i> Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ticket-divider"></div>

                            <div class="ticket-body">
                                <span class="ticket-category"><?= htmlspecialchars($reg['kategori'] ?? 'Event') ?></span>
                                <h3 class="ticket-title"><?= htmlspecialchars($reg['title']) ?></h3>

                                <div class="ticket-info">
                                    <div class="info-item">
                                        <i class="bi bi-clock"></i>
                                        <span><?= date('H:i', strtotime($reg['tanggal'])) ?> WIB - Selesai</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="bi bi-geo-alt"></i>
                                        <span><?= htmlspecialchars($reg['lokasi']) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="bi bi-ticket-perforated"></i>
                                        <span>
                                            <?php if (!empty($reg['price']) && $reg['price'] > 0): ?>
                                                Rp <?= number_format($reg['price'], 0, ',', '.') ?>
                                            <?php else: ?>
                                                Gratis
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-auto d-flex gap-2">
                                    <?php if ($reg['status'] === 'pending'): ?>
                                        <a href="payment.php?id=<?= $reg['event_id'] ?>" class="btn-ticket btn-ticket-primary">
                                            <i class="bi bi-credit-card"></i> Bayar Sekarang
                                        </a>
                                    <?php else: ?>
                                        <a href="export-calendar.php?id=<?= $reg['event_id'] ?>" target="_blank"
                                            class="btn-ticket btn-ticket-outline">
                                            <i class="bi bi-calendar-plus"></i> Add to Calendar
                                        </a>
                                    <?php endif; ?>

                                    <a href="event-detail.php?id=<?= $reg['event_id'] ?>" class="btn-ticket btn-ticket-outline"
                                        style="width: auto;">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
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
        // Init Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

</body>

</html>