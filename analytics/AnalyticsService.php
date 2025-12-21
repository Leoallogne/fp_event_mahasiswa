<?php

require_once __DIR__ . '/../config/database.php';

class AnalyticsService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function hitungKategoriEventTerbanyakPeminat() {
        try {
            $stmt = $this->db->prepare("SELECT e.kategori, 
                                      COUNT(r.id) as total_peserta,
                                      COUNT(DISTINCT e.id) as total_event
                                      FROM events e
                                      LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                                      GROUP BY e.kategori
                                      ORDER BY total_peserta DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Analytics Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function hitungRataRataPesertaPerEvent() {
        try {
            $stmt = $this->db->prepare("SELECT 
                                      COUNT(DISTINCT e.id) as total_event,
                                      COUNT(r.id) as total_peserta,
                                      ROUND(COUNT(r.id) / COUNT(DISTINCT e.id), 2) as rata_rata
                                      FROM events e
                                      LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'");
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Analytics Error: " . $e->getMessage());
            return null;
        }
    }
    
    public function trenJumlahEventBulanan($limit = 12) {
        try {
            $stmt = $this->db->prepare("SELECT 
                                        DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                                        COUNT(*) as jumlah_event,
                                        COUNT(DISTINCT r.user_id) as total_peserta
                                        FROM events e
                                        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                                        WHERE tanggal >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                                        GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                                        ORDER BY bulan ASC");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Analytics Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function rekomendasiEvent($userId = null, $limit = 5) {
        try {
            // Recommend events based on:
            // 1. Upcoming events
            // 2. Events with available quota
            // 3. Events in categories user hasn't registered yet (if userId provided)
            
            $sql = "SELECT e.*, 
                    COUNT(r.id) as registered_count,
                    (e.kuota - COUNT(r.id)) as available_quota
                    FROM events e
                    LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                    WHERE e.tanggal >= NOW()
                    AND e.kuota > COUNT(r.id)";
            
            if ($userId) {
                // Exclude events user already registered
                $sql .= " AND e.id NOT IN (
                            SELECT event_id FROM registrations 
                            WHERE user_id = ? AND status = 'confirmed'
                         )";
            }
            
            $sql .= " GROUP BY e.id
                      HAVING available_quota > 0
                      ORDER BY e.tanggal ASC
                      LIMIT ?";
            
            if ($userId) {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId, $limit]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$limit]);
            }
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Analytics Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getEventStats() {
        try {
            $stats = [];
            
            // Total events
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM events");
            $stmt->execute();
            $stats['total_events'] = $stmt->fetch()['total'];
            
            // Total registrations
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM registrations WHERE status = 'confirmed'");
            $stmt->execute();
            $stats['total_registrations'] = $stmt->fetch()['total'];
            
            // Total users
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetch()['total'];
            
            // Upcoming events
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM events WHERE tanggal >= NOW()");
            $stmt->execute();
            $stats['upcoming_events'] = $stmt->fetch()['total'];
            
            return $stats;
        } catch(PDOException $e) {
            error_log("Analytics Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function exportToCSV($data, $filename = 'report.csv') {
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (Excel compatibility)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (empty($data)) {
            // If no data, write header only with message
            fputcsv($output, ['No Data Available']);
            fputcsv($output, ['Tidak ada data untuk diekspor']);
        } else {
            // Get headers from first row
            $headers = array_keys($data[0]);
            
            // Write headers
            fputcsv($output, $headers);
            
            // Write data rows
            foreach ($data as $row) {
                // Ensure all values are properly formatted
                $csvRow = [];
                foreach ($headers as $header) {
                    $value = $row[$header] ?? '';
                    // Convert to string and handle special characters
                    $csvRow[] = is_numeric($value) ? $value : (string)$value;
                }
                fputcsv($output, $csvRow);
            }
        }
        
        fclose($output);
        exit;
    }
    
    public function exportEventsToCSV() {
        try {
            $stmt = $this->db->prepare("SELECT 
                e.id,
                e.title,
                e.kategori,
                e.tanggal,
                e.lokasi,
                e.kuota,
                COUNT(r.id) as jumlah_peserta,
                u.nama as dibuat_oleh,
                e.created_at
                FROM events e
                LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
                LEFT JOIN users u ON e.created_by = u.id
                GROUP BY e.id
                ORDER BY e.tanggal DESC");
            $stmt->execute();
            $rawData = $stmt->fetchAll();
            
            // Format data dengan header yang jelas
            $data = [];
            foreach ($rawData as $row) {
                $data[] = [
                    'ID' => $row['id'],
                    'Judul Event' => $row['title'],
                    'Kategori' => $row['kategori'],
                    'Tanggal' => $row['tanggal'],
                    'Lokasi' => $row['lokasi'],
                    'Kuota' => (int)$row['kuota'],
                    'Jumlah Peserta' => (int)$row['jumlah_peserta'],
                    'Dibuat Oleh' => $row['dibuat_oleh'] ?? '-',
                    'Tanggal Dibuat' => $row['created_at']
                ];
            }
            
            $this->exportToCSV($data, 'daftar_event_' . date('Y-m-d') . '.csv');
        } catch(PDOException $e) {
            error_log("Export Events Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function exportRegistrationsToCSV() {
        try {
            $stmt = $this->db->prepare("SELECT 
                r.id,
                u.nama,
                u.email,
                e.title,
                e.kategori,
                e.tanggal,
                r.daftar_waktu,
                r.status
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                JOIN events e ON r.event_id = e.id
                WHERE r.status = 'confirmed'
                ORDER BY r.daftar_waktu DESC");
            $stmt->execute();
            $rawData = $stmt->fetchAll();
            
            // Format data dengan header yang jelas
            $data = [];
            foreach ($rawData as $row) {
                $data[] = [
                    'ID' => $row['id'],
                    'Nama Peserta' => $row['nama'],
                    'Email' => $row['email'],
                    'Event' => $row['title'],
                    'Kategori' => $row['kategori'],
                    'Tanggal Event' => $row['tanggal'],
                    'Waktu Daftar' => $row['daftar_waktu'],
                    'Status' => $row['status']
                ];
            }
            
            $this->exportToCSV($data, 'daftar_peserta_' . date('Y-m-d') . '.csv');
        } catch(PDOException $e) {
            error_log("Export Registrations Error: " . $e->getMessage());
            return false;
        }
    }
}

