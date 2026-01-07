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
                        Session::set('user_avatar', $avatarFileName); // Update avatar in session

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
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/profile.css?v=<?= time() ?>">
    <style>
        /* Quick responsive tweaks */
        .profile-hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
        }

        @media (min-width: 768px) {
            .profile-hero {
                flex-direction: row;
                justify-content: flex-start;
            }
        }

        .avatar-wrapper {
            margin-bottom: 1rem;
        }

        .stat-card {
            text-align: center;
            padding: 1rem;
        }

        .content-card {
            margin-top: 2rem;
        }

        /* Force horizontal tabs */
        .nav-tabs-custom {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
        }

        .nav-tabs-custom .nav-item {
            display: inline-block !important;
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
                            <img src="uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="Avatar"
                                class="img-fluid rounded-circle" loading="lazy" style="max-width:150px; height:auto;">
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
                <ul class="nav nav-tabs nav-tabs-custom d-flex flex-row" role="tablist" style="flex-wrap: wrap;">
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

                            <div class="row g-3 mb-4">
                                <!-- Avatar Section -->
                                <div class="col-md-4 text-center">
                                    <div class="avatar-preview mb-3" id="avatarPreview" style="display: inline-block;">
                                        <?php if (!empty($avatar) && file_exists(__DIR__ . '/uploads/avatars/' . $avatar)): ?>
                                            <img src="uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="Avatar"
                                                class="img-fluid rounded-circle" loading="lazy"
                                                style="max-width:150px; height:auto;">
                                        <?php else: ?>
                                            <i class="bi bi-person"></i>
                                        <?php endif; ?>
                                    </div>
                                    <label class="btn btn-outline-secondary btn-sm w-100 mb-2">
                                        <i class="bi bi-camera me-2"></i>Pilih Foto
                                        <input type="file" id="avatar" name="avatar" accept="image/*"
                                            style="display:none;" onchange="previewAvatar(event)">
                                    </label>
                                    <p class="text-muted small mb-0">JPG, PNG, atau GIF (Max 2MB)</p>
                                </div>

                                <!-- Form Fields -->
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="nama" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                                        </div>
                                    </div>
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

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="current_password" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="current_password"
                                        name="current_password" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        required minlength="6">
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>
                                <div class="col-md-4">
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
                                        $months = [
                                            'January' => 'Januari',
                                            'February' => 'Februari',
                                            'March' => 'Maret',
                                            'April' => 'April',
                                            'May' => 'Mei',
                                            'June' => 'Juni',
                                            'July' => 'Juli',
                                            'August' => 'Agustus',
                                            'September' => 'September',
                                            'October' => 'Oktober',
                                            'November' => 'November',
                                            'December' => 'Desember'
                                        ];
                                        $dateStr = date('d F Y', strtotime($userData['created_at'] ?? 'now'));
                                        echo strtr($dateStr, $months);
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
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid rounded-circle" loading="lazy" style="max-width:150px; height:auto;">`;
                    heroAvatar.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid rounded-circle" loading="lazy" style="max-width:150px; height:auto;">`;
                }
                reader.readAsDataURL(file);
            }
        }


    </script>
</body>

</html>