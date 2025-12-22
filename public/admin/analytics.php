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
                'Total Peserta' => (int) $row['total_peserta'],
                'Total Event' => (int) $row['total_event']
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
                'Jumlah Event' => (int) $row['jumlah_event'],
                'Total Peserta' => (int) $row['total_peserta']
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e79a7;
            --secondary-color: #f28e2b;
            --bg-gradient: linear-gradient(135deg, #e0f7fa, #e0f2f1);
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Inter', sans-serif;
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background: transparent;
            font-weight: 600;
            color: #2c3e50;
        }

        .card-body {
            color: #34495e;
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

        /* Custom UI */
        .btn-modern {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-modern-primary {
            background: linear-gradient(135deg, var(--primary-color), #2c5282);
            color: white;
        }

        .btn-modern-success {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
        }

        .btn-modern-info {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }

        .table-custom th {
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid #f0f2f5;
        }

        .table-custom td {
            vertical-align: middle;
            color: #2c3e50;
        }

        .table-custom tr:hover td {
            background-color: rgba(240, 242, 245, 0.4);
        }

        .hero-banner {
            background: linear-gradient(135deg, #ffffff, #f0f7ff);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
    </style>

</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Hero Banner -->
            <div class="p-4 mb-4 rounded-3 shadow-sm hero-banner">
                <h1 class="display-5 fw-bold" style="color: #2c3e50;">Dashboard Analitik</h1>
                <p class="lead mb-0" style="color: #34495e;">Ringkasan performa event, tren bulanan, dan rekomendasi
                    terbaru.</p>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-graph-up"></i> Analitik & Laporan</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="?export=category" class="btn-modern btn-modern-success text-decoration-none">
                        <i class="bi bi-download"></i> Kategori
                    </a>
                    <a href="?export=monthly" class="btn-modern btn-modern-success text-decoration-none">
                        <i class="bi bi-download"></i> Tren Bulanan
                    </a>
                    <a href="?export=events" class="btn-modern btn-modern-info text-decoration-none">
                        <i class="bi bi-download"></i> Event
                    </a>
                    <a href="?export=registrations" class="btn-modern btn-modern-info text-decoration-none">
                        <i class="bi bi-download"></i> Peserta
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
                                <table class="table table-custom table-borderless align-middle">
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
                                    <table class="table table-custom table-borderless align-middle">
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