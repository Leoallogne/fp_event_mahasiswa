# Sistem Manajemen Event Mahasiswa (EventKu)

Platform berbasis web untuk manajemen kegiatan kemahasiswaan yang komprehensif, efisien, dan modern. Memudahkan penyelenggara dalam mengelola event dan membantu mahasiswa menemukan kegiatan yang sesuai dengan minat mereka.

---

## 🚀 Fitur Unggulan

### 👑 Untuk Administrator (Penyelenggara)
*   **Dashboard Executif**: Ringkasan statistik real-time (Total Event, Peserta, Kategori Populer).
*   **Manajemen Event Lengkap**: Buat, edit, dan hapus event dengan detail (lokasi, kuota, tanggal).
*   **Sistem Tiket & Peserta**:
    *   Verifikasi pembayaran manual.
    *   Monitor kuota peserta.
    *   Export data peserta ke CSV.
*   **Broadcast Notifikasi**: Kirim pengumuman penting langsung ke email seluruh peserta atau pengguna sistem.
*   **Manajemen Kategori**: Kelompokkan event agar mudah dicari (Seminar, Workshop, Lomba).

### 🎓 Untuk Mahasiswa (User)
*   **Pencarian Canggih**: Filter event berdasarkan kategori atau kata kunci.
*   **Pendaftaran Mudah**: Flow pendaftaran yang simpel dengan status real-time (Pending/Confirmed).
*   **Integrasi Kalender**: Tombol "Add to Google Calendar" untuk setiap event yang diikuti.
*   **Notifikasi Cerdas**:
    *   Email konfirmasi pendaftaran.
    *   Email reminder H-1 acara.
    *   Notifikasi update status pembayaran.
*   **Tiket Digital**: QR Code dan detail tiket di halaman "Event Saya".

---

## 🛠 Teknologi & Dependensi

Project ini dibangun dengan arsitektur **PHP Native (OOP MVC Pattern)** yang ringan dan aman, tanpa framework berat yang membebani server.

*   **Backend**: PHP 7.4+
*   **Frontend**: Bootstrap 5 (Responsive UI), Vanilla JS
*   **Database**: MySQL / MariaDB
*   **Email Engine**: PHPMailer (via Composer)
*   **Security**: Password Hashing (Bcrypt), Prepared Statements (PDO), CSRF Protection (Basic), XSS Filtering.

---

## ⚙️ Panduan Instalasi (Hosting Ready)

Ikuti langkah ini untuk deploy di local (XAMPP/MAMP) atau Hosting (CPanel).

### 1. Persiapan Database
1.  Buat database baru, misal: `event_management`.
2.  Import file `database/schema.sql` ke database tersebut.
3.  *(Opsional)* Jika migrasi dari versi lama, jalankan script di folder `database/updates/`.

### 2. Konfigurasi Sistem
Duplikat file `.env.example` menjadi `.env` dan sesuaikan isinya:

```ini
# Koneksi Database
DB_HOST=localhost
DB_NAME=event_management
DB_USER=root
DB_PASS=password_db_anda

# Konfigurasi URL Website (PENTING untuk link di email)
APP_URL=http://localhost/folder_projek_anda

# Konfigurasi Email (SMTP Gmail)
# Wajib diisi agar fitur Lupa Password & Notifikasi berjalan
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=email_anda@gmail.com
SMTP_PASS="app_password_anda" 
# Catatan: Gunakan 'App Password' Gmail, bukan password login biasa.
```

### 3. Install Dependensi (Composer)
Masuk ke terminal di root folder project, lalu jalankan:
```bash
composer install
```
*Jika di hosting CPanel, Anda bisa upload folder `vendor` dari local jika tidak ada akses terminal.*

---

## 🔐 Akun Demo
Gunakan akun ini untuk pengujian awal:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@event.com` | `admin123` |
| **User** | *(Silakan Daftar Baru)* | - |

---

## 📂 Struktur Direktori Utama

*   `public/`: Folder root akses web (index.php, assets, upload).
*   `modules/`: Core Logic (Services, Models).
    *   `auth/`: Logika Login/Register.
    *   `events/`: Logika CRUD Event.
    *   `registrations/`: Logika Pendaftaran.
    *   `notifications/`: Logika Email & Notifikasi.
*   `config/`: Konfigurasi Database & Helper.
*   `database/`: Skema SQL.
*   `vendor/`: Library pihak ketiga (PHPMailer, dll).

---

## ⚠️ Troubleshooting Umum

1.  **Email Tidak Terkirim?**
    *   Pastikan `SMTP_PASS` di `.env` sudah benar (tanpa spasi).
    *   Cek apakah ekstensi `openssl` di PHP sudah aktif.
2.  **Gagal Upload Gambar?**
    *   Pastikan folder `public/uploads` memiliki permission write (755 atau 777).
3.  **Halaman Not Found (404)?**
    *   Pastikan konfigurasi `APP_URL` di `.env` sesuai dengan path website Anda.

---
Dikembangkan untuk Tugas Akhir / Projek Perkuliahan.
**© 2024 EventKu Management System**
