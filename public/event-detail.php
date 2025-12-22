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
    <title><?= htmlspecialchars($event['title']) ?> - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php if ($event['latitude'] && $event['longitude']): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <?php endif; ?>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            border: 1px solid #ddd;
            margin-top: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
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
        
        .leaflet-popup-content i {
            width: 16px;
            text-align: center;
            margin-right: 5px;
            color: #7f8c8d;
        }
        
        .map-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 5px;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.2);
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
            transition: all 0.2s ease-in-out;
            transform: scale(1);
        }
        
        .btn-map:hover {
            background: #f1f3f5;
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .btn-map:active {
            transform: scale(0.95) !important;
            transition: transform 0.1s ease !important;
        }
        
        .btn-map.active {
            background-color: #e7f1ff;
            color: #0d6efd;
            border-color: #9ec5fe;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }
        
        /* Animasi marker */
        @keyframes bounce {
            0% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.1); }
            100% { transform: translateY(0) scale(1); }
        }
        
        .bouncing-marker {
            animation: bounce 1.5s infinite;
        }
        
        /* Efek loading */
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            text-align: center;
            font-size: 14px;
        }
        
        .loading-spinner.show {
            display: block;
        }
        
        .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        /* Efek hover untuk popup */
        .leaflet-popup-content-wrapper {
            transition: all 0.3s ease;
            border-radius: 8px !important;
        }
        
        .leaflet-popup-content-wrapper:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15) !important;
        }
        
        .tooltip {
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
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> Pendaftaran berhasil!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title"><?= htmlspecialchars($event['title']) ?></h2>

                            <div class="mb-3">
                                <span class="badge bg-primary"><?= htmlspecialchars($event['kategori']) ?></span>
                            </div>

                            <div class="mb-3">
                                <p><strong><i class="bi bi-calendar"></i> Tanggal & Waktu:</strong><br>
                                    <?= date('d F Y, H:i', strtotime($event['tanggal'])) ?></p>
                            </div>

                            <div class="mb-3">
                                <p><strong><i class="bi bi-geo-alt"></i> Lokasi:</strong><br>
                                    <?= htmlspecialchars($event['lokasi']) ?></p>
                            </div>

                            <div class="mb-3">
                                <p><strong><i class="bi bi-people"></i> Kuota:</strong><br>
                                    <?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?> peserta</p>
                            </div>

                            <div class="mb-3">
                                <h5>Deskripsi</h5>
                                <p><?= nl2br(htmlspecialchars($event['deskripsi'])) ?></p>
                            </div>

                            <?php if ($event['latitude'] && $event['longitude']): ?>
                                <div class="mb-3">
                                    <h5><i class="bi bi-geo-alt-fill"></i> Lokasi di Peta</h5>
                                    <div class="map-container">
                                        <div id="map"></div>
                                        <div class="map-controls">
                                            <button id="zoom-in" class="btn-map" title="Perbesar">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                            <button id="zoom-out" class="btn-map" title="Perkecil">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <button id="locate-me" class="btn-map" title="Lokasi Saya">
                                                <i class="bi bi-geo"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-muted small">
                                        <i class="bi bi-info-circle"></i> Klik pada peta untuk melihat rute
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Aksi</h5>

                            <?php if (!$auth->isLoggedIn()): ?>
                                <p>Silakan login untuk mendaftar event ini.</p>
                                <a href="login.php" class="btn btn-primary w-100">Login</a>
                            <?php elseif ($isRegistered): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> Anda sudah terdaftar pada event ini.
                                </div>
                                <a href="cancel-registration.php?id=<?= $eventId ?>" class="btn btn-danger w-100"
                                    onclick="return confirm('Yakin ingin membatalkan pendaftaran?')">
                                    <i class="bi bi-x-circle"></i> Batalkan Pendaftaran
                                </a>
                            <?php elseif ($availableQuota <= 0): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Kuota sudah penuh.
                                </div>
                            <?php else: ?>
                                <a href="register-event.php?id=<?= $eventId ?>" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Daftar Event
                                </a>
                            <?php endif; ?>

                            <hr>

                            <a href="export-calendar.php?id=<?= $eventId ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-calendar-plus"></i> Export ke Google Calendar
                            </a>
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
                    map.on('click', function(e) {
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
                    zoomInBtn.addEventListener('click', function() {
                        map.zoomIn(1, {
                            animate: true,
                            duration: 0.3
                        });
                    });

                    zoomOutBtn.addEventListener('click', function() {
                        map.zoomOut(1, {
                            animate: true,
                            duration: 0.3
                        });
                    });

                    // Add pulsing effect to locate me button when active
                    locateMeBtn.addEventListener('click', function() {
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
                            navigator.geolocation.getCurrentPosition(function(position) {
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
                                
                            }, function(error) {
                                let errorMessage = 'Tidak dapat mengakses lokasi Anda';
                                switch(error.code) {
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
                    map.on('popupopen', function(e) {
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
                    map.on('movestart', function() {
                        map._container.style.transition = 'all 0.3s ease-out';
                    });
                    
                    map.on('moveend', function() {
                        map._container.style.transition = '';
                    });
                    
                    // Handle window resize
                    let resizeTimer;
                    window.addEventListener('resize', function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(function() {
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