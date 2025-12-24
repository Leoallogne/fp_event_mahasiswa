<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/notifications/NotificationService.php';

$auth = new Auth();
$auth->requireUser();

$notificationService = new NotificationService();
$currentUser = $auth->getCurrentUser();
$message = '';
$messageType = '';

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notificationId = $_POST['notification_id'] ?? 0;
    $result = $notificationService->markAsRead($notificationId, $currentUser['id']);

    if ($result['success']) {
        $message = "Notifikasi ditandai sebagai sudah dibaca";
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $result = $notificationService->markAllAsRead($currentUser['id']);

    if ($result['success']) {
        $message = "Semua notifikasi ditandai sebagai sudah dibaca";
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// Handle notification preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    $emailNotifications = $_POST['email_notifications'] ?? 'off';
    $reminderNotifications = $_POST['reminder_notifications'] ?? 'off';
    $updateNotifications = $_POST['update_notifications'] ?? 'off';

    // Save preferences to database (you'll need to create a user_preferences table)
    $stmt = $this->db->prepare("UPDATE users SET 
        email_notifications = ?,
        reminder_notifications = ?, 
        update_notifications = ?
        WHERE id = ?");
    $stmt->execute([
        $emailNotifications === 'on' ? 1 : 0,
        $reminderNotifications === 'on' ? 1 : 0,
        $updateNotifications === 'on' ? 1 : 0,
        $currentUser['id']
    ]);

    $message = "Preferensi notifikasi berhasil diperbarui";
    $messageType = 'success';
}

$notifications = $notificationService->getUserNotifications($currentUser['id']);
$unreadCount = $notificationService->getUnreadCount($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --font-inter: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            font-family: var(--font-inter);
            color: #1f2937;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s;
            position: relative;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: #f9fafb;
        }

        .notification-item.unread {
            background-color: rgba(79, 70, 229, 0.03);
            border-left: 4px solid #4f46e5;
        }

        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1.25rem;
            flex-shrink: 0;
        }

        .icon-reminder {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .icon-confirmation {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .icon-update {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .icon-cancelled {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notif-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }

        .notif-message {
            color: #4b5563;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .notif-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notif-time {
            font-size: 0.8rem;
            color: #9ca3af;
            display: flex;
            align-items: center;
        }

        .btn-mark-read {
            border: none;
            background: none;
            color: #4f46e5;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            padding: 0;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .notification-item:hover .btn-mark-read {
            opacity: 1;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .btn-mark-read {
                opacity: 1;
                /* Always show on mobile */
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Notifikasi</h1>
                <p class="text-muted mb-0">Update terbaru seputar event Anda</p>
            </div>

            <?php if ($unreadCount > 0): ?>
                <form method="POST">
                    <button type="submit" name="mark_all_read" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                        <i class="bi bi-check-all me-1"></i>Tandai Semua Dibaca
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4 shadow-sm border-0">
                <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="bi bi-bell-slash empty-icon"></i>
                    <h5 class="fw-bold text-gray-800 mb-2">Tidak ada notifikasi</h5>
                    <p class="text-muted">Anda tidak memiliki notifikasi baru saat ini.</p>
                </div>
            <?php else: ?>
                <ul class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                        <li class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                            <div class="notification-icon <?= getNotificationTypeClass($notification['type']) ?>">
                                <i class="bi <?= getNotificationIcon($notification['type']) ?>"></i>
                            </div>
                            <div class="notification-content">
                                <h5 class="notif-title"><?= htmlspecialchars($notification['title'] ?? 'Notifikasi') ?></h5>
                                <p class="notif-message">
                                    <?= htmlspecialchars($notification['message'] ?? '') ?>
                                </p>
                                <div class="notif-footer">
                                    <span class="notif-time">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= time_elapsed_string($notification['created_at']) ?>
                                    </span>

                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                            <button type="submit" name="mark_read" class="btn-mark-read">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    // Helper functions
    function getNotificationTypeClass($type)
    {
        switch ($type) {
            case 'reminder':
                return 'icon-reminder';
            case 'confirmation':
                return 'icon-confirmation';
            case 'update':
                return 'icon-update';
            case 'cancelled':
                return 'icon-cancelled';
            default:
                return 'icon-reminder';
        }
    }

    function getNotificationIcon($type)
    {
        switch ($type) {
            case 'reminder':
                return 'bi-bell-fill';
            case 'confirmation':
                return 'bi-check-circle-fill';
            case 'update':
                return 'bi-info-circle-fill';
            case 'cancelled':
                return 'bi-x-circle-fill';
            default:
                return 'bi-bell-fill';
        }
    }

    function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $weeks = floor($diff->d / 7);
        $diff->d -= $weeks * 7;

        $string = array(
            'y' => 'tahun',
            'm' => 'bulan',
            'w' => $weeks,
            'd' => $diff->d,
            'h' => $diff->h,
            'i' => $diff->i,
            's' => $diff->s,
        );

        foreach ($string as $k => &$v) {
            if ($k === 'w' && $v === 0) {
                unset($string[$k]);
                continue;
            }
            if ($v) {
                $v = $v . ' ' . ($k == 'y' ? 'tahun' : ($k == 'm' ? 'bulan' : ($k == 'w' ? 'minggu' : ($k == 'd' ? 'hari' : ($k == 'h' ? 'jam' : ($k == 'i' ? 'menit' : 'detik'))))));
            } else {
                unset($string[$k]);
            }
        }

        if (!$full)
            $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
    }
    ?>
</body>

</html>