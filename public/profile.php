<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../config/validator.php';

$auth = new Auth();
$auth->requireUser();

$database = new Database();
$db = $database->getConnection();
$currentUser = $auth->getCurrentUser();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nama = Validator::sanitize($_POST['nama'] ?? '');
        $email = Validator::sanitize($_POST['email'] ?? '');

        // Validasi
        if (empty($nama) || empty($email)) {
            $error = 'Semua field harus diisi';
        } elseif (!Validator::validateEmail($email)) {
            $error = 'Email tidak valid';
        } else {
            try {
                // Cek apakah email sudah digunakan oleh user lain
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $currentUser['id']]);
                if ($stmt->fetch()) {
                    $error = 'Email sudah digunakan oleh user lain';
                } else {
                    // Handle upload avatar
                    $avatarFileName = $currentUser['avatar'] ?? null;
                    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['avatar']['tmp_name'];
                        $fileName = $_FILES['avatar']['name'];
                        $fileSize = $_FILES['avatar']['size'];
                        $fileType = $_FILES['avatar']['type'];
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));

                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $newFileName = 'avatar_' . $currentUser['id'] . '_' . time() . '.' . $fileExtension;
                            $uploadFileDir = __DIR__ . '/uploads/avatars/';
                            if (!is_dir($uploadFileDir)) {
                                mkdir($uploadFileDir, 0755, true);
                            }
                            $destPath = $uploadFileDir . $newFileName;

                            if (move_uploaded_file($fileTmpPath, $destPath)) {
                                $avatarFileName = $newFileName;
                            } else {
                                $error = 'Gagal mengunggah avatar';
                            }
                        } else {
                            $error = 'Format avatar tidak didukung (jpg, jpeg, png, gif)';
                        }
                    }

                    if (!$error) {
                        // Update profile
                        $stmt = $db->prepare("UPDATE users SET nama = ?, email = ?, avatar = ? WHERE id = ?");
                        $stmt->execute([$nama, $email, $avatarFileName, $currentUser['id']]);

                        // Update session
                        Session::set('user_nama', $nama);
                        Session::set('user_email', $email);
                        $currentUser['nama'] = $nama;
                        $currentUser['email'] = $email;
                        $currentUser['avatar'] = $avatarFileName;

                        $success = 'Profile berhasil diperbarui';
                    }
                }
            } catch (PDOException $e) {
                error_log("Update Profile Error: " . $e->getMessage());
                $error = 'Terjadi kesalahan saat memperbarui profile';
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validasi
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Semua field password harus diisi';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Password baru tidak cocok';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password baru minimal 6 karakter';
        } else {
            try {
                // Verifikasi password lama
                $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$currentUser['id']]);
                $user = $stmt->fetch();

                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Password lama salah';
                } else {
                    // Update password
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $currentUser['id']]);

                    $success = 'Password berhasil diubah';
                }
            } catch (PDOException $e) {
                error_log("Change Password Error: " . $e->getMessage());
                $error = 'Terjadi kesalahan saat mengubah password';
            }
        }
    }
}

