<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/analytics/AnalyticsService.php';

$auth = new Auth();
$auth->requireAdmin();

$analytics = new AnalyticsService();
$stats = $analytics->getEventStats();
$eventParticipation = $analytics->getEventParticipationStats(10);
$revenueByEvent = $analytics->getRevenueByEvent(10);
$revenueTrend = $analytics->getRevenueTrend(6);
$avgParticipants = $analytics->hitungRataRataPesertaPerEvent();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik & Laporan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-modern.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Hero Banner -->
            <div class="hero-banner mb-4">
                <h2 class="fw-bold mb-2"><i class="bi bi-graph-up me-2"></i>Analitik & Laporan</h2>
                <p class="mb-0 opacity-75">Analisis mendalam aktivitas event, partisipasi, dan pendapatan</p>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4 g-3">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <div class="card stat-card h-100" style="background: linear-gradient(135deg, #00b74a, #00a846);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Total Peserta</h6>
                                <i class="bi bi-people fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['total_registrations'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card h-100" style="background: linear-gradient(135deg, #f2994a, #f2c94c);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Total Pendapatan</h6>
                                <i class="bi bi-cash-coin fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card h-100" style="background: var(--info-gradient);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Rata-rata Peserta</h6>
                                <i class="bi bi-graph-up fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= number_format($avgParticipants['rata_rata'] ?? 0, 1) ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4 g-3">
                <!-- Chart 1: Event Participation -->
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Partisipasi Event</h5>
                            <small class="text-muted">Top 10 event berdasarkan jumlah peserta</small>
                        </div>
                        <div class="card-body">
                            <canvas id="participationChart" style="max-height: 350px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart 2: Revenue by Event -->
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Pendapatan per Event</h5>
                            <small class="text-muted">Top 10 event berbayar berdasarkan revenue</small>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueByEventChart" style="max-height: 350px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart 3: Revenue Trend -->
                <div class="col-lg-4">
                    <div class="glass-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Tren Pendapatan</h5>
                            <small class="text-muted">Pendapatan 6 bulan terakhir</small>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueTrendChart" style="max-height: 350px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="glass-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export Data</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Download laporan lengkap registrasi dalam format CSV</p>
                    <a href="export-csv.php" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Download CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart 1: Event Participation
        const participationData = <?= json_encode($eventParticipation) ?>;
        const participationLabels = participationData.map(item => {
            // Truncate long titles
            return item.title.length > 15 ? item.title.substring(0, 15) + '...' : item.title;
        });
        const participationValues = participationData.map(item => parseInt(item.participant_count));

        new Chart(document.getElementById('participationChart'), {
            type: 'bar',
            data: {
                labels: participationLabels,
                datasets: [{
                    label: 'Jumlah Peserta',
                    data: participationValues,
                    backgroundColor: 'rgba(67, 97, 238, 0.7)',
                    borderColor: '#4361ee',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        // Chart 2: Revenue by Event
        const revenueData = <?= json_encode($revenueByEvent) ?>;
        const revenueLabels = revenueData.map(item => {
            return item.title.length > 15 ? item.title.substring(0, 15) + '...' : item.title;
        });
        const revenueValues = revenueData.map(item => parseInt(item.total_revenue));

        new Chart(document.getElementById('revenueByEventChart'), {
            type: 'bar',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: revenueValues,
                    backgroundColor: 'rgba(242, 153, 74, 0.7)',
                    borderColor: '#f2994a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Rp ' + context.parsed.x.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + (value / 1000) + 'K';
                            }
                        }
                    }
                }
            }
        });

        // Chart 3: Revenue Trend
        const trendData = <?= json_encode($revenueTrend) ?>;
        const trendLabels = trendData.map(item => item.month);
        const trendValues = trendData.map(item => parseInt(item.monthly_revenue));

        new Chart(document.getElementById('revenueTrendChart'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pendapatan Bulanan',
                    data: trendValues,
                    borderColor: '#00b74a',
                    backgroundColor: 'rgba(0, 183, 74, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#00b74a',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'Rp ' + (value / 1000) + 'K';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>