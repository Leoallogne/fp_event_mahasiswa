<?php

require_once __DIR__ . '/../../config/database.php';

class GoogleAuth
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $db;

    public function __construct()
    {
        // Load .env
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0)
                    continue;
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Remove quotes if present
                    if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match("/^'(.*)'$/", $value, $matches)) {
                        $value = $matches[1];
                    }
                    $_ENV[$key] = $value;
                }
            }
        }

        $this->clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $this->redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAuthUrl()
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
            'access_type' => 'offline',
            'prompt' => 'select_account'
        ];
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function authenticate($code)
    {
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $code
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            return $this->getUserInfo($data['access_token']);
        }
        return null;
    }

    private function getUserInfo($accessToken)
    {
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function findOrCreateUser($googleUser)
    {
        try {
            // Check if user exists by google_id
            $stmt = $this->db->prepare("SELECT * FROM users WHERE google_id = ?");
            $stmt->execute([$googleUser['sub']]);
            $user = $stmt->fetch();

            if ($user) {
                return $user;
            }

            // Check if user exists by email
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$googleUser['email']]);
            $user = $stmt->fetch();

            if ($user) {
                // Link google_id to existing user
                $stmt = $this->db->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                $stmt->execute([$googleUser['sub'], $user['id']]);
                $user['google_id'] = $googleUser['sub'];
                return $user;
            }

            // Create new user
            $stmt = $this->db->prepare("INSERT INTO users (nama, email, google_id, role, password) VALUES (?, ?, ?, 'user', '')");
            $stmt->execute([
                $googleUser['name'],
                $googleUser['email'],
                $googleUser['sub']
            ]);

            $newId = $this->db->lastInsertId();
            return [
                'id' => $newId,
                'nama' => $googleUser['name'],
                'email' => $googleUser['email'],
                'role' => 'user'
            ];
        } catch (PDOException $e) {
            error_log("Google Auth Error: " . $e->getMessage());
            return null;
        }
    }
}
