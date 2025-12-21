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
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h2>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-calendar-event"></i> Total Event</h5>
                        <h2><?= $stats['total_events'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> Total Pendaftaran</h5>
                        <h2><?= $stats['total_registrations'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person"></i> Total User</h5>
                        <h2><?= $stats['total_users'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-calendar-check"></i> Event Mendatang</h5>
                        <h2><?= $stats['upcoming_events'] ?? 0 ?></h2>
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
                        <p>Rata-rata peserta per event: <strong><?= $avgParticipants['rata_rata'] ?? 0 ?></strong></p>
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
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Chart
        const categoryData = <?= json_encode($categoryStats) ?>;
        const categoryLabels = categoryData.map(item => item.kategori);
        const categoryValues = categoryData.map(item => parseInt(item.total_peserta));

        new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Total Peserta',
                    data: categoryValues,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
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

