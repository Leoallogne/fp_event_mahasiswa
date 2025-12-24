<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/events/EventService.php';
require_once __DIR__ . '/../../modules/events/CategoryService.php';
require_once __DIR__ . '/../../api/ApiClientCalendar.php';

$auth = new Auth();
$auth->requireAdmin();

$eventService = new EventService();
$categoryService = new CategoryService();
$calendarApi = new ApiClientCalendar();
$currentUser = $auth->getCurrentUser();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        // Convert datetime-local to MySQL datetime format
        $tanggal = str_replace('T', ' ', $_POST['tanggal']) . ':00';

        $is_paid = isset($_POST['is_paid']) ? 1 : 0;
        $price = $is_paid ? ($_POST['price'] ?? 0) : 0;

        $data = [
            'title' => $_POST['title'],
            'kategori' => $_POST['kategori'],
            'tanggal' => $tanggal,
            'lokasi' => $_POST['lokasi'],
            'deskripsi' => $_POST['deskripsi'] ?? '',
            'kuota' => $_POST['kuota'],
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'created_by' => $currentUser['id'],
            'is_paid' => $is_paid,
            'price' => $price
        ];

        $result = $eventService->createEvent($data);

        if ($result['success']) {
            // Sync to Google Calendar
            $calendarResult = $calendarApi->pushEvent($data);
            if ($calendarResult['success'] && isset($calendarResult['eventId'])) {
                $eventService->updateCalendarEventId($result['id'], $calendarResult['eventId']);
            }

            $message = 'Event berhasil dibuat';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'];
        // Convert datetime-local to MySQL datetime format
        $tanggal = str_replace('T', ' ', $_POST['tanggal']) . ':00';

        $is_paid = isset($_POST['is_paid']) ? 1 : 0;
        $price = $is_paid ? ($_POST['price'] ?? 0) : 0;

        $data = [
            'title' => $_POST['title'],
            'kategori' => $_POST['kategori'],
            'tanggal' => $tanggal,
            'lokasi' => $_POST['lokasi'],
            'deskripsi' => $_POST['deskripsi'] ?? '',
            'kuota' => $_POST['kuota'],
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'is_paid' => $is_paid,
            'price' => $price
        ];

        $result = $eventService->updateEvent($id, $data);

        if ($result['success']) {
            // Update Google Calendar
            $event = $eventService->getEventById($id);
            if ($event && $event['calendar_event_id']) {
                $calendarApi->updateEvent($event['calendar_event_id'], $data);
            }

            $message = 'Event berhasil diperbarui';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $event = $eventService->getEventById($id);

        $result = $eventService->deleteEvent($id);

        if ($result['success']) {
            // Delete from Google Calendar
            if ($event && $event['calendar_event_id']) {
                $calendarApi->deleteEvent($event['calendar_event_id']);
            }

            $message = 'Event berhasil dihapus';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

$events = $eventService->getAllEvents();
$categories = $categoryService->getAllCategories();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Event - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 12px;
            border: 1px solid #ddd;
            margin-top: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        }

        .btn-map:hover {
            background: #f8f9fa;
        }

        .search-container {
            margin-bottom: 15px;
        }

        #search-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .search-result-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-calendar-event"></i> Manajemen Event</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal"
                    onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Tambah Event
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Harga</th>
                            <th>Kuota</th>
                            <th>Peserta</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= $event['id'] ?></td>
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($event['kategori']) ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($event['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($event['lokasi']) ?></td>
                                <td>
                                    <?php if (!empty($event['price']) && $event['price'] > 0): ?>
                                        <span class="badge bg-success">Rp
                                            <?= number_format($event['price'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Gratis</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $event['kuota'] ?></td>
                                <td><?= $event['registered_count'] ?? 0 ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                        onclick="editEvent(<?= htmlspecialchars(json_encode($event)) ?>)">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                    <a href="event-participants.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-people"></i> Peserta
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Event Modal -->
        <div class="modal fade" id="eventModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah/Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="eventForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" id="formAction" value="create">
                            <input type="hidden" name="id" id="eventId">

                            <div class="mb-3">
                                <label class="form-label">Judul Event</label>
                                <input type="text" class="form-control" name="title" id="eventTitle" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="kategori" id="eventKategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['nama']) ?>">
                                            <?= htmlspecialchars($cat['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal & Waktu</label>
                                <input type="datetime-local" class="form-control" name="tanggal" id="eventTanggal"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <div class="search-container">
                                    <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                                    <div class="search-results" id="search-results"></div>
                                </div>
                                <input type="hidden" id="latitude" name="latitude">
                                <input type="hidden" id="longitude" name="longitude">

                                <div class="mt-3">
                                    <label class="form-label">Pilih Lokasi di Peta</label>
                                    <div class="map-container">
                                        <div id="map"></div>
                                        <div class="map-controls">
                                            <button type="button" id="zoom-in" class="btn-map" title="Perbesar">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                            <button type="button" id="zoom-out" class="btn-map" title="Perkecil">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <button type="button" id="locate-me" class="btn-map" title="Lokasi Saya">
                                                <i class="bi bi-geo"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="search-container mt-2">
                                        <input type="text" id="search-input" class="form-control"
                                            placeholder="Cari lokasi...">
                                    </div>
                                    <small class="text-muted">Geser marker untuk menyesuaikan lokasi. Klik pada peta
                                        untuk menambahkan marker baru.</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" id="eventDeskripsi" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kuota</label>
                                <input type="number" class="form-control" name="kuota" id="eventKuota" min="1" required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid"
                                        onchange="togglePriceInput()">
                                    <label class="form-check-label" for="is_paid">Event Berbayar</label>
                                </div>
                            </div>

                            <div class="mb-3" id="priceContainer" style="display: none;">
                                <label class="form-label">Harga Tiket (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="price" id="eventPrice" min="0"
                                        step="1000" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            let map, marker, searchResults = [];
            const defaultLat = -6.200000;
            const defaultLng = 106.816666;

            // Custom marker icon
            const eventIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            // Custom user location icon
            const userIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            // Fungsi untuk melakukan pencarian lokasi
            async function searchLocation(query) {
                if (!query || query.length < 3) {
                    document.getElementById('search-results').style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`);
                    const results = await response.json();
                    searchResults = results;

                    const resultsContainer = document.getElementById('search-results');
                    resultsContainer.innerHTML = '';

                    if (results.length === 0) {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.textContent = 'Tidak ditemukan hasil';
                        resultsContainer.appendChild(item);
                    } else {
                        results.forEach((result, index) => {
                            const item = document.createElement('div');
                            item.className = 'search-result-item';
                            item.textContent = result.display_name;
                            item.onclick = () => selectLocation(index);
                            resultsContainer.appendChild(item);
                        });
                    }

                    resultsContainer.style.display = 'block';
                } catch (error) {
                    console.error('Error searching location:', error);
                }
            }

            // Fungsi untuk memilih lokasi dari hasil pencarian
            function selectLocation(index) {
                const result = searchResults[index];
                if (!result) return;

                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);

                // Update map view
                map.setView([lat, lng], 16);

                // Update marker position
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], {
                        draggable: true,
                        icon: eventIcon
                    }).addTo(map);
                    marker.on('dragend', onMarkerDragEnd);
                }

                // Update form fields
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById('lokasi').value = result.display_name;

                // Hide results
                document.getElementById('search-results').style.display = 'none';
            }

            // Fungsi yang dipanggil saat marker digeser
            function onMarkerDragEnd() {
                const latlng = marker.getLatLng();
                document.getElementById('latitude').value = latlng.lat;
                document.getElementById('longitude').value = latlng.lng;

                // Update alamat berdasarkan koordinat terbaru (reverse geocoding)
                updateAddressFromCoordinates(latlng.lat, latlng.lng);
            }

            // Fungsi untuk mendapatkan alamat dari koordinat (reverse geocoding)
            async function updateAddressFromCoordinates(lat, lng) {
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
                    const data = await response.json();

                    if (data.display_name) {
                        document.getElementById('lokasi').value = data.display_name;
                    }
                } catch (error) {
                    console.error('Error getting address:', error);
                }
            }

            function initMap() {
                if (map) return;

                // Initialize map with better options
                map = L.map('map', {
                    center: [defaultLat, defaultLng],
                    zoom: 13,
                    zoomControl: false,
                    scrollWheelZoom: true
                });

                // Add tile layer with better attribution (default light theme, no dark mode)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19,
                    detectRetina: true
                }).addTo(map);

                // Add custom zoom controls
                L.control.zoom({
                    position: 'bottomright'
                }).addTo(map);

                // Add scale control
                L.control.scale({
                    imperial: false,
                    metric: true,
                    position: 'bottomleft'
                }).addTo(map);

                // Create initial marker
                marker = L.marker([defaultLat, defaultLng], {
                    draggable: true,
                    icon: eventIcon
                }).addTo(map);

                // Set initial values for hidden inputs
                document.getElementById('latitude').value = defaultLat;
                document.getElementById('longitude').value = defaultLng;

                // Add click event to map
                map.on('click', function (e) {
                    const latlng = e.latlng;

                    if (marker) {
                        marker.setLatLng(latlng);
                    } else {
                        marker = L.marker(latlng, {
                            draggable: true,
                            icon: eventIcon
                        }).addTo(map);
                    }

                    document.getElementById('latitude').value = latlng.lat;
                    document.getElementById('longitude').value = latlng.lng;

                    // Update address based on coordinates
                    updateAddressFromCoordinates(latlng.lat, latlng.lng);
                });

                // Handle marker drag end
                marker.on('dragend', onMarkerDragEnd);

                // Add double click zoom
                map.doubleClickZoom.enable();

                // Add keyboard navigation
                map.keyboard.enable();

                // Add touch zoom on mobile
                map.touchZoom.enable();
                map.dragging.enable();

                // Add fullscreen control
                map.addControl(new L.Control.Fullscreen());

                // Add locate control
                L.control.locate({
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
                        title: 'Tampilkan lokasi saya',
                        popup: 'Anda berada dalam radius {distance} {unit} dari titik ini',
                        outsideMapBoundsMsg: 'Anda berada di luar area peta yang terlihat'
                    },
                    locateOptions: {
                        maxZoom: 15,
                        watch: true,
                        enableHighAccuracy: true,
                        maximumAge: 10000,
                        timeout: 10000
                    }
                }).addTo(map);
            }

            const eventModal = document.getElementById('eventModal');
            let mapInitialized = false;

            eventModal.addEventListener('shown.bs.modal', function () {
                if (!mapInitialized) {
                    initMap();
                    mapInitialized = true;

                    // Setup event listeners for search
                    const searchInput = document.getElementById('search-input');
                    if (searchInput) {
                        searchInput.addEventListener('input', (e) => searchLocation(e.target.value));
                    }

                    // Close search results when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('.search-container')) {
                            const searchResults = document.getElementById('search-results');
                            if (searchResults) {
                                searchResults.style.display = 'none';
                            }
                        }
                    });

                    // Initialize form with current date and time
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');

                    const dateInput = document.getElementById('eventTanggal');
                    if (dateInput) {
                        dateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                    }
                } else {
                    // Invalidate size when modal is shown again
                    setTimeout(() => {
                        if (map) map.invalidateSize();
                    }, 300);
                }
            });

            // Reset form when modal is hidden
            eventModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('eventForm').reset();
                if (marker) {
                    marker.setLatLng([defaultLat, defaultLng]);
                    map.setView([defaultLat, defaultLng], 13);
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                // Custom controls
                document.getElementById('zoom-in').addEventListener('click', function () {
                    map.zoomIn();
                });

                document.getElementById('zoom-out').addEventListener('click', function () {
                    map.zoomOut();
                });

                document.getElementById('locate-me').addEventListener('click', function () {
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

                                window.userLocationMarker.bindPopup('Lokasi Anda').openPopup();
                            }

                            // Center map on user location
                            map.setView(userLatLng, 15);

                            // Update form fields if needed
                            if (document.activeElement !== searchInput) {
                                document.getElementById('latitude').value = userLatLng[0];
                                document.getElementById('longitude').value = userLatLng[1];
                                updateAddressFromCoordinates(userLatLng[0], userLatLng[1]);
                            }

                        }, function (error) {
                            alert('Tidak dapat mengakses lokasi Anda. Pastikan Anda mengizinkan akses lokasi.');
                            console.error('Geolocation error:', error);
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        });
                    } else {
                        alert('Browser Anda tidak mendukung geolokasi');
                    }
                });
            });

            function resetForm() {
                document.getElementById('eventForm').reset();
                document.getElementById('action').value = 'create';
                document.getElementById('eventId').value = '';
                document.getElementById('eventModalLabel').textContent = 'Tambah Event';
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                togglePriceInput(); // Reset price field visibility

                if (map) {
                    if (marker) map.removeLayer(marker);
                    map.setView([defaultLat, defaultLng], 13);
                    marker = L.marker([defaultLat, defaultLng], {
                        draggable: true,
                        icon: eventIcon
                    }).addTo(map);
                    marker.on('dragend', onMarkerDragEnd);
                }
            }

            function togglePriceInput() {
                const isPaid = document.getElementById('is_paid').checked;
                const priceContainer = document.getElementById('priceContainer');
                const priceInput = document.getElementById('eventPrice');

                if (isPaid) {
                    priceContainer.style.display = 'block';
                    priceInput.setAttribute('required', 'required');
                } else {
                    priceContainer.style.display = 'none';
                    priceInput.removeAttribute('required');
                    priceInput.value = '';
                }
            }

            function editEvent(event) {
                document.getElementById('eventModalLabel').textContent = 'Edit Event';
                document.getElementById('eventId').value = event.id;
                document.getElementById('action').value = 'update';
                document.getElementById('eventTitle').value = event.title;
                document.getElementById('eventKategori').value = event.kategori;
                document.getElementById('eventTanggal').value = event.tanggal;
                document.getElementById('lokasi').value = event.lokasi;
                document.getElementById('eventDeskripsi').value = event.deskripsi;
                document.getElementById('eventKuota').value = event.kuota;

                // Handle Map
                if (event.latitude && event.longitude) {
                    const lat = parseFloat(event.latitude);
                    const lng = parseFloat(event.longitude);
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng], {
                        icon: eventIcon,
                        draggable: true
                    }).addTo(map);

                    map.setView([lat, lng], 15);

                    marker.on('dragend', function (e) {
                        const position = marker.getLatLng();
                        updateLocationInput(position.lat, position.lng);
                    });
                }

                // Handle Paid/Price
                const isPaidCheckbox = document.getElementById('is_paid');
                const priceInput = document.getElementById('eventPrice');
                // Check if is_paid property exists (it might be 1 or 0 string from DB)
                const isPaid = event.is_paid == 1 || event.price > 0;

                isPaidCheckbox.checked = isPaid;
                togglePriceInput();

                if (isPaid) {
                    priceInput.value = event.price;
                }

                new bootstrap.Modal(document.getElementById('eventModal')).show();

                // Refresh map size after modal opens
                setTimeout(() => {
                    map.invalidateSize();
                }, 500);
            }
        </script>
        <script>
            function toggleSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('active');
            }
        </script>
</body>

</html>