<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Clear - EventKu</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            margin: 0 0 20px;
            font-size: 2rem;
        }

        p {
            margin: 15px 0;
            opacity: 0.9;
        }

        .btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .success {
            background: #10b981;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .list {
            text-align: left;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .list h3 {
            margin-top: 0;
        }

        .list ul {
            line-height: 1.8;
        }

        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔄 Force Cache Refresh</h1>
        <p>Klik tombol di bawah untuk clear cache dan lihat semua perubahan responsive:</p>

        <div class="list">
            <h3>✅ Perubahan Yang Sudah Diterapkan:</h3>
            <ul>
                <li>Dashboard: Responsive layout untuk mobile</li>
                <li>Sidebar: Fixed duplicate "Notifikasi"</li>
                <li>Login/Register: Google button diperkecil</li>
                <li>Profile: Avatar upload berfungsi</li>
                <li>Semua halaman: Improved mobile spacing</li>
            </ul>
        </div>

        <button class="btn" onclick="clearCacheAndRedirect()">Clear Cache & Refresh All</button>
        <button class="btn" onclick="forceReload()">Hard Refresh (Ctrl+Shift+R)</button>

        <div class="success" id="success">
            ✅ Cache cleared! Redirecting to Dashboard...
        </div>

        <p style="margin-top: 30px; font-size: 0.9rem;">
            Jika masih tidak terlihat, tekan <code>Cmd + Shift + R</code> (Mac)<br>
            atau <code>Ctrl + Shift + R</code> (Windows)
        </p>
    </div>

    <script>
        function clearCacheAndRedirect() {
            // Clear local storage
            localStorage.clear();

            // Clear session storage
            sessionStorage.clear();

            // Show success message
            document.getElementById('success').style.display = 'block';

            // Add timestamp to force new requests
            const timestamp = new Date().getTime();

            // Redirect after 1.5 seconds
            setTimeout(function () {
                window.location.href = 'dashboard.php?nocache=' + timestamp;
            }, 1500);
        }

        function forceReload() {
            // Force hard reload
            window.location.reload(true);
        }

        // Service Worker unregister if exists
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function (registrations) {
                for (let registration of registrations) {
                    registration.unregister();
                }
            });
        }
    </script>
</body>

</html>