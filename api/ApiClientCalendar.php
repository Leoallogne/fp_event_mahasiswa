<?php

require_once __DIR__ . '/../config/database.php';

class ApiClientCalendar {
    private $db;
    private $apiKey;
    private $clientId;
    private $clientSecret;
    private $baseUrl = 'https://www.googleapis.com/calendar/v3';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Load environment variables
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
        
        $this->apiKey = $_ENV['GOOGLE_CALENDAR_API_KEY'] ?? '';
        $this->clientId = $_ENV['GOOGLE_CALENDAR_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['GOOGLE_CALENDAR_CLIENT_SECRET'] ?? '';
    }
    
    public function fetch($calendarId = 'primary', $timeMin = null, $timeMax = null) {
        try {
            $url = $this->baseUrl . '/calendars/' . urlencode($calendarId) . '/events';
            $params = ['key' => $this->apiKey];
            
            if ($timeMin) $params['timeMin'] = $timeMin;
            if ($timeMax) $params['timeMax'] = $timeMax;
            
            $url .= '?' . http_build_query($params);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $this->cacheEvents($data['items'] ?? []);
                return ['success' => true, 'data' => $data];
            }
            
            return ['success' => false, 'message' => 'Failed to fetch calendar events'];
        } catch(Exception $e) {
            error_log("Calendar Fetch Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function pushEvent($eventData) {
        try {
            $url = $this->baseUrl . '/calendars/primary/events?key=' . $this->apiKey;
            
            $event = [
                'summary' => $eventData['title'],
                'description' => $eventData['deskripsi'] ?? '',
                'location' => $eventData['lokasi'] ?? '',
                'start' => [
                    'dateTime' => $eventData['tanggal'],
                    'timeZone' => 'Asia/Jakarta'
                ],
                'end' => [
                    'dateTime' => date('Y-m-d\TH:i:s', strtotime($eventData['tanggal'] . ' +2 hours')),
                    'timeZone' => 'Asia/Jakarta'
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->getAccessToken()
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 || $httpCode === 201) {
                $data = json_decode($response, true);
                return ['success' => true, 'eventId' => $data['id'] ?? null, 'data' => $data];
            }
            
            return ['success' => false, 'message' => 'Failed to create calendar event'];
        } catch(Exception $e) {
            error_log("Calendar Push Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateEvent($eventId, $eventData) {
        try {
            $url = $this->baseUrl . '/calendars/primary/events/' . urlencode($eventId) . '?key=' . $this->apiKey;
            
            $event = [
                'summary' => $eventData['title'],
                'description' => $eventData['deskripsi'] ?? '',
                'location' => $eventData['lokasi'] ?? '',
                'start' => [
                    'dateTime' => $eventData['tanggal'],
                    'timeZone' => 'Asia/Jakarta'
                ],
                'end' => [
                    'dateTime' => date('Y-m-d\TH:i:s', strtotime($eventData['tanggal'] . ' +2 hours')),
                    'timeZone' => 'Asia/Jakarta'
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->getAccessToken()
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return ['success' => true, 'data' => json_decode($response, true)];
            }
            
            return ['success' => false, 'message' => 'Failed to update calendar event'];
        } catch(Exception $e) {
            error_log("Calendar Update Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteEvent($eventId) {
        try {
            $url = $this->baseUrl . '/calendars/primary/events/' . urlencode($eventId) . '?key=' . $this->apiKey;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->getAccessToken()
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 204 || $httpCode === 200) {
                return ['success' => true];
            }
            
            return ['success' => false, 'message' => 'Failed to delete calendar event'];
        } catch(Exception $e) {
            error_log("Calendar Delete Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function cacheEvents($events) {
        try {
            // Store cached events in database
            $stmt = $this->db->prepare("INSERT INTO calendar_cache (event_id, event_data, cached_at) 
                                        VALUES (?, ?, NOW())
                                        ON DUPLICATE KEY UPDATE event_data = ?, cached_at = NOW()");
            
            foreach ($events as $event) {
                $stmt->execute([
                    $event['id'] ?? '',
                    json_encode($event),
                    json_encode($event)
                ]);
            }
        } catch(PDOException $e) {
            // Table might not exist, ignore
            error_log("Cache Events Error: " . $e->getMessage());
        }
    }
    
    private function getAccessToken() {
        // In a real implementation, you would use OAuth2 flow
        // For now, return empty or use API key only
        return '';
    }
}

