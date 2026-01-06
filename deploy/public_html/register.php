<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Password tidak cocok';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        $result = $auth->register($nama, $email, $password);

        if ($result['success']) {
            $success = $result['message'] . '. Silakan login.';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EventKu</title>
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
                    <i class="bi bi-person-plus"></i>
                </div>
                <h1 class="hero-title">Bergabung Sekarang!</h1>
                <p class="hero-subtitle">Mulai perjalanan Anda dalam dunia event kampus</p>
                <div class="hero-features">
                    <div class="hero-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>100% Gratis</span>
                    </div>
                    <div class="hero-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Akses Penuh</span>
                    </div>
                    <div class="hero-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Daftar Cepat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Section -->
        <div class="auth-form-section">
            <div class="form-wrapper">
                <div class="form-header">
                    <h2 class="form-title">Buat Akun</h2>
                    <p class="form-subtitle">Isi data Anda untuk membuat akun baru</p>
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
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" class="form-control-custom" id="nama" name="nama" placeholder="Nama Lengkap"
                            required>
                    </div>

                    <div class="input-group-custom">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" class="form-control-custom" id="email" name="email" placeholder="Email"
                            required>
                    </div>

                    <div class="input-group-custom">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" class="form-control-custom" id="password" name="password"
                            placeholder="Password" required minlength="6" oninput="checkPasswordStrength()">
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="bi bi-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar">
                            <div class="strength-fill"></div>
                        </div>
                        <small id="strengthText" class="text-muted">Minimal 6 karakter</small>
                    </div>

                    <div class="input-group-custom">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control-custom" id="confirm_password" name="confirm_password"
                            placeholder="Konfirmasi Password" required minlength="6">
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="bi bi-eye" id="toggleIcon2"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                    </button>

                    <div class="form-footer">
                        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
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
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

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

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;

            strengthBar.className = 'strength-bar';
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Password lemah';
                strengthText.style.color = '#ef4444';
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Password sedang';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Password kuat';
                strengthText.style.color = '#10b981';
            }
        }
    </script>
</body>

</html>