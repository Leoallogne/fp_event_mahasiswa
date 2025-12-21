<?php
session_start();
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

// Handle send reminder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reminder'])) {
    $eventId = $_POST['event_id'] ?? 0;
    $result = $notificationService->sendEventReminder($eventId);
    
    if ($result['success']) {
        $message = "Reminder berhasil dikirim ke {$result['sent']} peserta";
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// Handle send custom notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_custom'])) {
    $eventId = $_POST['event_id'] ?? 0;
    $customMessage = trim($_POST['custom_message'] ?? '');
    
    if (empty($customMessage)) {
        $message = "Pesan notifikasi tidak boleh kosong";
        $messageType = 'danger';
    } else {
        $result = $notificationService->createEventUpdateNotification($eventId, $customMessage);
        
        if ($result['success']) {
            $message = "Notifikasi berhasil dikirim ke {$result['sent']} peserta";
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

// Handle test email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testEmail = 'leoallogne@gmail.com';
    $result = $notificationService->sendEmail(
        $testEmail,
        'Admin Test',
        'Test Email dari Event Management System',
        'Ini adalah email test untuk memastikan PHPMailer berfungsi dengan benar.'
    );
    
    if ($result) {
        $message = "Email test berhasil dikirim ke $testEmail";
        $messageType = 'success';
    } else {
        $message = "Email test gagal dikirim. Periksa konfigurasi SMTP.";
        $messageType = 'danger';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a56d4;
            --success-color: #00b74a;
            --warning-color: #f9c74f;
            --danger-color: #f94144;
            --info-color: #4cc9f0;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
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
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100px, -100px); }
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(67, 97, 238, 0.3);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .stat-card p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-gradient:hover::before {
            left: 100%;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
        }

        .notification-item {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
        }

        .notification-item.success {
            border-left-color: var(--success-color);
        }

        .notification-item.warning {
            border-left-color: var(--warning-color);
        }

        .notification-item.danger {
            border-left-color: var(--danger-color);
        }

        .notification-item.info {
            border-left-color: var(--info-color);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #dee2e6;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .loading-spinner.active {
            display: block;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1><i class="bi bi-bell-fill me-3"></i>Notification Center</h1>
                <p>Kelola notifikasi sistem dan komunikasi dengan pengguna secara profesional</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show glass-card" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Professional Statistics Dashboard -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card">
                        <i class="bi bi-envelope-check-fill mb-3" style="font-size: 2rem;"></i>
                        <h3><?= count($notifications) ?></h3>
                        <p>Total Notifikasi</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #00b74a 0%, #00a846 100%);">
                        <i class="bi bi-check-circle-fill mb-3" style="font-size: 2rem;"></i>
                        <h3><?= count(array_filter($notifications, fn($n) => $n['status'] === 'sent')) ?></h3>
                        <p>Terkirim</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #f9c74f 0%, #f8b500 100%);">
                        <i class="bi bi-clock-history mb-3" style="font-size: 2rem;"></i>
                        <h3><?= count(array_filter($notifications, fn($n) => $n['status'] === 'pending')) ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card" style="background: linear-gradient(135deg, #4cc9f0 0%, #3bb6c7 100%);">
                        <i class="bi bi-calendar-event mb-3" style="font-size: 2rem;"></i>
                        <h3><?= count($events) ?></h3>
                        <p>Total Event</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass-card p-4 mb-4">
                <h5 class="mb-4"><i class="bi bi-lightning-fill me-2"></i>Quick Actions</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-gradient w-100" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                            <i class="bi bi-envelope-test me-2"></i>Test Email
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-gradient w-100" data-bs-toggle="modal" data-bs-target="#reminderModal">
                            <i class="bi bi-clock-history me-2"></i>Send Reminder
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-gradient w-100" data-bs-toggle="modal" data-bs-target="#customModal">
                            <i class="bi bi-chat-text me-2"></i>Custom Message
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-gradient w-100" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                            <i class="bi bi-calendar-check me-2"></i>Schedule
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="glass-card">
                <div class="card-header bg-transparent border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Recent Notifications</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                            <button class="btn btn-sm btn-primary">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <i class="bi bi-bell-slash"></i>
                            <h5>No Notifications Yet</h5>
                            <p>Start sending notifications to engage with your users effectively.</p>
                            <button class="btn btn-gradient mt-3" data-bs-toggle="modal" data-bs-target="#customModal">
                                <i class="bi bi-plus-circle me-2"></i>Send First Notification
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-hash me-1"></i>ID</th>
                                        <th><i class="bi bi-person me-1"></i>User</th>
                                        <th><i class="bi bi-calendar-event me-1"></i>Event</th>
                                        <th><i class="bi bi-chat-dots me-1"></i>Message</th>
                                        <th><i class="bi bi-info-circle me-1"></i>Status</th>
                                        <th><i class="bi bi-clock me-1"></i>Time</th>
                                        <th><i class="bi bi-gear me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($notifications, 0, 10) as $index => $notif): ?>
                                        <tr class="notification-item <?= $notif['status'] ?>">
                                            <td><?= $notif['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                        <?= substr($notif['user_name'] ?? 'S', 0, 1) ?>
                                                    </div>
                                                    <?= htmlspecialchars($notif['user_name'] ?? 'System') ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($notif['event_title'] ?? '-') ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($notif['message']) ?>">
                                                    <?= htmlspecialchars(substr($notif['message'], 0, 50)) ?>...
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $notif['status'] === 'sent' ? 'success' : 'warning' ?>">
                                                    <i class="bi bi-<?= $notif['status'] === 'sent' ? 'check-circle' : 'clock' ?> me-1"></i>
                                                    <?= ucfirst($notif['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i>
                                                    <?= time_elapsed_string($notif['created_at']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success" title="Resend">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($notifications) > 10): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-down-circle me-2"></i>Load More
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Modals -->
    <!-- Test Email Modal -->
    <div class="modal fade" id="testEmailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header bg-transparent border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope-test text-primary me-2"></i>Test Email Configuration
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p class="text-muted">Test konfigurasi PHPMailer dengan mengirim email ke leoallogne@gmail.com</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Email test akan dikirim ke <strong>leoallogne@gmail.com</strong> untuk memverifikasi konfigurasi SMTP.
                        </div>
                    </div>
                    <div class="modal-footer bg-transparent border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="test_email" value="1">
                        <button type="submit" class="btn btn-gradient">
                            <i class="bi bi-send me-2"></i>Kirim Email Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Send Reminder Modal -->
    <div class="modal fade" id="reminderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header bg-transparent border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-clock-history text-warning me-2"></i>Kirim Reminder Event
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Event</label>
                            <select name="event_id" class="form-select" required>
                                <option value="">Pilih Event</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>">
                                        <?= htmlspecialchars($event['title']) ?> - 
                                        <?= date('d/m/Y', strtotime($event['tanggal'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-transparent border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="send_reminder" value="1">
                        <button type="submit" class="btn btn-gradient">
                            <i class="bi bi-send me-2"></i>Kirim Reminder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Message Modal -->
    <div class="modal fade" id="customModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header bg-transparent border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-chat-text text-info me-2"></i>Kirim Notifikasi Kustom
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Event</label>
                            <select name="event_id" class="form-select" required>
                                <option value="">Pilih Event</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>">
                                        <?= htmlspecialchars($event['title']) ?> - 
                                        <?= date('d/m/Y', strtotime($event['tanggal'])) ?>
                                        (<?= $event['registered_count'] ?? 0 ?> peserta)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pesan Notifikasi</label>
                            <textarea name="custom_message" class="form-control" rows="4" 
                                      placeholder="Masukkan pesan notifikasi..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-transparent border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="send_custom" value="1">
                        <button type="submit" class="btn btn-gradient">
                            <i class="bi bi-send me-2"></i>Kirim Notifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Reminder Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header bg-transparent border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-check text-success me-2"></i>Jadwalkan Reminder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Event</label>
                            <select name="event_id" class="form-select" required>
                                <option value="">Pilih Event</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>">
                                        <?= htmlspecialchars($event['title']) ?> - 
                                        <?= date('d/m/Y', strtotime($event['tanggal'])) ?>
                                        (<?= $event['registered_count'] ?? 0 ?> peserta)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Waktu Kirim</label>
                            <input type="datetime-local" name="schedule_time" class="form-control" required>
                            <small class="text-muted">Pilih waktu untuk mengirim reminder otomatis</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pesan Reminder</label>
                            <textarea name="reminder_message" class="form-control" rows="4" 
                                      placeholder="Pesan reminder yang akan dikirim...">Hai {nama}, jangan lupa event "{event_title}" akan diselenggarakan pada {tanggal} di {lokasi}. Sampai jumpa!</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-transparent border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <input type="hidden" name="schedule_reminder" value="1">
                        <button type="submit" class="btn btn-gradient">
                            <i class="bi bi-clock me-2"></i>Jadwalkan Reminder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);

        // Loading states for buttons
        document.querySelectorAll('button[type="submit"]').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });

        // Smooth scroll animations
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    </script>
</body>
</html>

<?php
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks from days (fix deprecated $w property)
    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => $weeks > 0 ? $weeks : null,
        'd' => $diff->d,
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    
    foreach ($string as $k => &$v) {
        if ($k === 'w' && $v === null) {
            unset($string[$k]);
            continue;
        }
        
        if ($k === 'w' && $v > 0) {
            $v = $v . ' ' . $string[$k] . ($v > 1 ? '' : '');
        } elseif ($k !== 'w' && $diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
?>

