<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/analytics/AnalyticsService.php';

$auth = new Auth();
$auth->requireAdmin();

$analytics = new AnalyticsService();
$stats = $analytics->getEventStats();
$monthlyTrend = $analytics->trenJumlahEventBulanan(12);
$categoryStats = $analytics->hitungKategoriEventTerbanyakPeminat();
$avgParticipants = $analytics->hitungRataRataPesertaPerEvent();
$userTrend = $analytics->getUserRegistrationTrend(6); // New: User registration trend
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-modern.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Hero Banner -->
            <div class="hero-banner glass-card mb-4 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-1" style="color: var(--text-primary);"><i
                                class="bi bi-rhombus-fill text-primary me-2"></i>Dashboard</h2>
                        <p class="text-secondary mb-0">Ringkasan aktivitas dan performa sistem.</p>
                    </div>
                    <div>
                        <a href="export-csv.php" class="btn btn-success rounded-pill px-4 shadow-sm">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export Laporan
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mb-4 g-3">
                <div class="col-md">
                    <div class="card stat-card h-100" style="background: var(--primary-gradient);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Total Event</h6>
                                <i class="bi bi-calendar-event fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['total_events'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card stat-card h-100"
                        style="background: linear-gradient(135deg, #00b74a 0%, #00a846 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Pendaftaran</h6>
                                <i class="bi bi-people fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['total_registrations'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card stat-card h-100" style="background: linear-gradient(135deg, #f2994a, #f2c94c);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Estimasi Pendapatan</h6>
                                <i class="bi bi-cash-coin fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card stat-card h-100" style="background: var(--info-gradient);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Total User</h6>
                                <i class="bi bi-person fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['total_users'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card stat-card h-100" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Event Mendatang</h6>
                                <i class="bi bi-calendar-check fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['upcoming_events'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row - 3 Charts -->
            <div class="row mb-4 g-3">
                <!-- Chart 1: User Registration Trend -->
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Tren Pendaftaran User</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="userTrendChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart 2: Monthly Event Trend -->
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Tren Event Bulanan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyTrendChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart 3: Category Participation -->
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Peserta per Kategori</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Stats -->
            <?php if ($avgParticipants): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="glass-card">
                            <div class="card-body">
                                <h5>Statistik Rata-rata</h5>
                                <p>Rata-rata peserta per event: <strong><?= $avgParticipants['rata_rata'] ?? 0 ?></strong>
                                </p>
                                <p>Total event: <strong><?= $avgParticipants['total_event'] ?? 0 ?></strong></p>
                                <p>Total peserta: <strong><?= $avgParticipants['total_peserta'] ?? 0 ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // User Registration Trend Chart
        const userTrendData = <?= json_encode($userTrend) ?>;
        const userLabels = userTrendData.map(item => item.month);
        const userValues = userTrendData.map(item => parseInt(item.user_count));

        new Chart(document.getElementById('userTrendChart'), {
            type: 'line',
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'Pendaftaran User',
                    data: userValues,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        // Monthly Trend Chart
        const monthlyData = <?= json_encode($monthlyTrend) ?>;
        const monthlyLabels = monthlyData.map(item => item.bulan);
        const monthlyValues = monthlyData.map(item => parseInt(item.jumlah_event));

        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Jumlah Event',
                    data: monthlyValues,
                    borderColor: '#4e79a7',
                    backgroundColor: 'rgba(78, 121, 167, 0.2)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4e79a7',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { font: { family: 'Inter', size: 12 } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(44, 62, 80, 0.9)',
                        padding: 10,
                        titleFont: { family: 'Inter', size: 13 },
                        bodyFont: { family: 'Inter', size: 12 },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f0f0f0' },
                        ticks: { font: { family: 'Inter' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter' } }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Category Chart
        const categoryData = <?= json_encode($categoryStats) ?>;
        const categoryLabels = categoryData.map(item => item.kategori);
        const categoryValues = categoryData.map(item => parseInt(item.total_peserta));

        const palette = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ac'];

        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Total Peserta',
                    data: categoryValues,
                    backgroundColor: palette,
                    borderColor: 'transparent',
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(44, 62, 80, 0.9)',
                        titleFont: { family: 'Inter' },
                        bodyFont: { family: 'Inter' },
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f0f0f0' },
                        ticks: { font: { family: 'Inter' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter' } }
                    }
                },
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>

</html>