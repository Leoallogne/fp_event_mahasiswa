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
$keyword = $_GET['q'] ?? '';
$category = $_GET['kategori'] ?? '';

if (!empty($keyword) || !empty($category)) {
    $events = $eventService->searchEvents($keyword, $category);
} else {
    $events = $eventService->getAllEvents();
}

// Get Stats
$stats = $analyticsService->getEventStats();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventKu - Jelajahi Event Kampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css?v=<?= time() ?>">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Navbar Styling */
        .navbar-landing {
            background-color: transparent;
            transition: all 0.3s ease-in-out;
            padding: 1.5rem 0;
        }

        .navbar-landing.scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: white;
        }

        .navbar-landing.scrolled .navbar-brand {
            color: var(--text-main);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
        }

        .navbar-landing.scrolled .nav-link {
            color: var(--text-main) !important;
        }

        .navbar-landing.scrolled .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .btn-nav-light {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-nav-light:hover {
            background: white;
            color: var(--primary-color);
        }

        .navbar-landing.scrolled .btn-nav-light {
            background: transparent;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .navbar-landing.scrolled .btn-nav-light:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Adjust Hero needed due to fixed navbar */
        .hero-wrapper {
            padding-top: 8rem;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-landing">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Event<span class="text-primary">Ku.</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Jelajahi</a></li>

                    <li class="nav-item"><a class="nav-link" href="#stats">Statistik</a></li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="my-events.php">Event Saya</a></li>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex gap-2">
                    <?php if ($auth->isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-nav-light rounded-pill px-4 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars(explode(' ', $currentUser['nama'])[0]) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-4">
                                <li><a class="dropdown-item rounded-3 mb-1" href="profile.php"><i
                                            class="bi bi-person me-2 text-primary"></i> Profil</a></li>
                                <li><a class="dropdown-item rounded-3 mb-1" href="my-events.php"><i
                                            class="bi bi-calendar-check me-2 text-primary"></i> Tiket Saya</a></li>
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

    <div class="main-content">
        <!-- Hero Section -->
        <div class="hero-wrapper">
            <div class="hero-bg-animated"></div>
            <div class="hero-content" data-aos="fade-up">
                <span
                    class="badge bg-white text-primary rounded-pill px-3 py-2 mb-4 fw-bold bg-opacity-10 backdrop-blur border border-white border-opacity-25">
                    ✨ Platform Event Mahasiswa Terlengkap
                </span>
                <h1 class="hero-title mb-4">Temukan Pengalaman Baru, <br>Raih Prestasi Gemilang</h1>
                <p class="hero-subtitle mx-auto" style="max-width: 600px;">
                    Bergabunglah dengan ribuan mahasiswa lainnya. Temukan seminar, workshop, dan kompetisi yang akan
                    mengembangkan potensimu.
                </p>

                <form action="" method="GET" class="hero-search-container mt-5">
                    <input type="text" name="q" class="hero-search-input"
                        placeholder="Cari event (contoh: Seminar AI)..." value="<?= htmlspecialchars($keyword) ?>">
                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="kategori" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <button type="submit" class="hero-search-btn">
                        <i class="bi bi-search me-2"></i>Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-container" id="stats">
            <div class="stats-grid">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <span class="stat-value"><?= $stats['total_events'] ?? 0 ?>+</span>
                    <span class="stat-label">Event Tersedia</span>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <span class="stat-value"><?= $stats['upcoming_events'] ?? 0 ?></span>
                    <span class="stat-label">Event Mendatang</span>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                    <span class="stat-value"><?= $stats['total_registrations'] ?? 0 ?>+</span>
                    <span class="stat-label">Peserta Bergabung</span>
                </div>
                <div class="stat-card" data-aos="fade-up" data-aos-delay="400">
                    <span class="stat-value"><?= $stats['total_users'] ?? 0 ?></span>
                    <span class="stat-label">Mahasiswa Aktif</span>
                </div>
            </div>
        </div>

        <!-- Categories & Filter -->
        <div class="category-section container" id="categories">
            <!-- Events Grid -->
            <div class="events-section p-0 bg-transparent">
                <div class="section-title mb-4">
                    <span>Event Terbaru</span>
                </div>



                <?php if (empty($events)): ?>
                    <div class="text-center py-5">
                        <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-536.jpg"
                            alt="No Events" style="max-width: 300px; opacity: 0.8; mix-blend-mode: multiply;">
                        <h5 class="text-muted mt-4 fw-normal">Belum ada event yang sesuai kriteria Anda.</h5>
                        <a href="index.php" class="btn btn-primary mt-3 rounded-pill px-4">Lihat Semua Event</a>
                    </div>
                <?php else: ?>
                    <div class="event-grid">
                        <?php foreach ($events as $index => $event):
                            $isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $event['id']) : false;
                            $availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);

                            $patternClass = 'pattern-seminar';
                            if ($event['kategori'] == 'Akademik')
                                $patternClass = 'pattern-academic';
                            if ($event['kategori'] == 'Non-Akademik')
                                $patternClass = 'pattern-non-academic';
                            ?>
                            <div class="modern-card" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                                <div class="card-image-wrapper <?= $patternClass ?>">
                                    <div class="card-badges">
                                        <span class="badge-custom badge-category">
                                            <?= htmlspecialchars($event['kategori']) ?>
                                        </span>
                                        <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                            <span class="badge-custom badge-price">
                                                Rp <?= number_format($event['price'], 0, ',', '.') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-custom badge-price free">Gratis</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-content">
                                    <div class="event-date-modern">
                                        <i class="bi bi-calendar4-week"></i>
                                        <?= date('d M Y, H:i', strtotime($event['tanggal'])) ?>
                                    </div>
                                    <h3 class="event-title-modern">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h3>
                                    <div class="event-location-modern">
                                        <i class="bi bi-geo-alt"></i>
                                        <?= htmlspecialchars($event['lokasi']) ?>
                                    </div>
                                    <p class="event-description-modern">
                                        <?= htmlspecialchars(strip_tags($event['deskripsi'])) ?>
                                    </p>

                                    <div class="card-footer-modern">
                                        <div class="participants-info">
                                            <span class="participants-count">
                                                <i class="bi bi-people-fill me-1 text-primary"></i>
                                                <?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?>
                                            </span>
                                        </div>

                                        <?php if ($auth->isLoggedIn() && !$isRegistered && $availableQuota > 0): ?>
                                            <a href="event-detail.php?id=<?= $event['id'] ?>"
                                                class="btn-card-action btn-card-primary">
                                                Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        <?php elseif ($isRegistered): ?>
                                            <a href="event-detail.php?id=<?= $event['id'] ?>"
                                                class="btn-card-action btn-card-outline text-success border-success">
                                                <i class="bi bi-check-circle me-1"></i> Terdaftar
                                            </a>
                                        <?php elseif ($availableQuota <= 0): ?>
                                            <button disabled class="btn-card-action btn-secondary opacity-75">
                                                Penuh
                                            </button>
                                        <?php else: ?>
                                            <a href="event-detail.php?id=<?= $event['id'] ?>"
                                                class="btn-card-action btn-card-outline">
                                                Lihat Detail
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="modern-footer bg-white border-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4">
                        <a href="index.php" class="footer-brand text-decoration-none">EventKu.</a>
                        <p class="text-muted small">
                            Platform manajemen event mahasiswa terdepan. Temukan, ikuti, dan kembangkan potensimu
                            melalui berbagai kegiatan positif.
                        </p>
                    </div>
                    <div class="col-lg-2 col-6">
                        <h6 class="fw-bold mb-3">Event</h6>
                        <ul class="footer-links">
                            <li><a href="index.php">Jelajahi Semua</a></li>
                            <li><a href="#stats">Statistik</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-6">
                        <h6 class="fw-bold mb-3">Akun</h6>
                        <ul class="footer-links">
                            <?php if ($auth->isLoggedIn()): ?>
                                <li><a href="profile.php">Profile</a></li>
                                <li><a href="my-events.php">Event Saya</a></li>
                                <li><a href="logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a href="login.php">Login</a></li>
                                <li><a href="register.php">Daftar</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                        <p class="text-muted small mb-2"><i class="bi bi-envelope me-2"></i>
                            info@eventku.campus.ac.id
                        </p>
                        <p class="text-muted small"><i class="bi bi-telephone me-2"></i> +62 812 3456 7890</p>
                        <div class="d-flex gap-3 mt-3">
                            <a href="#" class="text-secondary"><i class="bi bi-instagram fs-5"></i></a>
                            <a href="#" class="text-secondary"><i class="bi bi-twitter-x fs-5"></i></a>
                            <a href="#" class="text-secondary"><i class="bi bi-facebook fs-5"></i></a>
                        </div>
                    </div>
                </div>
                <div class="border-top mt-5 pt-4 text-center text-muted small">
                    &copy; <?= date('Y') ?> EventKu. All rights reserved. Made with ❤️ for Students.
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Init AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Navbar Scroll Effect
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar-landing');
            const toggleIcon = document.querySelector('.navbar-toggler i');

            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
                if (toggleIcon) toggleIcon.classList.replace('text-white', 'text-dark');
            } else {
                navbar.classList.remove('scrolled');
                if (toggleIcon) toggleIcon.classList.replace('text-dark', 'text-white');
            }
        });
    </script>
</body>

</html>