<?php
session_start();
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
    <title>Profile - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .profile-card {
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #667eea;
            margin: 0 auto 1rem;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content" style="margin-left: 250px; padding: 20px;">
        <div class="container-fluid">
            <h2 class="mb-4"><i class="bi bi-person-circle"></i> Profile Saya</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profile Info Card -->
                <div class="col-md-4 mb-4">
                    <div class="card profile-card">
                        <div class="profile-header text-center">
                            <div class="profile-avatar">
                                <?php
                                $avatar = $currentUser['avatar'] ?? '';
                                if (!empty($avatar) && file_exists(__DIR__ . '/uploads/avatars/' . $avatar)): ?>
                                    <img src="uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="Avatar">
                                <?php else: ?>
                                    <i class="bi bi-person"></i>
                                <?php endif; ?>
                            </div>
                            <h4><?= htmlspecialchars($currentUser['nama']) ?></h4>
                            <p class="mb-0"><?= htmlspecialchars($currentUser['email']) ?></p>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h5 class="text-primary"><?= $totalRegistrations ?></h5>
                                <small class="text-muted">Event Terdaftar</small>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <small class="text-muted">Role:</small>
                                <span class="badge bg-primary"><?= ucfirst($currentUser['role']) ?></span>
                            </div>
                            <div>
                                <small class="text-muted">Member sejak:</small>
                                <br>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="col-md-8 mb-4">
                    <div class="card profile-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_profile">

                                <div class="mb-3 text-center">
                                    <label for="avatar" class="form-label">Avatar</label>
                                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                    <small class="text-muted">Format: jpg, jpeg, png, gif</small>
                                </div>

                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                        value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Form -->
                    <div class="card profile-card mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-lock"></i> Ubah Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="change_password">

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="current_password"
                                        name="current_password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        required minlength="6">
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required minlength="6">
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key"></i> Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>

</html>