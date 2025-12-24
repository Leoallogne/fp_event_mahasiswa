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

$eventId = $_GET['id'] ?? 0;
$event = $eventService->getEventById($eventId);

if (!$event) {
    header('Location: index.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$isRegistered = $auth->isLoggedIn() ? $registrationService->isRegistered($currentUser['id'], $eventId) : false;
$availableQuota = $event['kuota'] - ($event['registered_count'] ?? 0);

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php if ($event['latitude'] && $event['longitude']): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <?php endif; ?>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --font-inter: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            font-family: var(--font-inter);
            color: #1f2937;
        }

        .main-content {
            margin-left: 250px;
            padding: 0;
            /* Remove padding to allow hero to be full width */
            transition: all 0.3s ease;
        }

        /* Hero Wrapper */
        .event-hero {
            background: var(--primary-gradient);
            padding: 4rem 2rem 6rem;
            /* Extra padding bottom for overlap */
            color: white;
            position: relative;
            overflow: hidden;
        }

        .event-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-badges .badge {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .event-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            text-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .content-container {
            max-width: 1200px;
            margin: -4rem auto 2rem;
            /* Negative margin to pull up */
            padding: 0 2rem;
            position: relative;
            z-index: 3;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .sticky-sidebar {
            position: sticky;
            top: 2rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f3f4f6;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 1.25rem;
        }

        .info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
            line-height: 1.4;
        }

        .map-section {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        #map {
            height: 400px;
            width: 100%;
            z-index: 1;
        }

        .btn-action-lg {
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .btn-action-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .event-hero {
                padding: 3rem 1.5rem 5rem;
            }

            .content-container {
                padding: 0 1rem;
                margin-top: -3rem;
            }

            .event-title {
                font-size: 1.75rem;
            }

            .sticky-sidebar {
                position: static;
            }
        }

        /* Keep existing map styles */
        .leaflet-popup-content {
            margin: 10px 15px;
            line-height: 1.4;
        }

        .leaflet-popup-content h3 {
            margin: 0 0 5px 0;
            font-size: 1.1em;
            color: #2c3e50;
        }

        .leaflet-popup-content p {
            margin: 5px 0;
            color: #34495e;
        }

        .map-container {
            position: relative;
        }

        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 5px;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-map {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            margin: 2px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-map:hover {
            background: #f1f3f5;
            transform: scale(1.05);
        }

        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1001;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .loading-spinner.show {
            display: block;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Hero Header -->
        <div class="event-hero">
            <div class="hero-content">
                <a href="index.php"
                    class="text-white text-decoration-none mb-4 d-inline-block opacity-75 hover-opacity-100">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Jelajahi
                </a>

                <div class="hero-badges mb-3 d-flex gap-2">
                    <span class="badge rounded-pill text-uppercase">
                        <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($event['kategori']) ?>
                    </span>
                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                        <span class="badge rounded-pill bg-warning text-dark border-0">
                            Premium
                        </span>
                    <?php else: ?>
                        <span class="badge rounded-pill bg-success border-0">
                            Gratis
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="event-title"><?= htmlspecialchars($event['title']) ?></h1>

                <div class="d-flex align-items-center text-white opacity-90 gap-4 flex-wrap">
                    <span><i
                            class="bi bi-calendar-event me-2"></i><?= date('d F Y', strtotime($event['tanggal'])) ?></span>
                    <span><i class="bi bi-clock me-2"></i><?= date('H:i', strtotime($event['tanggal'])) ?> WIB</span>
                    <span><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($event['lokasi']) ?></span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-container">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0">
                    <i class="bi bi-check-circle-fill me-2"></i> Pendaftaran berhasil! Silakan cek email Anda.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <div class="content-card">
                        <h4 class="fw-bold mb-4">Tentang Event</h4>
                        <p class="text-secondary lh-lg mb-0" style="white-space: pre-line;">
                            <?= htmlspecialchars($event['deskripsi']) ?>
                        </p>
                    </div>

                    <?php if ($event['latitude'] && $event['longitude']): ?>
                        <div class="content-card p-0 overflow-hidden">
                            <div class="p-4 border-bottom bg-light">
                                <h5 class="fw-bold mb-0"><i class="bi bi-map me-2 text-primary"></i>Lokasi Acara</h5>
                            </div>
                            <div class="map-container">
                                <div id="map"></div>
                                <div class="map-controls">
                                    <button id="zoom-in" class="btn-map" title="Perbesar"><i
                                            class="bi bi-plus"></i></button>
                                    <button id="zoom-out" class="btn-map" title="Perkecil"><i
                                            class="bi bi-dash"></i></button>
                                    <button id="locate-me" class="btn-map" title="Lokasi Saya"><i
                                            class="bi bi-geo"></i></button>
                                </div>
                            </div>
                            <div class="p-3 bg-light text-center">
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $event['latitude'] ?>,<?= $event['longitude'] ?>"
                                    target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column (Sticky) -->
                <div class="col-lg-4">
                    <div class="sticky-sidebar">
                        <div class="content-card border-top-0 border-start-0 border-end-0 border-4 border-primary">
                            <h5 class="fw-bold mb-4">Detail Pendaftaran</h5>

                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-ticket-perforated"></i></div>
                                <div>
                                    <div class="info-label">Harga Tiket</div>
                                    <div class="info-value fs-5 text-primary">
                                        <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                            Rp <?= number_format($event['price'], 0, ',', '.') ?>
                                        <?php else: ?>
                                            Gratis
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon"><i class="bi bi-people"></i></div>
                                <div>
                                    <div class="info-label">Sisa Kuota</div>
                                    <div class="info-value">
                                        <?= $availableQuota ?> / <?= $event['kuota'] ?> Kursi
                                        <?php if ($availableQuota <= 5 && $availableQuota > 0): ?>
                                            <span class="text-danger small ms-1">(Terbatas!)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <?php if (!$auth->isLoggedIn()): ?>
                                <div class="text-center">
                                    <p class="small text-muted mb-3">Login untuk mendaftar event ini</p>
                                    <a href="login.php" class="btn btn-primary btn-action-lg w-100">Login Sekarang</a>
                                </div>
                            <?php elseif ($isRegistered): ?>
                                <div
                                    class="alert alert-success border-0 bg-success bg-opacity-10 text-success text-center mb-3">
                                    <i class="bi bi-check-circle-fill me-1"></i> Anda Terdaftar
                                </div>
                                <a href="cancel-registration.php?id=<?= $eventId ?>"
                                    class="btn btn-outline-danger w-100 mb-2"
                                    onclick="return confirm('Yakin ingin membatalkan pendaftaran?')">
                                    Batalkan Pendaftaran
                                </a>
                            <?php elseif ($availableQuota <= 0): ?>
                                <button class="btn btn-secondary btn-action-lg w-100 disabled" disabled>
                                    Kuota Penuh
                                </button>
                            <?php else: ?>
                                <a href="register-event.php?id=<?= $eventId ?>"
                                    class="btn btn-primary btn-action-lg w-100 shadow-lg">
                                    Daftar Sekarang
                                </a>
                            <?php endif; ?>

                            <div class="mt-3">
                                <a href="export-calendar.php?id=<?= $eventId ?>"
                                    class="btn btn-light text-muted w-100 btn-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Simpan ke Kalender
                                </a>
                            </div>
                        </div>

                        <div class="text-center mt-3 text-muted small">
                            Butuh bantuan? <a href="#" class="text-decoration-none">Hubungi Panitia</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <?php if ($event['latitude'] && $event['longitude']): ?>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                // Custom marker icons
                const eventIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                    shadowSize: [41, 41]
                });

                const userIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                    shadowSize: [41, 41]
                });

                // Add loading spinner element
                const mapContainer = document.querySelector('.map-container');
                const loadingSpinner = document.createElement('div');
                loadingSpinner.className = 'loading-spinner';
                loadingSpinner.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Mengambil lokasi...</span>
                    </div>
                `;
                mapContainer.appendChild(loadingSpinner);

                // Show loading state
                const showLoading = (show) => {
                    loadingSpinner.classList.toggle('show', show);
                };

                document.addEventListener('DOMContentLoaded', function () {
                    const lat = <?= $event['latitude'] ?>;
                    const lng = <?= $event['longitude'] ?>;
                    const eventTitle = "<?= addslashes(htmlspecialchars($event['title'])) ?>";
                    const eventLocation = "<?= addslashes(htmlspecialchars($event['lokasi'])) ?>";
                    const eventDate = new Date('<?= $event['tanggal'] ?>').toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Initialize map with better options
                    const map = L.map('map', {
                        center: [lat, lng],
                        zoom: 15,
                        zoomControl: false,
                        scrollWheelZoom: true
                    });

                    // Add tile layer with better attribution
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19,
                        detectRetina: true
                    }).addTo(map);

                    // Add custom zoom controls
                    L.control.zoom({
                        position: 'bottomright'
                    }).addTo(map);

                    // Add marker with custom popup and animation
                    const marker = L.marker([lat, lng], {
                        icon: eventIcon,
                        title: eventTitle,
                        riseOnHover: true,
                        autoPanOnFocus: true
                    }).addTo(map);

                    // Add bounce animation class to marker
                    const markerElement = marker.getElement();
                    if (markerElement) {
                        markerElement.classList.add('bouncing-marker');

                        // Pause animation on hover
                        markerElement.addEventListener('mouseenter', () => {
                            markerElement.style.animationPlayState = 'paused';
                        });

                        // Resume animation when mouse leaves
                        markerElement.addEventListener('mouseleave', () => {
                            markerElement.style.animationPlayState = 'running';
                        });
                    }

                    // Custom popup content with more details
                    const popupContent = `
                        <div style="min-width: 250px;">
                            <h5 class="mb-2"><i class="bi bi-geo-alt-fill"></i> ${eventTitle}</h5>
                            <div class="mb-2">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-geo-alt mt-1 me-2"></i>
                                    <div>${eventLocation}</div>
                                </div>
                                <div class="d-flex align-items-start mt-1">
                                    <i class="bi bi-calendar-event mt-1 me-2"></i>
                                    <div>${eventDate}</div>
                                </div>
                                <div class="d-flex align-items-start mt-1">
                                    <i class="bi bi-people mt-1 me-2"></i>
                                    <div>${<?= $event['registered_count'] ?? 0 ?>} / ${<?= $event['kuota'] ?>} peserta terdaftar</div>
                                </div>
                            </div>
                            <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" 
                               target="_blank" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-signpost-split"></i> Dapatkan Petunjuk Arah
                            </a>
                        </div>
                    `;

                    marker.bindPopup(popupContent).openPopup();

                    // Add locate control with better options
                    const locateControl = L.control.locate({
                        position: 'topleft',
                        drawCircle: true,
                        follow: true,
                        setView: 'untilPanOrZoom',
                        keepCurrentZoomLevel: true,
                        markerStyle: {
                            weight: 1,
                            opacity: 0.8,
                            fillOpacity: 0.8
                        },
                        circleStyle: {
                            weight: 1,
                            clickable: false
                        },
                        icon: 'bi bi-geo',
                        metric: true,
                        strings: {
                            title: 'Lokasi Saya',
                            popup: 'Anda berada dalam radius {distance} {unit} dari lokasi event',
                            outsideMapBoundsMsg: 'Anda berada di luar area peta yang terlihat',
                            metersUnit: 'meter',
                            feetUnit: 'kaki',
                            popup: 'Anda berada dalam radius {distance} {unit} dari lokasi event',
                            outsideMapBoundsMsg: 'Anda berada di luar area peta yang terlihat'
                        },
                        locateOptions: {
                            maxZoom: 16,
                            watch: true,
                            enableHighAccuracy: true,
                            maximumAge: 10000,
                            timeout: 10000
                        }
                    }).addTo(map);

                    // Add click event to open directions in new tab
                    map.on('click', function (e) {
                        const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
                        window.open(url, '_blank');
                    });

                    // Custom controls with better tooltips and feedback
                    const zoomInBtn = document.getElementById('zoom-in');
                    const zoomOutBtn = document.getElementById('zoom-out');
                    const locateMeBtn = document.getElementById('locate-me');

                    // Add tooltips
                    const tooltips = [
                        { element: zoomInBtn, text: 'Perbesar' },
                        { element: zoomOutBtn, text: 'Perkecil' },
                        { element: locateMeBtn, text: 'Lokasi Saya' }
                    ];

                    tooltips.forEach(({ element, text }) => {
                        const tooltip = document.createElement('div');
                        tooltip.className = 'tooltip';
                        tooltip.textContent = text;
                        tooltip.style.cssText = `
                            position: absolute;
                            background: rgba(0, 0, 0, 0.8);
                            color: white;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 12px;
                            white-space: nowrap;
                            pointer-events: none;
                            opacity: 0;
                            transition: opacity 0.2s;
                            z-index: 1000;
                            right: 40px;
                            top: 50%;
                            transform: translateY(-50%);
                        `;

                        element.style.position = 'relative';
                        element.appendChild(tooltip);

                        element.addEventListener('mouseenter', (e) => {
                            tooltip.style.opacity = '1';
                        });

                        element.addEventListener('mouseleave', () => {
                            tooltip.style.opacity = '0';
                        });
                    });

                    // Add click effects
                    const addClickEffect = (element) => {
                        element.addEventListener('mousedown', () => {
                            element.style.transform = 'scale(0.95)';
                        });

                        element.addEventListener('mouseup', () => {
                            element.style.transform = 'scale(1.05)';
                        });

                        element.addEventListener('mouseleave', () => {
                            element.style.transform = 'scale(1)';
                        });
                    };

                    [zoomInBtn, zoomOutBtn, locateMeBtn].forEach(btn => addClickEffect(btn));

                    // Zoom controls with animation
                    zoomInBtn.addEventListener('click', function () {
                        map.zoomIn(1, {
                            animate: true,
                            duration: 0.3
                        });
                    });

                    zoomOutBtn.addEventListener('click', function () {
                        map.zoomOut(1, {
                            animate: true,
                            duration: 0.3
                        });
                    });

                    // Add pulsing effect to locate me button when active
                    locateMeBtn.addEventListener('click', function () {
                        // Toggle active state
                        locateMeBtn.classList.toggle('active');

                        // Add pulsing effect when active
                        if (locateMeBtn.classList.contains('active')) {
                            locateMeBtn.style.animation = 'pulse 1.5s infinite';
                            locateMeBtn.style.boxShadow = '0 0 0 0 rgba(13, 110, 253, 0.7)';
                        } else {
                            locateMeBtn.style.animation = '';
                            locateMeBtn.style.boxShadow = '';
                        }

                        showLoading(true);
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(function (position) {
                                const userLatLng = [position.coords.latitude, position.coords.longitude];

                                // Add or update user location marker
                                if (window.userLocationMarker) {
                                    window.userLocationMarker.setLatLng(userLatLng);
                                } else {
                                    window.userLocationMarker = L.marker(userLatLng, {
                                        icon: userIcon,
                                        title: 'Lokasi Anda',
                                        zIndexOffset: 1000
                                    }).addTo(map);

                                    window.userLocationMarker.bindPopup('<b>Lokasi Anda</b><br>Akurasi: ' +
                                        Math.round(position.coords.accuracy) + ' meter').openPopup();
                                }

                                // Center map on user location
                                map.setView(userLatLng, 15);

                                // Show route from user to event
                                const userLatLngStr = `${userLatLng[0]},${userLatLng[1]}`;
                                const eventLatLngStr = `${lat},${lng}`;
                                const routeUrl = `https://www.google.com/maps/dir/${userLatLngStr}/${eventLatLngStr}`;

                                // Update popup with route info
                                window.userLocationMarker.setPopupContent(
                                    `<b>Lokasi Anda</b><br>
                                    Akurasi: ${Math.round(position.coords.accuracy)} meter<br>
                                    <a href="${routeUrl}" target="_blank" class="btn btn-sm btn-primary mt-2 w-100">
                                        <i class="bi bi-signpost-split"></i> Rute ke Event
                                    </a>`
                                ).openPopup();

                            }, function (error) {
                                let errorMessage = 'Tidak dapat mengakses lokasi Anda';
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = 'Akses lokasi ditolak. Harap aktifkan izin lokasi di pengaturan browser Anda.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = 'Informasi lokasi tidak tersedia.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = 'Permintaan lokasi melebihi batas waktu.';
                                        break;
                                }
                                // Show error in a more user-friendly way
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'alert alert-warning alert-dismissible fade show position-absolute';
                                errorDiv.style.top = '10px';
                                errorDiv.style.left = '50%';
                                errorDiv.style.transform = 'translateX(-50%)';
                                errorDiv.style.zIndex = '1000';
                                errorDiv.style.maxWidth = '90%';
                                errorDiv.innerHTML = `
                                    <i class="bi bi-exclamation-triangle-fill"></i> ${errorMessage}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                `;

                                document.body.appendChild(errorDiv);

                                // Auto-remove after 5 seconds
                                setTimeout(() => {
                                    errorDiv.remove();
                                }, 5000);
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            });
                        } else {
                            alert('Browser Anda tidak mendukung geolokasi');
                        }
                    });

                    // Add scale control with better options
                    L.control.scale({
                        imperial: false,
                        metric: true,
                        position: 'bottomleft',
                        maxWidth: 200
                    }).addTo(map);

                    // Add fullscreen control
                    L.control.fullscreen({
                        position: 'topleft',
                        title: 'Tampilkan layar penuh',
                        titleCancel: 'Keluar dari layar penuh',
                        content: '<i class="bi bi-fullscreen"></i>',
                        forceSeparateButton: true
                    }).addTo(map);

                    // Add more touch support for mobile devices
                    map.touchZoom.enable();
                    map.dragging.enable();
                    map.scrollWheelZoom.enable();

                    // Add animation to popup when it opens
                    map.on('popupopen', function (e) {
                        const popup = e.popup;
                        const popupElement = popup.getElement();
                        if (popupElement) {
                            popupElement.style.opacity = '0';
                            popupElement.style.transform = 'translateY(10px)';
                            popupElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                            // Trigger reflow
                            void popupElement.offsetWidth;

                            popupElement.style.opacity = '1';
                            popupElement.style.transform = 'translateY(0)';
                        }
                    });

                    // Add smooth transition when changing views
                    map.on('movestart', function () {
                        map._container.style.transition = 'all 0.3s ease-out';
                    });

                    map.on('moveend', function () {
                        map._container.style.transition = '';
                    });

                    // Handle window resize
                    let resizeTimer;
                    window.addEventListener('resize', function () {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function () {
                            map.invalidateSize();
                        }, 250);
                    });
                });
            </script>
        <?php endif; ?>
        <script>
            function toggleSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('active');
            }
        </script>
</body>

</html>