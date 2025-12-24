<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';
require_once __DIR__ . '/../modules/registrations/RegistrationService.php';

$auth = new Auth();
$auth->requireUser();

$registrationService = new RegistrationService();
$currentUser = $auth->getCurrentUser();
$eventId = $_GET['id'] ?? 0;

$registration = $registrationService->getRegistration($currentUser['id'], $eventId);

if (!$registration) {
    header('Location: my-events.php');
    exit;
}

// Jika sudah confirmed, jangan ke halaman ini
if ($registration['status'] === 'confirmed') {
    header('Location: event-detail.php?id=' . $eventId . '&success=1');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof'])) {
    $file = $_FILES['payment_proof'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
    } else {
        $newFilename = 'proof_' . $currentUser['id'] . '_' . $eventId . '_' . time() . '.' . $fileExtension;
        $uploadPath = __DIR__ . '/uploads/payments/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            if ($registrationService->updatePaymentProof($currentUser['id'], $eventId, $newFilename)) {
                $success = 'Bukti pembayaran berhasil diunggah. Tunggu verifikasi admin.';
                // Refresh data registration
                $registration = $registrationService->getRegistration($currentUser['id'], $eventId);
            } else {
                $error = 'Gagal menyimpan data bukti pembayaran.';
            }
        } else {
            $error = 'Gagal mengunggah file. Pastikan folder uploads/payments dapat ditulis.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?= htmlspecialchars($registration['title']) ?></title>
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
            margin-left: 260px;
            padding: 3rem 2rem;
            transition: all 0.3s ease;
        }

        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .payment-header {
            background: var(--primary-gradient);
            padding: 2.5rem 2rem;
            color: white;
            text-align: center;
        }

        .instruction-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px dashed #cbd5e1;
            margin-bottom: 2rem;
        }

        .bank-detail {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fff;
        }

        .upload-area:hover {
            border-color: #4f46e5;
            background: #f5f3ff;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .btn-upload {
            background: var(--primary-gradient);
            border: none;
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="payment-card">
                        <div class="payment-header">
                            <div class="mb-3">
                                <span class="status-badge status-pending">
                                    <i class="bi bi-clock-history"></i> Menunggu Pembayaran
                                </span>
                            </div>
                            <h2 class="fw-bold mb-1"><?= htmlspecialchars($registration['title']) ?></h2>
                            <p class="opacity-75 mb-0">Selesaikan pembayaran untuk mengonfirmasi pendaftaran Anda.</p>
                        </div>

                        <div class="p-4 p-md-5">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-2 d-block">Total
                                        Pembayaran</label>
                                    <h3 class="text-primary fw-bold mb-0">Rp
                                        <?= number_format($registration['price'], 0, ',', '.') ?></h3>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <label class="text-muted small text-uppercase fw-bold mb-2 d-block">ID
                                        Registrasi</label>
                                    <span class="fw-bold text-dark">#REG-<?= $registration['id'] ?></span>
                                </div>
                            </div>

                            <div class="instruction-card">
                                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i> Instruksi Pembayaran
                                </h5>
                                <p class="text-secondary small mb-3">Silakan lakukan transfer sesuai nominal pajak (IDR)
                                    ke salah satu rekening di bawah ini:</p>

                                <div class="bank-detail">
                                    <div>
                                        <div class="text-muted small">Transfer Bank BCA</div>
                                        <div class="fw-bold fs-5" id="rekBca">1234567890</div>
                                        <div class="text-muted small">a/n Panitia EventKu</div>
                                    </div>
                                    <button class="btn btn-sm btn-light border"
                                        onclick="copyText('1234567890')">Salin</button>
                                </div>

                                <div class="bank-detail">
                                    <div>
                                        <div class="text-muted small">Transfer Bank Mandiri</div>
                                        <div class="fw-bold fs-5" id="rekMandiri">0987654321</div>
                                        <div class="text-muted small">a/n Panitia EventKu</div>
                                    </div>
                                    <button class="btn btn-sm btn-light border"
                                        onclick="copyText('0987654321')">Salin</button>
                                </div>
                            </div>

                            <form action="" method="POST" enctype="multipart/form-data">
                                <h5 class="fw-bold mb-3"><i class="bi bi-cloud-arrow-up me-2"></i> Unggah Bukti
                                    Pembayaran</h5>

                                <?php if ($registration['payment_proof']): ?>
                                    <div class="alert alert-info bg-opacity-10 border-info d-flex align-items-center mb-3">
                                        <i class="bi bi-file-earmark-check fs-4 me-3"></i>
                                        <div>
                                            <div class="fw-bold">Anda sudah mengunggah bukti.</div>
                                            <a href="uploads/payments/<?= $registration['payment_proof'] ?>" target="_blank"
                                                class="text-decoration-none small">Lihat bukti yang diunggah</a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                                    <i class="bi bi-image text-muted display-4 mb-3 d-block"></i>
                                    <p class="mb-1 fw-bold">Klik untuk memilih file</p>
                                    <p class="text-muted small mb-0">PNG, JPG, atau PDF (Maks. 5MB)</p>
                                    <input type="file" id="fileInput" name="payment_proof" class="d-none" required
                                        onchange="handleFileSelect(this)">
                                </div>
                                <div id="fileNameDisplay" class="mt-2 text-center text-primary fw-medium small"></div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-upload text-white shadow-sm">
                                        <i class="bi bi-send me-2"></i> Kirim Bukti Pembayaran
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4 text-center">
                                <a href="my-events.php" class="text-muted text-decoration-none small">
                                    Lanjutkan nanti saja <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyText(text) {
            navigator.clipboard.writeText(text);
            alert('Nomor rekening berhasil disalin!');
        }

        function handleFileSelect(input) {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            if (input.files.length > 0) {
                fileNameDisplay.textContent = 'File terpilih: ' + input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }
    </script>
</body>

</html>