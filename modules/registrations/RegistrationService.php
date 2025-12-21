<?php

require_once __DIR__ . '/../../config/database.php';

class RegistrationService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function registerForEvent($userId, $eventId) {
        try {
            // Check if already registered
            $stmt = $this->db->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Anda sudah terdaftar pada event ini'];
            }
            
            // Check quota
            $stmt = $this->db->prepare("SELECT kuota, 
                                        COUNT(r.id) as registered_count
                                        FROM events e
                                        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                                        WHERE e.id = ?
                                        GROUP BY e.id");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            if (!$event) {
                return ['success' => false, 'message' => 'Event tidak ditemukan'];
            }
            
            if ($event['registered_count'] >= $event['kuota']) {
                return ['success' => false, 'message' => 'Kuota event sudah penuh'];
            }
            
            // Register
            $stmt = $this->db->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'confirmed')");
            $stmt->execute([$userId, $eventId]);
            
            // Create notification for successful registration
            require_once __DIR__ . '/../notifications/NotificationService.php';
            $notificationService = new NotificationService();
            $notificationService->createRegistrationNotification($userId, $eventId);
            
            return ['success' => true, 'message' => 'Pendaftaran berhasil'];
        } catch(PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mendaftar event'];
        }
    }
    
    public function cancelRegistration($userId, $eventId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Pendaftaran dibatalkan'];
            }
            
            return ['success' => false, 'message' => 'Pendaftaran tidak ditemukan'];
        } catch(PDOException $e) {
            error_log("Cancel Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membatalkan pendaftaran'];
        }
    }
    
    public function getUserRegistrations($userId) {
        try {
            $stmt = $this->db->prepare("SELECT r.*, e.title, e.kategori, e.tanggal, e.lokasi, e.deskripsi
                                        FROM registrations r
                                        JOIN events e ON r.event_id = e.id
                                        WHERE r.user_id = ? AND r.status = 'confirmed'
                                        ORDER BY e.tanggal DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get User Registrations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getEventRegistrations($eventId) {
        try {
            $stmt = $this->db->prepare("SELECT r.*, u.nama, u.email
                                        FROM registrations r
                                        JOIN users u ON r.user_id = u.id
                                        WHERE r.event_id = ? AND r.status = 'confirmed'
                                        ORDER BY r.daftar_waktu DESC");
            $stmt->execute([$eventId]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Event Registrations Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function isRegistered($userId, $eventId) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'confirmed'");
            $stmt->execute([$userId, $eventId]);
            return $stmt->fetch() !== false;
        } catch(PDOException $e) {
            error_log("Check Registration Error: " . $e->getMessage());
            return false;
        }
    }
}

