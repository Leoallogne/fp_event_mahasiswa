<?php
if (!isset($auth)) {
    require_once __DIR__ . '/../../config/session.php';
    require_once __DIR__ . '/../../modules/users/Auth.php';
    $auth = new Auth();
}

$currentUser = $auth->getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = $auth->isAdmin();
$isLoggedIn = $auth->isLoggedIn();
$basePath = strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : '';
$userInitial = !empty($currentUser['nama']) ? strtoupper(substr($currentUser['nama'], 0, 1)) : 'U';
$userName = !empty($currentUser['nama']) ? htmlspecialchars($currentUser['nama']) : 'Pengguna';
$userEmail = !empty($currentUser['email']) ? htmlspecialchars($currentUser['email']) : '';
?>

<!-- Mobile Toggle Button -->
<button class="mobile-toggle" aria-label="Toggle navigation">
    <i class="bi bi-list"></i>
</button>

<!-- Overlay -->
<div class="sidebar-overlay"></div>

<!-- Sidebar Navigation -->
<nav class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <!-- Brand Logo -->
            <a href="<?= $basePath ?>index.php" class="text-decoration-none d-flex align-items-center">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center me-2 shadow-sm"
                    style="width: 40px; height: 40px;">
                    <i class="bi bi-calendar-event-fill fs-5"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold text-white" style="letter-spacing: -0.5px;">Event<span
                            class="text-primary">Ku.</span></h4>
                    <small class="text-light opacity-75"
                        style="font-size: 0.75rem;"><?= $isAdmin ? 'Admin Workspace' : 'Student Portal' ?></small>
                </div>
            </a>
        </div>
        <!-- Close Button (Mobile Only) -->
        <button class="btn btn-link text-white-50 p-2 d-lg-none" id="sidebarClose" aria-label="Close sidebar">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
    </div>

    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
        <ul class="nav flex-column gap-1">
            <?php if ($isAdmin): ?>
                <div class="nav-label mt-2 mb-2 px-3 text-uppercase small fw-bold"
                    style="font-size: 0.7rem; color: #64748b;">
                    Main Menu</div>

                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/dashboard.php"
                        class="nav-link <?= (strpos($currentPage, 'dashboard') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/events.php"
                        class="nav-link <?= (strpos($currentPage, 'events') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-calendar-week-fill"></i>
                        <span>Manajemen Event</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/event-participants.php"
                        class="nav-link <?= (strpos($currentPage, 'participants') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-people-fill"></i>
                        <span>Peserta Event</span>
                    </a>
                </li>

                <div class="nav-label mt-4 mb-2 px-3 text-uppercase small fw-bold"
                    style="font-size: 0.7rem; color: #64748b;">
                    System</div>

                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/users.php"
                        class="nav-link <?= (strpos($currentPage, 'users') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/categories.php"
                        class="nav-link <?= (strpos($currentPage, 'categor') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-tags-fill"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/analytics.php"
                        class="nav-link <?= (strpos($currentPage, 'analytics') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-fill"></i>
                        <span>Analitik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/notifications.php"
                        class="nav-link <?= (strpos($currentPage, 'notif') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-bell-fill"></i>
                        <span>Notifikasi</span>
                        <span class="badge bg-danger rounded-pill ms-auto shadow-sm">3</span>
                    </a>
                </li>
            <?php else: ?>
                <!-- User Menu -->
                <div class="nav-label mt-2 mb-2 px-3 text-uppercase small fw-bold"
                    style="font-size: 0.7rem; color: #64748b;">
                    Menu</div>

                <li class="nav-item">
                    <a href="<?= $basePath ?>dashboard.php"
                        class="nav-link <?= (strpos($currentPage, 'dashboard') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>index.php"
                        class="nav-link <?= in_array($currentPage, ['index.php', '']) ? 'active' : '' ?>">
                        <i class="bi bi-compass-fill"></i>
                        <span>Jelajahi Event</span>
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a href="<?= $basePath ?>my-events.php"
                            class="nav-link <?= (strpos($currentPage, 'my-events') !== false) ? 'active' : '' ?>">
                            <i class="bi bi-ticket-perforated-fill"></i>
                            <span>Tiket Saya</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $basePath ?>notifications.php"
                            class="nav-link <?= (strpos($currentPage, 'notifications') !== false) ? 'active' : '' ?>">
                            <i class="bi bi-bell-fill"></i>
                            <span>Notifikasi</span>
                            <?php
                            if (isset($notificationService) && method_exists($notificationService, 'getUnreadCount')):
                                $unreadCount = $notificationService->getUnreadCount($currentUser['id']);
                                if ($unreadCount > 0):
                                    ?>
                                    <span class="badge bg-danger rounded-pill ms-auto shadow-sm"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="<?= $basePath ?>profile.php"
                        class="nav-link <?= (strpos($currentPage, 'profile') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-person-circle"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <?php if ($isLoggedIn): ?>
            <div class="d-flex align-items-center justify-content-between px-2">
                <div class="d-flex align-items-center overflow-hidden">
                    <div class="avatar me-2 flex-shrink-0" style="width: 32px; height: 32px;">
                        <?php if (!empty($currentUser['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $currentUser['avatar'])): ?>
                            <img src="<?= $basePath ?>uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>"
                                class="rounded-circle border" width="32" height="32" alt="<?= $userName ?>">
                        <?php else: ?>
                            <div class="avatar-initial rounded-circle bg-white border d-flex align-items-center justify-content-center text-primary fw-bold"
                                style="width: 32px; height: 32px; font-size: 0.8rem;">
                                <?= $userInitial ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="user-details overflow-hidden">
                        <div class="user-name fw-bold text-white text-truncate" style="font-size: 0.85rem;"
                            title="<?= $userName ?>"><?= $userName ?></div>
                    </div>
                </div>
                <a href="<?= $basePath ?>logout.php" class="text-danger opacity-75 hover-opacity-100 p-1"
                    data-bs-toggle="tooltip" title="Keluar">
                    <i class="bi bi-box-arrow-right fs-6"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="auth-buttons d-grid gap-2">
                <a href="<?= $basePath ?>login.php" class="btn btn-primary btn-sm fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </a>
                <a href="<?= $basePath ?>register.php" class="btn btn-outline-secondary btn-sm fw-bold">
                    Daftar
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        const mobileToggle = document.querySelector('.mobile-toggle');

        function toggleSidebar() {
            const isActive = sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        }

        if (mobileToggle) mobileToggle.addEventListener('click', toggleSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

        // Close button logic
        const sidebarClose = document.getElementById('sidebarClose');
        if (sidebarClose) sidebarClose.addEventListener('click', toggleSidebar);

        // Auto initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>