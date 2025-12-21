<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/events/EventService.php';

// Check if PHPMailer is available
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class NotificationService {
    private $db;
    private $eventService;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->eventService = new EventService();
    }
    
    public function createNotification($userId, $eventId, $message) {
        try {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, event_id, message, status, created_at) 
                                        VALUES (?, ?, ?, 'sent', NOW())");
            $stmt->execute([$userId, $eventId, $message]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch(PDOException $e) {
            error_log("Create Notification Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membuat notifikasi'];
        }
    }
    
    public function createRegistrationNotification($userId, $eventId) {
        try {
            $event = $this->eventService->getEventById($eventId);
            if (!$event) {
                return ['success' => false, 'message' => 'Event tidak ditemukan'];
            }
            
            $message = "Anda berhasil mendaftar untuk event '{$event['title']}' yang akan diselenggarakan pada " . 
                      date('d/m/Y H:i', strtotime($event['tanggal'])) . 
                      " di {$event['lokasi']}";
            
            return $this->createNotification($userId, $eventId, $message);
        } catch(PDOException $e) {
            error_log("Create Registration Notification Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membuat notifikasi pendaftaran'];
        }
    }
    
    public function createEventUpdateNotification($eventId, $message) {
        try {
            $event = $this->eventService->getEventById($eventId);
            if (!$event) {
                return ['success' => false, 'message' => 'Event tidak ditemukan'];
            }
            
            // Get registrations directly from database
            $stmt = $this->db->prepare("SELECT r.user_id, r.email, r.nama FROM registrations r WHERE r.event_id = ? AND r.status = 'confirmed'");
            $stmt->execute([$eventId]);
            $registrations = $stmt->fetchAll();
            
            $successCount = 0;
            
            foreach ($registrations as $registration) {
                $result = $this->createNotification($registration['user_id'], $eventId, $message);
                if ($result['success']) {
                    $successCount++;
                }
            }
            
            return ['success' => true, 'sent' => $successCount, 'message' => "Notifikasi update dikirim ke {$successCount} peserta"];
        } catch(PDOException $e) {
            error_log("Create Event Update Notification Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim notifikasi update'];
        }
    }
    
    public function sendEventReminder($eventId) {
        try {
            $event = $this->eventService->getEventById($eventId);
            if (!$event) {
                return ['success' => false, 'message' => 'Event tidak ditemukan'];
            }
            
            // Get registrations directly from database
            $stmt = $this->db->prepare("SELECT r.user_id, u.email, u.nama FROM registrations r 
                                       JOIN users u ON r.user_id = u.id 
                                       WHERE r.event_id = ? AND r.status = 'confirmed'");
            $stmt->execute([$eventId]);
            $registrations = $stmt->fetchAll();
            
            $sentCount = 0;
            
            foreach ($registrations as $registration) {
                $message = "Reminder: Event '{$event['title']}' akan dilaksanakan pada " . 
                          date('d/m/Y H:i', strtotime($event['tanggal'])) . 
                          " di {$event['lokasi']}";
                
                // Create notification record
                $notification = $this->createNotification($registration['user_id'], $eventId, $message);
                
                if ($notification['success']) {
                    // Send email
                    $emailSent = $this->sendEmail(
                        $registration['email'],
                        $registration['nama'],
                        "Reminder Event: {$event['title']}",
                        $message
                    );
                    
                    if ($emailSent) {
                        // Update notification status
                        $stmt = $this->db->prepare("UPDATE notifications SET status = 'sent', sent_time = NOW() WHERE id = ?");
                        $stmt->execute([$notification['id']]);
                        $sentCount++;
                    }
                }
            }
            
            return ['success' => true, 'sent' => $sentCount, 'total' => count($registrations)];
        } catch(Exception $e) {
            error_log("Send Reminder Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim reminder'];
        }
    }
    
    public function sendEmail($to, $name, $subject, $message) {
        try {
            // Load PHPMailer if available
            if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
                require_once __DIR__ . '/../../vendor/autoload.php';
                
                // Load environment variables
                $envFile = __DIR__ . '/../../.env';
                $env = [];
                if (file_exists($envFile)) {
                    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        if (strpos(trim($line), '#') === 0) continue;
                        list($key, $value) = explode('=', $line, 2);
                        $env[trim($key)] = trim($value);
                    }
                }
                
                if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    
                    $mail->isSMTP();
                    $mail->Host = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = $env['SMTP_USER'] ?? '';
                    $mail->Password = $env['SMTP_PASS'] ?? '';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = $env['SMTP_PORT'] ?? 587;
                    $mail->CharSet = 'UTF-8';
                    
                    $mail->setFrom($env['SMTP_USER'] ?? '', $env['SMTP_FROM_NAME'] ?? 'Event Management System');
                    $mail->addAddress($to, $name);
                    
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $this->generateEmailTemplate($subject, $message, $name);
                    $mail->AltBody = strip_tags($message);
                    
                    $mail->send();
                    return true;
                } else {
                    // Fallback: log email (for development)
                    error_log("Email would be sent to: $to\nSubject: $subject\nMessage: $message");
                    return true;
                }
            } else {
                // Fallback: log email (for development)
                error_log("Email would be sent to: $to\nSubject: $subject\nMessage: $message");
                return true;
            }
        } catch(Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function generateEmailTemplate($subject, $message, $recipientName) {
        $logoUrl = "https://via.placeholder.com/200x60/4361ee/ffffff?text=EventKu";
        $currentYear = date('Y');
        
        return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$subject}</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
                .header { background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%); padding: 30px; text-align: center; }
                .header img { max-width: 200px; height: auto; margin-bottom: 20px; }
                .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 600; }
                .content { padding: 40px 30px; }
                .content h2 { color: #333333; margin-bottom: 20px; font-size: 24px; }
                .content p { color: #666666; line-height: 1.6; margin-bottom: 20px; font-size: 16px; }
                .button { display: inline-block; background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%); color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
                .footer { background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef; }
                .footer p { color: #6c757d; margin: 5px 0; font-size: 14px; }
                .footer a { color: #4361ee; text-decoration: none; }
                .social-links { margin: 20px 0; }
                .social-links a { margin: 0 10px; }
                @media only screen and (max-width: 600px) {
                    .container { width: 100%; }
                    .header { padding: 20px; }
                    .content { padding: 30px 20px; }
                    .footer { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>EventKu</h1>
                    <p style='color: #ffffff; margin: 10px 0 0 0;'>Sistem Manajemen Event Profesional</p>
                </div>
                
                <div class='content'>
                    <h2>{$subject}</h2>
                    <p>Dear {$recipientName},</p>
                    <p>{$message}</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='#' class='button'>Lihat Detail Event</a>
                    </div>
                    
                    <p>Terima kasih telah menggunakan layanan EventKu. Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami.</p>
                </div>
                
                <div class='footer'>
                    <p>&copy; {$currentYear} EventKu. All rights reserved.</p>
                    <p>This email was sent to {$to}. If you no longer wish to receive these emails, please contact us.</p>
                    <div class='social-links'>
                        <a href='#' style='color: #4361ee;'>Website</a> | 
                        <a href='#' style='color: #4361ee;'>Support</a> | 
                        <a href='#' style='color: #4361ee;'>Contact</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    public function getNotifications($userId = null, $status = null) {
        try {
            $sql = "SELECT n.*, e.title as event_title, u.nama as user_name
                    FROM notifications n
                    LEFT JOIN events e ON n.event_id = e.id
                    LEFT JOIN users u ON n.user_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if ($userId !== null) {
                $sql .= " AND n.user_id = ?";
                $params[] = $userId;
            }
            
            if ($status !== null) {
                $sql .= " AND n.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY n.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Notifications Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function markAsSent($notificationId) {
        try {
            $stmt = $this->db->prepare("UPDATE notifications SET status = 'sent', sent_time = NOW() WHERE id = ?");
            $stmt->execute([$notificationId]);
            return true;
        } catch(PDOException $e) {
            error_log("Mark Notification Sent Error: " . $e->getMessage());
            return false;
        }
    }
    
    // User notification methods
    public function getUserNotifications($userId, $limit = null) {
        try {
            $sql = "SELECT n.*, e.title as event_title, e.nama as event_name, e.lokasi as event_location,
                           CASE 
                               WHEN n.status = 'sent' AND n.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'unread'
                               WHEN n.status = 'sent' AND n.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'unread'
                               ELSE 'read'
                           END as is_read,
                           'reminder' as type
                    FROM notifications n
                    LEFT JOIN events e ON n.event_id = e.id
                    WHERE n.user_id = ? AND n.status = 'sent'
                    ORDER BY n.created_at DESC";
            
            if ($limit !== null) {
                $sql .= " LIMIT ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId, $limit]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
            }
            
            $notifications = $stmt->fetchAll();
            
            // Add title and message formatting
            foreach ($notifications as &$notif) {
                if (!$notif['event_title'] && $notif['event_name']) {
                    $notif['event_title'] = $notif['event_name'];
                }
                
                if (empty($notif['title'])) {
                    $notif['title'] = 'Notifikasi Event';
                }
                
                if (empty($notif['message'])) {
                    $notif['message'] = 'Anda memiliki notifikasi terkait event.';
                }
            }
            
            return $notifications;
        } catch(PDOException $e) {
            error_log("Get User Notifications Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = ? 
                AND status = 'sent' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute([$userId]);
            return (int)$stmt->fetch()['count'];
        } catch(PDOException $e) {
            error_log("Get Unread Count Error: " . $e->getMessage());
            return 0;
        }
    }
    
    public function markAsRead($notificationId, $userId) {
        try {
            // For this implementation, we'll consider notifications older than 24 hours as read
            // In a real implementation, you might want to add an is_read column to the database
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET created_at = DATE_SUB(created_at, INTERVAL 2 DAY) 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$notificationId, $userId]);
            
            return ['success' => true, 'message' => 'Notifikasi ditandai sebagai dibaca'];
        } catch(PDOException $e) {
            error_log("Mark As Read Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menandai notifikasi sebagai dibaca'];
        }
    }
    
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET created_at = DATE_SUB(created_at, INTERVAL 2 DAY) 
                WHERE user_id = ? 
                AND status = 'sent' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stmt->execute([$userId]);
            
            return ['success' => true, 'message' => 'Semua notifikasi ditandai sebagai dibaca'];
        } catch(PDOException $e) {
            error_log("Mark All As Read Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menandai semua notifikasi sebagai dibaca'];
        }
    }
}

