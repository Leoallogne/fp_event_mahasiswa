<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

// Get redirect from landing page
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $auth->login($email, $password);

    if ($result['success']) {
        if ($result['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: ' . $redirect);
        }
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css?v=4">
</head>

<body>
    <div class="auth-container">
        <!-- Left Hero Section -->
        <div class="auth-hero">
            <div class="hero-content">
                <div class="hero-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <h1 class="hero-title">Selamat Datang Kembali!</h1>
                <p class="hero-subtitle">Kelola dan ikuti event kampus dengan mudah</p>
                <div class="hero-features">
                    <div class="hero-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Gratis & Mudah</span>
                    </div>
                    <div class="hero-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Event Terpercaya</span>
                    </div>
                    <div class="hero-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Notifikasi Real-time</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Section -->
        <div class="auth-form-section">
            <div class="form-wrapper">
                <div class="form-header">
                    <h2 class="form-title">Login</h2>
                    <p class="form-subtitle">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-custom alert-danger-custom">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert-custom alert-success-custom">
                        <i class="bi bi-check-circle-fill"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group-custom">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" class="form-control-custom" id="email" name="email" placeholder="Email"
                            required>
                    </div>

                    <div class="input-group-custom">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" class="form-control-custom" id="password" name="password"
                            placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>

                    <div class="divider">
                        <span class="divider-text">atau masuk dengan</span>
                    </div>

                    <a href="auth/google.php" class="btn-google">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                        Google
                    </a>

                    <div class="form-footer">
                        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                        <a href="index.php" class="back-link">
                            <i class="bi bi-arrow-left me-1"></i>Kembali ke halaman utama
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>

</html>