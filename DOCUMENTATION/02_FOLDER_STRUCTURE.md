# 📂 Dokumen 2: Struktur File & Folder (Deep Dive)
## Peta Navigasi untuk Developer

> **Navigasi Dokumentasi**:
> [🏠 Home](../README.md) | [⚙️ Setup & Install](00_SETUP_AND_INSTALLATION.md) | [📘 Overview](01_PROJECT_OVERVIEW.md) | [🏗️ Repo-Service Pattern](05_SERVICE_REPOSITORY_PATTERN.md) | [🗄️ Database](06_DATABASE_SCHEMA.md)

---

Project ini tidak menggunakan struktur PHP biasa (seperti semua file ditumpuk di satu folder). Kita menggunakan **"Public Folder Strategy"**.

### 🤔 Kenapa Struktur Ini?
Banyak website PHP pemula di-hack karena file konfigurasi (seperti `.env` atau `database.php`) bisa dibuka langsung di browser (misal: `website.com/config/database.php`).

Di EventKu, hal itu **TIDAK BISA DILAKUKAN**.
Web Server (Apache/Nginx) hanya diarahkan ke folder `public/`. Semua file di luar folder itu **gaib** bagi browser, tapi bisa dibaca oleh PHP.

---

### 🌳 Pohon Direktori (Directory Tree)

Berikut penjelasan detail setiap folder dan file penting di dalamnya:

```plaintext
mahasiswa_fp/  (ROOT PROJECT - AREA TERLARANG BAGI BROWSER)
│
├── .env                [PENTING] File rahasia! Simpan password DB & API Key disini.
├── composer.json       Daftar pustaka tambahan (library) yang dipakai project.
├── index.php           Redirector sederhana (jika user akses root folder).
│
├── 📁 config/          (PENGATURAN DASAR)
│   ├── database.php    Jantung koneksi database (PDO).
│   └── session.php     Mengatur keamanan sesi login.
│
├── 📁 modules/         (OTAK APLIKASI - LOGIC DISINI)
│   ├── 📁 users/       
│   │   └── Auth.php-       -> Class Login & Register.
│   │
│   ├── 📁 events/
│   │   ├── EventService.php    -> Logic CRUD Event.
│   │
│   ├── 📁 registrations/
│   │   └── RegistrationService.php -> Logic Pendaftaran & Transaksi.
│   │
│   └── 📁 notifications/
│       └── NotificationService.php -> Logic Notifikasi.
│
├── 📁 public/          (AREA PUBLIK - BAGIAN YANG DILIHAT USER)
│   │   (Hanya file di folder ini yang punya URL: evenku.com/...)
│   │
│   ├── index.php       -> Landing Page.
│   ├── login.php       -> Halaman Login.
│   ├── dashboard.php   -> Dashboard User.
│   │
│   ├── 📁 admin/       (PANEL ADMIN)
│   │   ├── dashboard.php   -> Statistik Admin.
│   │   └── events.php      -> Form Kelola Event.
│   │
│   ├── 📁 assets/      (DANDANAN WEBSITE)
│   │   ├── 📁 css/     (Style)
│   │   └── 📁 js/      (Interaktif)
│   │
│   └── 📁 uploads/     (STORAGE)
│       ├── 📁 avatars/  -> Foto user.
│       └── 📁 payments/ -> Bukti bayar.
│
├── 📁 database/        (SQL)
│   └── schema.sql      -> Script pembuatan tabel database.
│
└── 📁 scripts/          (UTILITY)
    └── ...             -> Script bantuan (misal: setup wizard).
```

---

### 🚦 Alur Pemanggilan File (How it works)

Bagaimana cara file di `public` (luar) bisa memanggil file di `modules` (dalam)?

Mari kita lihat baris pertama di setiap file PHP di `public`:

```php
// Contoh di public/dashboard.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/events/EventService.php';
```

*   `__DIR__`: Artinya "Folder tempat file ini berada" (yaitu `public`).
*   `/../`: Artinya "Mundur satu langkah ke folder induk" (keluar dari `public`, masuk ke `mahasiswa_fp`).
*   `/modules/...`: Masuk ke folder modules.

**Analogi:**
*   Folder `public` adalah **Ruang Tamu**. Tamu (User) hanya boleh di sini.
*   Folder `modules` adalah **Dapur**. Tamu tidak boleh masuk dapur, tapi Pelayan (Script PHP) bisa bolak-balik dari Ruang Tamu ke Dapur untuk mengambilkan Makanan (Data) untuk Tamu.

### 📝 Tips untuk Developer
1.  **Mau ganti warna website?**
    Edit file di `public/assets/css/`. Jangan ubah file PHP-nya.
2.  **Mau ubah logika pendaftaran?**
    Jangan cari di `public/register-event.php`. Buka `modules/registrations/RegistrationService.php`.
3.  **Mau tambah kolom di tabel database?**
    Ubah database-nya dulu, lalu update class Model di `modules/`.

Struktur ini membuat kode Anda **bersih**, **terorganisir**, dan **profesional**.

---
**Dokumentasi Selanjutnya**:
[-> Lihat Arsitektur OOP](03_OOP_ARCHITECTURE.md)
