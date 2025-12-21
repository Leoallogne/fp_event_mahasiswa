# Use Case Diagram - User Module EventKu

## Overview

Use case diagram untuk modul user pada sistem Event Management System (EventKu) mencakup semua fitur yang dapat diakses oleh user, mulai dari registrasi hingga manajemen notifikasi.

## Actor

**User** - Pengguna sistem yang dapat mendaftar, login, dan menggunakan fitur-fitur event management

## Use Cases

### 1. Authentication

- **UC-01: Register Account**

  - User mengisi form registrasi dengan data pribadi
  - System memvalidasi data dan membuat akun baru
  - User mendapatkan konfirmasi registrasi berhasil

- **UC-02: Login**

  - User memasukkan email dan password
  - System memvalidasi kredensial
  - User diarahkan ke dashboard jika berhasil

- **UC-03: Logout**
  - User memilih logout dari menu
  - System menghapus session dan mengarahkan ke halaman login

### 2. Profile Management

- **UC-04: View Profile**

  - User mengakses halaman profil
  - System menampilkan data profil user (nama, email, foto, dll)

- **UC-05: Edit Profile**
  - User mengubah data profil (nama, email, foto)
  - System memvalidasi dan menyimpan perubahan
  - User mendapatkan konfirmasi update berhasil

### 3. Event Management

- **UC-06: Browse Events**

  - User melihat daftar event yang tersedia
  - System menampilkan event dengan filter (kategori, tanggal, lokasi)
  - User dapat melakukan pencarian event

- **UC-07: View Event Details**

  - User memilih event dari daftar
  - System menampilkan detail lengkap event (deskripsi, tanggal, lokasi, kuota)
  - User dapat melihat jumlah peserta yang sudah terdaftar

- **UC-08: Register for Event**

  - User memilih tombol daftar pada event detail
  - System menampilkan form registrasi event
  - User mengkonfirmasi pendaftaran
  - System menyimpan registrasi dan mengirim notifikasi

- **UC-09: Cancel Registration**
  - User membatalkan pendaftaran event dari my-events
  - System memvalidasi pembatalan (sesuai batas waktu)
  - System mengupdate status registrasi dan mengirim notifikasi

### 4. My Events Management

- **UC-10: View My Events**

  - User mengakses halaman "Event Saya"
  - System menampilkan semua event yang diikuti user
  - System menampilkan status (upcoming, completed, cancelled)

- **UC-11: View Registration Details**
  - User melihat detail registrasi event
  - System menampilkan informasi lengkap registrasi
  - User dapat melihat status dan aksi yang tersedia

### 5. Dashboard

- **UC-12: View Dashboard**
  - User login dan diarahkan ke dashboard
  - System menampilkan statistik (total event, upcoming, completed)
  - System menampilkan chart aktivitas event
  - System menampilkan event mendatang
  - System menampilkan aktivitas terbaru
  - System menampilkan notifikasi terbaru

### 6. Notifications

- **UC-13: View Notifications**

  - User mengakses halaman notifikasi
  - System menampilkan daftar notifikasi user
  - System menandai notifikasi yang belum dibaca

- **UC-14: Mark Notification as Read**

  - User menandai notifikasi sebagai dibaca
  - System mengupdate status notifikasi
  - System mengurangi count unread

- **UC-15: Mark All Notifications as Read**
  - User menandai semua notifikasi sebagai dibaca
  - System mengupdate semua status notifikasi
  - System reset count unread ke 0

### 7. Additional Features

- **UC-16: Export Calendar**

  - User mengekspor event ke format calendar
  - System menggenerate file calendar (.ics)
  - User dapat mengunduh file calendar

- **UC-17: Search Events**
  - User memasukkan kata kunci pencarian
  - System memfilter event berdasarkan kata kunci
  - System menampilkan hasil pencarian

## Use Case Relationships

### Include Relationships

- UC-08 (Register for Event) includes UC-07 (View Event Details)
- UC-09 (Cancel Registration) includes UC-10 (View My Events)
- UC-11 (View Registration Details) includes UC-10 (View My Events)

### Extend Relationships

- UC-12 (View Dashboard) extends dengan UC-13 (View Notifications)
- UC-06 (Browse Events) extends dengan UC-17 (Search Events)

### Generalization

- **Authentication** (UC-01, UC-02, UC-03) adalah general use case untuk akses sistem
- **Profile Management** (UC-04, UC-05) adalah general use case untuk manajemen profil

## System Boundaries

```
+-------------------------------------------+
|              EventKu System               |
|                                           |
|  +-------------------------------------+  |
|  |           User Module               |  |
|  |                                     |  |
|  |  [User]                             |  |
|  |    |                               |  |
|  |    +-- Register Account             |  |
|  |    +-- Login                        |  |
|  |    +-- Logout                       |  |
|  |    +-- View/Edit Profile            |  |
|  |    +-- Browse/Search Events         |  |
|  |    +-- View Event Details           |  |
|  |    +-- Register for Event           |  |
|  |    +-- Cancel Registration          |  |
|  |    +-- View My Events               |  |
|  |    +-- View Dashboard               |  |
|  |    +-- Manage Notifications          |  |
|  |    +-- Export Calendar              |  |
|  |                                     |  |
|  +-------------------------------------+  |
|                                           |
+-------------------------------------------+
```

## Business Rules

1. User harus registrasi sebelum dapat mengakses fitur event
2. User hanya dapat mendaftar satu kali per event
3. Pembatalan registrasi hanya dapat dilakukan H-3 sebelum event
4. Notifikasi otomatis dikirim untuk reminder event (H-1)
5. User dapat mengelola profil dan notifikasi pribadi

## Non-Functional Requirements

- **Performance**: Dashboard harus load dalam <3 detik
- **Security**: Password user di-hash dengan bcrypt
- **Usability**: Interface responsive untuk mobile dan desktop
- **Availability**: System uptime 99% untuk user access
- **Scalability**: Support hingga 1000 concurrent users

## Implementation Notes

- Menggunakan PHP 8+ dengan MySQL database
- Frontend menggunakan Bootstrap 5 untuk responsive design
- Authentication menggunakan session management
- Notifikasi system real-time dengan badge count
- Chart visualization menggunakan Chart.js
- Email notifications untuk event reminders
