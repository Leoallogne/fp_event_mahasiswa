<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';
require_once __DIR__ . '/../modules/notifications/NotificationService.php';

$auth = new Auth();
$auth->requireUser();

// Redirect admin ke dashboard admin
if ($auth->isAdmin()) {
    header('Location: /admin/dashboard.php');
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
    $chartValues[] = (int)$data['jumlah'];
}

$chartLabels = array_reverse($chartLabels);
$chartValues = array_reverse($chartValues);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - EventKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f7b731;
            --danger: #ee5a24;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Main Content - Compatibility with Sidebar */
        .main-content {
            margin-left: 260px;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            background-color: #f5f7fb;
        }

        .main-content.sidebar-collapsed {
            margin-left: 70px;
        }

        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .dashboard-header h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .dashboard-header p {
            opacity: 0.9;
            margin: 0;
            font-size: 1rem;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.75rem 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
            border-radius: 50%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .stat-card.primary { border-color: var(--primary); }
        .stat-card.success { border-color: var(--success); }
        .stat-card.info { border-color: var(--info); }
        .stat-card.warning { border-color: var(--warning); }

        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.8;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-card .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Chart Card */
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            height: 100%;
        }

        .chart-card .card-header {
            background: transparent;
            border: none;
            padding: 0 0 1rem 0;
            margin-bottom: 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .chart-card .card-header h3 {
            color: var(--dark);
            font-weight: 600;
            margin: 0;
            font-size: 1.2rem;
        }

        /* Event Cards */
        .event-card {
            background: white;
            border-radius: 8px;
            padding: 1.25rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            border-left: 3px solid var(--primary);
        }

        .event-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .event-date {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            text-align: center;
            min-width: 70px;
        }

        .event-date .day {
            font-size: 1.5rem;
            font-weight: bold;
            line-height: 1;
        }

        .event-date .month {
            font-size: 0.75rem;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .event-info h5 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 0.4rem;
            font-size: 1rem;
        }

        .event-info .event-meta {
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0 !important;
                padding-top: 80px;
            }
            
            .dashboard-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
            }
            
            .dashboard-header p {
                font-size: 0.9rem;
            }
            
            .dashboard-header .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .stat-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
                min-height: 120px;
            }
            
            .stat-card .stat-number {
                font-size: 2rem;
            }
            
            .stat-card .stat-label {
                font-size: 0.85rem;
            }
            
            .chart-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .chart-card .card-header h3 {
                font-size: 1.1rem;
            }
            
            .event-card {
                padding: 1rem !important;
            }
            
            .event-date {
                min-width: 50px !important;
            }
            
            .event-date .day {
                font-size: 1.2rem;
            }
            
            .event-date .month {
                font-size: 0.8rem;
            }
            
            .event-info h5 {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }
            
            .event-meta {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 767.98px) {
            .dashboard-header .row {
                text-align: center;
            }
            
            .dashboard-header .col-md-4 {
                text-align: center !important;
                margin-top: 1rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.6rem;
            }
            
            .stat-card {
                min-height: 100px;
                padding: 1.2rem;
            }
            
            .stat-card i {
                font-size: 2.5rem;
            }
            
            .stat-card .stat-number {
                font-size: 1.8rem;
            }
            
            .chart-card .btn-group {
                flex-direction: column;
                width: 100%;
            }
            
            .chart-card .btn-group .btn {
                margin-bottom: 0.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .dashboard-header {
                padding: 1rem 0;
                margin-bottom: 1rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .dashboard-header p {
                font-size: 0.85rem;
            }
            
            .container-fluid {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            .row.g-3 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }
            
            .stat-card {
                padding: 1rem;
                min-height: 90px;
            }
            
            .stat-card i {
                font-size: 2rem;
            }
            
            .stat-card .stat-number {
                font-size: 1.6rem;
            }
            
            .stat-card .stat-label {
                font-size: 0.75rem;
            }
            
            .chart-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .chart-card .card-header {
                padding-bottom: 0.75rem;
            }
            
            .chart-card .card-header h3 {
                font-size: 1rem;
            }
            
            .event-card {
                padding: 0.75rem !important;
            }
            
            .event-date {
                min-width: 45px !important;
            }
            
            .event-date .day {
                font-size: 1rem;
            }
            
            .event-date .month {
                font-size: 0.7rem;
            }
            
            .event-info h5 {
                font-size: 0.9rem;
            }
            
            .event-meta {
                font-size: 0.8rem;
            }
            
            .table {
                font-size: 0.85rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
            }
            
            .badge {
                font-size: 0.75rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    /* Notification Items */
        .notification-item {
            transition: background-color 0.2s ease;
        }
        
        .notification-item:hover {
            background-color: rgba(67, 97, 238, 0.05) !important;
        }
        
        .notification-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    
    <?php
    // Helper functions for notifications
    function getNotificationIcon($type) {
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

    function formatNotificationTime($datetime) {
        $timestamp = strtotime($datetime);
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' menit yang lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari yang lalu';
        } else {
            return date('d M Y H:i', $timestamp);
        }
    }
    ?>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1>Selamat Datang, <?= htmlspecialchars($currentUser['nama'] ?? 'User') ?>!</h1>
                        <p>Kelola event Anda dan pantau aktivitas terbaru</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="index.php" class="btn btn-light btn-lg">
                            <i class="bi bi-search"></i> Jelajahi Event
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-3">
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card primary">
                        <i class="bi bi-calendar-event text-primary"></i>
                        <div class="stat-number"><?= $totalEvents ?></div>
                        <div class="stat-label">Total Event Diikuti</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card success">
                        <i class="bi bi-calendar-check text-success"></i>
                        <div class="stat-number"><?= $upcomingCount ?></div>
                        <div class="stat-label">Event Mendatang</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card info">
                        <i class="bi bi-check-circle text-info"></i>
                        <div class="stat-number"><?= $completedEvents ?></div>
                        <div class="stat-label">Event Selesai</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card warning">
                        <i class="bi bi-graph-up text-warning"></i>
                        <div class="stat-number"><?= number_format(($completedEvents / max($totalEvents, 1)) * 100, 0) ?>%</div>
                        <div class="stat-label">Tingkat Kehadiran</div>
                    </div>
                </div>
            </div>

                <!-- Chart and Events Section -->
            <div class="row g-3 mb-4">
                <!-- Chart Section -->
                <div class="col-lg-8">
                    <div class="chart-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Aktivitas Event Saya</h3>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary">Minggu Ini</button>
                                <button class="btn btn-outline-secondary active">Bulan Ini</button>
                                <button class="btn btn-outline-secondary">Tahun Ini</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($chartLabels)): ?>
                                <canvas id="activityChart" height="120"></canvas>
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-graph-up"></i>
                                    <p class="mb-0">Belum ada data aktivitas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="col-lg-4">
                    <div class="chart-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Event Mendatang</h3>
                            <a href="my-events.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($upcomingEvents) > 0): ?>
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <a href="event-detail.php?id=<?= $event['id'] ?>" class="text-decoration-none text-dark">
                                        <div class="d-flex align-items-center p-3 event-card">
                                            <div class="event-date me-3">
                                                <div class="day"><?= date('d', strtotime($event['tanggal'])) ?></div>
                                                <div class="month"><?= date('M', strtotime($event['tanggal'])) ?></div>
                                            </div>
                                            <div class="event-info flex-grow-1">
                                                <h5 class="mb-1"><?= htmlspecialchars($event['nama'] ?? 'Event Tidak Diketahui') ?></h5>
                                                <div class="event-meta">
                                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['lokasi'] ?? 'Lokasi Tidak Diketahui') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-calendar-x"></i>
                                    <p class="mb-0">Tidak ada event mendatang</p>
                                    <a href="index.php" class="btn btn-primary btn-sm mt-2">Cari Event</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Notifications Section -->
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Notifikasi Terbaru</h3>
                            <a href="notifications.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $recentNotifications = $notificationService->getUserNotifications($currentUser['id'], 3);
                            ?>
                            <?php if (!empty($recentNotifications)): ?>
                                <?php foreach ($recentNotifications as $notification): ?>
                                    <div class="notification-item p-3 border-bottom <?= !$notification['is_read'] ? 'bg-light' : '' ?>">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon me-3">
                                                <i class="bi <?= getNotificationIcon($notification['type']) ?> text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($notification['title'] ?? 'Notifikasi') ?></h6>
                                                <p class="mb-1 small text-muted"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                                                <small class="text-muted"><?= formatNotificationTime($notification['created_at']) ?></small>
                                            </div>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary rounded-pill">Baru</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-bell-slash"></i>
                                    <p class="mb-0">Tidak ada notifikasi</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="card-header">
                            <h3>Aktivitas Terbaru</h3>
                        </div>
                        <div class="card-body p-0">
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
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Event</th>
                                                <th>Tanggal</th>
                                                <th>Lokasi</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentActivities as $activity): ?>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                if ($activity['status'] === 'confirmed') {
                                                    $statusClass = 'success';
                                                    $statusText = 'Dikonfirmasi';
                                                } elseif ($activity['status'] === 'pending') {
                                                    $statusClass = 'warning';
                                                    $statusText = 'Menunggu';
                                                } else {
                                                    $statusClass = 'danger';
                                                    $statusText = 'Dibatalkan';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="event-date me-3" style="min-width: 60px;">
                                                                <div class="day"><?= date('d', strtotime($activity['tanggal'])) ?></div>
                                                                <div class="month"><?= date('M', strtotime($activity['tanggal'])) ?></div>
                                                            </div>
                                                            <div>
                                                                <strong><?= htmlspecialchars($activity['nama'] ?? 'Event Tidak Diketahui') ?></strong>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= date('d M Y', strtotime($activity['tanggal'])) ?></td>
                                                    <td>
                                                        <i class="bi bi-geo-alt text-muted me-1"></i>
                                                        <?= htmlspecialchars($activity['lokasi'] ?? 'Lokasi Tidak Diketahui') ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $statusClass ?> rounded-pill">
                                                            <?= $statusText ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="event-detail.php?id=<?= $activity['id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="bi bi-clock-history"></i>
                                    <p class="mb-0">Belum ada aktivitas</p>
                                    <a href="index.php" class="btn btn-primary btn-sm mt-2">Mulai Jelajahi Event</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End container-fluid -->
    </div> <!-- End main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize Chart when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Activity Chart
            const chartCanvas = document.getElementById('activityChart');
            if (chartCanvas && <?= !empty($chartLabels) ? 'true' : 'false' ?>) {
                const ctx = chartCanvas.getContext('2d');
                const activityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [{
                            label: 'Jumlah Event',
                            data: <?= json_encode($chartValues) ?>,
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67, 97, 238, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#4361ee',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    precision: 0,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }

            // Animate stat cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeIn 0.6s ease-out forwards';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.stat-card').forEach(card => {
                observer.observe(card);
            });
        });

        // Add fade in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>