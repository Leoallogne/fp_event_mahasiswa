<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';
require_once __DIR__ . '/../modules/notifications/NotificationService.php';

$auth = new Auth();
$auth->requireUser();

// Redirect admin ke dashboard admin
if ($auth->isAdmin()) {
    header('Location: admin/dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$notificationService = new NotificationService();
$currentUser = $auth->getCurrentUser();

// Ambil data event yang akan datang
$stmt = $db->prepare("
    SELECT e.* 
    FROM events e
    JOIN registrations r ON e.id = r.event_id
    WHERE r.user_id = ? 
    AND e.tanggal >= CURDATE()
    ORDER BY e.tanggal ASC
    LIMIT 5
");
$stmt->execute([$currentUser['id']]);
$upcomingEvents = $stmt->fetchAll();

// Hitung total event yang diikuti
$stmt = $db->prepare("SELECT COUNT(*) as total FROM registrations WHERE user_id = ?");
$stmt->execute([$currentUser['id']]);
$totalEvents = $stmt->fetch()['total'];

// Hitung event yang sudah selesai
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM registrations r
    JOIN events e ON e.id = r.event_id
    WHERE r.user_id = ?
    AND e.tanggal < CURDATE()
");
$stmt->execute([$currentUser['id']]);
$completedEvents = $stmt->fetch()['total'];

// Hitung event yang akan datang
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM registrations r
    JOIN events e ON e.id = r.event_id
    WHERE r.user_id = ?
    AND e.tanggal >= CURDATE()
");
$stmt->execute([$currentUser['id']]);
$upcomingCount = $stmt->fetch()['total'];

// Data untuk chart
$stmt = $db->prepare("
    SELECT 
        DATE_FORMAT(e.tanggal, '%M %Y') as bulan,
        COUNT(*) as jumlah,
        MIN(e.tanggal) as min_tanggal
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    GROUP BY bulan
    ORDER BY min_tanggal DESC
    LIMIT 6
");
$stmt->execute([$currentUser['id']]);
$chartData = $stmt->fetchAll();

$chartLabels = [];
$chartValues = [];

foreach ($chartData as $data) {
    $chartLabels[] = $data['bulan'];
    $chartValues[] = (int) $data['jumlah'];
}

$chartLabels = array_reverse($chartLabels);
$chartValues = array_reverse($chartValues);

// Data untuk pie chart (distribusi kategori)
$stmt = $db->prepare("
    SELECT e.kategori, COUNT(*) as jumlah
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.user_id = ?
    GROUP BY e.kategori
");
$stmt->execute([$currentUser['id']]);
$categoryData = $stmt->fetchAll();

$pieLabels = [];
$pieValues = [];
foreach ($categoryData as $data) {
    $pieLabels[] = $data['kategori'];
    $pieValues[] = (int) $data['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?= time() ?>">

    <link rel="stylesheet" href="assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/app.css?v=<?= time() ?>">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-lg-8">
                    <h1 class="display-6 fw-bold mb-2">Halo, <?= htmlspecialchars($currentUser['nama'] ?? 'User') ?>! 👋
                    </h1>
                    <p class="mb-0 opacity-90 fs-5">Selamat datang kembali di dashboard event Anda.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="index.php" class="btn btn-light text-primary fw-semibold px-4 py-2 shadow-sm">
                        <i class="bi bi-compass me-2"></i>Jelajahi Event
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-0">
            <!-- Stats Grid -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h2 class="fw-bold mb-1"><?= $totalEvents ?></h2>
                        <span class="text-muted fw-medium">Total Event Diikuti</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h2 class="fw-bold mb-1"><?= $upcomingCount ?></h2>
                        <span class="text-muted fw-medium">Event Mendatang</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <h2 class="fw-bold mb-1"><?= $completedEvents ?></h2>
                        <span class="text-muted fw-medium">Event Selesai</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="bi bi-activity"></i>
                        </div>
                        <h2 class="fw-bold mb-1">
                            <?= number_format(($completedEvents / max($totalEvents, 1)) * 100, 0) ?>%
                        </h2>
                        <span class="text-muted fw-medium">Tingkat Kehadiran</span>
                    </div>
                </div>
            </div>

            <?php
            // Helper functions for notifications
            if (!function_exists('getNotificationIcon')) {
                function getNotificationIcon($type)
                {
                    switch ($type) {
                        case 'reminder':
                            return 'bi-bell';
                        case 'confirmation':
                            return 'bi-check-circle';
                        case 'update':
                            return 'bi-info-circle';
                        case 'cancelled':
                            return 'bi-x-circle';
                        default:
                            return 'bi-bell';
                    }
                }
            }

            if (!function_exists('formatNotificationTime')) {
                function formatNotificationTime($datetime)
                {
                    $timestamp = strtotime($datetime);
                    $now = time();
                    $diff = $now - $timestamp;

                    if ($diff < 60)
                        return 'Baru saja';
                    elseif ($diff < 3600)
                        return floor($diff / 60) . ' menit yang lalu';
                    elseif ($diff < 86400)
                        return floor($diff / 3600) . ' jam yang lalu';
                    elseif ($diff < 604800)
                        return floor($diff / 86400) . ' hari yang lalu';
                    else
                        return date('d M Y H:i', $timestamp);
                }
            }
            ?>

            <!-- Chart and Events Section -->
            <div class="row g-4 mb-4">
                <!-- Activity Chart Section -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Aktivitas Bulanan</h5>
                        </div>
                        <div style="height: 300px;">
                            <?php if (!empty($chartLabels)): ?>
                                <canvas id="activityChart"></canvas>
                            <?php else: ?>
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="bi bi-bar-chart fs-1 mb-2 opacity-25"></i>
                                    <p class="mb-0">Belum ada data aktivitas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Category Pie Chart Section -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Distribusi Kategori</h5>
                        </div>
                        <div style="height: 300px;">
                            <?php if (!empty($pieLabels)): ?>
                                <canvas id="categoryChart"></canvas>
                            <?php else: ?>
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                    <i class="bi bi-pie-chart fs-1 mb-2 opacity-25"></i>
                                    <p class="mb-0">Belum ada data kategori</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="col-lg-12">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Event Mendatang</h5>
                            <a href="my-events.php" class="text-decoration-none text-primary fw-semibold small">Lihat
                                Semua</a>
                        </div>

                        <?php if (count($upcomingEvents) > 0): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <a href="event-detail.php?id=<?= $event['id'] ?>" class="text-decoration-none text-dark">
                                        <div class="event-item p-0 border-0">
                                            <div class="date-badge">
                                                <div class="day"><?= date('d', strtotime($event['tanggal'])) ?></div>
                                                <div class="month"><?= date('M', strtotime($event['tanggal'])) ?></div>
                                            </div>
                                            <div class="flex-grow-1 min-width-0">
                                                <h6 class="fw-bold mb-1 text-truncate">
                                                    <?= htmlspecialchars($event['nama'] ?? 'Event') ?>
                                                </h6>
                                                <div class="text-muted small text-truncate">
                                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($event['lokasi']) ?>
                                                </div>
                                            </div>
                                            <i class="bi bi-chevron-right text-muted ms-2"></i>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="bi bi-calendar-x fs-1 opacity-25"></i>
                                </div>
                                <p class="mb-3">Tidak ada event mendatang</p>
                                <a href="index.php" class="btn btn-primary btn-sm rounded-pill px-3">Cari Event</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Notifications and Activities Section -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Notifikasi Terbaru</h5>
                            <a href="notifications.php"
                                class="text-decoration-none text-primary fw-semibold small">Lihat Semua</a>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <?php
                            $recentNotifications = $notificationService->getUserNotifications($currentUser['id'], 3);
                            ?>
                            <?php if (!empty($recentNotifications)): ?>
                                <?php foreach ($recentNotifications as $notification): ?>
                                    <div class="d-flex align-items-start p-3 bg-light rounded-3 bg-opacity-50">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-white rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i
                                                    class="bi <?= getNotificationIcon($notification['type']) ?> text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="fw-bold mb-0 text-dark">
                                                    <?= htmlspecialchars($notification['title'] ?? 'Notifikasi') ?>
                                                </h6>
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge bg-danger rounded-pill flex-shrink-0 ms-2"
                                                        style="font-size: 0.6rem;">BARU</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-secondary small mb-1 lh-sm">
                                                <?= $notification['message'] ?? '' ?>
                                            </p>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i
                                                    class="bi bi-clock me-1"></i><?= formatNotificationTime($notification['created_at']) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash fs-1 opacity-25 mb-3"></i>
                                    <p class="mb-0">Tidak ada notifikasi baru</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Aktivitas Terbaru</h5>
                        </div>

                        <?php
                        $stmt = $db->prepare("
                            SELECT e.*, r.status, e.tanggal as event_date
                            FROM registrations r
                            JOIN events e ON r.event_id = e.id
                            WHERE r.user_id = ?
                            ORDER BY e.tanggal DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$currentUser['id']]);
                        $recentActivities = $stmt->fetchAll();
                        ?>

                        <?php if (!empty($recentActivities)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="border-0 ps-3 py-3 text-secondary text-uppercase small fw-bold">Event
                                            </th>
                                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Status
                                            </th>
                                            <th
                                                class="border-0 pe-3 py-3 text-end text-secondary text-uppercase small fw-bold">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <?php
                                            $statusBadge = '';
                                            if ($activity['status'] === 'confirmed')
                                                $statusBadge = '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Terdaftar</span>';
                                            elseif ($activity['status'] === 'pending')
                                                $statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Menunggu</span>';
                                            else
                                                $statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Dibatalkan</span>';
                                            ?>
                                            <tr>
                                                <td class="border-bottom-0 ps-3 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3 text-primary text-center"
                                                            style="min-width: 50px;">
                                                            <div class="fw-bold small">
                                                                <?= date('M', strtotime($activity['tanggal'])) ?>
                                                            </div>
                                                            <div class="h6 mb-0 fw-bold">
                                                                <?= date('d', strtotime($activity['tanggal'])) ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-0 text-dark">
                                                                <?= htmlspecialchars($activity['nama'] ?? 'Event') ?>
                                                            </h6>
                                                            <small class="text-muted"><i
                                                                    class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($activity['lokasi'] ?? '') ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="border-bottom-0 py-3"><?= $statusBadge ?></td>
                                                <td class="border-bottom-0 pe-3 py-3 text-end">
                                                    <a href="event-detail.php?id=<?= $activity['id'] ?>"
                                                        class="btn btn-sm btn-light rounded-circle" data-bs-toggle="tooltip"
                                                        title="Lihat Detail">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 opacity-25 mb-3"></i>
                                <p class="mb-3">Belum ada aktivitas</p>
                                <a href="index.php" class="btn btn-primary btn-sm rounded-pill px-3">Mulai Jelajahi</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div> <!-- End container-fluid -->
    </div> <!-- End main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize Chart when DOM is ready
        document.addEventListener('DOMContentLoaded', function () {
            // Enable Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Chart Defaults
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';

            // Activity Chart (Bar)
            const activityCanvas = document.getElementById('activityChart');
            if (activityCanvas && <?= !empty($chartLabels) ? 'true' : 'false' ?>) {
                const activityCtx = activityCanvas.getContext('2d');

                // Create Gradient
                const gradientBar = activityCtx.createLinearGradient(0, 0, 0, 300);
                gradientBar.addColorStop(0, '#4f46e5'); // Primary
                gradientBar.addColorStop(1, '#818cf8'); // Lighter purple

                new Chart(activityCtx, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [{
                            label: 'Jumlah Event',
                            data: <?= json_encode($chartValues) ?>,
                            backgroundColor: gradientBar,
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: 24,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#111827',
                                bodyColor: '#4b5563',
                                borderColor: '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: false,
                                callbacks: {
                                    label: function (context) {
                                        return context.parsed.y + ' Event';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    borderDash: [5, 5],
                                    drawBorder: false
                                },
                                ticks: {
                                    font: { size: 11 },
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // Category Chart (Doughnut)
            const categoryCanvas = document.getElementById('categoryChart');
            if (categoryCanvas && <?= !empty($pieLabels) ? 'true' : 'false' ?>) {
                const categoryCtx = categoryCanvas.getContext('2d');

                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?= json_encode($pieLabels) ?>,
                        datasets: [{
                            data: <?= json_encode($pieValues) ?>,
                            backgroundColor: [
                                '#4f46e5', // Indigo
                                '#0ea5e9', // Sky
                                '#10b981', // Emerald
                                '#f59e0b', // Amber
                                '#f43f5e', // Rose
                                '#8b5cf6'  // Violet
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%', // Thinner ring
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 20,
                                    font: { size: 11 }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#111827',
                                bodyColor: '#4b5563',
                                borderColor: '#e5e7eb',
                                borderWidth: 1,
                                padding: 12,
                                callbacks: {
                                    label: function (context) {
                                        let label = context.label || '';
                                        let value = context.parsed || 0;
                                        let total = context.chart._metasets[context.datasetIndex].total;
                                        let percentage = Math.round((value / total) * 100) + '%';
                                        return `${label}: ${value} (${percentage})`;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 1500,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>