// Get user stats
try {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM registrations WHERE user_id = ? AND status = 'confirmed'");
    $stmt->execute([$currentUser['id']]);
    $stats = $stmt->fetch();
    $totalRegistrations = $stats['total'] ?? 0;
} catch (PDOException $e) {
    $totalRegistrations = 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Saya - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --font-inter: 'Inter', sans-serif;
        }

        body {
            background-color: #f3f4f6;
            font-family: var(--font-inter);
            color: #1f2937;
        }

        .main-content {
            margin-left: 250px;
            padding: 0;
            min-height: 100vh;
        }

        /* Hero Profile Section */
        .profile-hero {
            background: var(--primary-gradient);
            padding: 3rem 2rem 8rem;
            position: relative;
            overflow: hidden;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
        }

        .profile-hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .avatar-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 1.5rem;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #4f46e5;
            overflow: hidden;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .hero-email {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .hero-badge {
            display: inline-block;
            padding: 0.5rem 1.25rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Stats Section */
        .stats-section {
            margin-top: -5rem;
            padding: 0 2rem 2rem;
            position: relative;
            z-index: 2;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(229, 231, 235, 0.5);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.primary {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }

        .stat-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-icon.info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
        }

        /* Content Section */
        .content-section {
            padding: 0 2rem 2rem;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Tabs */
        .nav-tabs-custom {
            border-bottom: 2px solid #f3f4f6;
            padding: 0 1.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 500;
            padding: 1rem 1.5rem;
            position: relative;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4f46e5;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4f46e5;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #4f46e5;
        }

        .tab-content {
            padding: 2rem 1.5rem;
        }

        /* Form Styling */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Avatar Upload */
        .avatar-upload-wrapper {
            text-align: center;
            margin-bottom: 2rem;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            overflow: hidden;
            border: 3px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            font-size: 3rem;
            color: #d1d5db;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .btn-upload input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #6b7280;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }

            .profile-hero {
                padding: 2rem 1rem 6rem;
            }

            .hero-name {
                font-size: 1.5rem;
            }

            .stats-section {
                padding: 0 1rem 1rem;
                margin-top: -4rem;
            }

            .content-section {
                padding: 0 1rem 1rem;
            }

            .nav-tabs-custom {
                padding: 0 0.5rem;
            }

            .nav-tabs-custom .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Hero Profile Section -->
        <div class="profile-hero">
            <div class="profile-hero-content">
                <div class="avatar-wrapper">
                    <div class="profile-avatar" id="heroAvatar">
                        <?php
                        $avatar = $currentUser['avatar'] ?? '';
                        if (!empty($avatar) && file_exists(__DIR__ . '/uploads/avatars/' . $avatar)): ?>
                            <img src="uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="Avatar">
                        <?php else: ?>
                            <i class="bi bi-person"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <h1 class="hero-name"><?= htmlspecialchars($currentUser['nama']) ?></h1>
                <p class="hero-email"><?= htmlspecialchars($currentUser['email']) ?></p>
                <span class="hero-badge">
                    <i class="bi bi-shield-check me-1"></i><?= ucfirst($currentUser['role']) ?>
                </span>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-value"><?= $totalRegistrations ?></div>
                        <div class="stat-label">Total Event Terdaftar</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-value">
                            <?php
                            try {
                                $stmt = $db->prepare("SELECT COUNT(*) as total FROM registrations r 
                                    JOIN events e ON r.event_id = e.id 
                                    WHERE r.user_id = ? AND e.tanggal > NOW()");
                                $stmt->execute([$currentUser['id']]);
                                echo $stmt->fetch()['total'] ?? 0;
                            } catch (PDOException $e) {
                                echo '0';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Event Mendatang</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div class="stat-value">
                            <?php
                            try {
                                $stmt = $db->prepare("SELECT created_at FROM users WHERE id = ?");
                                $stmt->execute([$currentUser['id']]);
                                $userData = $stmt->fetch();
                                $joinDate = new DateTime($userData['created_at'] ?? 'now');
                                $now = new DateTime();
                                $diff = $now->diff($joinDate);
                                echo $diff->days;
                            } catch (Exception $e) {
                                echo '0';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Hari Bergabung</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="content-section">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-tab"
                            type="button">
                            <i class="bi bi-person me-2"></i>Edit Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password-tab" type="button">
                            <i class="bi bi-lock me-2"></i>Ubah Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#info-tab" type="button">
                            <i class="bi bi-info-circle me-2"></i>Informasi Akun
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="profile-tab">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="avatar-upload-wrapper">
                                <div class="avatar-preview" id="avatarPreview">
                                    <?php if (!empty($avatar) && file_exists(__DIR__ . '/uploads/avatars/' . $avatar)): ?>
                                        <img src="uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="Avatar">
                                    <?php else: ?>
                                        <i class="bi bi-person"></i>
                                    <?php endif; ?>
                                </div>
                                <label class="btn btn-outline-secondary btn-upload">
                                    <i class="bi bi-camera me-2"></i>Pilih Foto
                                    <input type="file" id="avatar" name="avatar" accept="image/*"
                                        onchange="previewAvatar(event)">
                                </label>
                                <p class="text-muted small mt-2 mb-0">JPG, PNG, atau GIF (Max 2MB)</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="password-tab">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="change_password">

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Lama</label>
                                <input type="password" class="form-control" id="current_password"
                                    name="current_password" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        required minlength="6">
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required minlength="6">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key me-2"></i>Ubah Password
                            </button>
                        </form>
                    </div>

                    <!-- Account Info Tab -->
                    <div class="tab-pane fade" id="info-tab">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">User ID</label>
                                <p class="fw-semibold">#<?= $currentUser['id'] ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Role</label>
                                <p class="fw-semibold">
                                    <span class="badge bg-primary"><?= ucfirst($currentUser['role']) ?></span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tanggal Bergabung</label>
                                <p class="fw-semibold">
                                    <?php
                                    try {
                                        $stmt = $db->prepare("SELECT created_at FROM users WHERE id = ?");
                                        $stmt->execute([$currentUser['id']]);
                                        $userData = $stmt->fetch();
                                        echo date('d F Y', strtotime($userData['created_at'] ?? 'now'));
                                    } catch (PDOException $e) {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Total Event Terdaftar</label>
                                <p class="fw-semibold"><?= $totalRegistrations ?> Event</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('avatarPreview');
                    const heroAvatar = document.getElementById('heroAvatar');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    heroAvatar.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>

</html>