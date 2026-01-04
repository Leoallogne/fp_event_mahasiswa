<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/url_helper.php';
require_once __DIR__ . '/../../modules/notifications/NotificationService.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function register($nama, $email, $password)
    {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email sudah terdaftar'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$nama, $email, $hashedPassword]);
            $userId = $this->db->lastInsertId();

            // Send Welcome Notification
            try {
                $notificationService = new NotificationService();
                $notificationService->createWelcomeNotification($userId, $nama, $email);
            } catch (Exception $e) {
                error_log("Welcome Notification Error: " . $e->getMessage());
            }

            return ['success' => true, 'message' => 'Registrasi berhasil'];
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat registrasi'];
        }
    }

    public function login($email, $password)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                Session::start();
                Session::regenerate();
                Session::set('user_id', $user['id']);
                Session::set('user_nama', $user['nama']);
                Session::set('user_email', $user['email']);
                Session::set('user_role', $user['role']);
                Session::set('logged_in', true);

                // Send Login Notification (Security Alert)
                try {
                    $notificationService = new NotificationService();
                    $notificationService->createLoginNotification($user['id']);
                } catch (Exception $e) {
                    error_log("Login Notification Error: " . $e->getMessage());
                }

                return ['success' => true, 'role' => $user['role'], 'message' => 'Login berhasil'];
            }

            return ['success' => false, 'message' => 'Email atau password salah'];
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat login'];
        }
    }

    public function logout()
    {
        Session::destroy();
        return ['success' => true, 'message' => 'Logout berhasil'];
    }

    public function isLoggedIn()
    {
        return Session::has('logged_in') && Session::get('logged_in') === true;
    }

    public function isAdmin()
    {
        return $this->isLoggedIn() && Session::get('user_role') === 'admin';
    }

    public function isUser()
    {
        return $this->isLoggedIn() && Session::get('user_role') === 'user';
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            UrlHelper::redirect('login.php');
        }
    }

    public function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            UrlHelper::redirect('index.php');
        }
    }

    public function requireUser()
    {
        $this->requireLogin();
        if (!$this->isUser()) {
            UrlHelper::redirect('admin/dashboard.php');
        }
    }

    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => Session::get('user_id'),
            'nama' => Session::get('user_nama'),
            'email' => Session::get('user_email'),
            'role' => Session::get('user_role')
        ];
    }

    public function getAllUsers()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get All Users Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nama, email, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get User By ID Error: " . $e->getMessage());
            return null;
        }
    }

    public function updateUser($id, $nama, $email, $role)
    {
        try {
            // Check if email already exists for other user
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email sudah digunakan oleh user lain'];
            }

            // Update user
            $stmt = $this->db->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $role, $id]);

            return ['success' => true, 'message' => 'User berhasil diupdate'];
        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat update user'];
        }
    }

    public function deleteUser($id)
    {
        try {
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'User tidak ditemukan'];
            }

            // Prevent deleting the last admin
            if ($user['role'] === 'admin') {
                $stmt = $this->db->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
                $stmt->execute();
                $result = $stmt->fetch();

                if ($result['admin_count'] <= 1) {
                    return ['success' => false, 'message' => 'Tidak dapat menghapus admin terakhir'];
                }
            }

            // Delete user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'User berhasil dihapus'];
        } catch (PDOException $e) {
            error_log("Delete User Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menghapus user'];
        }
    }

    public function createUser($nama, $email, $password, $role = 'user')
    {
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email sudah terdaftar'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $hashedPassword, $role]);

            return ['success' => true, 'message' => 'User berhasil dibuat'];
        } catch (PDOException $e) {
            error_log("Create User Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat membuat user'];
        }
    }
}

