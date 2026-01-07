# 📘 Dokumen 1: Project Overview & Features
## EventKu - Sistem Manajemen Event Mahasiswa

### 1. Deskripsi Project
**EventKu** adalah platform berbasis web yang dirancang untuk memudahkan mahasiswa dalam mengelola, mencari, dan mendaftar event di lingkungan kampus maupun umum. Sistem ini memisahkan peran antara **Pengguna Biasa (Mahasiswa/Umum)** dan **Administrator**, dengan fokus pada kemudahan penggunaan, keamanan data, dan integrasi modern (Google Login, Maps, Calendar).

### 2. Teknologi (Tech Stack)
Project ini dibangun menggunakan teknologi **PHP Native** dengan pendekatan modern:
*   **Backend Language**: PHP 8.0+
*   **Database**: MySQL / MariaDB
*   **Frontend**: HTML5, CSS3 (Custom + Bootstrap 5), JavaScript (Vanilla)
*   **Architecture**: Service-Oriented Architecture (Logic dipisah dalam Service Classes)
*   **External APIs**:
    *   **Google OAuth 2.0**: Untuk login praktis menggunakan akun Google.
    *   **Google Calendar API**: Sinkronisasi jadwal event otomatis ke kalender peserta.
    *   **Leaflet JS / OpenStreetMap**: Peta interaktif untuk lokasi event.
*   **Server Compatibility**: Apache (Support `.htaccess` untuk routing dan security).

---

### 3. Fitur Utama

#### 🅰️ Fitur User (Mahasiswa/Umum)
1.  **Authentication Modern**:
    *   Login & Register via Email/Password (Encrypted).
    *   **Login with Google** (One-click sign in).
    *   Forgot Password & Reset flow.
2.  **Dashboard User**:
    *   Melihat statistik ringkas (Event diikuti, Status pendaftaran).
    *   Kalender interaktif.
3.  **Jelajah Event (Explore)**:
    *   Pencarian event (Search bar).
    *   Filter berdasarkan Kategori (Seminar, Workshop, Lomba, dll).
    *   Filter berdasarkan Status (Open, Closed).
4.  **Pendaftaran Event**:
    *   Detail event lengkap (Peta lokasi, Deskripsi, Kuota).
    *   **Sistem Tiket**:
        *   **Event Gratis**: Langsung terdaftar.
        *   **Event Berbayar**: Upload bukti pembayaran & menunggu verifikasi Admin.
    *   Cek sisa kuota real-time.
5.  **Manajemen Tiket Saya**:
    *   Melihat riwayat event yang diikuti.
    *   Cetak Tiket / Bukti Pendaftaran.
    *   Sinkronisasi ulang ke Google Calendar.
6.  **Profile Management**:
    *   Ganti foto profil (Avatar).
    *   Update biodata dan password.

#### 🅱️ Fitur Administrator
1.  **Dashboard Admin**:
    *   **Analitik Grafis**: Grafik pendaftaran per bulan (Chart.js), Perbandingan kategori, Total user.
    *   Statistik pendapatan (untuk event berbayar).
2.  **Manajemen Event (CRUD)**:
    *   Tambah/Edit/Hapus Event.
    *   Integration Map Picker (Pilih lokasi di peta).
    *   Atur Kuota dan Harga Tiket.
3.  **Manajemen Peserta (Verifikasi)**:
    *   Melihat daftar pendaftar per event.
    *   **Verifikasi Pembayaran**: Button Terima (Approve) atau Tolak (Reject) untuk event berbayar.
    *   Lihat bukti transfer (Image viewer).
4.  **Laporan (Reporting)**:
    *   **Export CSV**: Download data peserta event untuk keperluan absensi/sertifikat.
5.  **Manajemen Kategori**:
    *   Tambah/Hapus kategori event dinamis.

---

### 4. Skema Database (Ringkasan)
Sistem menggunakan database relasional yang kuat:

*   **`users`**: Menyimpan data akun (email, password hash, role, google_id, avatar).
*   **`events_categories`**: Master data kategori event.
*   **`events`**: Data utama event (judul, tanggal, lokasi, harga, kuota, lat/long).
*   **`registrations`**: Tabel transaksi pendaftaran (menghubungkan user & event, status pembayaran, bukti bayar).
*   **`notifications`**: Sistem notifikasi internal.
*   **`calendar_cache`**: Caching data kalender untuk performa.

---

### 5. Keunggulan Sistem
1.  **Public Folder Strategy**: File inti (backend) diletakkan di luar folder public untuk keamanan maksimal. Akses langsung ke file config/modules ditutup.
2.  **Responsive Design**: Tampilan optimal di Laptop, Tablet, maupun HP.
3.  **Secure**:
    *   Password hashing (Bcrypt).
    *   SQL Injection Protection (PDO Prepared Statements).
    *   XSS Protection (Output Escaping).
    *   CSRF Protection (Basic session validation).

Dokumen ini memberikan gambaran umum tentang apa yang bisa dilakukan oleh sistem **EventKu**.
