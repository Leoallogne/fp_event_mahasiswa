<?php
// Try to include Auth to check if logged in (for sidebar)
// Using relative paths assuming this is in public/
$authPath = __DIR__ . '/../modules/users/Auth.php';
$sessionPath = __DIR__ . '/../config/session.php';
$isLoggedIn = false;

if (file_exists($sessionPath)) {
    require_once $sessionPath;
}

if (file_exists($authPath)) {
    require_once $authPath;
    $auth = new Auth();
    $isLoggedIn = $auth->isLoggedIn();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - EventKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fa;
        }

        .error-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: #e9ecef;
            line-height: 1;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: #343a40;
            margin-bottom: 1rem;
        }

        .error-desc {
            color: #6c757d;
            max-width: 500px;
            margin: 0 auto 2rem;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="container">
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Halaman Tidak Ditemukan</h2>
            <p class="error-desc">Maaf, halaman yang Anda cari tidak tersedia. Mungkin link rusak atau halaman telah
                dipindahkan.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>

</html>