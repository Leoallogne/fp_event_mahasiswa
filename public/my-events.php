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
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Kategori</th>
                                <th>Tanggal Event</th>
                                <th>Tiket</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td data-label="Event" style="min-width: 200px;">
                                        <div class="event-meta">
                                            <a href="event-detail.php?id=<?= $reg['event_id'] ?>" class="event-meta-title">
                                                <?= htmlspecialchars($reg['title']) ?>
                                            </a>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($reg['lokasi']) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td data-label="Kategori">
                                        <span class="badge badge-soft bg-label-primary">
                                            <?= htmlspecialchars($reg['kategori']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Tanggal">
                                        <?= date('d M Y, H:i', strtotime($reg['tanggal'])) ?>
                                    </td>
                                    <td data-label="Tiket">
                                        <?php if (!empty($reg['price']) && $reg['price'] > 0): ?>
                                            <span class="fw-medium text-dark">Rp
                                                <?= number_format($reg['price'], 0, ',', '.') ?></span>
                                        <?php else: ?>
                                            <span class="text-success fw-medium">Gratis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Status">
                                        <?php if ($reg['status'] === 'confirmed'): ?>
                                            <span class="badge-soft bg-label-success px-2 py-1 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Terkonfirmasi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-soft bg-label-warning px-2 py-1 rounded-pill text-warning"
                                                style="background-color: rgba(245, 158, 11, 0.1);">
                                                <i class="bi bi-clock-history me-1"></i> Menunggu Bayar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="d-flex gap-2 justify-content-end justify-content-lg-start">
                                            <?php if ($reg['status'] === 'pending'): ?>
                                                <a href="payment.php?id=<?= $reg['event_id'] ?>"
                                                    class="btn btn-sm btn-primary px-3 rounded-pill" title="Bayar Sekarang">
                                                    <i class="bi bi-credit-card me-1"></i> Bayar
                                                </a>
                                            <?php endif; ?>
                                            <a href="event-detail.php?id=<?= $reg['event_id'] ?>" class="btn-action"
                                                title="Lihat Detail" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <!-- Google Calendar Link (Direct) -->
                                            <?php if ($reg['status'] === 'confirmed'): ?>
                                                <a href="export-calendar.php?id=<?= $reg['event_id'] ?>" class="btn-action"
                                                    title="Add to Google Calendar" target="_blank" data-bs-toggle="tooltip">
                                                    <i class="bi bi-calendar-plus"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
        // Init Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

</body>

</html>