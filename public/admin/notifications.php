<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/events/EventService.php';
require_once __DIR__ . '/../../modules/notifications/NotificationService.php';

$auth = new Auth();
$auth->requireAdmin();

$eventService = new EventService();
$notificationService = new NotificationService();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send Reminder
    if (isset($_POST['send_reminder'])) {
        $eventId = $_POST['event_id'] ?? 0;
        $result = $notificationService->sendEventReminder($eventId);
        $message = $result['success'] ? "Reminder berhasil dikirim ke {$result['sent']} peserta" : $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }

    // Send Custom Message
    if (isset($_POST['send_custom'])) {
        $eventId = $_POST['event_id'] ?? 0;
        $customMessage = trim($_POST['custom_message'] ?? '');

        if (empty($customMessage)) {
            $message = "Pesan notifikasi tidak boleh kosong";
            $messageType = 'danger';
        } else {
            if ($eventId === 'all') {
                $result = $notificationService->createGlobalNotification($customMessage, true);
            } else {
                $result = $notificationService->createEventUpdateNotification($eventId, $customMessage);
            }
            $message = $result['success'] ? $result['message'] : $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        }
    }

    // Schedule Reminder (Simulation)
    if (isset($_POST['schedule_reminder'])) {
        $time = $_POST['schedule_time'] ?? '';
        $message = "Reminder berhasil dijadwalkan untuk " . date('d/m/Y H:i', strtotime($time));
        $messageType = 'success';
    }

    // Test Email
    if (isset($_POST['test_email'])) {
        $currentUser = $auth->getCurrentUser();
        $testEmail = $currentUser['email'] ?? 'admin@example.com';
        $result = $notificationService->sendEmail(
            $testEmail,
            $currentUser['nama'] ?? 'Admin',
            'Test Email System - EventKu',
            '<h3>Sistem Email Berfungsi!</h3><p>Ini adalah email percobaan untuk memverifikasi fitur email notifikasi.</p>'
        );
        $message = $result ? "Email test berhasil dikirim ke $testEmail" : "Email test gagal dikirim. Periksa konfigurasi SMTP.";
        $messageType = $result ? 'success' : 'danger';
    }

    // Resend
    if (isset($_POST['resend_notification'])) {
        $notificationId = $_POST['resend_notification'];
        $notif = $notificationService->getNotificationById($notificationId);
        if ($notif) {
            $emailSent = $notificationService->sendEmail(
                $notif['email'],
                $notif['nama'],
                "Resend: " . ($notif['event_title'] ?? 'Notification'),
                $notif['message']
            );
            $message = $emailSent ? "Notifikasi berhasil dikirim ulang" : "Gagal mengirim ulang notifikasi";
            $messageType = $emailSent ? 'success' : 'danger';
        } else {
            $message = "Notifikasi tidak ditemukan";
            $messageType = 'danger';
        }
    }

    // Delete
    if (isset($_POST['delete_notification'])) {
        $notificationId = $_POST['delete_notification'];
        $result = $notificationService->deleteNotification($notificationId);
        $message = $result ? "Notifikasi berhasil dihapus" : "Gagal menghapus notifikasi";
        $messageType = $result ? 'success' : 'danger';
    }
}

