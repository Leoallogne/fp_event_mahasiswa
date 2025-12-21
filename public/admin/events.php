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

        $data = [
            'title' => $_POST['title'],
            'kategori' => $_POST['kategori'],
            'tanggal' => $tanggal,
            'lokasi' => $_POST['lokasi'],
            'deskripsi' => $_POST['deskripsi'] ?? '',
            'kuota' => $_POST['kuota'],
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'created_by' => $currentUser['id']
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

        $data = [
            'title' => $_POST['title'],
            'kategori' => $_POST['kategori'],
            'tanggal' => $tanggal,
            'lokasi' => $_POST['lokasi'],
            'deskripsi' => $_POST['deskripsi'] ?? '',
            'kuota' => $_POST['kuota'],
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null
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

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
        }

        #map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-top: 10px;
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
                                            <?= htmlspecialchars($cat['nama']) ?></option>
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
                                <input type="text" class="form-control" name="lokasi" id="eventLokasi" required>
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
                                <label class="form-label">Pilih Lokasi di Map</label>
                                <input type="hidden" name="latitude" id="eventLat">
                                <input type="hidden" name="longitude" id="eventLng">
                                <div id="map"></div>
                                <small class="text-muted">Klik pada peta untuk menentukan lokasi event.</small>
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
            let map, marker;
            const defaultLat = -6.200000;
            const defaultLng = 106.816666;

            function initMap() {
                if (map) return;

                map = L.map('map').setView([defaultLat, defaultLng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

                map.on('click', function (e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    updateMarker(lat, lng);
                });

                marker.on('dragend', function (e) {
                    const position = marker.getLatLng();
                    updateMarker(position.lat, position.lng);
                });
            }

            function updateMarker(lat, lng) {
                marker.setLatLng([lat, lng]);
                document.getElementById('eventLat').value = lat;
                document.getElementById('eventLng').value = lng;
            }

            // Initialize map when modal is shown
            const eventModal = document.getElementById('eventModal');
            eventModal.addEventListener('shown.bs.modal', function () {
                initMap();
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            });

            function resetForm() {
                document.getElementById('eventForm').reset();
                document.getElementById('formAction').value = 'create';
                document.getElementById('eventId').value = '';
                if (marker) {
                    updateMarker(defaultLat, defaultLng);
                    map.setView([defaultLat, defaultLng], 13);
                }
            }

            function editEvent(event) {
                document.getElementById('formAction').value = 'update';
                document.getElementById('eventId').value = event.id;
                document.getElementById('eventTitle').value = event.title;
                document.getElementById('eventKategori').value = event.kategori;

                let tanggal = event.tanggal;
                if (tanggal.includes(' ')) {
                    tanggal = tanggal.replace(' ', 'T').substring(0, 16);
                } else if (tanggal.includes('T')) {
                    tanggal = tanggal.substring(0, 16);
                }
                document.getElementById('eventTanggal').value = tanggal;

                document.getElementById('eventLokasi').value = event.lokasi;
                document.getElementById('eventDeskripsi').value = event.deskripsi || '';
                document.getElementById('eventKuota').value = event.kuota;

                const lat = event.latitude ? parseFloat(event.latitude) : defaultLat;
                const lng = event.longitude ? parseFloat(event.longitude) : defaultLng;

                document.getElementById('eventLat').value = event.latitude || '';
                document.getElementById('eventLng').value = event.longitude || '';

                const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();

                // Modal shown event will trigger initMap, but we need to set the marker
                if (map) {
                    updateMarker(lat, lng);
                    map.setView([lat, lng], 13);
                } else {
                    // If map not initialized yet, wait for shown.bs.modal
                    eventModal.addEventListener('shown.bs.modal', function () {
                        updateMarker(lat, lng);
                        map.setView([lat, lng], 13);
                    }, { once: true });
                }
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