<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';
require_once __DIR__ . '/../modules/registrations/RegistrationService.php';
require_once __DIR__ . '/../modules/analytics/AnalyticsService.php';

$auth = new Auth();
$eventService = new EventService();
$registrationService = new RegistrationService();
$analyticsService = new AnalyticsService();

// Redirect if admin
if ($auth->isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$isLoggedIn = $auth->isLoggedIn();
$keyword = $_GET['q'] ?? '';
$category = $_GET['kategori'] ?? '';

// Event Fetching Logic
if ($isLoggedIn) {
    if (!empty($keyword) || !empty($category)) {
        $events = $eventService->searchEvents($keyword, $category);
    } else {
        $events = $eventService->getAllEvents();
    }
} else {
    // Guest View: Fallback logic for content fullness
    $events = $eventService->getUpcomingEvents(6);
    if (count($events) < 3) {
        $events = $eventService->getAllEvents(6);
    }
}

// Get Stats
$stats = $analyticsService->getEventStats();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventKu - Platform Event Mahasiswa Terdepan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css?v=<?= time() ?>">
</head>

<body>

    <!-- Floating Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-landing <?= $isLoggedIn ? 'scrolled' : '' ?>">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Event<span class="text-primary">Ku.</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Jelajahi</a></li>
                    <?php if (!$isLoggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="#features">Fitur</a></li>
                        <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="my-events.php">Tiket Saya</a></li>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex gap-2">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-nav-light rounded-pill px-4 dropdown-toggle ps-3" type="button"
                                data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-2"></i>
                                <?= htmlspecialchars(explode(' ', $currentUser['nama'])[0]) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-4 mt-2">
                                <li><a class="dropdown-item rounded-3 mb-1" href="profile.php"><i
                                            class="bi bi-person me-2 text-primary"></i> Profil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item rounded-3 text-danger" href="logout.php"><i
                                            class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-nav-light rounded-pill px-4 fw-bold">Masuk</a>
                        <a href="register.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container pb-5">

        <?php if (!$isLoggedIn): ?>
            <!-- ================= GUEST HERO SECTION ================= -->
            <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                </div>
                <div class="carousel-inner">
                    <!-- Slide 1 -->
                    <div class="carousel-item active"
                        style="background-image: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'); background-size: cover; background-position: center;">
                        <div class="hero-overlay"></div>
                        <div class="carousel-caption-custom">
                            <span
                                class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill mb-4 fw-bold">✨
                                Platform #1 Mahasiswa</span>
                            <h1 class="display-title">Temukan Event Kampus Terbaik</h1>
                            <p class="lead-text">Satu tempat untuk semua kebutuhan upgrade skill, seminar, dan lomba
                                mahasiswa Indonesia.</p>
                            <div class="d-flex gap-3 justify-content-start">
                                <a href="register.php"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg">Mulai
                                    Sekarang</a>
                                <a href="#events" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold">Lihat
                                    Event</a>
                            </div>
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="carousel-item"
                        style="background-image: url('https://images.unsplash.com/photo-1515187029135-18ee286d815b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'); background-size: cover; background-position: center;">
                        <div class="hero-overlay"></div>
                        <div class="carousel-caption-custom">
                            <span
                                class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill mb-4 fw-bold">🚀
                                Komunitas Aktif</span>
                            <h1 class="display-title">Bangun Relasi & Prestasi</h1>
                            <p class="lead-text">Bergabunglah dengan ribuan mahasiswa lainnya untuk berkolaborasi dan
                                berkompetisi.</p>
                            <div class="d-flex gap-3 justify-content-start">
                                <a href="register.php"
                                    class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold text-dark shadow-lg">Gabung
                                    Komunitas</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="py-5 mt-5" id="features">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-rocket-takeoff"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Daftar Cepat</h4>
                            <p class="text-muted mb-0">Proses pendaftaran event yang seamless tanpa ribet, langsung
                                terkonfirmasi otomatis.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon text-success" style="background: #ECFDF5;">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Terverifikasi</h4>
                            <p class="text-muted mb-0">Semua event dikurasi secara ketat untuk memastikan kualitas dan
                                keamanan peserta.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon text-warning" style="background: #FFFBEB;">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Sertifikat Digital</h4>
                            <p class="text-muted mb-0">Dapatkan e-sertifikat resmi langsung setelah menyelesaikan event
                                untuk portofoliomu.</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ================= LOGGED IN DASHBOARD HEADER ================= -->
            <div class="dashboard-header mb-5">
                <div class="dashboard-content">
                    <h1 class="fw-bold mb-3">Hi, <?= htmlspecialchars(explode(' ', $currentUser['nama'])[0]) ?>! 👋</h1>
                    <p class="fs-5 opacity-90 mb-4">Temukan event menarik untuk upgrade skill kamu hari ini.</p>

                    <form action="" method="GET">
                        <div class="search-bar-lg">
                            <i class="bi bi-search fs-5 text-muted ms-2 me-3"></i>
                            <input type="text" name="q" class="search-input"
                                placeholder="Cari seminar, workshop, atau lomba..."
                                value="<?= htmlspecialchars($keyword) ?>">
                            <button type="submit" class="search-btn">Cari</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <h4 class="fw-bold mb-0">Kategori Populer</h4>
                        <a href="index.php" class="text-primary text-decoration-none fw-semibold small">Reset Filter</a>
                    </div>
                    <div class="category-scroller">
                        <a href="index.php" class="cat-pill <?= empty($category) ? 'active' : '' ?>">Semua Event</a>
                        <a href="index.php?kategori=Akademik"
                            class="cat-pill <?= $category === 'Akademik' ? 'active' : '' ?>">📚 Akademik</a>
                        <a href="index.php?kategori=Non-Akademik"
                            class="cat-pill <?= $category === 'Non-Akademik' ? 'active' : '' ?>">⚽ Non-Akademik</a>
                        <a href="index.php?kategori=Seminar"
                            class="cat-pill <?= $category === 'Seminar' ? 'active' : '' ?>">🎤 Seminar</a>
                        <a href="index.php?kategori=Workshop"
                            class="cat-pill <?= $category === 'Workshop' ? 'active' : '' ?>">🛠️ Workshop</a>
                        <a href="index.php?kategori=Lomba" class="cat-pill <?= $category === 'Lomba' ? 'active' : '' ?>">🏆
                            Lomba</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <!-- ================= EVENT GRID (SHARED) ================= -->
        <div class="row g-4" id="events">
            <?php if (empty($events)): ?>
                <div class="col-12 text-center py-5">
                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-536.jpg" alt="No data"
                        style="width: 200px; opacity: 0.7; mix-blend-mode: multiply;">
                    <h5 class="text-muted mt-4">Belum ada event ditemukan.</h5>
                    <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Lihat Semua</a>
                </div>
            <?php else: ?>
                <?php if (!$isLoggedIn): ?>
                    <div class="col-12 text-center mb-4">
                        <h2 class="fw-bold">Event Terbaru</h2>
                        <p class="text-muted">Jangan lewatkan kesempatan emas ini</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($events as $index => $event):
                    $isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $event['id']) : false;
                    $availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);

                    // Determine Pattern
                    $patternClass = 'pattern-seminar';
                    if ($event['kategori'] == 'Akademik')
                        $patternClass = 'pattern-academic';
                    if ($event['kategori'] == 'Non-Akademik')
                        $patternClass = 'pattern-non-academic';
                    ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                        <div class="modern-card">
                            <div class="card-image-wrapper <?= $patternClass ?>">
                                <!-- Badges -->
                                <div class="card-badges">
                                    <span class="badge-float">
                                        <?= htmlspecialchars($event['kategori']) ?>
                                    </span>
                                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                        <span class="badge-float price">
                                            Rp <?= number_format($event['price'], 0, ',', '.') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-float price">Gratis</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body-custom">
                                <div class="event-date">
                                    <i class="bi bi-calendar-event"></i>
                                    <?= date('d M Y, H:i', strtotime($event['tanggal'])) ?>
                                </div>
                                <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                                <div class="event-location">
                                    <i class="bi bi-geo-alt-fill text-muted"></i>
                                    <?= htmlspecialchars($event['lokasi']) ?>
                                </div>

                                <div class="card-footer-custom">
                                    <div class="participant-count">
                                        <i class="bi bi-people-fill"></i>
                                        <span><?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?></span>
                                    </div>

                                    <?php if (!$isLoggedIn): ?>
                                        <a href="login.php" class="btn-card btn-card-primary">Detail</a>
                                    <?php elseif ($isRegistered): ?>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i> Terdaftar
                                        </span>
                                    <?php elseif ($availableQuota <= 0): ?>
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill">Penuh</span>
                                    <?php else: ?>
                                        <a href="event-detail.php?id=<?= $event['id'] ?>" class="btn-card btn-card-primary">
                                            Daftar <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!$isLoggedIn): ?>
            <!-- Guest CTA Footer -->
            <div class="mt-5 pt-5">
                <div class="cta-box">
                    <h2 class="fw-bold mb-3 display-6">Siap Mengembangkan Diri?</h2>
                    <p class="lead mb-4 opacity-75">Bergabung sekarang dan dapatkan akses ke ribuan event berkualitas.</p>
                    <a href="register.php"
                        class="btn btn-light rounded-pill px-5 py-3 fw-bold text-primary shadow-lg">Daftar Gratis</a>
                </div>
            </div>
        <?php endif; ?>

    </div> <!-- End Container -->

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <a href="index.php" class="footer-brand">EventKu.</a>
                    <p class="text-secondary">Platform manajemen event mahasiswa terpercaya. Temukan, ikuti, dan
                        kembangkan potensimu.</p>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="fw-bold mb-3 text-dark">Platform</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#" class="text-secondary text-decoration-none">Tentang Kami</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none">Karir</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none">Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="fw-bold mb-3 text-dark">Bantuan</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#" class="text-secondary text-decoration-none">Pusat Bantuan</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="text-secondary text-decoration-none">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold mb-3 text-dark">Hubungi Kami</h6>
                    <p class="text-secondary mb-2"><i class="bi bi-envelope me-2"></i> info@eventku.id</p>
                    <p class="text-secondary"><i class="bi bi-whatsapp me-2"></i> +62 812 3456 7890</p>
                </div>
            </div>
            <div class="border-top mt-5 pt-4 text-center text-muted small">
                &copy; <?= date('Y') ?> EventKu. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                document.querySelector('.navbar-landing').classList.add('scrolled');
            } else {
                // If logged in, always keep 'scrolled' style for contrast if you want, 
                // BUT our CSS handles .navbar-landing.scrolled AND we added a conditional PHP class for logged in users?
                // Actually the PHP adds 'scrolled' class initially if logged in.
                // Let's check logic: if logged in, we want white bg ALWAYS?
                // The PHP logic was: <?= $isLoggedIn ? 'scrolled' : '' ?> on nav.
                // So if logged in, it starts scrolled.
                // If NOT logged in (guest), it starts transparent.

                const nav = document.querySelector('.navbar-landing');
                // Only remove scrolled if we are NOT logged in (implicitly handled by session check in PHP, but JS doesn't know)
                // We'll trust the CSS transitions.
                // If user is guest, we want transparent at top.
                <?php if (!$isLoggedIn): ?>
                    nav.classList.remove('scrolled');
                <?php endif; ?>
            }
        });
    </script>
</body>

</html>