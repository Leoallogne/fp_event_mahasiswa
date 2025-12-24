<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/events/EventService.php';
require_once __DIR__ . '/../../modules/registrations/RegistrationService.php';

$auth = new Auth();
$auth->requireAdmin();

$eventService = new EventService();
$registrationService = new RegistrationService();

$eventId = $_GET['id'] ?? 0;
$event = $eventService->getEventById($eventId);

if (!$event) {
    header('Location: event-participants.php');
    exit;
}

$participants = $registrationService->getEventRegistrations($eventId);

// Handle verification action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = $_POST['user_id'];
    $action = $_POST['action'];
    $status = ($action === 'verify') ? 'confirmed' : 'rejected';

    if ($registrationService->verifyPayment($userId, $eventId, $status)) {
        header("Location: event-participants-detail.php?id=$eventId&success=1");
    } else {
        header("Location: event-participants-detail.php?id=$eventId&error=1");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .proof-img {
            max-width: 100px;
            cursor: pointer;
            border-radius: 4px;
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
            <h2 class="mb-4">
                <i class="bi bi-people"></i> Daftar Peserta: <?= htmlspecialchars($event['title']) ?>
            </h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Status peserta berhasil diperbarui.</div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0"><strong>Total Peserta:</strong> <?= count($participants) ?> /
                            <?= $event['kuota'] ?></p>
                        <a href="event-participants.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if (empty($participants)): ?>
                        <div class="alert alert-info">
                            Belum ada peserta yang terdaftar pada event ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama & Email</th>
                                        <th>Waktu Daftar</th>
                                        <th>Status</th>
                                        <th>Bukti Bayar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participants as $index => $participant): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($participant['nama']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($participant['email']) ?></small>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($participant['daftar_waktu'])) ?></td>
                                            <td>
                                                <?php if ($participant['status'] === 'confirmed'): ?>
                                                    <span class="badge bg-success">Terkonfirmasi</span>
                                                <?php elseif ($participant['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><?= ucfirst($participant['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($participant['payment_proof']): ?>
                                                    <a href="../uploads/payments/<?= $participant['payment_proof'] ?>"
                                                        target="_blank">
                                                        <?php
                                                        $ext = pathinfo($participant['payment_proof'], PATHINFO_EXTENSION);
                                                        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])):
                                                            ?>
                                                            <img src="../uploads/payments/<?= $participant['payment_proof'] ?>"
                                                                class="proof-img border shadow-sm">
                                                        <?php else: ?>
                                                            <span class="badge bg-info"><i class="bi bi-file-earmark-pdf"></i> Lihat
                                                                PDF</span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($participant['status'] === 'pending'): ?>
                                                    <form action="" method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $participant['user_id'] ?>">
                                                        <button type="submit" name="action" value="verify"
                                                            class="btn btn-sm btn-success"
                                                            onclick="return confirm('Konfirmasi pendaftaran peserta ini?')">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button type="submit" name="action" value="reject"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Tolak pendaftaran peserta ini?')">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
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