<?php

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
    <!-- Premium Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1">

    <?php if ($event['latitude'] && $event['longitude']): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />
        <link rel='stylesheet'
            href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' />
    <?php endif; ?>

    <link rel="stylesheet" href="assets/css/responsive.css?v=1">
    <link rel="stylesheet" href="assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/app.css?v=<?= time() ?>">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- HERO SECTION -->
        <div class="event-hero">
            <div class="hero-layout">
                <div class="back-nav">
                    <a href="index.php"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Event</a>
                </div>

                <div class="hero-tags">
                    <div class="hero-tag">
                        <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($event['kategori']) ?>
                    </div>
                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                        <div class="hero-tag premium">
                            <i class="bi bi-star-fill me-1"></i> Premium
                        </div>
                    <?php endif; ?>
                </div>

                <div class="hero-title-wrapper">
                    <h1><?= htmlspecialchars($event['title']) ?></h1>
                </div>

                <div class="info-grid">
                    <div class="info-cell">
                        <label><i class="bi bi-calendar4 me-1"></i> Tanggal</label>
                        <div class="val"><?= date('d F Y', strtotime($event['tanggal'])) ?></div>
                    </div>
                    <div class="info-cell">
                        <label><i class="bi bi-clock me-1"></i> Waktu</label>
                        <div class="val"><?= date('H:i', strtotime($event['tanggal'])) ?> WIB - Selesai</div>
                    </div>
                    <div class="info-cell">
                        <label><i class="bi bi-geo-alt me-1"></i> Lokasi</label>
                        <div class="val"><?= htmlspecialchars($event['lokasi']) ?></div>
                    </div>
                    <div class="info-cell">
                        <label><i class="bi bi-person-fill"></i> Penyelenggara</label>
                        <div class="val text-truncate" style="font-size: 1rem;">
                            <?= htmlspecialchars($event['creator_name'] ?? 'Panitia Event') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENT SECTION -->
        <div class="content-container">
            <!-- Left Column: Details -->
            <div class="main-column">

                <!-- Alert Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center rounded-3 mb-4 shadow-sm border-0">
                        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                        <div>
                            <div class="fw-bold">Registrasi Berhasil!</div>
                            <?= ($success == '1') ? 'Pembayaran terkonfirmasi.' : 'Selamat bergabung di event ini.' ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Description Card -->
                <div class="section-card primary-border">
                    <h3 class="section-title"><i class="bi bi-file-text"></i> Deskripsi Event</h3>
                    <div class="description-text">
                        <?= nl2br(htmlspecialchars($event['deskripsi'])) ?>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <h5 class="fw-bold mb-3 small text-uppercase text-muted">Fasilitas Peserta</h5>
                        <div class="facilities-grid">
                            <div class="facility-item">
                                <i class="bi bi-wifi"></i>
                                <span>Akses Internet / Wifi</span>
                            </div>
                            <div class="facility-item">
                                <i class="bi bi-cup-hot"></i>
                                <span>Snack & Minuman</span>
                            </div>
                            <div class="facility-item">
                                <i class="bi bi-file-earmark-pdf"></i>
                                <span>E-Sertifikat</span>
                            </div>
                            <div class="facility-item">
                                <i class="bi bi-people"></i>
                                <span>Networking</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms Card -->
                <div class="section-card info-border">
                    <h3 class="section-title"><i class="bi bi-info-circle"></i> Syarat & Ketentuan</h3>
                    <ul class="mb-0 ps-3 text-secondary lh-lg">
                        <li>Peserta wajib hadir 15 menit sebelum acara dimulai.</li>
                        <li>Membawa kartu identitas (KTM/KTP) untuk registrasi ulang.</li>
                        <li>Dilarang membawa senjata tajam atau benda berbahaya.</li>
                        <li>Tiket yang sudah dibeli/diklaim tidak dapat dipindahtangankan tanpa konfirmasi panitia.</li>
                        <li>Jagalah kebersihan dan ketertiban selama acara berlangsung.</li>
                    </ul>
                </div>

                <!-- Map Card -->
                <?php if ($event['latitude'] && $event['longitude']): ?>
                    <div class="section-card">
                        <h3 class="section-title"><i class="bi bi-map"></i> Peta Lokasi</h3>
                        <p class="text-muted mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <?= htmlspecialchars($event['lokasi']) ?></p>
                        <div id="map"></div>
                        <div class="text-end mt-2">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $event['latitude'] ?>,<?= $event['longitude'] ?>"
                                target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Google Maps
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Right Column: Sidebar -->
            <div class="sidebar-column">
                <div class="sticky-sidebar">
                    <div class="reg-box">
                        <!-- Countdown -->
                        <div class="countdown-box">
                            <div class="small mb-2 text-warning fw-bold">EVENT DIMULAI DALAM</div>
                            <div class="countdown-timer" id="countdown">
                                <div><span id="d">00</span>
                                    <div class="countdown-label">Hari</div>
                                </div> :
                                <div><span id="h">00</span>
                                    <div class="countdown-label">Jam</div>
                                </div> :
                                <div><span id="m">00</span>
                                    <div class="countdown-label">Menit</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <span class="text-uppercase small fw-bold text-muted">Investasi / Tiket</span>
                            <div class="price-huge">
                                <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                    Rp <?= number_format($event['price'], 0, ',', '.') ?>
                                <?php else: ?>
                                    GRATIS
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quota -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span>Kuota Terisi</span>
                                <span><?= $event['registered_count'] ?? 0 ?> / <?= $event['kuota'] ?></span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <?php
                                $pct = ($event['kuota'] > 0) ? (($event['registered_count'] ?? 0) / $event['kuota']) * 100 : 0;
                                $cls = ($pct > 90) ? 'bg-danger' : 'bg-primary';
                                ?>
                                <div class="progress-bar <?= $cls ?>" style="width: <?= $pct ?>%"></div>
                            </div>
                            <?php if ($availableQuota < 5 && $availableQuota > 0): ?>
                                <div class="text-center small text-danger fw-bold mt-1">Sisa <?= $availableQuota ?> kursi
                                    lagi!</div>
                            <?php endif; ?>
                        </div>

                        <!-- Highlights -->
                        <ul class="benefits-list">
                            <li><i class="bi bi-check-circle-fill"></i> Materi Eksklusif</li>
                            <li><i class="bi bi-check-circle-fill"></i> E-Sertifikat Bernama</li>
                            <li><i class="bi bi-check-circle-fill"></i> Sesi Tanya Jawab</li>
                        </ul>

                        <!-- Action Buttons -->
                        <?php if (!$auth->isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-primary w-100 py-3 fw-bold rounded-3 mb-2 shadow-sm">Login
                                untuk Daftar</a>
                            <div class="text-center small">Belum punya akun? <a href="register.php">Daftar</a></div>
                        <?php elseif ($isRegistered): ?>
                            <div class="alert alert-success text-center border-0 small py-2 fw-bold mb-3">
                                <i class="bi bi-check-circle me-1"></i> Anda Terdaftar
                            </div>
                            <a href="export-calendar.php?id=<?= $eventId ?>"
                                class="btn btn-outline-dark w-100 mb-2 btn-sm fw-bold">
                                <i class="bi bi-calendar-plus me-1"></i> Add to Calendar
                            </a>
                            <a href="cancel-registration.php?id=<?= $eventId ?>"
                                class="btn btn-link text-danger w-100 btn-sm text-decoration-none"
                                onclick="return confirm('Batalkan pendaftaran?')">Batalkan Pendaftaran</a>
                        <?php elseif ($availableQuota <= 0): ?>
                            <button class="btn btn-secondary w-100 py-3 fw-bold disabled">Kuota Full</button>
                        <?php else: ?>
                            <a href="register-event.php?id=<?= $eventId ?>"
                                class="btn btn-primary w-100 py-3 fw-bold shadow hover-scale">
                                DAFTAR SEKARANG
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Host Info Small -->
                    <div class="mt-4 host-card">
                        <div class="host-avatar">
                            <?= strtoupper(substr($event['creator_name'] ?? 'P', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase">Organized By</div>
                            <div class="fw-bold"><?= htmlspecialchars($event['creator_name'] ?? 'Panitia Acara') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Countdown Logic -->
    <script>
        const eventDate = new Date("<?= date('Y-m-d H:i:s', strtotime($event['tanggal'])) ?>").getTime();

        const timer = setInterval(function () {
            const now = new Date().getTime();
            const distance = eventDate - now;

            if (distance < 0) {
                clearInterval(timer);
                document.getElementById("countdown").innerHTML = "<div class='text-danger'>EVENT SELESAI</div>";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            document.getElementById("d").innerHTML = days < 10 ? '0' + days : days;
            document.getElementById("h").innerHTML = hours < 10 ? '0' + hours : hours;
            document.getElementById("m").innerHTML = minutes < 10 ? '0' + minutes : minutes;
        }, 1000);
    </script>

    <?php if ($event['latitude'] && $event['longitude']): ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>
        <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const lat = <?= $event['latitude'] ?>;
                const lng = <?= $event['longitude'] ?>;
                const map = L.map('map', { scrollWheelZoom: false }).setView([lat, lng], 15);

                // Standard OSM
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19
                }).addTo(map);

                // Custom Marker
                const icon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                L.marker([lat, lng], { icon: icon }).addTo(map)
                    .bindPopup("<b><?= addslashes($event['title']) ?></b><br><?= addslashes($event['lokasi']) ?>").openPopup();

                L.control.locate({ position: 'topright' }).addTo(map);
                L.control.fullscreen({ position: 'topright' }).addTo(map);
            });
        </script>
    <?php endif; ?>
</body>

</html>