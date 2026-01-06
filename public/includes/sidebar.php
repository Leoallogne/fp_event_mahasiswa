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
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <i class="bi bi-calendar-event me-2 sidebar-logo"></i>
            <div>
                <h4 class="mb-0">EventKu</h4>
                <small class="text-muted"><?= $isAdmin ? 'Admin Panel' : 'User Dashboard' ?></small>
            </div>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <?php if ($isAdmin): ?>
                <!-- Admin Menu -->
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/dashboard.php"
                        class="nav-link <?= (strpos($currentPage, 'dashboard') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/events.php"
                        class="nav-link <?= (strpos($currentPage, 'events') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Manajemen Event">
                        <i class="bi bi-calendar-event"></i>
                        <span>Manajemen Event</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/event-participants.php"
                        class="nav-link <?= (strpos($currentPage, 'participants') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Peserta Event">
                        <i class="bi bi-people"></i>
                        <span>Peserta Event</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/users.php"
                        class="nav-link <?= (strpos($currentPage, 'users') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Manajemen User">
                        <i class="bi bi-people"></i>
                        <span>Manajemen User</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/categories.php"
                        class="nav-link <?= (strpos($currentPage, 'categor') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Manajemen Kategori">
                        <i class="bi bi-tags"></i>
                        <span>Kategori</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/analytics.php"
                        class="nav-link <?= (strpos($currentPage, 'analytics') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Analitik & Laporan">
                        <i class="bi bi-graph-up"></i>
                        <span>Analitik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>admin/notifications.php"
                        class="nav-link <?= (strpos($currentPage, 'notif') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Notifikasi">
                        <i class="bi bi-bell"></i>
                        <span>Notifikasi</span>
                        <span class="badge bg-danger rounded-pill ms-auto">3</span>
                    </a>
                </li>
            <?php else: ?>
                <!-- User Menu -->
                <li class="nav-item">
                    <a href="<?= $basePath ?>dashboard.php"
                        class="nav-link <?= (strpos($currentPage, 'dashboard') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= $basePath ?>index.php"
                        class="nav-link <?= in_array($currentPage, ['index.php', '']) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Jelajahi Event">
                        <i class="bi bi-search"></i>
                        <span>Jelajahi Event</span>
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a href="<?= $basePath ?>my-events.php"
                            class="nav-link <?= (strpos($currentPage, 'my-events') !== false) ? 'active' : '' ?>"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Event Saya">
                            <i class="bi bi-calendar-check"></i>
                            <span>Event Saya</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $basePath ?>notifications.php"
                            class="nav-link <?= (strpos($currentPage, 'notifications') !== false) ? 'active' : '' ?>"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Notifikasi">
                            <i class="bi bi-bell"></i>
                            <span>Notifikasi</span>
                            <?php
                            // Get unread count for badge
                            if (isset($notificationService) && method_exists($notificationService, 'getUnreadCount')):
                                $unreadCount = $notificationService->getUnreadCount($currentUser['id']);
                                if ($unreadCount > 0):
                                    ?>
                                    <span class="badge bg-danger rounded-pill ms-auto"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="<?= $basePath ?>profile.php"
                        class="nav-link <?= (strpos($currentPage, 'profile') !== false) ? 'active' : '' ?>"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Saya">
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
            <div class="user-info">
                <div class="d-flex align-items-center" style="min-width: 0;">
                    <!-- min-width 0 allows flex child to shrink properly -->
                    <div class="avatar me-3">
                        <?php if (!empty($currentUser['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $currentUser['avatar'])): ?>
                            <img src="<?= $basePath ?>uploads/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>"
                                class="rounded-circle" width="40" height="40" alt="<?= $userName ?>">
                        <?php else: ?>
                            <div class="avatar-initial"><?= $userInitial ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name" title="<?= $userName ?>"><?= $userName ?></div>
                        <small class="user-email text-muted" title="<?= $userEmail ?>"><?= $userEmail ?></small>
                    </div>
                </div>
                <!-- Independent Logout Button -->
                <a href="<?= $basePath ?>logout.php" class="btn-logout ms-2" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="<?= $basePath ?>login.php" class="btn btn-primary btn-sm w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </a>
                <a href="<?= $basePath ?>register.php" class="btn btn-outline-light btn-sm w-100">
                    Daftar Akun
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<!-- CSS Layout is now in public/assets/css/layout.css - Included in parent files -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        const mobileToggle = document.querySelector('.mobile-toggle');
        // Performance optimization: Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Find existing main-content or create it if needed
        let mainContent = document.querySelector('.main-content');
        if (!mainContent) {
            // Only create wrapper if main-content doesn't exist
            const wrapper = document.createElement('div');
            wrapper.className = 'main-wrapper';

            // Move all content after sidebar into main-content
            let nextSibling = sidebar.nextSibling;
            while (nextSibling) {
                const current = nextSibling;
                nextSibling = nextSibling.nextSibling;
                wrapper.appendChild(current);
            }

            document.body.insertBefore(wrapper, document.body.firstChild);

            // Create main-content div
            const mainDiv = document.createElement('div');
            mainDiv.className = 'main-content';
            wrapper.appendChild(mainDiv);
            mainContent = mainDiv;
        }

        // Toggle sidebar for mobile only
        function toggleSidebar() {
            const isActive = sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');

            // Update ARIA attributes
            mobileToggle.setAttribute('aria-expanded', isActive);
            sidebar.setAttribute('aria-hidden', !isActive);
        }

        // Close sidebar when clicking outside (mobile only)
        function handleClickOutside(event) {
            const isClickInside = sidebar.contains(event.target) ||
                event.target === mobileToggle ||
                event.target.closest('.mobile-toggle');

            if (!isClickInside && window.innerWidth < 992 && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        }

        // Handle resize with debouncing for performance
        const handleResize = debounce(function () {
            const width = window.innerWidth;

            if (width >= 992) {
                // Desktop mode - sidebar is always visible
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
                if (mobileToggle) {
                    mobileToggle.setAttribute('aria-expanded', 'false');
                }
                sidebar.setAttribute('aria-hidden', 'false');
            } else {
                // Mobile mode
                sidebar.setAttribute('aria-hidden', 'true');
            }
        }, 250);

        // Handle keyboard navigation (mobile only)
        function handleKeyboard(event) {
            // ESC key to close sidebar on mobile
            if (event.key === 'Escape' && window.innerWidth < 992 && sidebar.classList.contains('active')) {
                toggleSidebar();
                if (mobileToggle) {
                    mobileToggle.focus();
                }
            }
        }

        // Event listeners with passive flag for performance
        document.addEventListener('click', handleClickOutside, { passive: true });
        window.addEventListener('resize', handleResize, { passive: true });
        document.addEventListener('keydown', handleKeyboard);

        // Mobile toggle
        if (mobileToggle) {
            mobileToggle.addEventListener('click', toggleSidebar);
            mobileToggle.setAttribute('aria-expanded', 'false');
        }

        // Overlay click to close
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        // Close sidebar on nav link click (mobile only)
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                if (window.innerWidth < 992) {
                    // Small delay to allow navigation
                    setTimeout(toggleSidebar, 100);
                }
            });
        });

        // Handle dropdown menus (if any)
        const dropdownItems = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function (e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const bsCollapse = new bootstrap.Collapse(target, { toggle: true });
                    }
                }
            });
        });

        // Load saved state
        const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (savedCollapsed && window.innerWidth >= 992) {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('sidebar-collapsed');
            }
            toggleBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
        }

        // Initialize tooltips after DOM is ready
        setTimeout(() => {
            updateTooltips();
        }, 100);

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Performance: Preload critical resources
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                // Preload fonts and icons
                const link = document.createElement('link');
                link.rel = 'preload';
                link.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css';
                link.as = 'style';
                document.head.appendChild(link);
            });
        }
    });
</script>