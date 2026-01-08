# ⚙️ Dokumen 0: Panduan Instalasi & Setup (Wajib Baca)
## Menjalankan EventKu dari Nol di Localhost

Selamat datang di panduan instalasi. Ikuti langkah-langkah di bawah ini secara **berurutan** agar sistem berjalan lancar tanpa error.

---

### 1. 📋 Persyaratan Sistem (Prerequisites)

Sebelum mulai, pastikan komputer Anda sudah terinstall software berikut:

*   **XAMPP / MAMP / Laragon**: Untuk server PHP & Database MySQL.
    *   *PHP Version*: Wajib **8.0** ke atas.
    *   *MySQL Version*: 5.7 atau lebih baru.
*   **Composer**: Manajer paket PHP (mirip NPM di JS).
    *   [Download Composer disini](https://getcomposer.org/download/)
*   **Git Bash (Windows) / Terminal (Mac/Linux)**: Untuk menjalankan perintah instalasi.

---

### 2. 🗄️ Persiapan Database

1.  Buka **phpMyAdmin** (biasanya di `http://localhost/phpmyadmin`).
2.  Buat database baru dengan nama: `event_management`
3.  Klik tab **Import**.
4.  Pilih file dari folder project ini: `database/schema.sql`
5.  Klik **Go/Kirim**. Pastikan semua tabel (`users`, `events`, `registrations`, dll) berhasil dibuat.

> [!TIP]
> **Data Awal**: Script SQL di atas otomatis membuat akun Admin default:
> *   Email: `admin@event.com`
> *   Pass: `admin123`

---

### 3. 🔐 Konfigurasi Environment (.env)

Sistem ini menyimpan setting rahasia di file `.env`.

1.  Cari file bernama `.env.example` di folder utama project.
2.  Duplikat/Copy file tersebut lalu ganti namanya menjadi `.env`.
3.  Buka file `.env` pakai Text Editor (VS Code/Notepad) dan sesuaikan:

```ini
# --- DATABASE ---
DB_HOST=localhost
DB_NAME=event_management
DB_USER=root
DB_PASS=              <-- Kosongkan jika XAMPP. Isi 'root' jika MAMP.

# --- WEBSITE URL ---
# Sesuaikan dengan nama folder di htdocs Anda
APP_URL=http://localhost/mahasiswa_fp/public

# --- EMAIL (SMTP) ---
# Wajib diisi agar fitur Lupa Password & Notifikasi jalan
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=email_anda@gmail.com
SMTP_PASS=xxxx xxxx xxxx xxxx  <-- Pakai 'App Password' Gmail, BUKAN password login biasa.
```

> [!WARNING]
> Jangan lupa simpan (Save) perubahan file `.env` Anda!

---

### 4. 📦 Install Library (Composer)

Project ini butuh beberapa library tambahan (PHPMailer, Dotenv, dll) yang belum ada di folder.

1.  Buka Terminal / Command Prompt.
2.  Arahkan ke folder project ini. Contoh:
    ```bash
    cd C:\xampp\htdocs\mahasiswa_fp
    ```
3.  Jalankan perintah ini dan tunggu sampai selesai:
    ```bash
    composer install
    ```
4.  Jika sukses, folder `vendor` akan muncul otomatis.

---

### 5. 🚀 Menjalankan Aplikasi

Sekarang saatnya mencoba!

1.  Pastikan Apache & MySQL di XAMPP/MAMP sudah **Start/Running**.
2.  Buka browser (Chrome/Firefox).
3.  Ketik alamat berikut (sesuaikan dengan nama folder Anda):
    ```
    http://localhost/mahasiswa_fp/public
    ```
    *(Ingat: Wajib pakai `/public` di belakangnya)*

4.  Jika muncul halaman Landing Page EventKu yang bagus, **SELAMAT!** Instalasi berhasil.

---

### ❓ Troubleshooting (Masalah Umum)

**Q: Error "Composer is not recognized" di terminal?**
A: Anda belum install Composer, atau perlu restart komputer setelah install.

**Q: Halaman putih / Error 500?**
A: Cek folder `vendor` sudah ada belum? Kalau belum, jalankan `composer install` lagi.
A: Cek `.env` apakah password database sudah benar?

**Q: Gambar tidak bisa di-upload?**
A: Cek folder `public/uploads`. Pastikan permission-nya sudah *Writeable*.

**Q: Email tidak terkirim?**
A: Pastikan `SMTP_PASS` di `.env` adalah **App Password** 16 digit dari Google, bukan password login email biasa.

---
**Dokumentasi Selanjutnya**:
[-> Lihat Struktur Folder](02_FOLDER_STRUCTURE.md) | [-> Repo-Service Pattern](05_SERVICE_REPOSITORY_PATTERN.md)
