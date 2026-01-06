<?php
error_reporting(E_ALL);
// ini_set('display_errors', 1);


require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/events/EventService.php';
require_once __DIR__ . '/../../modules/registrations/RegistrationService.php';

$auth = new Auth();
$auth->requireAdmin();

$eventService = new EventService();
$registrationService = new RegistrationService();

// Get all events with participant counts
$events = $eventService->getAllEvents();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peserta Event - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/responsive.css?v=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-modern.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 fw-bold">
                <i class="bi bi-people me-2"></i>Daftar Peserta Event
            </h2>

            <div class="glass-card">
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <div class="alert alert-info">
                            Belum ada event yang tersedia.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">No</th>
                                        <th>Nama Event</th>
                                        <th>Tanggal</th>
                                        <th>Lokasi</th>
                                        <th>Kuota</th>
                                        <th>Peserta Terdaftar</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $index => $event): ?>
                                        <?php
                                        $participantCount = $event['registered_count'] ?? 0;
                                        $quota = $event['kuota'] ?? 0;
                                        $isFull = $participantCount >= $quota;
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($event['title']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($event['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($event['lokasi']) ?></td>
                                            <td><?= $quota ?></td>
                                            <td>
                                                <span class="badge <?= $isFull ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= $participantCount ?> / <?= $quota ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($isFull): ?>
                                                    <span class="badge bg-danger">Penuh</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="event-participants-detail.php?id=<?= $event['id'] ?>"
                                                    class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> Lihat Peserta
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>