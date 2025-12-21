# Web Sistem Manajemen Event Mahasiswa

Sistem manajemen event berbasis web untuk mengelola event mahasiswa dengan fitur lengkap termasuk integrasi Google Calendar, notifikasi email, dan analitik.

## Teknologi yang Digunakan

- **Backend**: PHP Native (tanpa framework)
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5
- **Grafik**: Chart.js
- **Email**: PHPMailer (via Composer)
- **Arsitektur**: OOP dengan struktur modular

## Fitur Utama

### Role Administrator
- ✅ Login/Logout
- ✅ CRUD Event
- ✅ CRUD Kategori Event
- ✅ Lihat daftar peserta event
- ✅ Kirim notifikasi reminder event
- ✅ Hapus event
- ✅ Generate analitik dan grafik
- ✅ Sinkronisasi API Google Calendar
- ✅ Export laporan CSV

### Role User Mahasiswa
- ✅ Registrasi akun
- ✅ Login/Logout
- ✅ Lihat daftar event
- ✅ Daftar event
- ✅ Lihat event history
- ✅ Terima email reminder event
- ✅ Export jadwal ke Google Calendar

## Struktur Folder

```
/ptojrct_putra
├── /public              # File yang diakses publik
│   ├── index.php        # Halaman utama (daftar event)
│   ├── login.php        # Halaman login
│   ├── register.php     # Halaman registrasi
│   ├── event-detail.php # Detail event
│   ├── my-events.php    # Event yang diikuti user
│   └── /admin           # Halaman admin
│       ├── dashboard.php
│       ├── events.php
│       ├── categories.php
│       ├── analytics.php
│       └── notifications.php
├── /modules             # Modul aplikasi
│   ├── /events
│   │   ├── EventService.php
│   │   └── CategoryService.php
│   ├── /users
│   │   └── Auth.php
│   ├── /registrations
│   │   └── RegistrationService.php
│   └── /notifications
│       └── NotificationService.php
├── /api                 # API integrations
│   └── ApiClientCalendar.php
├── /analytics           # Service analitik
│   └── AnalyticsService.php
├── /config              # Konfigurasi
│   ├── database.php
│   └── session.php
├── /database            # Database files
│   └── schema.sql
├── /assets              # Assets (CSS, JS)
│   ├── /css
│   └── /js
├── .env.example         # Template file environment
├── composer.json        # Dependencies
└── README.md            # Dokumentasi
```

## Instalasi & Setup

### 1. Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB 5.7 atau lebih tinggi
- Web server (Apache/Nginx) atau XAMPP/WAMP
- Composer (untuk PHPMailer)

### 2. Clone atau Download Project
```bash
cd C:\xampp\htdocs\ptojrct_putra
```

### 3. Install Dependencies
```bash
composer install
```

### 4. Setup Database
1. Buka phpMyAdmin atau MySQL client
2. Import file `database/schema.sql` ke database MySQL
3. Database akan dibuat otomatis dengan nama `event_management`

### 5. Konfigurasi Environment
1. Copy file `.env.example` menjadi `.env`
2. Edit file `.env` dan sesuaikan konfigurasi:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=event_management
DB_USER=root
DB_PASS=

# Google Calendar API Configuration
GOOGLE_CALENDAR_API_KEY=your_api_key_here
GOOGLE_CALENDAR_CLIENT_ID=your_client_id_here
GOOGLE_CALENDAR_CLIENT_SECRET=your_client_secret_here

# Email Configuration (PHPMailer)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password_here
SMTP_FROM_NAME=Event Management System

