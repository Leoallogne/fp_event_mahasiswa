# 📂 Dokumen 2: Struktur File & Folder (Deep Dive)
## Peta Navigasi untuk Developer

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
│
├── 📁 config/          (PENGATURAN DASAR)
│   ├── database.php    Jantung koneksi database. File ini baca password dari .env.
│   └── session.php     Mengatur agar login user tidak gampang dicuri (session hijacking).
│
├── 📁 modules/         (OTAK APLIKASI - LOGIC DISINI)
│   ├── 📁 users/       
│   │   ├── Auth.php        -> Mengurus Login, Logout, Cek Password.
│   │   └── GoogleAuth.php  -> Mengurus komunikasi ribet dengan Google.
│   │
│   ├── 📁 events/
│   │   ├── EventService.php    -> Mengurus Tambah/Edit/Hapus Event.
│   │   └── CategoryService.php -> Mengurus Kategori Event.
│   │
│   ├── 📁 registrations/
│   │   └── RegistrationService.php -> Mengurus Pendaftaran (Cek kuota, simpan data).
│   │
│   └── 📁 analytics/
│       └── AnalyticsService.php    -> Mengurus perhitungan statistik admin.
│
├── 📁 api/             (JEMBATAN EKSTERNAL)
│   └── ApiClientCalendar.php -> Helper khusus untuk kirim data ke Google Calendar.
│
└── 📁 public/          (AREA PUBLIK - BAGIAN YANG DILIHAT USER)
    │   (Hanya file di folder ini yang punya URL: evenku.com/...)
    │
    ├── index.php       -> Halaman Landing Page (Depan).
    ├── login.php       -> Halaman Login.
    ├── dashboard.php   -> Halaman Utama User setelah login.
    ├── profile.php     -> Halaman Edit Profil.
    ├── payment.php     -> Halaman Upload Bukti Bayar.
    │
    ├── 📁 admin/       (AREA ADMIN - DILINDUNGI PASSWORD)
    │   ├── dashboard.php       -> Pusat kontrol admin.
    │   ├── events.php          -> Form tambah event baru.
    │   ├── event-participants.php -> Cek siapa saja yang daftar.
    │   └── users.php           -> Kelola user manual.
    │
    ├── 📁 assets/      (DANDANAN WEBSITE)
    │   ├── 📁 css/     
    │   │   ├── layout.css      -> Mengatur sidebar, header, layout utama.
    │   │   ├── responsive.css  -> Mengatur tampilan di HP (Mobile).
    │   │   └── admin-modern.css -> Tema khusus halaman admin.
    │   └── 📁 js/      (Script interaktif)
    │
    └── 📁 uploads/     (GUDANG FILE USER)
        ├── 📁 avatars/  -> Foto profil user disimpan di sini.
        └── 📁 payments/ -> Bukti transfer user disimpan di sini.
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
