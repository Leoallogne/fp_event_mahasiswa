<?php
require_once __DIR__ . '/../../config/database.php';

class AnalyticsService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getEventStats()
    {
        // Total Events
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events");
        $totalEvents = $stmt->fetch()['total'];

        // Total Registrations
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM registrations");
        $totalRegistrations = $stmt->fetch()['total'];

        // Total Users (non-admin)
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        $totalUsers = $stmt->fetch()['total'];

        // Upcoming Events
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM events WHERE tanggal >= CURDATE()");
        $upcomingEvents = $stmt->fetch()['total'];

        // Revenue (Confirmed registrations * price)
        // Assuming 'pending' doesn't count, and 'confirmed' means paid.
        // Joining to get price from events
        $stmt = $this->db->query("
            SELECT SUM(e.price) as total 
            FROM registrations r 
            JOIN events e ON r.event_id = e.id 
            WHERE r.status = 'confirmed'
        ");
        $revenue = $stmt->fetch()['total'] ?? 0;

        return [
            'total_events' => $totalEvents,
            'total_registrations' => $totalRegistrations,
            'total_users' => $totalUsers,
            'upcoming_events' => $upcomingEvents,
            'total_revenue' => $revenue
        ];
    }

    public function trenJumlahEventBulanan($limit = 12)
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(MAX(tanggal), '%M %Y') as bulan, 
                COUNT(*) as jumlah_event
            FROM events
            GROUP BY YEAR(tanggal), MONTH(tanggal)
            ORDER BY YEAR(tanggal) DESC, MONTH(tanggal) DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_reverse($results); // Chronological order
    }

    public function hitungKategoriEventTerbanyakPeminat()
    {
        $stmt = $this->db->query("
            SELECT e.kategori, COUNT(r.id) as total_peserta
            FROM events e
            LEFT JOIN registrations r ON e.id = r.event_id
            GROUP BY e.kategori
            ORDER BY total_peserta DESC
            LIMIT 10
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hitungRataRataPesertaPerEvent()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(r.id) as total_peserta, 
                COUNT(DISTINCT e.id) as total_event,
                (COUNT(r.id) / NULLIF(COUNT(DISTINCT e.id), 0)) as rata_rata
            FROM events e
            LEFT JOIN registrations r ON e.id = r.event_id
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_peserta' => $result['total_peserta'],
            'total_event' => $result['total_event'],
            'rata_rata' => round($result['rata_rata'], 1)
        ];
    }

    // New Method for Export CSV
    public function getExportData()
    {
        // Get detailed list of all registrations
        // Columns: Registration ID, User Name, User Email, Event Title, Event Date, Category, Status, Price, Registered At
        $sql = "
            SELECT 
                r.id as registration_id,
                u.nama as user_name,
                u.email as user_email,
                e.title as event_title,
                e.tanggal as event_date,
                e.kategori as event_category,
                e.price as event_price,
                r.status as registration_status,
                r.daftar_waktu as registered_at
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            JOIN events e ON r.event_id = e.id
            ORDER BY r.daftar_waktu DESC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Dashboard: User Registration Trend
    public function getUserRegistrationTrend($months = 6)
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(MAX(created_at), '%b %Y') as month,
                COUNT(*) as user_count
            FROM users 
            WHERE role = 'user'
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at) DESC, MONTH(created_at) DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $months, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_reverse($results); // Chronological order
    }

    // Dashboard: Event Type Distribution (Free vs Paid)
    public function getEventTypeDistribution()
    {
        $stmt = $this->db->query("
            SELECT 
                CASE 
                    WHEN price > 0 THEN 'Berbayar'
                    ELSE 'Gratis'
                END as event_type,
                COUNT(*) as count
            FROM events
            GROUP BY event_type
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Analytics: Event Participation Stats
    public function getEventParticipationStats($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.title,
                COUNT(r.id) as participant_count,
                e.kuota
            FROM events e
            LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
            GROUP BY e.id
            ORDER BY participant_count DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Analytics: Revenue by Event
    public function getRevenueByEvent($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.title,
                e.price,
                COUNT(r.id) as confirmed_count,
                (e.price * COUNT(r.id)) as total_revenue
            FROM events e
            JOIN registrations r ON e.id = r.event_id
            WHERE e.price > 0 AND r.status = 'confirmed'
            GROUP BY e.id
            ORDER BY total_revenue DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Analytics: Revenue Trend Over Time
    public function getRevenueTrend($months = 6)
    {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(MAX(r.daftar_waktu), '%b %Y') as month,
                SUM(e.price) as monthly_revenue
            FROM registrations r
            JOIN events e ON r.event_id = e.id
            WHERE r.status = 'confirmed' AND e.price > 0
            GROUP BY YEAR(r.daftar_waktu), MONTH(r.daftar_waktu)
            ORDER BY YEAR(r.daftar_waktu) DESC, MONTH(r.daftar_waktu) DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $months, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_reverse($results); // Chronological order
    }
}

