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
    // User View: Full access
    if (!empty($keyword) || !empty($category)) {
        $events = $eventService->searchEvents($keyword, $category);
    } else {
        $events = $eventService->getAllEvents();
    }
} else {
    // Guest View: Show LATEST 6 events to ensure content exists (fallback if no upcoming)
    // We prefer upcoming if available, but for landing page "fullness" we might just show latest.
    // Let's grab all upcoming first
    $events = $eventService->getUpcomingEvents(6);
    // If fewer than 3 events, fallback to just get latest 6 regardless of date
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
    <title>EventKu - Jelajahi Event Kampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/landing.css?v=<?= time() ?>">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-landing <?= $isLoggedIn ? 'bg-white shadow-sm' : '' ?>">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Event<span class="text-primary">Ku.</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2 <?= $isLoggedIn ? 'text-dark' : 'text-white' ?>"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link <?= $isLoggedIn ? 'text-dark' : '' ?>" href="index.php">Jelajahi</a></li>
                    <li class="nav-item"><a class="nav-link <?= $isLoggedIn ? 'text-dark' : '' ?>" href="#stats">Statistik</a></li>
                    <?php if (!$isLoggedIn): ?>
                       <li class="nav-item"><a class="nav-link" href="#features">Fitur</a></li>
                       <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a class="nav-link text-dark" href="my-events.php">Event Saya</a></li>
                        <li class="nav-item"><a class="nav-link text-dark" href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex gap-2">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-nav-light rounded-pill px-4 dropdown-toggle border" type="button"
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

    <div class="main-content" style="padding-top: <?= $isLoggedIn ? '100px' : '0' ?>;">
        
        <?php if (!$isLoggedIn): ?>
            <!-- ================= GUEST LANDING PAGE ================= -->
            
            <!-- 1. Hero Carousel -->
            <div id="heroCarousel" class="carousel slide hero-carousel mx-3 mt-3 shadow-lg" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner">
                    <!-- Slide 1 -->
                    <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
                        <div class="carousel-caption-custom">
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-bold shadow-sm">✨ Upgrade Skillmu</span>
                            <h1 class="display-4 fw-bold text-white mb-3">Temukan Event Kampus Terbaik Disini</h1>
                            <p class="lead text-white-50 mb-4">Platform satu pintu untuk seminar, workshop, dan lomba mahasiswa. Kembangkan potensimu sekarang.</p>
                            <div class="d-flex gap-3">
                                <a href="register.php" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-lg hover-lift-lg">Daftar Sekarang</a>
                                <a href="#events" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold hover-lift-lg">Lihat Event</a>
                            </div>
                        </div>
                    </div>
                    <!-- Slide 2 -->
                    <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
                         <div class="carousel-caption-custom">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 mb-3 fw-bold shadow-sm">🚀 Komunitas Aktif</span>
                            <h1 class="display-4 fw-bold text-white mb-3">Bangun Jaringan & Prestasi Mahasiswa</h1>
                            <p class="lead text-white-50 mb-4">Bergabung dengan ribuan mahasiswa lainnya untuk berkolaborasi dan berkompetisi di tingkat nasional.</p>
                            <a href="#features" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold text-dark shadow-lg hover-lift-lg">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                    <!-- Slide 3 -->
                     <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
                         <div class="carousel-caption-custom">
                            <span class="badge bg-success text-white rounded-pill px-3 py-2 mb-3 fw-bold shadow-sm">🎓 Sertifikat Resmi</span>
                            <h1 class="display-4 fw-bold text-white mb-3">Dapatkan Pengakuan Akademik</h1>
                            <p class="lead text-white-50 mb-4">Ikuti event bersertifikat untuk menunjang portofolio karir masa depanmu.</p>
                            <a href="register.php" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow-lg hover-lift-lg">Mulai Karirmu</a>
                        </div>
                    </div>
                </div>
                <!-- Controls -->
                 <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-3 bg-opacity-25"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-3 bg-opacity-25"></span>
                </button>
            </div>

            <!-- 2. Partners Logo Strip -->
            <div class="py-4 bg-white border-bottom">
                <div class="container">
                    <p class="text-center text-muted small fw-bold text-uppercase ls-1 mb-4">Dipercaya oleh Komunitas Kampus</p>
                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-5 opacity-50">
                        <h4 class="fw-bold mb-0 text-secondary"><i class="bi bi-building"></i> Univ. Indonesia</h4>
                        <h4 class="fw-bold mb-0 text-secondary"><i class="bi bi-mortarboard"></i> ITB Bandung</h4>
                        <h4 class="fw-bold mb-0 text-secondary"><i class="bi bi-globe"></i> UGM Yogyakarta</h4>
                        <h4 class="fw-bold mb-0 text-secondary"><i class="bi bi-cpu"></i> ITS Surabaya</h4>
                    </div>
                </div>
            </div>

            <!-- 3. Features Section -->
            <section class="feature-section bg-light py-5" id="features">
                <div class="container py-5">
                    <div class="text-center mb-5" data-aos="fade-up">
                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">Kenapa EventKu?</span>
                        <h2 class="fw-bold display-6 mb-3">Platform Event #1 Mahasiswa</h2>
                        <p class="text-muted lead mx-auto" style="max-width: 600px;">Solusi lengkap manajemen kegiatan kampus yang terintegrasi, aman, dan mudah digunakan.</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                            <div class="feature-card p-4 h-100 bg-white shadow-sm rounded-4 border">
                                <div class="feature-icon-wrapper mb-4">
                                    <i class="bi bi-lightning-charge-fill fs-2 text-primary"></i>
                                </div>
                                <h4 class="fw-bold mb-3">Pendaftaran Kilat</h4>
                                <p class="text-muted mb-0">Daftar event dalam hitungan detik. Tanpa ribet, langsung terkonfirmasi otomatis oleh sistem.</p>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                             <div class="feature-card p-4 h-100 bg-white shadow-sm rounded-4 border">
                                <div class="feature-icon-wrapper mb-4">
                                    <i class="bi bi-patch-check-fill fs-2 text-success"></i>
                                </div>
                                <h4 class="fw-bold mb-3">Valid & Terverifikasi</h4>
                                <p class="text-muted mb-0">Semua event telah melalui proses kurasi ketat oleh admin kampus untuk menjamin kualitas terbaik.</p>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                             <div class="feature-card p-4 h-100 bg-white shadow-sm rounded-4 border">
                                <div class="feature-icon-wrapper mb-4">
                                    <i class="bi bi-people-fill fs-2 text-warning"></i>
                                </div>
                                <h4 class="fw-bold mb-3">Jejaring Luas</h4>
                                <p class="text-muted mb-0">Terhubung dengan mahasiswa dari berbagai jurusan dan kampus untuk kolaborasi masa depan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

             <!-- 4. How It Works Section -->
             <section class="py-5 bg-white">
                <div class="container py-5">
                     <div class="text-center mb-5" data-aos="fade-up">
                        <h2 class="fw-bold display-6">Cara Kerja EventKu</h2>
                        <p class="text-muted">Mulai perjalanan prestasimu dalam 3 langkah mudah.</p>
                    </div>
                    <div class="row g-4 justify-content-center text-center">
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                             <div class="position-relative">
                                 <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold fs-3 mb-4 shadow" style="width: 80px; height: 80px;">1</div>
                                 <h4 class="fw-bold">Buat Akun</h4>
                                 <p class="text-muted">Datar gratis menggunakan email kamu untuk akses penuh.</p>
                             </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                             <div class="position-relative">
                                 <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold fs-3 mb-4 shadow" style="width: 80px; height: 80px;">2</div>
                                 <h4 class="fw-bold">Pilih Event</h4>
                                 <p class="text-muted">Cari dan daftar seminar atau lomba yang kamu minati.</p>
                             </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                             <div class="position-relative">
                                 <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold fs-3 mb-4 shadow" style="width: 80px; height: 80px;">3</div>
                                 <h4 class="fw-bold">Raih Prestasi</h4>
                                 <p class="text-muted">Ikuti acaranya, dapatkan ilmu, dan klaim sertifikatmu.</p>
                             </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 5. Testimonials (Scrolling Marquee) -->
             <section class="py-5 bg-light overflow-hidden">
                <div class="container-fluid">
                    <div class="text-center mb-5">
                         <span class="text-primary fw-bold text-uppercase small ls-1">Testimoni</span>
                         <h2 class="fw-bold mb-0">Kata Mahasiswa</h2>
                    </div>
                    <!-- Marquee Container -->
                    <div class="testimonial-scroll-container">
                        <div class="testimonial-track">
                            <!-- Items (Repeated for infinite scroll illusion) -->
                            <?php 
                            $testimonials = [
                                ["name" => "Rina A.", "text" => "EventKu sangat membantu saya menemukan info lomba coding terbaru. Sangat recommended!"],
                                ["name" => "Budi S.", "text" => "Pendaftaran seminar jadi lebih gampang, gak perlu antri manual lagi. Sertifikat juga langsung dapet."],
                                ["name" => "Siti M.", "text" => "Platform terbaik buat cari kegiatan produktif di sela-sela kuliah. UI-nya juga bagus banget."],
                                ["name" => "Dimas P.", "text" => "Dapet juara 1 Hackathon berkat info dari sini. Makasih EventKu!"],
                                ["name" => "Putri L.", "text" => "Sangat user friendly, bahkan buat maba kayak aku. Suka banget fitur remindernya."]
                            ];
                            foreach(array_merge($testimonials, $testimonials) as $testi): // Duplicate for scroll
                            ?>
                            <div class="testimonial-card-premium">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;"><?= substr($testi['name'],0,1) ?></div>
                                    <h6 class="fw-bold mb-0"><?= $testi['name'] ?></h6>
                                </div>
                                <p class="text-muted mb-0 small">"<?= $testi['text'] ?>"</p>
                                <div class="mt-3 text-warning small">★★★★★</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif ($isLoggedIn): ?>
            <!-- ================= LOGGED IN USER VIEW ================= -->
            
             <div class="container mb-5">
                <div class="p-5 rounded-4 bg-primary text-white position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);">
                    <div class="position-relative z-2">
                        <h1 class="fw-bold">Halo, <?= htmlspecialchars(explode(' ', $currentUser['nama'])[0]) ?>! 👋</h1>
                        <p class="lead opacity-75">Mau cari event apa hari ini?</p>
                        
                        <form action="" method="GET" class="mt-4">
                            <div class="input-group input-group-lg bg-white p-2 rounded-pill shadow-sm" style="max-width: 600px;">
                                <span class="input-group-text bg-transparent border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control border-0 fs-6" placeholder="Cari seminar, workshop..." value="<?= htmlspecialchars($keyword) ?>">
                                <button class="btn btn-primary rounded-pill px-4" type="submit">Cari</button>
                            </div>
                        </form>
                    </div>
                             <!-- Decorative Circle -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                </div>
            </div>
            
        <?php endif; ?>

        <!-- Stats Section (Global) -->
        <div class="stats-container py-5" id="stats">
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

        <!-- Events Section -->
        <div class="container pb-5" id="events">
            
            <?php if ($isLoggedIn): ?>
                <!-- Category Filter for Users -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Jelajahi Event</h3>
                </div>
                
                <div class="category-pills-container mb-4">
                    <div class="category-pills">
                        <a href="index.php" class="cat-pill <?= empty($category) ? 'active' : '' ?>">Semua</a>
                        <a href="index.php?kategori=Akademik" class="cat-pill <?= $category === 'Akademik' ? 'active' : '' ?>">🎓 Akademik</a>
                        <a href="index.php?kategori=Non-Akademik" class="cat-pill <?= $category === 'Non-Akademik' ? 'active' : '' ?>">⚽ Non-Akademik</a>
                        <a href="index.php?kategori=Seminar" class="cat-pill <?= $category === 'Seminar' ? 'active' : '' ?>">🎤 Seminar</a>
                        <a href="index.php?kategori=Workshop" class="cat-pill <?= $category === 'Workshop' ? 'active' : '' ?>">🛠️ Workshop</a>
                        <a href="index.php?kategori=Lomba" class="cat-pill <?= $category === 'Lomba' ? 'active' : '' ?>">🏆 Lomba</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Header for Guests -->
                <div class="text-center mb-5">
                    <h2 class="fw-bold display-6">Event Terbaru</h2>
                    <p class="text-muted">Ikuti event terbaru yang paling banyak diminati.</p>
                </div>
            <?php endif; ?>

            <!-- Events Grid -->
            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <img src="https://img.freepik.com/free-vector/no-data-concept-illustration_114360-536.jpg"
                        alt="No Events" style="max-width: 300px; opacity: 0.8; mix-blend-mode: multiply;">
                    <h5 class="text-muted mt-4 fw-normal">Belum ada event yang sesuai kriteria Anda.</h5>
                    <a href="index.php" class="btn btn-primary mt-3 rounded-pill px-4">Lihat Semua Event</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($events as $index => $event):
                        $isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $event['id']) : false;
                        $availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);
                        
                        $patternClass = 'pattern-seminar';
                        if ($event['kategori'] == 'Akademik') $patternClass = 'pattern-academic';
                        if ($event['kategori'] == 'Non-Akademik') $patternClass = 'pattern-non-academic';
                        ?>
                        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                            <div class="modern-card h-100 d-flex flex-column">
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

                                <div class="card-content flex-grow-1 d-flex flex-column">
                                    <div class="event-date-modern">
                                        <i class="bi bi-calendar4-week"></i>
                                        <?= date('d M Y, H:i', strtotime($event['tanggal'])) ?>
                                    </div>
                                    <h3 class="event-title-modern mb-2">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h3>
                                    <div class="event-location-modern mb-3">
                                        <i class="bi bi-geo-alt"></i>
                                        <?= htmlspecialchars($event['lokasi']) ?>
                                    </div>
                                    <p class="event-description-modern flex-grow-1">
                                        <?= htmlspecialchars(substr(strip_tags($event['deskripsi']), 0, 100)) ?>...
                                    </p>

                                    <div class="card-footer-modern mt-auto pt-3 border-top">
                                        <div class="participants-info">
                                            <span class="participants-count">
                                                <i class="bi bi-people-fill me-1 text-primary"></i>
                                                <?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?>
                                            </span>
                                        </div>

                                        <?php if (!$isLoggedIn): ?>
                                             <a href="login.php" class="btn-card-action btn-card-primary">
                                                Login untuk Detail
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
                                            <a href="event-detail.php?id=<?= $event['id'] ?>" class="btn-card-action btn-card-primary">
                                                Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!$isLoggedIn && count($events) >= 6): ?>
                    <div class="text-center mt-5 mb-5">
                         <div class="p-4 bg-light rounded-4 border border-dashed">
                            <h4>Ingin melihat lebih banyak event?</h4>
                            <p class="text-muted">Masuk atau daftar sekarang untuk mengakses semua event yang tersedia.</p>
                            <a href="register.php" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Daftar Akun Gratis</a>
                         </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$isLoggedIn): ?>
                <!-- 6. FAQ Section -->
                <section class="py-5" id="faq">
                    <div class="container py-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h2 class="fw-bold display-6">Pertanyaan Umum (FAQ)</h2>
                                    <p class="text-muted">Jawaban untuk pertanyaan yang sering diajukan</p>
                                </div>
                                <div class="accordion accordion-flush accordion-premium shadow-sm rounded-4 bg-white p-3" id="faqAccordion">
                                    <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                Apakah mendaftar di EventKu gratis?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-muted">
                                                Ya! Pendaftaran akun di EventKu 100% gratis. Untuk event, sebagian besar gratis namun ada beberapa event workshop atau seminar khusus yang mungkin berbayar sesuai kebijakan penyelenggara.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                Bagaimana cara mendapatkan sertifikat?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-muted">
                                                Setelah kamu menyelesaikan event (hadir dan mengikuti sampai akhir), sertifikat akan otomatis tersedia di menu "Tiket Saya" atau dikirimkan melalui email yang terdaftar.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                Apakah saya bisa membatalkan pendaftaran?
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body text-muted">
                                                Tentu. Jika kamu berhalangan hadir, kamu bisa membatalkan pendaftaran melalui menu "Event Saya" agar slotmu bisa diberikan kepada peserta lain yang membutuhkan.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 7. Final CTA -->
                <section class="py-5">
                    <div class="cta-premium-wrapper p-5 text-center text-white position-relative container">
                        <div class="cta-glow-effect"></div>
                        <div class="position-relative z-2">
                             <h2 class="fw-bold display-5 mb-3">Siap Mengembangkan Diri?</h2>
                             <p class="lead mb-4 opacity-75">Jangan lewatkan kesempatan untuk belajar dan berprestasi.</p>
                             <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary shadow hover-lift-lg">Mulai Sekarang - Gratis</a>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

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