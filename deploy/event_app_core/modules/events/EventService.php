<?php

require_once __DIR__ . '/../../config/database.php';

class EventService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllEvents($limit = null, $offset = null)
    {
        try {
            $sql = "SELECT e.*, u.nama as creator_name, 
                    COUNT(r.id) as registered_count
                    FROM events e
                    LEFT JOIN users u ON e.created_by = u.id
                    LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                    GROUP BY e.id
                    ORDER BY e.tanggal DESC";

            $params = [];
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $params[] = intval($limit);
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                    $params[] = intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Events Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEventById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT e.*, u.nama as creator_name,
                                        COUNT(r.id) as registered_count
                                        FROM events e
                                        LEFT JOIN users u ON e.created_by = u.id
                                        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                                        WHERE e.id = ?
                                        GROUP BY e.id");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Event Error: " . $e->getMessage());
            return null;
        }
    }

    public function createEvent($data)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO events (title, kategori, tanggal, lokasi, deskripsi, kuota, latitude, longitude, created_by, is_paid, price) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['title'],
                $data['kategori'],
                $data['tanggal'],
                $data['lokasi'],
                $data['deskripsi'] ?? '',
                $data['kuota'],
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $data['created_by'],
                $data['is_paid'] ?? 0,
                $data['price'] ?? 0
            ]);

            return ['success' => true, 'id' => $this->db->lastInsertId(), 'message' => 'Event berhasil dibuat'];
        } catch (PDOException $e) {
            error_log("Create Event Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membuat event'];
        }
    }

    public function updateEvent($id, $data)
    {
        try {
            $stmt = $this->db->prepare("UPDATE events SET title = ?, kategori = ?, tanggal = ?, 
                                        lokasi = ?, deskripsi = ?, kuota = ?, latitude = ?, longitude = ?, is_paid = ?, price = ? WHERE id = ?");
            $stmt->execute([
                $data['title'],
                $data['kategori'],
                $data['tanggal'],
                $data['lokasi'],
                $data['deskripsi'],
                $data['kuota'],
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $data['is_paid'] ?? 0,
                $data['price'] ?? 0,
                $id
            ]);

            // Send notification to registered users about event update
            if ($stmt->rowCount() > 0) {
                require_once __DIR__ . '/../notifications/NotificationService.php';
                $notificationService = new NotificationService();
                $updateMessage = "Event '{$data['title']}' telah diperbarui. Silakan cek detail event untuk informasi terbaru.";
                $notificationService->createEventUpdateNotification($id, $updateMessage);
            }

            return ['success' => true, 'message' => 'Event berhasil diperbarui'];
        } catch (PDOException $e) {
            error_log("Update Event Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memperbarui event'];
        }
    }

    public function deleteEvent($id)
    {
        try {
            // Get event details before deletion for notification
            $stmt = $this->db->prepare("SELECT title FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $event = $stmt->fetch();

            if ($event) {
                // Get confirmed participants
                $stmt = $this->db->prepare("SELECT user_id FROM registrations WHERE event_id = ? AND status = 'confirmed'");
                $stmt->execute([$id]);
                $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($participants)) {
                    require_once __DIR__ . '/../notifications/NotificationService.php';
                    $notificationService = new NotificationService();
                    $message = "⚠️ <strong>Event Dibatalkan</strong><br>Mohon maaf, event <strong>{$event['title']}</strong> yang Anda ikuti telah dibatalkan oleh penyelenggara.";

                    foreach ($participants as $userId) {
                        // Pass null as event_id so notification persists after event deletion
                        $notificationService->createNotification($userId, null, $message, 'info');
                    }
                }
            }

            $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Event berhasil dihapus'];
        } catch (PDOException $e) {
            error_log("Delete Event Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus event'];
        }
    }

    public function updateCalendarEventId($eventId, $calendarEventId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE events SET calendar_event_id = ? WHERE id = ?");
            $stmt->execute([$calendarEventId, $eventId]);
            return true;
        } catch (PDOException $e) {
            error_log("Update Calendar Event ID Error: " . $e->getMessage());
            return false;
        }
    }

    public function getUpcomingEvents($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("SELECT e.*, u.nama as creator_name,
                                        COUNT(r.id) as registered_count
                                        FROM events e
                                        LEFT JOIN users u ON e.created_by = u.id
                                        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                                        WHERE DATE(e.tanggal) >= CURDATE()
                                        GROUP BY e.id
                                        ORDER BY e.tanggal ASC
                                        LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Upcoming Events Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserEvents($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, r.status as registration_status, r.created_at as registration_date
                FROM events e
                JOIN registrations r ON e.id = r.event_id
                WHERE r.user_id = ?
                ORDER BY e.tanggal DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get User Events Error: " . $e->getMessage());
            return [];
        }
    }
    public function searchEvents($keyword = null, $category = null, $limit = null, $offset = null)
    {
        try {
            $sql = "SELECT e.*, u.nama as creator_name, 
                    COUNT(r.id) as registered_count
                    FROM events e
                    LEFT JOIN users u ON e.created_by = u.id
                    LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                    WHERE 1=1";

            $params = [];

            if (!empty($keyword)) {
                $sql .= " AND (e.title LIKE ? OR e.deskripsi LIKE ? OR e.lokasi LIKE ?)";
                $searchTerm = "%{$keyword}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($category)) {
                $sql .= " AND e.kategori = ?";
                $params[] = $category;
            }

            $sql .= " GROUP BY e.id ORDER BY e.tanggal DESC";

            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $params[] = intval($limit);
                if ($offset !== null) {
                    $sql .= " OFFSET ?";
                    $params[] = intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search Events Error: " . $e->getMessage());
            return [];
        }
    }
}

