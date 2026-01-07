# 📂 Dokumen 2: Struktur Folder & File
## Panduan Navigasi Kode EventKu

Sistem ini menggunakan **Public Folder Strategy**, standar keamanan modern di mana hanya folder `public/` yang boleh diakses oleh browser (web server root).

### 🔽 Struktur Directory Tree

```
mahasiswa_fp/  (Root Project - Tidak boleh diakses public)
├── .env                # File Konfigurasi Rahasia (Database, API Keys)
├── composer.json       # Dependency Manager PHP
├── vendor/             # Library pihak ketiga (Google API Client, Dotenv)
│
├── api/                # Endpoint API khusus (Untuk AJAX / External)
│   └── ApiClientCalendar.php  # Class wrapper Google Calendar API
│
├── config/             # Konfigurasi Global Database & Session
│   ├── database.php    # Class 'Database' (PDO Connection)
│   └── session.php     # Helper untuk start session aman
│
├── modules/            # 🧠 CORE LOGIC (Service Layer)
│   ├── analytics/      # Logika Statistik & Laporan Admin
│   │   └── AnalyticsService.php
│   ├── events/         # Logika Manajemen Event
│   │   ├── EventService.php     # CRUD Event
│   │   └── CategoryService.php  # CRUD Kategori
│   ├── registrations/  # Logika Pendaftaran & Pembayaran
│   │   └── RegistrationService.php
│   ├── users/          # Logika User & Auth
│   │   ├── Auth.php        # Login, Register, Middleware Role
│   │   └── GoogleAuth.php  # Handle Login Google
│   └── notifications/  # Logika Notifikasi
│       └── NotificationService.php
│
└── public/             # 🌍 WEB ROOT (Satu-satunya yang diakses Browser)
    ├── .htaccess       # Aturan routing Apache
    ├── index.php       # Landing Page (Homepage)
    ├── login.php       # Halaman Login
    ├── register.php    # Halaman Register
    ├── dashboard.php   # Dashboard User
    ├── admin/          # Area Khusus Admin
    │   ├── index.php        # Redirect ke dashboard
    │   ├── dashboard.php    # Dashboard Admin & Chart
    │   ├── events.php       # Form Manajamen Event
    │   └── ... (file admin lainnya)
    ├── assets/         # File Statis (CSS, JS, Images)
    │   ├── css/        # Stylesheets (layout.css, admin-modern.css)
    │   ├── js/         # Javascript Logic
    │   └── images/     # Gambar statis
    └── uploads/        # File yang diupload User
        ├── avatars/    # Foto profil user
        └── payments/   # Bukti pembayaran transfer
```

---

### 🔍 Penjelasan Fungsi Folder Utama

#### 1. `public/` (The Front Door)
Ini adalah "wajah" aplikasi. Semua request dari browser masuk ke sini.
*   **Kenapa dipisah?** Agar hacker tidak bisa mengakses file sensitif seperti `.env` atau kode logika di `modules/`.
*   File di sini hanya berisi **View Logic** (HTML/Tampilan) dan pemanggilan ke `modules` (Backend Logic).
*   Contoh alur file `public/login.php`:
    1.  Include `config/database.php`.
    2.  Panggil class `Auth` dari `modules/users/Auth.php`.
    3.  Tampilkan Form HTML.
    4.  Jika tombol submit ditekan, panggil `Auth->login()`.

#### 2. `modules/` (The Brain)
Di sinilah semua "otak" aplikasi berada. Menggunakan pola **Service-Oriented**.
Setiap folder mewakili satu fitur besar:
*   **Auth.php**: Menangani siapa yang boleh masuk, cek password, cek session login.
*   **EventService.php**: Menangani simpan event ke database, ambil daftar event, hitung sisa kuota.
*   **RegistrationService.php**: Menangani logika rumit pendaftaran (Cek kuota -> Simpan -> Kurangi kuota -> Kirim notifikasi).

#### 3. `config/` (The Configuration)
*   **database.php**: Class tunggal yang bertugas membuka pintu koneksi ke MySQL. Menggunakan **PDO** (PHP Data Objects) yang lebih aman dan support Environment Variables (`.env`).
*   **session.php**: Memastikan session PHP dimulai dengan aman di setiap halaman.

#### 4. `api/` (The Bridge)
Folder ini berisi helper untuk komunikasi dengan layanan luar.
*   **ApiClientCalendar.php**: Class khusus yang membungkus kerumitan Google API. Punya fungsi `pushEvent()`, `deleteEvent()`, dll.

#### 5. `.env` (The Secrets)
File teks biasa yang **SANGAT RAHASIA**. Berisi password database, Client ID Google, dan setting SMTP email. File ini **TIDAK BOLEH** ada di folder public.

---

### ⚙️ Alur Kerja File (Request Lifecycle)
Contoh saat User membuka `https://evenku.com/register-event.php?id=1`:

1.  **Browser** meminta `public/register-event.php`.
2.  **register-event.php** memuat dependensi:
    ```php
    require_once '../config/database.php';
    require_once '../modules/events/EventService.php';
    ```
3.  Script membuat objek Service:
    ```php
    $eventService = new EventService();
    $event = $eventService->getEventById(1); // Ambil data dari DB
    ```
4.  **Tampilan (HTML)** dirender menggunakan data `$event` tadi.
5.  Browser menerima HTML utuh dan menampilkannya ke User.

Struktur ini membuat kode **Rapi**, **Mudah Di-maintenance**, dan **Aman**.
