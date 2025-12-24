<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/GoogleAuth.php';

$googleAuth = new GoogleAuth();

// Auto-migrate database if needed
try {
    $database = new Database();
    $db = $database->getConnection();
    // Check if google_id exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email");
    }
} catch (Exception $e) {
    // Silently continue if column already exists or other DB error
}

if (isset($_GET['code'])) {
    $googleUser = $googleAuth->authenticate($_GET['code']);

    if ($googleUser) {
        $user = $googleAuth->findOrCreateUser($googleUser);

        if ($user) {
            Session::start();
            Session::regenerate();
            Session::set('user_id', $user['id']);
            Session::set('user_nama', $user['nama']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);
            Session::set('logged_in', true);

            header('Location: ../dashboard.php');
            exit;
        }
    }
}

// If something goes wrong
header('Location: ../login.php?error=google_failed');
exit;