# Application Configuration
APP_URL=http://localhost/ptojrct_putra/public
SESSION_LIFETIME=3600
```

### 6. Setup Google Calendar API
1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang ada
3. Aktifkan Google Calendar API
4. Buat credentials (API Key atau OAuth 2.0)
5. Copy API Key ke file `.env`

### 7. Setup Email (PHPMailer)
1. Untuk Gmail, gunakan App Password:
   - Buka Google Account Settings
   - Security → 2-Step Verification → App Passwords
   - Generate password baru untuk aplikasi
   - Gunakan password tersebut di `.env`

### 8. Akses Aplikasi
Buka browser dan akses:
```
http://localhost/ptojrct_putra/public
```

## Setup Admin Pertama Kali

### Opsi 1: Menggunakan Halaman Setup (Direkomendasikan)
1. Setelah import database, akses halaman setup:
   ```
   http://localhost/ptojrct_putra/public/setup.php
   ```
2. Isi form untuk membuat admin pertama
3. Halaman ini hanya bisa diakses jika belum ada admin di database
4. Setelah admin dibuat, halaman ini akan redirect ke login

### Opsi 2: Menggunakan Default Admin
Jika menggunakan default admin dari database:
- **Email**: admin@event.com
- **Password**: admin123

**PENTING**: Ubah password default setelah pertama kali login!

### User
Silakan registrasi melalui halaman register.

## Endpoint & Routes

### Public Routes
- `GET /public/index.php` - Daftar event
- `GET /public/login.php` - Halaman login
- `GET /public/register.php` - Halaman registrasi
- `GET /public/event-detail.php?id={id}` - Detail event
- `GET /public/my-events.php` - Event yang diikuti user
- `GET /public/register-event.php?id={id}` - Daftar event
- `GET /public/cancel-registration.php?id={id}` - Batalkan pendaftaran
- `GET /public/export-calendar.php?id={id}` - Export ke Google Calendar
- `POST /public/logout.php` - Logout

### Admin Routes
- `GET /public/admin/dashboard.php` - Dashboard admin
- `GET /public/admin/events.php` - Manajemen event
- `GET /public/admin/categories.php` - Manajemen kategori
- `GET /public/admin/analytics.php` - Analitik & laporan
- `GET /public/admin/notifications.php` - Manajemen notifikasi
- `GET /public/admin/event-participants.php?id={id}` - Daftar peserta event

## Struktur Database

### Tabel: users
- `id` INT PRIMARY KEY
- `nama` VARCHAR(255)
- `email` VARCHAR(255) UNIQUE
- `password` VARCHAR(255)
- `role` ENUM('admin', 'user')
- `created_at` DATETIME

### Tabel: categories
- `id` INT PRIMARY KEY
- `nama` VARCHAR(255) UNIQUE
- `deskripsi` TEXT
- `created_at` DATETIME

### Tabel: events
- `id` INT PRIMARY KEY
- `title` VARCHAR(255)
- `kategori` VARCHAR(255)
- `tanggal` DATETIME
- `lokasi` VARCHAR(255)
- `deskripsi` TEXT
- `kuota` INT
- `calendar_event_id` VARCHAR(255)
- `created_by` INT (FK users.id)
- `created_at` DATETIME
- `updated_at` DATETIME

### Tabel: registrations
- `id` INT PRIMARY KEY
- `user_id` INT (FK users.id)
- `event_id` INT (FK events.id)
- `daftar_waktu` DATETIME
- `status` VARCHAR(50)

### Tabel: notifications
- `id` INT PRIMARY KEY
- `user_id` INT (FK users.id)
- `event_id` INT (FK events.id)
- `message` TEXT
- `status` VARCHAR(50)
- `sent_time` DATETIME
- `created_at` DATETIME

## Fitur Analitik

### AnalyticsService Methods
1. `hitungKategoriEventTerbanyakPeminat()` - Kategori dengan peserta terbanyak
2. `hitungRataRataPesertaPerEvent()` - Rata-rata peserta per event
3. `trenJumlahEventBulanan($limit)` - Tren event bulanan
4. `rekomendasiEvent($userId, $limit)` - Rekomendasi event untuk user

## Integrasi Google Calendar

### ApiClientCalendar Methods
- `fetch($calendarId, $timeMin, $timeMax)` - Ambil event dari calendar
- `pushEvent($eventData)` - Tambah event ke calendar
- `updateEvent($eventId, $eventData)` - Update event di calendar
- `deleteEvent($eventId)` - Hapus event dari calendar

## Notifikasi Email

Sistem menggunakan PHPMailer untuk mengirim email reminder. Notifikasi otomatis dikirim saat:
- Admin mengirim reminder untuk event tertentu
- Semua peserta yang terdaftar akan menerima email

## Export CSV

Admin dapat mengekspor data analitik ke CSV:
- Export kategori event
- Export tren bulanan

## Keamanan

- Password di-hash menggunakan `password_hash()`
- Prepared statements untuk mencegah SQL injection
- Session-based authentication
- Role-based access control
- Input validation dan sanitization

## Pengembangan

### Menambah Fitur Baru
1. Buat service class di folder `/modules`
2. Buat view di folder `/public` atau `/public/admin`
3. Update routing jika diperlukan

### Menambah Tabel Database
1. Update `database/schema.sql`
2. Buat migration atau update manual

## Troubleshooting

### Database Connection Error
- Pastikan MySQL service berjalan
- Cek konfigurasi di file `.env`
- Pastikan database `event_management` sudah dibuat

### Email Tidak Terkirim
- Cek konfigurasi SMTP di `.env`
- Pastikan menggunakan App Password untuk Gmail
- Cek firewall dan port 587

### Google Calendar API Error
- Pastikan API Key valid
- Cek apakah Google Calendar API sudah diaktifkan
- Verifikasi credentials di Google Cloud Console

## Lisensi

Project ini dibuat untuk keperluan akademik.

## Kontak & Support

Untuk pertanyaan atau bantuan, silakan hubungi administrator sistem.

---

**Catatan**: Pastikan semua file memiliki permission yang tepat dan web server dikonfigurasi dengan benar.

# fp_event_mahasiswa
