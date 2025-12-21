# Panduan Setup Admin Pertama Kali

## Halaman Setup Admin

Halaman setup admin adalah halaman tersembunyi yang memungkinkan Anda membuat akun administrator pertama kali tanpa harus menginsert langsung ke database.

## Cara Mengakses

### 1. Setelah Import Database

Setelah mengimport file `database/schema.sql`, akses:

```
http://localhost/ptojrct_putra/public/setup.php
```

### 2. Kondisi Akses

Halaman setup hanya bisa diakses jika:
- ✅ Belum ada admin di database
- ✅ Tabel `users` sudah dibuat

Jika sudah ada admin di database, halaman akan otomatis redirect ke `login.php`.

## Cara Menggunakan

1. **Akses Halaman Setup**
   - Buka browser
   - Akses URL: `http://localhost/ptojrct_putra/public/setup.php`

2. **Isi Form**
   - **Nama Lengkap**: Nama administrator
   - **Email**: Email untuk login (harus unik)
   - **Password**: Password minimal 6 karakter
   - **Konfirmasi Password**: Ketik ulang password

3. **Submit Form**
   - Klik tombol "Buat Admin"
   - Jika berhasil, akan muncul pesan sukses
   - Otomatis redirect ke halaman login setelah 2 detik

4. **Login**
   - Gunakan email dan password yang baru dibuat
   - Login di: `http://localhost/ptojrct_putra/public/login.php`

## Keamanan

### Fitur Keamanan:
- ✅ Hanya bisa diakses jika belum ada admin
- ✅ Validasi email format
- ✅ Validasi password minimal 6 karakter
- ✅ Password di-hash menggunakan `password_hash()`
- ✅ Cek duplikasi email
- ✅ Auto-redirect setelah admin dibuat

### Tips Keamanan:
1. **Simpan Informasi Login**
   - Simpan email dan password dengan aman
   - Jangan share informasi login

2. **Ubah Password Secara Berkala**
   - Login sebagai admin
   - Ubah password melalui profil (jika fitur tersedia)

3. **Hapus Default Admin (Opsional)**
   - Jika ingin menggunakan setup.php saja
   - Hapus baris INSERT default admin dari `database/schema.sql`
   - Atau hapus manual dari database setelah import

## Troubleshooting

### Error: "Terjadi kesalahan saat membuat admin"
**Solusi:**
- Pastikan database sudah diimport
- Pastikan tabel `users` sudah ada
- Cek koneksi database di file `.env`
- Cek error log PHP

### Error: "Email sudah terdaftar"
**Solusi:**
- Email tersebut sudah digunakan
- Gunakan email lain
- Atau hapus user dengan email tersebut dari database

### Halaman Setup Tidak Bisa Diakses
**Kemungkinan:**
- Sudah ada admin di database
- Tabel `users` belum dibuat
- File `setup.php` tidak ada

**Solusi:**
- Cek apakah sudah ada admin: `SELECT * FROM users WHERE role = 'admin'`
- Jika sudah ada, gunakan admin tersebut untuk login
- Jika belum ada, pastikan database sudah diimport

### Redirect ke Login Tapi Belum Buat Admin
**Solusi:**
- Cek database, mungkin sudah ada admin dari default insert
- Hapus admin default jika ingin menggunakan setup.php:
  ```sql
  DELETE FROM users WHERE role = 'admin';
  ```
- Kemudian akses setup.php lagi

## Alternatif: Menggunakan Default Admin

Jika tidak ingin menggunakan halaman setup, Anda bisa menggunakan default admin dari database:

- **Email**: admin@event.com
- **Password**: admin123

**PENTING**: Ubah password setelah pertama kali login!

## Menghapus Default Admin dari Schema

Jika ingin menggunakan setup.php saja, edit file `database/schema.sql`:

Hapus atau comment baris ini:
```sql
-- INSERT INTO users (nama, email, password, role) VALUES 
-- ('Administrator', 'admin@event.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

Kemudian import database dan akses setup.php.

## Catatan Penting

1. **Hanya Satu Kali**
   - Halaman setup hanya bisa digunakan sekali
   - Setelah admin pertama dibuat, halaman tidak bisa diakses lagi

2. **Backup Database**
   - Selalu backup database sebelum membuat perubahan
   - Simpan informasi login dengan aman

3. **Production**
   - Untuk production, pertimbangkan menghapus file setup.php setelah setup
   - Atau tambahkan proteksi tambahan (IP whitelist, dll)

---

**Tips**: Simpan URL setup.php dengan aman untuk referensi di masa depan, atau dokumentasikan di file setup project Anda.

