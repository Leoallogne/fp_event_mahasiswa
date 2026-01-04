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
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --font-inter: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            font-family: var(--font-inter);
            color: #1f2937;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-custom th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: #6b7280;
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-size: 0.95rem;
        }

        .table-custom tr:last-child td {
            border-bottom: none;
        }

        .table-custom tr:hover td {
            background-color: #f9fafb;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .event-meta-title {
            font-weight: 600;
            color: #111827;
            text-decoration: none;
        }

        .event-meta-title:hover {
            color: #4f46e5;
        }

        .badge-soft {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .bg-label-primary {
            background-color: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }

        .bg-label-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .bg-label-info {
            background-color: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        .bg-label-secondary {
            background-color: rgba(107, 114, 128, 0.1);
            color: #4b5563;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            color: #6b7280;
            background: transparent;
            border: 1px solid #e5e7eb;
        }

        .btn-action:hover {
            background: #f3f4f6;
            color: #4f46e5;
            border-color: #d1d5db;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }

            /* Mobile Card View */
            .table-responsive {
                border: 0;
            }

            .table-custom thead {
                display: none;
            }

            .table-custom tr {
                display: block;
                background: white;
                border-radius: 12px;
                border: 1px solid #e5e7eb;
                margin-bottom: 1rem;
                padding: 1rem;
            }

            .table-custom td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border: none;
                text-align: right;
            }

            .table-custom td::before {
                content: attr(data-label);
                font-weight: 600;
                font-size: 0.85rem;
                color: #6b7280;
                margin-right: 1rem;
            }

            .table-custom td:first-child {
                text-align: left;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid #f3f4f6;
                margin-bottom: 0.5rem;
            }

            .table-custom td:first-child::before {
                display: none;
            }
        }
    </style>
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
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>

</html>