$events = $eventService->getAllEvents();
$notifications = $notificationService->getNotifications();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Center - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/responsive.css?v=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-modern.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.2);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            background-size: 100px 100px;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(-100px, -100px);
            }
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: white;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-gradient {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
            color: white;
        }

        .notification-item {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.sent {
            border-left-color: #10b981;
        }

        .notification-item.pending {
            border-left-color: #f59e0b;
        }

        .notification-item.failed {
            border-left-color: #ef4444;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .avatar-initial {
            width: 35px;
            height: 35px;
            background: #e0e7ff;
            color: #4361ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="page-header">
                <div class="position-relative z-1">
                    <h1 class="fw-bold mb-2"><i class="bi bi-bell-fill me-3"></i>Notification Center</h1>
                    <p class="mb-0 opacity-75 fs-5">Kelola komunikasi dan notifikasi sistem secara terpusat.</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show glass-card border-0 shadow-sm mb-4"
                    role="alert">
                    <div class="d-flex align-items-center">
                        <i
                            class="bi bi-<?= $messageType == 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill fs-4 me-3"></i>
                        <div><?= $message ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card h-100 bg-success bg-gradient">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-2">Terkirim</h6>
                                    <h2 class="display-5 fw-bold mb-0">
                                        <?= count(array_filter($notifications, fn($n) => $n['status'] === 'sent')) ?>
                                    </h2>
                                </div>
                                <i class="bi bi-check2-circle display-4 opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card h-100 bg-warning bg-gradient">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-2">Pending</h6>
                                    <h2 class="display-5 fw-bold mb-0">
                                        <?= count(array_filter($notifications, fn($n) => $n['status'] === 'pending')) ?>
                                    </h2>
                                </div>
                                <i class="bi bi-hourglass-split display-4 opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card h-100 bg-primary bg-gradient">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-2">Total Event</h6>
                                    <h2 class="display-5 fw-bold mb-0"><?= count($events) ?></h2>
                                </div>
                                <i class="bi bi-calendar-check display-4 opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Actions -->
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4"><i
                            class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <button class="btn btn-gradient w-100" data-bs-toggle="modal" data-bs-target="#customModal">
                                <i class="bi bi-envelope-plus me-2"></i>Kirim Pesan
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-light w-100 border py-2 fw-semibold" data-bs-toggle="modal"
                                data-bs-target="#reminderModal">
                                <i class="bi bi-bell me-2"></i>Event Reminder
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-light w-100 border py-2 fw-semibold" data-bs-toggle="modal"
                                data-bs-target="#scheduleModal">
                                <i class="bi bi-calendar-plus me-2"></i>Jadwal
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-light w-100 border py-2 fw-semibold" data-bs-toggle="modal"
                                data-bs-target="#testEmailModal">
                                <i class="bi bi-envelope-check me-2"></i>Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Table -->
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-list-task me-2"></i>Riwayat Notifikasi</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary text-uppercase fs-7 fw-bold">User</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7 fw-bold">Pesan</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7 fw-bold">Event</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7 fw-bold">Status</th>
                                    <th class="py-3 text-secondary text-uppercase fs-7 fw-bold">Waktu</th>
                                    <th class="pe-4 py-3 text-end text-secondary text-uppercase fs-7 fw-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($notifications)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat notifikasi.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($notifications, 0, 15) as $notif): ?>
                                        <tr class="notification-item <?= $notif['status'] ?>">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-initial me-3">
                                                        <?= strtoupper(substr($notif['user_name'] ?? 'S', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">
                                                            <?= htmlspecialchars($notif['user_name'] ?? 'System') ?></div>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($notif['email'] ?? '') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;">
                                                    <?= strip_tags($notif['message']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($notif['event_title']): ?>
                                                    <span class="badge bg-light text-dark border"><i
                                                            class="bi bi-calendar-event me-1"></i>
                                                        <?= htmlspecialchars($notif['event_title']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-secondary border">General</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusIcon = 'question-circle-fill';
                                                switch ($notif['status']) {
                                                    case 'sent':
                                                        $statusClass = 'success';
                                                        $statusIcon = 'check-circle-fill';
                                                        break;
                                                    case 'pending':
                                                        $statusClass = 'warning';
                                                        $statusIcon = 'clock-fill';
                                                        break;
                                                    case 'failed':
                                                        $statusClass = 'danger';
                                                        $statusIcon = 'x-circle-fill';
                                                        break;
                                                }
                                                ?>
                                                <span
                                                    class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?> border border-<?= $statusClass ?>-subtle rounded-pill px-3">
                                                    <i class="bi bi-<?= $statusIcon ?> me-1"></i>
                                                    <?= ucfirst($notif['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <?= date('d M, H:i', strtotime($notif['created_at'])) ?>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button class="action-btn btn-light border me-1" title="Lihat Detail"
                                                    onclick="viewNotification(<?= htmlspecialchars(json_encode($notif)) ?>)">
                                                    <i class="bi bi-eye text-primary"></i>
                                                </button>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Kirim ulang notifikasi ini?');">
                                                    <input type="hidden" name="resend_notification" value="<?= $notif['id'] ?>">
                                                    <button class="action-btn btn-light border me-1" title="Kirim Ulang">
                                                        <i class="bi bi-arrow-repeat text-success"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Hapus notifikasi ini?');">
                                                    <input type="hidden" name="delete_notification" value="<?= $notif['id'] ?>">
                                                    <button class="action-btn btn-light border" title="Hapus">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </form>
                                            </td>
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

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold">Detail Notifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Penerima</label>
                        <div id="viewUser" class="fw-bold fs-5 text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Event</label>
                        <div id="viewEvent"></div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Waktu</label>
                        <div id="viewTime"></div>
                    </div>
                    <div class="p-3 bg-light rounded border">
                        <label class="small text-muted text-uppercase fw-bold mb-2">Pesan</label>
                        <div id="viewMessage" class="text-dark"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Message Modal -->
    <div class="modal fade" id="customModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-send-fill me-2"></i>Kirim Pesan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Target Penerima</label>
                            <select name="event_id" class="form-select" required>
                                <option value="">Pilih Target...</option>
                                <option value="all" class="fw-bold text-primary">📢 Broadcast ke Semua User</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>">Peserta: <?= htmlspecialchars($event['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pesan</label>
                            <textarea name="custom_message" class="form-control" rows="5"
                                placeholder="Tulis pesan anda disini..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="send_custom" value="1">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reminder Modal -->
    <div class="modal fade" id="reminderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-warning bg-gradient text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-bell-fill me-2"></i>Kirim Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis mb-3">
                            <small><i class="bi bi-info-circle me-1"></i> Reminder akan dikirim ke semua peserta yang
                                statusnya <b>Confirmed</b> pada event yang dipilih.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Event</label>
                            <select name="event_id" class="form-select" required>
                                <option value="">Pilih Event...</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?>
                                        (<?= date('d M', strtotime($event['tanggal'])) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="send_reminder" value="1">
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Kirim Reminder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-info bg-gradient text-white">
                    <h5 class="modal-title fw-bold">Jadwalkan Notifikasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Waktu Pengiriman</label>
                            <input type="datetime-local" name="schedule_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Event</label>
                            <select name="event_id" class="form-select" required>
                                <option value="">Pilih Event...</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-link text-muted text-decoration-none"
                            data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="schedule_reminder" value="1">
                        <button type="submit" class="btn btn-info text-white px-4 fw-bold">Jadwalkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Email Modal -->
    <div class="modal fade" id="testEmailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-dark text-white">
                    <h5 class="modal-title fw-bold">SMTP Configuration Test</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-envelope-check display-1 text-secondary mb-3"></i>
                        <p>Kirim email percobaan ke alamat email admin yang sedang login untuk memastikan konfigurasi
                            SMTP berjalan dengan baik.</p>
                    </div>
                    <div class="modal-footer border-0 bg-light justify-content-center">
                        <input type="hidden" name="test_email" value="1">
                        <button type="submit" class="btn btn-dark w-100 fw-bold">Kirim Email Test</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewNotification(data) {
            document.getElementById('viewUser').textContent = data.user_name || 'System';
            document.getElementById('viewEvent').textContent = data.event_title || 'General Notification';
            document.getElementById('viewTime').textContent = new Date(data.created_at).toLocaleString('id-ID');
            document.getElementById('viewMessage').innerHTML = data.message;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }
    </script>
</body>

</html>