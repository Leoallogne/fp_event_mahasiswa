<?php

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

// Helper Functions
function time_elapsed_string($datetime, $full = false)
{
    if (!$datetime)
        return 'Baru saja';
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );

    foreach ($string as $k => &$v) {
        if ($k === 'w') {
            if ($weeks > 0) {
                $v = $weeks . ' ' . $v;
            } else {
                unset($string[$k]);
            }
            continue;
        }
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}

function getIconByType($type)
{
    switch ($type) {
        case 'confirmation':
        case 'success':
            return 'check-circle-fill text-success';
        case 'update':
        case 'info':
            return 'info-circle-fill text-primary';
        case 'reminder':
        case 'warning':
            return 'bell-fill text-warning';
        case 'cancelled':
        case 'danger':
            return 'x-circle-fill text-danger';
        case 'welcome':
            return 'emoji-smile-fill text-info';
        default:
            return 'bell-fill text-secondary';
    }
}

// Handle Mark as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $notificationId = $_POST['notification_id'];
        $notificationService->markAsRead($notificationId, $currentUser['id']);
        // Redirect to avoid resubmission
        header("Location: notifications.php");
        exit;
    }
    if (isset($_POST['mark_all_read'])) {
        $notificationService->markAllAsRead($currentUser['id']);
        header("Location: notifications.php");
        exit;
    }
}

$notifications = $notificationService->getUserNotifications($currentUser['id']);
$unreadCount = $notificationService->getUnreadCount($currentUser['id']);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Saya - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1">
    <link rel="stylesheet" href="assets/css/notifications.css?v=<?= time() ?>">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header-notif d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Notifikasi</h2>
                    <p class="text-muted mb-0">Update terbaru seputar aktivitas dan event Anda.</p>
                </div>
                <?php if ($unreadCount > 0): ?>
                    <form method="POST">
                        <button type="submit" name="mark_all_read"
                            class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-check2-all me-2"></i>Tandai Semua Dibaca
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Notifications List -->
            <div class="notification-card">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state-notif">
                        <i class="bi bi-bell-slash"></i>
                        <h4 class="text-dark fw-bold">Tidak ada notifikasi</h4>
                        <p class="text-muted">Semua update terbaru akan muncul disini.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notif-item <?= $notif['is_read'] === 'unread' ? 'unread' : '' ?>">
                            <div class="d-flex gap-3">
                                <!-- Icon -->
                                <div class="notif-icon-box">
                                    <i class="bi bi-<?= getIconByType($notif['type']) ?>"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <?= htmlspecialchars($notif['title'] ?? 'Notifikasi System') ?>
                                        </h6>
                                        <small class="text-muted ms-2 text-nowrap">
                                            <?= time_elapsed_string($notif['created_at']) ?>
                                        </small>
                                    </div>

                                    <div class="text-secondary mb-2" style="font-size: 0.95rem; line-height: 1.5;">
                                        <?= $notif['message'] // Safe as created by system, but consider strip_tags if user input allowed ?>
                                    </div>

                                    <?php if ($notif['is_read'] === 'unread'): ?>
                                        <form method="POST" class="d-inline-block">
                                            <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                            <button type="submit" name="mark_read" class="mark-read-btn">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Script moved to sidebar or global layout, but ensuring bootstrap JS is present -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>