<?php

require_once __DIR__ . '/../../config/database.php';

class RegistrationService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ensureSchemaUpdated();
    }

    private function ensureSchemaUpdated()
    {
        try {
            // Check if column exists
            $stmt = $this->db->query("SHOW COLUMNS FROM registrations LIKE 'payment_proof'");
            if (!$stmt->fetch()) {
                $this->db->exec("ALTER TABLE registrations ADD COLUMN payment_proof VARCHAR(255) NULL");
            }

            // Ensure unique constraint for user_id + event_id to prevent double registration
            // Check if index exists first (MySQL specific)
            $stmt = $this->db->query("SHOW INDEX FROM registrations WHERE Key_name = 'unique_registration'");
            if (!$stmt->fetch()) {
                // Try adding it. If duplicate data exists, this might fail, so we catch exception
                try {
                    $this->db->exec("ALTER TABLE registrations ADD CONSTRAINT unique_registration UNIQUE (user_id, event_id)");
                } catch (PDOException $e) {
                    // Ignore if data conflict, or manually cleanup duplicates? 
                    // For now, logged error is enough, we can't auto-clean safely.
                    error_log("Could not add unique constraint: " . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            error_log("Migration Error: " . $e->getMessage());
        }
    }

    public function registerForEvent($userId, $eventId)
    {
        try {
            // Check if already registered
            $stmt = $this->db->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Anda sudah terdaftar pada event ini'];
            }

            // Check quota
            // Check quota using subquery for accuracy and performance
            $stmt = $this->db->prepare("SELECT kuota, 
                                        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'confirmed') as registered_count
                                        FROM events e
                                        WHERE e.id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();

            if (!$event) {
                return ['success' => false, 'message' => 'Event tidak ditemukan'];
            }

            if ($event['registered_count'] >= $event['kuota']) {
                return ['success' => false, 'message' => 'Kuota event sudah penuh'];
            }

            // Get event details to check price
            $stmt = $this->db->prepare("SELECT price FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $eventData = $stmt->fetch();
            $isPaidEvent = (!empty($eventData['price']) && $eventData['price'] > 0);
            $initialStatus = $isPaidEvent ? 'pending' : 'confirmed';

            // Register
            // Register
            // Use INSERT IGNORE or try-catch for unique constraint
            try {
                $stmt = $this->db->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $eventId, $initialStatus]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    return ['success' => false, 'message' => 'Anda sudah terdaftar pada event ini'];
                }
                throw $e;
            }

            // Create notification for successful registration
            require_once __DIR__ . '/../notifications/NotificationService.php';
            $notificationService = new NotificationService();
            $notificationService->createRegistrationNotification($userId, $eventId);

            return ['success' => true, 'message' => 'Pendaftaran berhasil'];
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mendaftar event'];
        }
    }

    public function cancelRegistration($userId, $eventId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Pendaftaran dibatalkan'];
            }

            return ['success' => false, 'message' => 'Pendaftaran tidak ditemukan'];
        } catch (PDOException $e) {
            error_log("Cancel Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membatalkan pendaftaran'];
        }
    }

    public function getUserRegistrations($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT r.*, e.title, e.kategori, e.tanggal, e.lokasi, e.deskripsi, e.price, e.is_paid
                                        FROM registrations r
                                        JOIN events e ON r.event_id = e.id
                                        WHERE r.user_id = ?
                                        ORDER BY e.tanggal DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get User Registrations Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEventRegistrations($eventId)
    {
        try {
            $stmt = $this->db->prepare("SELECT r.*, u.nama, u.email
                                        FROM registrations r
                                        JOIN users u ON r.user_id = u.id
                                        WHERE r.event_id = ?
                                        ORDER BY r.daftar_waktu DESC");
            $stmt->execute([$eventId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Event Registrations Error: " . $e->getMessage());
            return [];
        }
    }

    public function isRegistered($userId, $eventId)
    {
        try {
            // Check for any active registration (confirmed or pending)
            $stmt = $this->db->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Check Registration Error: " . $e->getMessage());
        }
    }

    public function updatePaymentProof($userId, $eventId, $proofFilename)
    {
        try {
            $stmt = $this->db->prepare("UPDATE registrations SET payment_proof = ? WHERE user_id = ? AND event_id = ?");
            return $stmt->execute([$proofFilename, $userId, $eventId]);
        } catch (PDOException $e) {
            error_log("Update Payment Proof Error: " . $e->getMessage());
            return false;
        }
    }

    public function verifyPayment($userId, $eventId, $status)
    {
        try {
            $stmt = $this->db->prepare("UPDATE registrations SET status = ? WHERE user_id = ? AND event_id = ?");
            $result = $stmt->execute([$status, $userId, $eventId]);

            if ($result && $status === 'confirmed') {
                require_once __DIR__ . '/../notifications/NotificationService.php';
                $notificationService = new NotificationService();
                $notificationService->createNotification($userId, $eventId, "Pembayaran Anda telah dikonfirmasi. Selamat mengikuti event!");
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Verify Payment Error: " . $e->getMessage());
            return false;
        }
    }

    public function getPendingRegistrations()
    {
        try {
            $stmt = $this->db->prepare("SELECT r.*, u.nama as user_name, u.email as user_email, e.title as event_title, e.price
                                        FROM registrations r
                                        JOIN users u ON r.user_id = u.id
                                        JOIN events e ON r.event_id = e.id
                                        WHERE r.status = 'pending' AND r.payment_proof IS NOT NULL
                                        ORDER BY r.daftar_waktu ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get Pending Registrations Error: " . $e->getMessage());
            return [];
        }
    }

    public function getRegistration($userId, $eventId)
    {
        try {
            $stmt = $this->db->prepare("SELECT r.*, e.title, e.price, e.lokasi, e.tanggal 
                                        FROM registrations r
                                        JOIN events e ON r.event_id = e.id
                                        WHERE r.user_id = ? AND r.event_id = ?");
            $stmt->execute([$userId, $eventId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get Registration Error: " . $e->getMessage());
            return null;
        }
    }
    public function processPayment($userId, $eventId, $method)
    {
        try {
            // Auto-confirm logic for demo
            $status = 'confirmed';
            // Store payment method as simple proof string
            $proof = 'DEMO_PAID_' . strtoupper(str_replace(' ', '_', $method)) . '_' . time();

            $stmt = $this->db->prepare("UPDATE registrations SET status = ?, payment_proof = ? WHERE user_id = ? AND event_id = ?");
            $result = $stmt->execute([$status, $proof, $userId, $eventId]);

            if ($result) {
                // Send Notification
                require_once __DIR__ . '/../notifications/NotificationService.php';
                $notificationService = new NotificationService();
                $notificationService->createPaymentSuccessNotification($userId, $eventId, $method);

                // Add to Google Calendar if needed (optional, assuming handled by sync cron or immediate trigger if desired)
                // For now we just confirm DB status
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Process Payment Error: " . $e->getMessage());
            return false;
        }
    }
}

