<?php
session_start();
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';

$auth = new Auth();
$eventService = new EventService();
$upcomingEvents = $eventService->getUpcomingEvents(6); // Ambil 6 event terdekat

// Redirect jika sudah login
if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EventKu - Platform Manajemen Event Mahasiswa Modern">
    <title>EventKu - Empowering Student Events</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #f59e0b;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.3);
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        /* Glassmorphism Generic */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
        }

        /* Navbar */
        .navbar {
            padding: 1.2rem 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
        }

        .navbar.scrolled {
            padding: 0.8rem 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--primary) !important;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 600;
            margin: 0 0.5rem;
            color: var(--text-dark) !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        /* Hero Section */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: url('img/landing-hero.png') center center no-repeat;
            background-size: cover;
            padding: 120px 0 100px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.4));
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .hero h1 span {
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin-bottom: 2.5rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        /* Buttons */
        .btn-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 28px;
            border-radius: 100px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border: none;
            color: white;
            padding: 14px 32px;
            border-radius: 100px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
            transition: all 0.3s;
        }

        .btn-primary-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.5);
            color: white;
        }

        /* Upcoming Events Section */
        .section-tag {
            text-transform: uppercase;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 2px;
            color: var(--primary);
            margin-bottom: 1rem;
            display: inline-block;
        }

        .event-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.1);
        }

        .event-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .event-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s;
        }

        .event-card:hover .event-img {
            transform: scale(1.1);
        }

        .event-category {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .event-details {
            padding: 2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-info {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        /* Features */
        .feature-icon-wrapper {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .feature-card:hover .feature-icon-wrapper {
            background: var(--primary);
            color: white;
            transform: rotate(-5deg);
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: white;
            padding: 100px 0 50px;
        }

        .footer-logo {
            font-weight: 800;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
            display: block;
            text-decoration: none;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-link:hover {
            background: var(--primary);
            transform: translateY(-3px);
            color: white;
        }

        /* Floating elements */
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* Responsive */
        @media (max-width: 991px) {
            .navbar {
                background: white;
                padding: 1rem 0;
            }

            .hero {
                padding-top: 140px;
                text-align: center;
            }

            .hero p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-btns {
                justify-content: center;
            }
        }
        }

        /* Partners Section */
        .partner-logo {
            filter: grayscale(100%);
            opacity: 0.6;
            transition: all 0.3s;
            max-height: 40px;
        }

        .partner-logo:hover {
            filter: grayscale(0%);
            opacity: 1;
        }

        /* How It Works */
        .step-card {
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        /* Gallery */
        .gallery-item {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            height: 250px;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        /* Testimonials */
        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .quote-icon {
            position: absolute;
            top: 2rem;
            right: 2rem;
            font-size: 4rem;
            opacity: 0.1;
            color: var(--primary);
            line-height: 0;
        }

        /* FAQ */
        .accordion-button:not(.collapsed) {
            background-color: rgba(99, 102, 241, 0.1);
            color: var(--primary-dark);
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(99, 102, 241, 0.1);
        }

        .accordion-item {
            border: none;
            margin-bottom: 1rem;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand" href="#">Event<span>Ku.</span></a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#upcoming">Upcoming</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                </ul>
                <div class="d-flex gap-3 mt-3 mt-lg-0">
                    <a href="login.php" class="btn btn-glass px-4">Login</a>
                    <a href="register.php" class="btn btn-primary-gradient px-4">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-content" data-aos="fade-right" data-aos-duration="1000">
                    <span class="section-tag" style="color: #818cf8;">Smart Event Platform</span>
                    <h1>Wujudkan <span>Event Impianmu</span> <br>dengan EventKu.</h1>
                    <p>Platform manajemen event tercanggih untuk mahasiswa. Kelola, promosi, dan ikuti berbagai event
                        menarik di kampusmu hanya dalam satu aplikasi.</p>
                    <div class="d-flex flex-wrap gap-3 hero-btns">
                        <a href="register.php" class="btn btn-primary-gradient btn-lg">Ayo Mulai Gratis</a>
                        <a href="#upcoming" class="btn btn-glass btn-lg">Lihat Event</a>
                    </div>

                    <div class="mt-5 pt-4 d-none d-md-flex align-items-center gap-4">
                        <div class="avatar-group d-flex">
                            <img src="https://i.pravatar.cc/150?u=1"
                                class="rounded-circle border border-3 border-white shadow-sm"
                                style="width: 45px; height: 45px; margin-right: -15px;">
                            <img src="https://i.pravatar.cc/150?u=2"
                                class="rounded-circle border border-3 border-white shadow-sm"
                                style="width: 45px; height: 45px; margin-right: -15px;">
                            <img src="https://i.pravatar.cc/150?u=3"
                                class="rounded-circle border border-3 border-white shadow-sm"
                                style="width: 45px; height: 45px; margin-right: -15px;">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center border border-3 border-white shadow-sm"
                                style="width: 45px; height: 45px; font-size: 0.8rem; font-weight: 700;">+1k</div>
                        </div>
                        <p class="mb-0 text-white-50 fw-semibold">Dipercaya oleh 1,000+ Mahasiswa Kreatif</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row g-4 text-center">
                <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <h2 class="fw-800 mb-0">500+</h2>
                    <p class="text-muted">Event Selesai</p>
                </div>
                <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="fw-800 mb-0">15+</h2>
                    <p class="text-muted">Kampus Aktif</p>
                </div>
                <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <h2 class="fw-800 mb-0">12k+</h2>
                    <p class="text-muted">Tiket Terjual</p>
                </div>
                <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                    <h2 class="fw-800 mb-0">24/7</h2>
                    <p class="text-muted">Dukungan Sistem</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners -->
    <section class="py-4 border-bottom border-light bg-white">
        <div class="container">
            <p class="text-center text-muted small fw-bold mb-4">DIPERCAYA OLEH ORGANISASI KAMPUS TERBAIK</p>
            <div class="row justify-content-center align-items-center g-5 opacity-75">
                <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="100">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/2560px-Google_2015_logo.svg.png"
                        alt="Partner 1" class="img-fluid partner-logo" style="height: 30px;">
                </div>
                <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="200">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/Microsoft_logo_%282012%29.svg/2560px-Microsoft_logo_%282012%29.svg.png"
                        alt="Partner 2" class="img-fluid partner-logo" style="height: 30px;">
                </div>
                <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="300">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/IBM_logo.svg/2560px-IBM_logo.svg.png"
                        alt="Partner 3" class="img-fluid partner-logo" style="height: 25px;">
                </div>
                <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="400">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Tata_Consultancy_Services_Logo.svg/2560px-Tata_Consultancy_Services_Logo.svg.png"
                        alt="Partner 4" class="img-fluid partner-logo" style="height: 25px;">
                </div>
                <div class="col-6 col-md-2 text-center" data-aos="fade-in" data-aos-delay="500">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Amazon_logo.svg/2560px-Amazon_logo.svg.png"
                        alt="Partner 5" class="img-fluid partner-logo" style="height: 25px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section class="py-5" id="upcoming" style="background-color: #f1f5f9;">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div data-aos="fade-up">
                    <span class="section-tag">Feature Events</span>
                    <h2 class="display-6 fw-800 mb-0">Jangan Lewatkan <br><span>Event Seru Kita.</span></h2>
                </div>
                <a href="login.php" class="btn btn-outline-primary rounded-pill px-4 d-none d-md-block"
                    data-aos="fade-left">Lihat Semua</a>
            </div>

            <div class="row g-4">
                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach ($upcomingEvents as $index => $event): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($index + 1) * 100 ?>">
                            <div class="event-card">
                                <div class="event-img-wrapper">
                                    <img src="<?= !empty($event['image']) ? 'uploads/' . $event['image'] : 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' ?>"
                                        class="event-img" alt="<?= htmlspecialchars($event['title']) ?>">
                                    <div class="event-category"><?= htmlspecialchars($event['kategori'] ?? 'Umum') ?></div>
                                </div>
                                <div class="event-details">
                                    <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                                    <div class="event-info">
                                        <i class="bi bi-calendar-check text-primary"></i>
                                        <span><?= date('d M Y', strtotime($event['tanggal'])) ?></span>
                                    </div>
                                    <div class="event-info">
                                        <i class="bi bi-geo-alt text-danger"></i>
                                        <span><?= htmlspecialchars($event['lokasi']) ?></span>
                                    </div>
                                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span
                                            class="fw-bold text-primary"><?= ($event['price'] > 0) ? 'Rp ' . number_format($event['price'], 0, ',', '.') : 'GRATIS' ?></span>
                                        <a href="login.php" class="btn btn-primary-gradient btn-sm px-4">Detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Belum ada event mendatang saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-5 d-md-none">
                <a href="login.php" class="btn btn-outline-primary rounded-pill px-5">Lihat Semua</a>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">Galeri Kegiatan</span>
                <h2 class="display-6 fw-800">Keseruan <span class="text-primary">Event Kami.</span></h2>
            </div>

            <div class="row g-3">
                <div class="col-md-8" data-aos="fade-right">
                    <div class="gallery-item" style="height: 400px;">
                        <img src="https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80"
                            alt="Gallery 1">
                        <div class="gallery-overlay">
                            <div class="text-white">
                                <h5 class="fw-bold mb-1">Seminar Nasional Teknologi</h5>
                                <p class="small mb-0">Auditorium Utama • 500 Peserta</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row g-3">
                        <div class="col-12" data-aos="fade-left" data-aos-delay="100">
                            <div class="gallery-item" style="height: 192px;">
                                <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                    alt="Gallery 2">
                                <div class="gallery-overlay">
                                    <div class="text-white">
                                        <h5 class="fw-bold mb-1">Workshop UI/UX</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" data-aos="fade-left" data-aos-delay="200">
                            <div class="gallery-item" style="height: 192px;">
                                <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                                    alt="Gallery 3">
                                <div class="gallery-overlay">
                                    <div class="text-white">
                                        <h5 class="fw-bold mb-1">Music Festival</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5" id="fitur">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5" data-aos="fade-right">
                    <span class="section-tag">Powerful Features</span>
                    <h2 class="display-6 fw-800 mb-4">Segala Sesuatunya <br>Didesain Untuk <span>Efisiensi.</span></h2>
                    <p class="text-muted mb-4">Kami menyediakan tools lengkap untuk membantu organisasi kampus mengelola
                        event dari tahap perencanaan hingga laporan akhir secara profesional.</p>

                    <ul class="list-unstyled mb-5">
                        <li class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-600">Terintegrasi Google Calendar</span>
                        </li>
                        <li class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-600">Pengelolaan Peserta Real-time</span>
                        </li>
                        <li class="d-flex align-items-center gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="fw-600">Dashboard Analitik Interaktif</span>
                        </li>
                    </ul>

                    <a href="register.php" class="btn btn-primary-gradient px-4 py-3">Coba Sekarang</a>
                </div>
                <div class="col-lg-7">
                    <div class="row g-4">
                        <div class="col-sm-6" data-aos="zoom-in" data-aos-delay="100">
                            <div class="feature-card glass-card p-4 h-100">
                                <div class="feature-icon-wrapper">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <h4 class="fw-bold">Aman</h4>
                                <p class="text-muted small mb-0">Data peserta dan organisasi dilindungi oleh sistem
                                    enkripsi tingkat tinggi.</p>
                            </div>
                        </div>
                        <div class="col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                            <div class="feature-card glass-card p-4 h-100">
                                <div class="feature-icon-wrapper" style="color: #ec4899;">
                                    <i class="bi bi-lightning-charge"></i>
                                </div>
                                <h4 class="fw-bold">Cepat</h4>
                                <p class="text-muted small mb-0">Proses pendaftaran hanya dalam hitungan detik. Tanpa
                                    ribet, tanpa antre.</p>
                            </div>
                        </div>
                        <div class="col-sm-6" data-aos="zoom-in" data-aos-delay="300">
                            <div class="feature-card glass-card p-4 h-100">
                                <div class="feature-icon-wrapper" style="color: #10b981;">
                                    <i class="bi bi-megaphone"></i>
                                </div>
                                <h4 class="fw-bold">Promosi</h4>
                                <p class="text-muted small mb-0">Jangkau lebih banyak mahasiswa dengan sistem notifikasi
                                    pintar kami.</p>
                            </div>
                        </div>
                        <div class="col-sm-6" data-aos="zoom-in" data-aos-delay="400">
                            <div class="feature-card glass-card p-4 h-100">
                                <div class="feature-icon-wrapper" style="color: #f59e0b;">
                                    <i class="bi bi-app-indicator"></i>
                                </div>
                                <h4 class="fw-bold">Mudah</h4>
                                <p class="text-muted small mb-0">Interface yang intuitif, didesain khusus agar mudah
                                    dipelajari siapapun.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">Alur Sistem</span>
                <h2 class="display-6 fw-800">Mulai dalam <span class="text-primary">4 Langkah.</span></h2>
            </div>

            <div class="row g-4 position-relative">
                <!-- Connector Line (Desktop only) -->
                <div class="d-none d-lg-block position-absolute top-0 start-0 w-100 h-100" style="z-index: 0;">
                    <div style="border-top: 2px dashed #e2e8f0; position: relative; top: 40px; margin: 0 50px;"></div>
                </div>

                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-card text-center bg-white p-3">
                        <div class="step-number mx-auto">1</div>
                        <h4 class="fw-bold mb-3">Daftar Akun</h4>
                        <p class="text-muted">Buat akun menggunakan email kampusmu untuk verifikasi otomatis.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-card text-center bg-white p-3">
                        <div class="step-number mx-auto">2</div>
                        <h4 class="fw-bold mb-3">Pilih Event</h4>
                        <p class="text-muted">Cari event yang sesuai dengan minat dan bakatmu di katalog kami.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-card text-center bg-white p-3">
                        <div class="step-number mx-auto">3</div>
                        <h4 class="fw-bold mb-3">Registrasi</h4>
                        <p class="text-muted">Daftar event dengan satu klik dan dapatkan tiket digitalmu.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-card text-center bg-white p-3">
                        <div class="step-number mx-auto">4</div>
                        <h4 class="fw-bold mb-3">Ikuti & Sertifikat</h4>
                        <p class="text-muted">Hadir di acara, check-in, dan dapatkan e-sertifikat langsung.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5" style="background-color: #f8fafc;">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="section-tag">Testimoni</span>
                <h2 class="display-6 fw-800">Kata <span class="text-primary">Mahasiswa.</span></h2>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card h-100">
                        <i class="bi bi-quote quote-icon"></i>
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://i.pravatar.cc/150?u=a042581f4e29026024d" class="rounded-circle me-3"
                                width="60" alt="User">
                            <div>
                                <h5 class="fw-bold mb-0">Sarah Wijaya</h5>
                                <p class="text-muted small mb-0">Informatika '22</p>
                            </div>
                        </div>
                        <p class="text-muted mb-0">"Platform ini sangat membantu saya mencari info lomba dan seminar.
                            Dulu harus cari satu-satu di IG, sekarang semua ada di sini!"</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card h-100">
                        <i class="bi bi-quote quote-icon"></i>
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://i.pravatar.cc/150?u=a04258a2462d826712d" class="rounded-circle me-3"
                                width="60" alt="User">
                            <div>
                                <h5 class="fw-bold mb-0">Budi Santoso</h5>
                                <p class="text-muted small mb-0">Ketua Hima Manajemen</p>
                            </div>
                        </div>
                        <p class="text-muted mb-0">"Sebagai panitia, EventKu memudahkan kami mendata peserta. Fitur
                            export datanya sangat berguna untuk laporan pertanggungjawaban."</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card h-100">
                        <i class="bi bi-quote quote-icon"></i>
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://i.pravatar.cc/150?u=a042581f4e29026704d" class="rounded-circle me-3"
                                width="60" alt="User">
                            <div>
                                <h5 class="fw-bold mb-0">Rina Aulia</h5>
                                <p class="text-muted small mb-0">Sastra Inggris '23</p>
                            </div>
                        </div>
                        <p class="text-muted mb-0">"Suka banget sama UI-nya yang modern dan gampang dipake. Notifikasi
                            event-nya juga ngebantu banget biar ga ketinggalan info."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5" data-aos="fade-up">
                        <span class="section-tag">FAQ</span>
                        <h2 class="display-6 fw-800">Pertanyaan <span class="text-primary">Umum.</span></h2>
                    </div>

                    <div class="accordion" id="faqAccordion" data-aos="fade-up">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq1">
                                    <strong>Apakah mendaftar di EventKu gratis?</strong>
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Ya! Pendaftaran akun di EventKu 100% gratis untuk seluruh mahasiswa aktif. Anda
                                    hanya perlu membayar jika mendaftar pada event yang berbayar.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq2">
                                    <strong>Bagaimana cara mendapatkan sertifikat?</strong>
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Sertifikat akan tersedia di menu "Event Saya" setelah Anda menyelesaikan event dan
                                    status kehadiran dikonfirmasi oleh panitia.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq3">
                                    <strong>Apakah saya bisa membatalkan pendaftaran?</strong>
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Bisa. Anda dapat membatalkan pendaftaran melalui halaman detail event
                                    selambat-lambatnya H-1 sebelum acara dimulai.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq4">
                                    <strong>Bagaimana jika saya lupa password?</strong>
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Anda dapat menggunakan fitur "Lupa Password" di halaman login. Link reset password
                                    akan dikirimkan ke email terdaftar Anda.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5">
        <div class="container py-5">
            <div class="glass-card p-5 text-center text-white"
                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);" data-aos="zoom-in">
                <h2 class="display-5 fw-800 mb-3">Siap Menjadi Bagian Dari Kami?</h2>
                <p class="lead mb-5 opacity-90">Ribuan mahasiswa sudah merasakan kemudahan mengelola event kampus.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="register.php"
                        class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary">Daftar Sekarang</a>
                    <a href="login.php" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4">
                    <a href="#" class="footer-logo">EventKu<span>.</span></a>
                    <p class="text-white-50 mb-4">Satu-satunya platform manajemen event kampus yang kamu butuhkan untuk
                        produktivitas organisasi yang maksimal.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-github"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><a href="#home" class="text-reset text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#fitur" class="text-reset text-decoration-none">Fitur</a></li>
                        <li class="mb-2"><a href="#upcoming" class="text-reset text-decoration-none">Upcoming</a></li>
                        <li class="mb-2"><a href="login.php" class="text-reset text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="fw-bold mb-3">Produk</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><a href="#" class="text-reset text-decoration-none">Pricing</a></li>
                        <li class="mb-2"><a href="#" class="text-reset text-decoration-none">Integrasi</a></li>
                        <li class="mb-2"><a href="#" class="text-reset text-decoration-none">API Docs</a></li>
                        <li class="mb-2"><a href="#" class="text-reset text-decoration-none">Status</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="fw-bold mb-3">Newsletter</h5>
                    <p class="text-white-50 small mb-4">Dapatkan update event terbaru langsung di email kamu.</p>
                    <div class="input-group">
                        <input type="text" class="form-control border-0 px-3" placeholder="Email kamu"
                            style="background: rgba(255,255,255,0.05); color: white; border-radius: 12px 0 0 12px;">
                        <button class="btn btn-primary" style="border-radius: 0 12px 12px 0;"><i
                                class="bi bi-send"></i></button>
                    </div>
                </div>
            </div>
            <hr class="my-5" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center text-white-50 small">
                <p>&copy; <?= date('Y') ?> EventKu Platform. Built for Students with ❤️</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>