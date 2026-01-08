# 🗄️ Dokumen 6: Struktur Database (Schema & Dictionary)
## Kamus Data Lengkap EventKu

Dokumen ini menjelaskan detail teknis dari setiap tabel di database `event_management`. Gunakan ini sebagai referensi saat melakukan query SQL manual atau debugging.

---

### 1. 📊 Overview Relasi (ERD Summary)

*   Satu **User** bisa mendaftar ke banyak **Event** (`1 to Many`).
*   Satu **Event** memiliki satu **Kategori** (`Many to 1`).
*   **Registrations** adalah tabel penghubung (*Pivot Table*) antara User dan Event.

---

### 2. 📝 Detail Tabel

#### A. Tabel `users`
Menyimpan data otentikasi dan profil pengguna.

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | ID Unik User (Auto Increment). |
| `nama` | VARCHAR(255) | Nama lengkap user. |
| `email` | VARCHAR(255) | Email login (Wajib Unik). |
| `password` | VARCHAR(255) | Password ter-enkripsi (Hash Bcrypt). |
| `avatar` | VARCHAR(255) | Nama file foto profil (Nullable). |
| `role` | ENUM | Peran user: `'admin'` atau `'user'`. |
| `email_notifications` | BOOLEAN | Preverensi notifikasi email (1=Ya, 0=Tidak). |
| `google_access_token`| TEXT | Token untuk integrasi Login Google & Calendar. |

#### B. Tabel `events`
Menyimpan data katalog kegiatan/acara.

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | ID Unik Event. |
| `title` | VARCHAR(255) | Judul Acara. |
| `kategori` | VARCHAR(255) | Nama kategori (Disarankan sesuai tabel categories). |
| `tanggal` | DATETIME | Waktu pelaksanaan event. |
| `lokasi` | VARCHAR(255) | Nama tempat/gedung. |
| `kuota` | INT | Sisa kursi yang tersedia. |
| `is_paid` | BOOLEAN | `0` = Gratis, `1` = Berbayar. |
| `price` | DECIMAL | Harga tiket (jika berbayar). |
| `created_by` | INT (FK) | ID Admin yang membuat event. |

#### C. Tabel `registrations` (Transaksi)
Tabel paling penting! Mencatat siapa mendaftar apa.

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | ID Transaksi. |
| `user_id` | INT (FK) | Siapa yang daftar? |
| `event_id` | INT (FK) | Event apa yang didaftar? |
| `status` | VARCHAR | `'pending'` (Belum bayar/verif), `'confirmed'` (Sah), `'rejected'`. |
| `payment_proof` | VARCHAR(255) | Nama file bukti transfer (jika ada). |
| `daftar_waktu` | DATETIME | Timestamp saat klik tombol daftar. |

> [!NOTE]
> **Unique Constraint**: Kombinasi `user_id` + `event_id` bersifat UNIK. Artinya, satu user tidak bisa mendaftar event yang sama dua kali.

#### D. Tabel `categories`
Data maser untuk pengelompokan event.

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK) | ID Kategori. |
| `nama` | VARCHAR | Contoh: 'Seminar', 'Music', 'Workshop'. |
| `deskripsi` | TEXT | Penjelasan singkat kategori. |

#### E. Tabel `notifications`
Inbox pesan untuk user di dalam aplikasi.

| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `user_id` | INT (FK) | Penerima pesan. |
| `message` | TEXT | Isi pesan notifikasi. |
| `is_read` | BOOLEAN | Status baca (`0`=Belum, `1`=Sudah). |
| `type` | VARCHAR | Jenis notifikasi (info, success, alert). |

---

### 3. 🔍 Query Penting (Cheat Sheet)

**Cek Sisa Kuota Event:**
```sql
SELECT title, kuota FROM events WHERE id = 1;
```

**Lihat Siapa Saja yang Daftar Event Tertentu (Join Table):**
```sql
SELECT users.nama, users.email, registrations.status 
FROM registrations
JOIN users ON registrations.user_id = users.id
WHERE registrations.event_id = 5;
```

**Reset Password Admin Manual:**
Jika lupa password admin, jalankan query ini untuk mereset jadi `admin123`:
```sql
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@event.com';
```

---
**Dokumentasi Selanjutnya**:
[-> Kembali ke Overview](01_PROJECT_OVERVIEW.md)
