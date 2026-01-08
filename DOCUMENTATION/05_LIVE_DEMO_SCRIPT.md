# 🎬 Dokumen 5: Skenario Live Demo (Naskah Presentasi)
## Panduan Presentasi "EventKu" di Depan Dosen

**Durasi Estimasi**: 5 - 7 Menit
**Persiapan**:
1.  Buka Browser (Chrome/Edge).
2.  Siapkan 2 Tab atau 2 Browser berbeda (misal: Chrome untuk **User**, Edge/Incognito untuk **Admin**).
3.  Pastikan XAMPP/MAMP sudah jalan.
4.  Buka Database (phpMyAdmin) di tab terpisah (jaga-jaga jika ditanya database).

---

### 🟢 Babak 1: Pembukaan (1 Menit)

**[Tampilan: Halaman Landing Page (index.php)]**

**🗣️ KATA PENGANTAR (Script):**
> "Assalamualaikum/Selamat Pagi Pak/Bu Dosen.
> Pada kesempatan ini, saya akan mendemokan project Akhir saya yang berjudul **EventKu**.
>
> Masalah yang saya angkat adalah sulitnya mahasiswa mendapatkan info event kampus yang terpusat dan ribetnya proses pendaftaran yang masih manual.
>
> Solusinya adalah sistem berbasis web ini. Secara teknis, web ini dibangun menggunakan **PHP Native** murni dengan konsep **OOP (Object Oriented Programming)**, bukan prosedural biasa, dan keamanan datanya menggunakan pemisahan folder Public dan Modules."

---

### 🟡 Babak 2: Demo Bagian User (2 Menit)

**[Aksi 1: Buka Halaman Login -> Klik Register]**

**🗣️ KATA PENGANTAR:**
> "Pertama, saya akan mendemokan alur sebagai Mahasiswa baru. Kita bisa mendaftar akun di sini."

**[Aksi 2: Isi form Register dengan data bebas (atau gunakan fitur Login Google jika lokal support)]**
*(Tips: Kalau mau cepat, bilang saja "Untuk menyingkat waktu, saya akan login menggunakan akun User yang sudah saya siapkan" -> Login pakai `user@event.com` / `user123`)*

**[Aksi 3: Masuk ke Dashboard User]**

**🗣️ KATA PENGANTAR:**
> "Ini adalah Dashboard User. Di sini mahasiswa bisa melihat ringkasan event yang diikuti."

**[Aksi 4: Klik Menu 'Jelajah Event' -> Pilih satu Event Berbayar]**

**🗣️ KATA PENGANTAR:**
> "Misalkan saya tertarik ikut Seminar Teknologi ini. Di sini saya bisa melihat detail lokasi menggunakan Peta (Leaflet JS). Karena ini event berbayar, sistem mewajibkan saya upload bukti transfer."

**[Aksi 5: Klik Daftar -> Upload Gambar Apa Saja (sebagai bukti bayar) -> Submit]**

**🗣️ KATA PENGANTAR:**
> "Setelah mendaftar, status saya tidak langsung aktif, melainkan **PENDING** (Menunggu Verifikasi). Tiket belum muncul. Sekarang mari kita lihat dari sisi Admin."

---

### 🔴 Babak 3: Demo Bagian Admin (2 Menit)

**[Aksi 6: Buka Tab Baru (Incognito) -> Login sebagai Admin (`admin@event.com` / `admin123`)]**

**🗣️ KATA PENGANTAR:**
> "Sekarang saya login sebagai Administrator. Di Dashboard Admin, saya disuguhkan Grafik Analitik pendaftaran per bulan."

**[Aksi 7: Klik Menu 'Manajemen Event' -> Pilih Event yang tadi didaftar User -> Klik 'Lihat Peserta']**

**🗣️ KATA PENGANTAR:**
> "Admin menerima notifikasi ada pendaftar baru. Di sini Admin bisa mengecek bukti transfer yang diupload tadi."

**[Aksi 8: Klik Icon Bukti Bayar (Preview Gambar) -> Tutup -> Klik Tombol Centang Hijau (Verifikasi)]**

**🗣️ KATA PENGANTAR:**
> "Jika bukti valid, Admin melakukan Verifikasi. Sistem akan otomatis mengirim notifikasi ke user dan memotong kuota event di database."

---

### 🔵 Babak 4: Kembali ke User & Penutup (1 Menit)

**[Aksi 9: Kembali ke Tab User -> Refresh Halaman 'Tiket Saya']**

**🗣️ KATA PENGANTAR:**
> "Kembali ke sisi mahasiswa. Setelah di-refresh, status berubah menjadi **TERKONFIRMASI** dan tombol **CETAK TIKET** muncul."

**[Aksi 10: Klik Cetak Tiket (Tampilkan Print Preview)]**

**🗣️ KATA PENGANTAR:**
> "Mahasiswa kini sudah resmi terdaftar."

---

### ⚫ Babak 5: Bedah Kode (Jika Ditanya)

**[Aksi 11: Buka VS Code -> Tunjukkan Folder `modules/` dan `public/`]**

**🗣️ KATA PENGANTAR:**
> "Untuk arsitekturnya, saya menerapkan **Service Repository Pattern**.
> Bapak/Ibu bisa lihat, file Logika Bisnis saya pisah di folder `modules`, sedangkan folder `public` hanya untuk tampilan.
> Contohnya class `EventService` ini menangani semua logika Database, sehingga kodenya rapi dan bisa dipakai ulang."

---

**🗣️ PENUTUP:**
> "Sekian demo dari saya. Sistem ini siap untuk di-hosting dan digunakan. Terima kasih."
