# Panduan Instalasi - Web Sistem Manajemen Event Mahasiswa

## Langkah-langkah Instalasi

### 1. Persiapan Environment

Pastikan Anda memiliki:
- ✅ PHP 7.4 atau lebih tinggi
- ✅ MySQL/MariaDB 5.7 atau lebih tinggi
- ✅ Web Server (Apache/Nginx) atau XAMPP/WAMP
- ✅ Composer (untuk dependency management)

### 2. Clone/Download Project

```bash
cd C:\xampp\htdocs\ptojrct_putra
```

### 3. Install Dependencies

Jalankan perintah berikut untuk menginstall PHPMailer:

```bash
composer install
```

Atau jika belum ada composer, download dari https://getcomposer.org/

### 4. Setup Database

#### Option A: Menggunakan phpMyAdmin
1. Buka phpMyAdmin di browser: `http://localhost/phpmyadmin`
2. Klik tab "Import"
3. Pilih file `database/schema.sql`
4. Klik "Go" untuk import

#### Option B: Menggunakan Command Line
```bash
mysql -u root -p < database/schema.sql
```

Database akan dibuat otomatis dengan nama `event_management` beserta semua tabel yang diperlukan.

### 5. Konfigurasi Environment

1. Copy file `.env.example` menjadi `.env`:
   ```bash
   copy .env.example .env
   ```
   (Windows PowerShell: `Copy-Item .env.example .env`)

2. Edit file `.env` dan sesuaikan konfigurasi:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=event_management
DB_USER=root
DB_PASS=

# Google Calendar API (Opsional - bisa dikosongkan dulu)
GOOGLE_CALENDAR_API_KEY=
GOOGLE_CALENDAR_CLIENT_ID=
GOOGLE_CALENDAR_CLIENT_SECRET=

# Email Configuration (Opsional - bisa dikosongkan dulu)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_FROM_NAME=Event Management System

# Application Configuration
APP_URL=http://localhost/ptojrct_putra/public
SESSION_LIFETIME=3600
```

### 6. Setup Google Calendar API (Opsional)

Jika ingin menggunakan fitur Google Calendar:

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang ada
3. Aktifkan Google Calendar API:
   - Pergi ke "APIs & Services" > "Library"
   - Cari "Google Calendar API"
   - Klik "Enable"
4. Buat API Key:
   - Pergi ke "APIs & Services" > "Credentials"
   - Klik "Create Credentials" > "API Key"
   - Copy API Key ke file `.env`

### 7. Setup Email dengan Gmail (Opsional)

Jika ingin menggunakan fitur email reminder:

1. Login ke Google Account
2. Aktifkan 2-Step Verification:
   - Buka Google Account Settings
   - Security > 2-Step Verification > Turn On
3. Generate App Password:
   - Security > 2-Step Verification > App Passwords
   - Select app: Mail
   - Select device: Other (Custom name)
   - Masukkan nama: "Event Management"
   - Klik "Generate"
   - Copy password yang dihasilkan
4. Masukkan ke file `.env`:
   ```
   SMTP_USER=your_email@gmail.com
   SMTP_PASS=generated_app_password_here
   ```

### 8. Set Permissions (Linux/Mac)

Jika menggunakan Linux/Mac, set permission untuk folder:

```bash
chmod -R 755 public/
chmod -R 755 modules/
chmod -R 755 config/
```

### 9. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/ptojrct_putra/public
```

### 10. Setup Admin Pertama Kali

Anda memiliki 2 opsi:

#### Opsi 1: Menggunakan Halaman Setup (Direkomendasikan)
1. Akses halaman setup:
   ```
   http://localhost/ptojrct_putra/public/setup.php
   ```
2. Isi form untuk membuat admin pertama
3. Halaman ini hanya bisa diakses jika belum ada admin di database
4. Setelah admin dibuat, halaman akan redirect ke login

#### Opsi 2: Menggunakan Default Admin
Jika menggunakan default admin dari database:
- **Email**: admin@event.com
- **Password**: admin123

**PENTING**: 
- Ubah password default setelah pertama kali login!
- Atau hapus INSERT default admin dari schema.sql dan gunakan halaman setup.php

## Troubleshooting

### Error: Database Connection Failed
- ✅ Pastikan MySQL service berjalan
- ✅ Cek konfigurasi di file `.env`
- ✅ Pastikan database `event_management` sudah dibuat
- ✅ Verifikasi username dan password MySQL

### Error: Class Not Found
- ✅ Pastikan sudah menjalankan `composer install`
- ✅ Cek apakah folder `vendor/` sudah ada
- ✅ Pastikan autoload sudah di-generate

### Error: Email Tidak Terkirim
- ✅ Pastikan menggunakan App Password (bukan password biasa)
- ✅ Cek konfigurasi SMTP di `.env`
- ✅ Pastikan port 587 tidak di-block firewall
- ✅ Untuk development, cek error log di PHP

### Error: Google Calendar API Error
- ✅ Pastikan API Key valid
- ✅ Pastikan Google Calendar API sudah diaktifkan
- ✅ Cek quota API di Google Cloud Console

### Error: Session Not Working
- ✅ Pastikan session folder writable
- ✅ Cek `session.save_path` di php.ini
- ✅ Pastikan cookies enabled di browser

## Verifikasi Instalasi

Setelah instalasi, verifikasi dengan:

1. ✅ Buka halaman utama: `http://localhost/ptojrct_putra/public`
2. ✅ Login sebagai admin
3. ✅ Buat event baru
4. ✅ Buat kategori baru
5. ✅ Lihat dashboard dengan grafik
6. ✅ Export CSV dari halaman analitik

## Next Steps

Setelah instalasi berhasil:

1. Ubah password admin default
2. Buat kategori event sesuai kebutuhan
3. Buat beberapa event contoh
4. Test fitur registrasi sebagai user
5. Test fitur notifikasi email
6. Test integrasi Google Calendar

## Support

Jika mengalami masalah, cek:
- Error log PHP
- Error log MySQL
- Browser console untuk JavaScript errors
- Network tab untuk HTTP errors

