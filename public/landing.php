<?php
session_start();
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';

$auth = new Auth();
$eventService = new EventService();
$upcomingEvents = $eventService->getUpcomingEvents(3); // Ambil 3 event terdekat

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
    <meta name="description" content="Kelola event kampus dengan mudah menggunakan EventKu - Platform Manajemen Event Mahasiswa">
    <title>EventKu - Platform Manajemen Event Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #2e59d9;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            color: #2d3748;
            line-height: 1.7;
        }

        /* Navbar */
        .navbar {
            padding: 1.5rem 0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        
        .navbar.scrolled {
            padding: 0.8rem 0;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.8rem;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1.2rem !important;
            color: var(--dark) !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            color: var(--primary) !important;
            font-weight: 600;
        }
        
        /* Modern Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 120px 0;
            color: white;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 25%),
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 25%);
            animation: float 6s ease-in-out infinite;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
            padding-top: 60px;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero h1 span {
            color: #f6c23e;
            position: relative;
            display: inline-block;
        }
        
        .hero h1 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 10px;
            background: rgba(246, 194, 62, 0.3);
            z-index: -1;
            border-radius: 5px;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease;
        }

        .hero-buttons {
            animation: fadeInUp 1.2s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            color: white;
        }

        /* Stats Section */
        .stats {
            padding: 60px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: white;
        }

        .cta-content {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        /* Buttons */
        .btn-custom {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-custom::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary);
            z-index: -2;
            border-radius: 50px;
        }
        
        .btn-custom::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background-color: var(--primary-dark);
            transition: all 0.3s;
            border-radius: 50px;
            z-index: -1;
        }
        
        .btn-custom:hover::before {
            width: 100%;
        }

        .btn-primary-custom {
            background: white;
            color: var(--primary);
            border: 2px solid white;
        }

        .btn-primary-custom:hover {
            background: transparent;
            color: white;
            border-color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline-custom:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-lg-custom {
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="landing.php">
                <i class="bi bi-calendar-event"></i>EventKu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#event">Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a href="login.php" class="btn btn-outline-primary btn-custom">Masuk</a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="register.php" class="btn btn-primary btn-custom">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill mb-3 d-inline-block">Platform Event Mahasiswa</span>
                        <h1 class="display-4 fw-bold mb-4">
                            Kelola <span>Event Kampus</span> <br>
                            <span style="color: #63b3ed">Lebih Mudah</span> dan Efisien
                        </h1>
                        <p class="lead mb-4">
                            Platform manajemen event terintegrasi untuk mahasiswa dan organisasi kampus. 
                            Buat, kelola, dan ikuti event dengan lebih efisien.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mb-5">
                            <a href="register.php" class="btn btn-primary btn-lg px-4 py-3 fw-semibold" style="background-color: #3182ce; border-color: #3182ce;">
                                <i class="bi bi-rocket-takeoff me-2"></i>Mulai Sekarang
                            </a>
                            <a href="#fitur" class="btn btn-outline-light btn-lg px-4 py-3 fw-semibold" style="border-color: #fff; color: #fff;">
                                <i class="bi bi-play-circle me-2"></i>Lihat Demo
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="d-flex">
                                <div class="avatar-group">
                                    <div class="avatar" style="margin-right: -15px;">
                                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="rounded-circle border border-3 border-white" style="width: 40px; height: 40px; object-fit: cover;">
                                    </div>
                                    <div class="avatar" style="margin-right: -15px;">
                                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="rounded-circle border border-3 border-white" style="width: 40px; height: 40px; object-fit: cover;">
                                    </div>
                                    <div class="avatar">
                                        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="User" class="rounded-circle border border-3 border-white" style="width: 40px; height: 40px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="mb-0 fw-semibold">10.000+ Pengguna</p>
                                <p class="text-white-50 small mb-0">Bergabung dari berbagai kampus</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image text-center">
                        <img src="https://img.freepik.com/free-vector/event-management-concept-illustration_114360-9317.jpg" alt="Event Management System" class="img-fluid" style="max-height: 500px; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120">
                <path fill="#ffffff" fill-opacity="1" d="M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,90.7C672,85,768,107,864,112C960,117,1056,107,1152,101.3C1248,96,1344,96,1392,96L1440,96L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <!-- Features Section -->
    <section class="py-5 bg-light" id="fitur">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="badge bg-primary-soft text-primary rounded-pill mb-3">Fitur Unggulan</span>
                <h2 class="display-5 fw-bold mb-3">Apa yang Kami Tawarkan</h2>
                <p class="lead text-muted mb-0">Solusi lengkap untuk manajemen event kampus Anda</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Manajemen Event</h4>
                        <p class="text-muted">
                            Buat, edit, dan kelola event dengan mudah. Sinkronisasi otomatis dengan Google Calendar.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Kelola Peserta</h4>
                        <p class="text-muted">
                            Lihat daftar peserta, kelola kuota, dan kirim notifikasi reminder secara otomatis.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Analitik & Laporan</h4>
                        <p class="text-muted">
                            Dapatkan insight lengkap dengan grafik interaktif dan export laporan ke CSV.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Notifikasi Email</h4>
                        <p class="text-muted">
                            Kirim reminder event otomatis via email kepada semua peserta yang terdaftar.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-plus"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Google Calendar</h4>
                        <p class="text-muted">
                            Integrasi langsung dengan Google Calendar untuk sinkronisasi event otomatis.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Aman & Terpercaya</h4>
                        <p class="text-muted">
                            Sistem keamanan lengkap dengan enkripsi password dan proteksi data.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 stat-item mb-4 mb-md-0">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Gratis</div>
                </div>
                <div class="col-md-3 col-6 stat-item mb-4 mb-md-0">
                    <div class="stat-number"><i class="bi bi-infinity"></i></div>
                    <div class="stat-label">Event Unlimited</div>
                </div>
                <div class="col-md-3 col-6 stat-item mb-4 mb-md-0">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Akses Kapan Saja</div>
                </div>
                <div class="col-md-3 col-6 stat-item">
                    <div class="stat-number"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-label">Mudah Digunakan</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2 class="display-5 fw-bold mb-4">Siap Memulai?</h2>
                <p class="lead text-muted mb-4">
                    Daftar sekarang dan mulai kelola event mahasiswa Anda dengan mudah dan efisien.
                </p>
                <div>
                    <a href="register.php" class="btn btn-primary btn-lg btn-custom me-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="bi bi-person-plus"></i> Daftar Gratis
                    </a>
                    <a href="login.php" class="btn btn-outline-primary btn-lg btn-custom">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3">
                        <i class="bi bi-calendar-event"></i> Event Management System
                    </h5>
                    <p class="mb-3 text-muted">
                        Sistem manajemen event untuk mahasiswa - Kelola event dengan mudah dan efisien
                    </p>
                    <div class="mb-3">
                        <a href="login.php" class="text-white me-3" style="text-decoration: none;">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a href="register.php" class="text-white" style="text-decoration: none;">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.2);">
                    <p class="mb-0 text-muted">
                        &copy; <?= date('Y') ?> Event Management System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</body>
</html>

