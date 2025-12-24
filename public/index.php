<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/events/EventService.php';
require_once __DIR__ . '/../modules/registrations/RegistrationService.php';

$auth = new Auth();
$eventService = new EventService();
$registrationService = new RegistrationService();

// Redirect if not logged in
if (!$auth->isLoggedIn()) {
    header('Location: landing.php');
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelajahi Event - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --card-gradient: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
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
            transition: all 0.3s ease;
        }

        /* Hero Wrapper */
        .hero-section {
            background: var(--primary-gradient);
            border-radius: 20px;
            padding: 3rem 2rem;
            color: white;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.5);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .search-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 0.5rem;
            max-width: 600px;
            margin-top: 2rem;
            display: flex;
            align-items: center;
        }

        .search-input {
            background: transparent;
            border: none;
            color: white;
            padding: 0.75rem 1rem;
            width: 100%;
            font-size: 1rem;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            outline: none;
        }

        .search-btn {
            background: white;
            color: #4f46e5;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .search-btn:hover {
            background: #f3f4f6;
            transform: translateY(-1px);
        }

        /* Event Card */
        .event-card {
            background: var(--card-gradient);
            border: 1px solid rgba(229, 231, 235, 0.5);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.1);
        }

        .card-header-visual {
            height: 140px;
            background: linear-gradient(45deg, #f3f4f6 0%, #e5e7eb 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-pattern-1 {
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
        }

        .card-pattern-2 {
            background: linear-gradient(120deg, #fccb90 0%, #d57eeb 100%);
        }

        .card-pattern-3 {
            background: linear-gradient(120deg, #e0c3fc 0%, #8ec5fc 100%);
        }

        .category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .price-badge {
            position: absolute;
            bottom: -15px;
            right: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .price-free {
            background: #374151;
            color: white;
        }

        .price-paid {
            background: #10b981;
            color: white;
        }

        .card-body {
            padding: 1.5rem;
            padding-top: 1.5rem;
        }

        .event-date {
            color: #6366f1;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .event-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .event-location {
            color: #6b7280;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .event-location i {
            margin-right: 0.5rem;
            color: #9ca3af;
        }

        .event-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }

        .quota-info {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }

        .btn-action {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .hero-section {
                padding: 2rem 1.5rem;
            }

            .search-container {
                flex-direction: column;
                padding: 0.75rem;
            }

            .search-input {
                margin-bottom: 0.75rem;
                text-align: center;
            }

            .search-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Hero Section -->
        <div class="hero-section text-center text-lg-start">
            <div class="position-relative z-2">
                <h1 class="display-5 fw-bold mb-3">Jelajahi Event Seru! 🚀</h1>
                <p class="fs-5 opacity-90 mb-4" style="max-width: 600px;">
                    Temukan dan ikuti berbagai kegiatan menarik di kampus. Mulai dari seminar, workshop, hingga
                    kompetisi.
                </p>
                
                <form action="" method="GET" class="search-container">
                    <i class="bi bi-search text-white opacity-75 ms-2 d-none d-lg-block"></i>
                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="kategori" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <input type="text" name="q" class="search-input" placeholder="Cari event menarik..." value="<?= htmlspecialchars($keyword) ?>">
                    <button type="submit" class="search-btn">Cari</button>
                </form>
            </div>
        </div>

        <div class="container-fluid px-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <?php if (!empty($keyword)): ?>
                        Hasil pencarian: "<?= htmlspecialchars($keyword) ?>"
                    <?php elseif (!empty($category)): ?>
                        Kategori: <?= htmlspecialchars($category) ?>
                    <?php else: ?>
                        Event Terbaru
                    <?php endif; ?>
                </h4>
                <div class="dropdown">
                    <button class="btn btn-white border shadow-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-filter me-1"></i> 
                        <?= !empty($category) ? htmlspecialchars($category) : 'Filter Kategori' ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="index.php<?= !empty($keyword) ? '?q='.urlencode($keyword) : '' ?>">Semua Kategori</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=Akademik<?= !empty($keyword) ? '&q='.urlencode($keyword) : '' ?>">Akademik</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=Non-Akademik<?= !empty($keyword) ? '&q='.urlencode($keyword) : '' ?>">Non-Akademik</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=Seminar<?= !empty($keyword) ? '&q='.urlencode($keyword) : '' ?>">Seminar</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=Workshop<?= !empty($keyword) ? '&q='.urlencode($keyword) : '' ?>">Workshop</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=Lomba<?= !empty($keyword) ? '&q='.urlencode($keyword) : '' ?>">Lomba</a></li>
                    </ul>
                </div>
            </div>

            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-calendar-x display-1 text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted fw-normal">Belum ada event yang tersedia saat ini.</h5>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php
                    $patterns = ['card-pattern-1', 'card-pattern-2', 'card-pattern-3'];
                    $i = 0;
                    foreach ($events as $event):
                        $isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $event['id']) : false;
                        $availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);
                        $patternClass = $patterns[$i % 3];
                        $i++;
                        ?>
                        <div class="col-md-6 col-lg-4 col-xl-4">
                            <div class="event-card">
                                <div class="card-header-visual <?= $patternClass ?>">
                                    <span class="category-badge">
                                        <i class="bi bi-tag-fill me-1 text-primary"></i>
                                        <?= htmlspecialchars($event['kategori']) ?>
                                    </span>
                                    <i class="bi bi-calendar-event fs-1 text-white opacity-50"></i>

                                    <!-- Price Badge Floating -->
                                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                        <div class="price-badge price-paid">
                                            Rp <?= number_format($event['price'], 0, ',', '.') ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="price-badge price-free">
                                            Gratis
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body">
                                    <span class="event-date">
                                        <?= date('l, d M Y • H:i', strtotime($event['tanggal'])) ?>
                                    </span>
                                    <h5 class="event-title text-truncate" title="<?= htmlspecialchars($event['title']) ?>">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h5>
                                    <div class="event-location">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span class="text-truncate"><?= htmlspecialchars($event['lokasi']) ?></span>
                                    </div>
                                    <p class="text-muted small mb-0"
                                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.6em;">
                                        <?= htmlspecialchars(strip_tags($event['deskripsi'])) ?>
                                    </p>
                                </div>

                                <div class="event-footer">
                                    <div class="quota-info">
                                        <i class="bi bi-people me-1"></i>
                                        <?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?> Peserta
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="event-detail.php?id=<?= $event['id'] ?>"
                                            class="btn btn-action btn-outline-primary">
                                            Detail
                                        </a>

                                        <?php if ($auth->isLoggedIn() && !$isRegistered && $availableQuota > 0): ?>
                                            <a href="register-event.php?id=<?= $event['id'] ?>" class="btn btn-action btn-primary">
                                                Daftar
                                            </a>
                                        <?php elseif ($isRegistered): ?>
                                            <button class="btn btn-action btn-success disabled border-0" style="opacity: 0.8;">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php elseif ($availableQuota <= 0): ?>
                                            <button class="btn btn-action btn-secondary disabled">
                                                Penuh
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>