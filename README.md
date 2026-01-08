# EventKu - Sistem Manajemen Event Mahasiswa 🎓

> **Platform Manajemen Kegiatan Kampus yang Modern, Terintegrasi, dan Profesional.**

![Status](https://img.shields.io/badge/Status-Production_Ready-success?style=for-the-badge&logo=mediamarkt)
![PHP](https://img.shields.io/badge/Backend-PHP_8.0_Native-blue?style=for-the-badge&logo=php)
![Database](https://img.shields.io/badge/Database-MySQL_%2F_MariaDB-orange?style=for-the-badge&logo=mysql)
![Frontend](https://img.shields.io/badge/Frontend-Bootstrap_5_%2B_Glassmorphism-purple?style=for-the-badge&logo=bootstrap)

---

## 🌟 Tentang Project (About)

**EventKu** adalah solusi digital komprehensif untuk mengatasi masalah klasik di lingkungan kampus: *penyebaran informasi kegiatan yang tidak terpusat*. Dengan EventKu, BEM, Hima, dan Organisasi Kampus dapat mempublikasikan event mereka secara profesional, sementara mahasiswa dapat menemukan dan mendaftar kegiatan hanya dengan sekali klik.

Sistem ini dibangun dengan pendekatan **"Service-Oriented Architecture"** menggunakan PHP Native murni tanpa framework, menjadikannya sarana pembelajaran yang sempurna untuk memahami konsep dasar *Software Engineering* dan *Web Security*.

---

## 📚 Pusat Dokumentasi (Documentation Hub)

Kami percaya dokumentasi yang baik adalah kunci dari software yang hebat. Silakan pelajari sistem ini melalui panduan berikut:

| Ikon | Dokumen | Deskripsi |
| :--- | :--- | :--- |
| ⚙️ | **[Setup & Instalasi](DOCUMENTATION/00_SETUP_AND_INSTALLATION.md)** | **MULAI DARI SINI!** Panduan langkah-demi-langkah install di Localhost. |
| 📘 | **[Project Overview](DOCUMENTATION/01_PROJECT_OVERVIEW.md)** | Latar belakang masalah, Solusi, dan Alur User (Flowchart). |
| 📂 | **[Struktur Folder](DOCUMENTATION/02_FOLDER_STRUCTURE.md)** | Penjelasan arsitektur file `public` vs `modules` (Security). |
| 🏗️ | **[Repo-Service Pattern](DOCUMENTATION/05_SERVICE_REPOSITORY_PATTERN.md)** | Penjelasan Logic vs View. |
| 🗄️ | **[Database Schema](DOCUMENTATION/06_DATABASE_SCHEMA.md)** | Kamus data lengkap, relasi tabel (ERD), dan query penting. |
| 📄 | **[Laporan Teknis](DOCUMENTATION/04_TECHNICAL_SPECIFICATION_REPORT.md)** | Spesifikasi detail untuk kebutuhan Tugas. |

---

## 🚀 Fitur Unggulan (Key Features)

### 🎓 Untuk Mahasiswa (User)
*   **🔎 Discovery**: Cari event berdasarkan kategori (Seminar, Workshop, Lomba) atau tanggal.
*   **🎫 One-Click Register**: Daftar event tanpa perlu isi Google Form berulang kali.
*   **📅 Calendar Sync**: Otomatis menambahkan jadwal event ke **Google Calendar** pribadi.

*   **🔔 Smart Notifications**: Notifikasi status pendaftaran via Email dan Dashboard.

### 👑 Untuk Penyelenggara (Admin)
*   **📊 Executive Dashboard**: Pantau total pendaftar, pendapatan tiket, dan grafik tren bulanan.
*   **💰 Verifikasi Pembayaran**: Cek bukti transfer peserta dan konfirmasi/tolak dengan satu tombol.
*   **👥 Manajemen Peserta**: Export data peserta ke Excel/CSV untuk keperluan absensi.
*   **📢 Broadcast Email**: Kirim pengumuman penting ke seluruh peserta event sekaligus.
*   **🛡️ Role Management**: Sistem login aman dengan pemisahan hak akses Admin vs User.

---

## 🛠️ Keunggulan Teknis (Technical Highlights)

Project ini dirancang *bukan* sekadar "asal jalan", tapi menerapkan standar industri:

1.  **Repo-Service Pattern**: Logika bisnis dipisah total dari tampilan. Code lebih rapi, mudah dites, dan *reusable*.
2.  **Security First**:
    *   **Folder Isolation**: Kode PHP inti (`modules/`) berada di luar folder public, mustahil diakses hacker via browser.
    *   **Anti SQL Injection**: 100% menggunakan `PDO Prepared Statements`.
    *   **Secure Auth**: Password di-hash menggunakan `Bcrypt`, session diproteksi dari hijacking.
3.  **Modern UI/UX**: Desain antarmuka *Glassmorphism* yang estetik dan responsif di semua perangkat (HP/Laptop).

---

## ⚡ Quick Start (Mulai Cepat)

Ingin langsung mencoba?

1.  **Clone & Database**:
    *   Import file `database/schema.sql` ke phpMyAdmin (Database baru: `event_management`).
2.  **Config**:
    *   Copy `.env.example` -> `.env`.
    *   Isi `DB_PASS` dan `SMTP_PASS` (untuk email).
3.  **Install & Run**:
    ```bash
    composer install  # Download dependencies
    # Buka browser: http://localhost/mahasiswa_fp/public
    ```

> *Butuh panduan lebih detail? Baca [Setup Guide](DOCUMENTATION/00_SETUP_AND_INSTALLATION.md).*

---

### 📬 Kontribusi & Lisensi

Project ini Open Source di bawah lisensi **MIT**. Silakan fork dan kembangkan sesuai kebutuhan kampus Anda!

**Dibuat dengan ❤️ untuk Masa Depan Kampus Digital**
*(c) 2024 EventKu Team*
