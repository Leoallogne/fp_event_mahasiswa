<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../analytics/AnalyticsService.php';

$auth = new Auth();
$auth->requireAdmin();

$analytics = new AnalyticsService();

// Handle CSV export - MUST BE BEFORE ANY HTML OUTPUT
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    
    if ($type === 'category') {
        $data = $analytics->hitungKategoriEventTerbanyakPeminat();
        // Format data dengan header yang jelas
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'Kategori' => $row['kategori'],
                'Total Peserta' => (int)$row['total_peserta'],
                'Total Event' => (int)$row['total_event']
            ];
        }
        $analytics->exportToCSV($formattedData, 'kategori_event_' . date('Y-m-d') . '.csv');
    } elseif ($type === 'monthly') {
        $data = $analytics->trenJumlahEventBulanan(12);
        // Format data dengan header yang jelas
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'Bulan' => $row['bulan'],
                'Jumlah Event' => (int)$row['jumlah_event'],
                'Total Peserta' => (int)$row['total_peserta']
            ];
        }
        $analytics->exportToCSV($formattedData, 'tren_bulanan_' . date('Y-m-d') . '.csv');
    } elseif ($type === 'events') {
        $analytics->exportEventsToCSV();
    } elseif ($type === 'registrations') {
        $analytics->exportRegistrationsToCSV();
    }
}

$categoryStats = $analytics->hitungKategoriEventTerbanyakPeminat();
$monthlyTrend = $analytics->trenJumlahEventBulanan(12);
$avgParticipants = $analytics->hitungRataRataPesertaPerEvent();
$recommendations = $analytics->rekomendasiEvent(null, 10);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analitik - Admin</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-graph-up"></i> Analitik & Laporan</h2>
            <div class="btn-group" role="group">
                <a href="?export=category" class="btn btn-success">
                    <i class="bi bi-download"></i> Export Kategori
                </a>
                <a href="?export=monthly" class="btn btn-success">
                    <i class="bi bi-download"></i> Export Tren Bulanan
                </a>
                <a href="?export=events" class="btn btn-info">
                    <i class="bi bi-download"></i> Export Event
                </a>
                <a href="?export=registrations" class="btn btn-info">
                    <i class="bi bi-download"></i> Export Peserta
                </a>
            </div>
        </div>

        <!-- Category Stats -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Kategori Event Terbanyak Peminat</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Total Peserta</th>
                                        <th>Total Event</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categoryStats)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                <i class="bi bi-info-circle"></i> Belum ada data kategori event
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categoryStats as $stat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($stat['kategori']) ?></td>
                                                <td><?= $stat['total_peserta'] ?></td>
                                                <td><?= $stat['total_event'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Stats -->
        <?php if ($avgParticipants): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Rata-rata Peserta per Event</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Rata-rata:</strong> <?= $avgParticipants['rata_rata'] ?> peserta/event</p>
                        <p><strong>Total Event:</strong> <?= $avgParticipants['total_event'] ?></p>
                        <p><strong>Total Peserta:</strong> <?= $avgParticipants['total_peserta'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Monthly Trend Chart -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Tren Jumlah Event Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Rekomendasi Event</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recommendations)): ?>
                            <p>Tidak ada rekomendasi event saat ini.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Kategori</th>
                                            <th>Tanggal</th>
                                            <th>Kuota Tersedia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recommendations as $rec): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($rec['title']) ?></td>
                                                <td><?= htmlspecialchars($rec['kategori']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($rec['tanggal'])) ?></td>
                                                <td><?= $rec['available_quota'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <script>
        const monthlyData = <?= json_encode($monthlyTrend) ?>;
        const monthlyLabels = monthlyData.map(item => item.bulan);
        const monthlyValues = monthlyData.map(item => parseInt(item.jumlah_event));

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Jumlah Event',
                    data: monthlyValues,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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

