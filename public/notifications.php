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
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
        }
        .notification-item {
            border-left: 4px solid #4361ee;
            transition: all 0.3s ease;
        }
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
        }
        .notification-item.unread {
            background-color: #f8f9ff;
            border-left-color: #4361ee;
        }
        .notification-item.read {
            border-left-color: #dee2e6;
            opacity: 0.8;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .notification-content p {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .notification-time {
            font-size: 0.8rem;
            color: #adb5bd;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0 !important;
                padding-top: 80px;
            }
            
            .notifications-header {
                padding: 1.5rem 0;
                margin-bottom: 1.5rem;
            }
            
            .notifications-header h1 {
                font-size: 1.8rem;
            }
            
            .notification-card {
                padding: 1rem;
            }
            
            .notification-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .notifications-header {
                padding: 1rem 0;
                margin-bottom: 1rem;
            }
            
            .notifications-header h1 {
                font-size: 1.5rem;
            }
            
            .notification-card {
                padding: 0.75rem;
            }
            
            .notification-content h5 {
                font-size: 0.9rem;
            }
            
            .notification-content p {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Notifications Header -->
        <div class="notifications-header">
            <div class="container-fluid px-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1><i class="bi bi-bell me-2"></i>Notifikasi Saya</h1>
                        <p>Kelola notifikasi event dan update terbaru</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-danger me-2"><?= $unreadCount ?> baru</span>
                        <?php endif; ?>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="mark_all_read" class="btn btn-light btn-sm">
                                <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-3">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Notifications List -->
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h4 class="mt-3 text-muted">Belum Ada Notifikasi</h4>
                    <p class="text-muted">Anda belum memiliki notifikasi saat ini</p>
                    <a href="index.php" class="btn btn-primary mt-2">
                        <i class="bi bi-search"></i> Jelajahi Event
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-card <?= $notification['is_read'] ? 'read' : 'unread' ?>">
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon <?= getNotificationTypeClass($notification['type']) ?>">
                                        <i class="bi <?= getNotificationIcon($notification['type']) ?>"></i>
                                    </div>
                                    <div class="notification-content flex-grow-1">
                                        <h5><?= htmlspecialchars($notification['title'] ?? 'Notifikasi') ?></h5>
                                        <p><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="notification-time">
                                                <i class="bi bi-clock"></i>
                                                <?= formatNotificationTime($notification['created_at']) ?>
                                            </small>
                                            <?php if (!$notification['is_read']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                    <button type="submit" name="mark_as_read" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-check"></i> Tandai Dibaca
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php
    // Helper functions
    function getNotificationTypeClass($type) {
        switch ($type) {
            case 'reminder':
                return 'reminder';
            case 'confirmation':
                return 'confirmation';
            case 'update':
                return 'update';
            case 'cancelled':
                return 'cancelled';
            default:
                return 'reminder';
        }
    }

    function getNotificationIcon($type) {
        switch ($type) {
            case 'reminder':
                return 'bi-bell';
            case 'confirmation':
                return 'bi-check-circle';
            case 'update':
                return 'bi-info-circle';
            case 'cancelled':
                return 'bi-x-circle';
            default:
                return 'bi-bell';
        }
    }

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
            $v = $v . ' minggu' . ($v > 1 ? '' : '');
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
</body>
</html>
