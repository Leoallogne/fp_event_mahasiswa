<?php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? 'Transfer Bank';

    // Simulate payment processing
    if ($registrationService->processPayment($currentUser['id'], $eventId, $paymentMethod)) {
        // Redirect to event detail with success message
        header("Location: event-detail.php?id=$eventId&payment_success=1");
        exit;
    } else {
        $error = 'Gagal memproses pembayaran. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?= htmlspecialchars($registration['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/responsive.css?v=1">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
        }

        .payment-container {
            max-width: 600px;
            margin: 4rem auto;
        }

        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: none;
        }

        .payment-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            padding: 2.5rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .payment-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .amount-display {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 1rem 0;
        }

        .payment-method-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .payment-method-card:hover,
        .payment-method-input:checked+.payment-method-card {
            border-color: #0d6efd;
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }

        .payment-method-input:checked+.payment-method-card {
            background-color: #ecf4ff;
        }

        .method-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #0d6efd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-right: 1rem;
        }

        .btn-pay {
            padding: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            border: none;
            transition: all 0.3s;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="payment-container">
            <!-- Back Button -->
            <a href="my-events.php" class="text-decoration-none text-muted mb-4 d-inline-block fw-medium">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Tiket Saya
            </a>

            <?php if ($error): ?>
                <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="payment-card">
                <div class="payment-header">
                    <div class="position-relative z-1">
                        <p class="mb-0 text-white-50 fw-medium text-uppercase ls-1">Total Pembayaran</p>
                        <div class="amount-display">
                            Rp <?= number_format($registration['price'], 0, ',', '.') ?>
                        </div>
                        <div class="badge bg-white bg-opacity-25 backdrop-blur rounded-pill px-3 py-2 fw-normal">
                            <i class="bi bi-ticket-perforated me-1"></i> <?= htmlspecialchars($registration['title']) ?>
                        </div>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form action="" method="POST">
                        <h5 class="fw-bold mb-4">Pilih Metode Pembayaran</h5>

                        <!-- BCA -->
                        <div class="mb-3">
                            <input type="radio" name="payment_method" id="bca" value="Transfer BCA"
                                class="d-none payment-method-input" checked>
                            <label for="bca" class="payment-method-card w-100 mb-0">
                                <div class="method-icon">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">Transfer BCA</div>
                                    <div class="small text-muted">Virtual Account Otomatis</div>
                                </div>
                                <i class="bi bi-check-circle-fill text-primary fs-4 opacity-0 check-icon"></i>
                            </label>
                        </div>

                        <!-- Mandiri -->
                        <div class="mb-3">
                            <input type="radio" name="payment_method" id="mandiri" value="Transfer Mandiri"
                                class="d-none payment-method-input">
                            <label for="mandiri" class="payment-method-card w-100 mb-0">
                                <div class="method-icon">
                                    <i class="bi bi-bank2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">Mandiri</div>
                                    <div class="small text-muted">Virtual Account Otomatis</div>
                                </div>
                                <i class="bi bi-check-circle-fill text-primary fs-4 opacity-0 check-icon"></i>
                            </label>
                        </div>

                        <!-- E-Wallet -->
                        <div class="mb-3">
                            <input type="radio" name="payment_method" id="ewallet" value="QRIS / E-Wallet"
                                class="d-none payment-method-input">
                            <label for="ewallet" class="payment-method-card w-100 mb-0">
                                <div class="method-icon">
                                    <i class="bi bi-qr-code-scan"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">QRIS / E-Wallet</div>
                                    <div class="small text-muted">GoPay, OVO, Dana, ShopeePay</div>
                                </div>
                                <i class="bi bi-check-circle-fill text-primary fs-4 opacity-0 check-icon"></i>
                            </label>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-pay text-white shadow">
                                <i class="bi bi-shield-lock-fill me-2"></i>Bayar Sekarang
                            </button>
                            <p class="text-center text-muted small mt-3 mb-0">
                                <i class="bi bi-lock-fill me-1"></i> Pembayaran aman & terverifikasi otomatis
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .payment-method-input:checked+.payment-method-card .check-icon {
            opacity: 1;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>