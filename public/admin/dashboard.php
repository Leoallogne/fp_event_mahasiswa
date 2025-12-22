<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../analytics/AnalyticsService.php';

$auth = new Auth();
$auth->requireAdmin();

$analytics = new AnalyticsService();
$stats = $analytics->getEventStats();
$monthlyTrend = $analytics->trenJumlahEventBulanan(12);
$categoryStats = $analytics->hitungKategoriEventTerbanyakPeminat();
$avgParticipants = $analytics->hitungRataRataPesertaPerEvent();
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
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4e79a7, #2c5282);
            --success-gradient: linear-gradient(135deg, #00b09b, #96c93d);
            --info-gradient: linear-gradient(135deg, #11998e, #38ef7d);
            --warning-gradient: linear-gradient(135deg, #f2994a, #f2c94c);
            --bg-gradient: linear-gradient(135deg, #e0f7fa, #e0f2f1);
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Inter', sans-serif;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .stat-card {
            color: white;
            border: none;
        }

        .bg-gradient-primary {
            background: var(--primary-gradient);
        }

        .bg-gradient-success {
            background: var(--success-gradient);
        }

        .bg-gradient-info {
            background: var(--info-gradient);
        }

        .bg-gradient-warning {
            background: var(--warning-gradient);
        }

        .hero-banner {
            background: linear-gradient(135deg, #ffffff, #f0f7ff);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            color: #2c3e50;
            padding: 1rem 1.25rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <h2 class="fw-bold mb-2" style="color: #2c3e50;"><i class="bi bi-speedometer2"></i> Dashboard Overview
                </h2>
                <p class="text-muted mb-0">Ringkasan aktivitas dan performa sistem event management Anda.</p>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4 g-3">
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-primary h-100">
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
                    <div class="card stat-card bg-gradient-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Pendaftaran</h6>
                                <i class="bi bi-people fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['total_registrations'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0 opacity-75">Total User</h6>
                                <i class="bi bi-person fs-4 opacity-75"></i>
                            </div>
                            <h2 class="mb-0 fw-bold"><?= $stats['total_users'] ?? 0 ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-gradient-warning h-100">
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

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tren Event Bulanan</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Peserta per Kategori</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Stats -->
            <?php if ($avgParticipants): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
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