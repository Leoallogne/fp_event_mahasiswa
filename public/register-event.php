<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/registrations/RegistrationService.php';
require_once __DIR__ . '/../modules/events/EventService.php';

$auth = new Auth();
$auth->requireUser();

$registrationService = new RegistrationService();
$currentUser = $auth->getCurrentUser();

$eventId = $_GET['id'] ?? 0;

$eventService = new EventService();
$event = $eventService->getEventById($eventId);

if (!$event) {
    header('Location: index.php');
    exit;
}

// Check if already registered
if ($registrationService->isRegistered($currentUser['id'], $eventId)) {
    header('Location: event-detail.php?id=' . $eventId . '&error=sudah_terdaftar');
    exit;
}

// Handle Confirmation Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $result = $registrationService->registerForEvent($currentUser['id'], $eventId);

    if ($result['success']) {
        if (!empty($event['price']) && $event['price'] > 0) {
            header('Location: payment.php?id=' . $eventId);
        } else {
            header('Location: event-detail.php?id=' . $eventId . '&success=1');
        }
    } else {
        header('Location: event-detail.php?id=' . $eventId . '&error=' . urlencode($result['message']));
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran - <?= htmlspecialchars($event['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/register-event.css?v=<?= time() ?>">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="confirmation-card">
                        <div class="card-header-gradient">
                            <div class="event-icon-wrapper">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Konfirmasi Pendaftaran</h2>
                            <p class="opacity-90 mb-0">Hampir selesai! Silakan tinjau detail event di bawah ini.</p>
                        </div>

                        <div class="p-4">
                            <div class="detail-item">
                                <div class="detail-icon"><i class="bi bi-bookmark-star"></i></div>
                                <div>
                                    <div class="detail-label">Nama Event</div>
                                    <div class="detail-value"><?= htmlspecialchars($event['title']) ?></div>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon"><i class="bi bi-calendar3"></i></div>
                                <div>
                                    <div class="detail-label">Waktu & Tanggal</div>
                                    <div class="detail-value"><?= date('d F Y', strtotime($event['tanggal'])) ?> pukul
                                        <?= date('H:i', strtotime($event['tanggal'])) ?> WIB
                                    </div>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <div class="detail-label">Lokasi</div>
                                    <div class="detail-value"><?= htmlspecialchars($event['lokasi']) ?></div>
                                </div>
                            </div>

                            <div class="detail-item justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="detail-icon"><i class="bi bi-cash-stack"></i></div>
                                    <div>
                                        <div class="detail-label">Biaya Pendaftaran</div>
                                        <div class="detail-value">Total Pembayaran</div>
                                    </div>
                                </div>
                                <div class="price-tag">
                                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                        Rp <?= number_format($event['price'], 0, ',', '.') ?>
                                    <?php else: ?>
                                        GRATIS
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div
                                class="alert alert-warning border-0 bg-warning bg-opacity-10 mt-4 d-flex align-items-start">
                                <i class="bi bi-info-circle-fill me-3 mt-1 fs-5 text-warning"></i>
                                <div>
                                    <span class="fw-semibold d-block mb-1 text-dark">Informasi Penting</span>
                                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                        <small class="text-secondary">Ini adalah event berbayar. Setelah Anda klik
                                            konfirmasi, Anda akan diarahkan ke halaman pembayaran untuk instruksi lebih
                                            lanjut.</small>
                                    <?php else: ?>
                                        <small class="text-secondary">Dengan klik konfirmasi, Anda secara resmi mendaftar
                                            untuk event ini. Harap hadir tepat waktu sesuai jadwal yang tertera.</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <form method="POST" class="mt-4">
                                <div class="row g-3">
                                    <div class="col-8">
                                        <button type="submit" name="confirm" value="1"
                                            class="btn btn-primary btn-confirm w-100 text-white shadow-sm">
                                            <i class="bi bi-check-circle me-2"></i> Konfirmasi & Lanjutkan
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <a href="event-detail.php?id=<?= $eventId ?>"
                                            class="btn btn-light h-100 w-100 d-flex align-items-center justify-content-center border"
                                            style="border-radius: 12px; font-weight: 500;">
                                            Batal
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>