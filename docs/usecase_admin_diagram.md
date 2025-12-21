# Use Case Diagram - Admin Module EventKu

## Overview

Use case diagram untuk modul admin pada sistem Event Management System (EventKu) mencakup semua fitur administratif untuk mengelola events, users, dan sistem secara keseluruhan.

## Actor

**Admin** - Administrator sistem yang memiliki akses penuh untuk mengelola seluruh aspek sistem event management

## Use Cases

### 1. Authentication

- **UC-ADMIN-01: Admin Login**

  - Admin memasukkan email dan password
  - System memvalidasi kredensial admin
  - Admin diarahkan ke admin dashboard

- **UC-ADMIN-02: Admin Logout**
  - Admin memilih logout dari menu
  - System menghapus session admin
  - Admin diarahkan ke halaman login

### 2. Dashboard Management

- **UC-ADMIN-03: View Admin Dashboard**
  - Admin login dan diarahkan ke dashboard
  - System menampilkan statistik sistem (total events, users, registrations)
  - System menampilkan chart analytics
  - System menampilkan recent activities
  - System menampilkan system notifications

### 3. Event Management

- **UC-ADMIN-04: Create Event**

  - Admin mengakses halaman create event
  - Admin mengisi form event (nama, deskripsi, lokasi, tanggal, kuota)
  - System memvalidasi input
  - System menyimpan event baru
  - System menampilkan konfirmasi sukses

- **UC-ADMIN-05: View All Events**

  - Admin mengakses halaman events
  - System menampilkan daftar semua events
  - System menyediakan fitur filter dan search
  - System menampilkan status event (draft, published, cancelled)

- **UC-ADMIN-06: Edit Event**

  - Admin memilih event untuk diedit
  - System menampilkan form edit dengan data existing
  - Admin mengubah informasi event
  - System memvalidasi dan menyimpan perubahan
  - System menampilkan konfirmasi update

- **UC-ADMIN-07: Delete Event**

  - Admin memilih event untuk dihapus
  - System menampilkan konfirmasi hapus
  - Admin mengkonfirmasi penghapusan
  - System menghapus event dan data terkait
  - System menampilkan konfirmasi sukses

- **UC-ADMIN-08: Publish/Unpublish Event**
  - Admin mengubah status event dari draft ke published
  - System memvalidasi perubahan status
  - System mengupdate status event
  - Notifikasi otomatis dikirim ke users yang berlangganan

### 4. Category Management

- **UC-ADMIN-09: View Categories**

  - Admin mengakses halaman kategori
  - System menampilkan daftar semua kategori
  - System menampilkan jumlah event per kategori

- **UC-ADMIN-10: Create Category**

  - Admin mengklik tombol tambah kategori
  - Admin mengisi nama kategori
  - System memvalidasi input
  - System menyimpan kategori baru

- **UC-ADMIN-11: Edit Category**

  - Admin memilih kategori untuk diedit
  - Admin mengubah nama kategori
  - System memvalidasi dan menyimpan perubahan

- **UC-ADMIN-12: Delete Category**
  - Admin memilih kategori untuk dihapus
  - System mengecek penggunaan kategori
  - System menghapus kategori jika tidak digunakan

### 5. Participant Management

- **UC-ADMIN-13: View Event Participants**

  - Admin memilih event untuk melihat peserta
  - System menampilkan daftar semua peserta
  - System menampilkan status registrasi
  - System menyediakan fitur export data

- **UC-ADMIN-14: Manage Registration Status**

  - Admin mengubah status registrasi peserta
  - System memvalidasi perubahan status
  - System mengupdate status registrasi
  - Notifikasi dikirim ke peserta terkait

- **UC-ADMIN-15: Export Participant Data**
  - Admin memilih format export (CSV, Excel)
  - System menggenerate file export
  - Admin dapat mengunduh file

### 6. Notification Management

- **UC-ADMIN-16: View Notification Log**

  - Admin mengakses halaman notifikasi
  - System menampilkan log semua notifikasi
  - System menampilkan status pengiriman
  - System menampilkan detail penerima

- **UC-ADMIN-17: Send Event Reminder**

  - Admin memilih event untuk reminder
  - Admin mengklik tombol kirim reminder
  - System mengirim reminder ke semua peserta
  - System menampilkan konfirmasi dan statistik pengiriman

- **UC-ADMIN-18: Create Custom Notification**
  - Admin membuat notifikasi kustom
  - Admin memilih target penerima (all users, specific users)
  - System mengirim notifikasi
  - System menampilkan konfirmasi sukses

### 7. Analytics & Reporting

- **UC-ADMIN-19: View System Analytics**

  - Admin mengakses halaman analytics
  - System menampilkan grafik registrasi trends
  - System menampilkan event popularity
  - System menampilkan user engagement metrics

- **UC-ADMIN-20: Generate Reports**

  - Admin memilih tipe report (monthly, yearly, custom)
  - Admin memilih parameter report
  - System menggenerate report
  - Admin dapat export atau print report

- **UC-ADMIN-21: View Real-time Statistics**
  - Admin melihat statistik real-time
  - System menampilkan active users
  - System menampilkan ongoing events
  - System menampilkan system performance metrics

### 8. User Management (Extended)

- **UC-ADMIN-22: View All Users**

  - Admin mengakses halaman users
  - System menampilkan daftar semua users
  - System menampilkan status user (active, inactive)

- **UC-ADMIN-23: Manage User Status**

  - Admin mengubah status user (activate/deactivate)
  - System memvalidasi perubahan
  - System mengupdate status user

- **UC-ADMIN-24: View User Activity**
  - Admin melihat aktivitas user tertentu
  - System menampilkan history registrasi
  - System menampilkan login history

## Use Case Relationships

### Include Relationships

- UC-ADMIN-04 (Create Event) includes UC-ADMIN-09 (View Categories)
- UC-ADMIN-06 (Edit Event) includes UC-ADMIN-09 (View Categories)
- UC-ADMIN-13 (View Event Participants) includes UC-ADMIN-05 (View All Events)
- UC-ADMIN-17 (Send Event Reminder) includes UC-ADMIN-05 (View All Events)

### Extend Relationships

- UC-ADMIN-03 (View Admin Dashboard) extends dengan UC-ADMIN-19 (View System Analytics)
- UC-ADMIN-05 (View All Events) extends dengan search dan filter functionality

### Generalization

- **Event Management** (UC-ADMIN-04, UC-ADMIN-05, UC-ADMIN-06, UC-ADMIN-07, UC-ADMIN-08)
- **Category Management** (UC-ADMIN-09, UC-ADMIN-10, UC-ADMIN-11, UC-ADMIN-12)
- **Notification Management** (UC-ADMIN-16, UC-ADMIN-17, UC-ADMIN-18)

## System Boundaries

```
+-------------------------------------------+
|              EventKu System               |
|                                           |
|  +-------------------------------------+  |
|  |           Admin Module              |  |
|  |                                     |  |
|  |  [Admin]                            |  |
|  |    |                               |  |
|  |    +-- Admin Login/Logout          |  |
|  |    +-- View Dashboard              |  |
|  |    +-- Event Management            |  |
|  |    |  +-- Create Event            |  |
|  |    |  +-- Edit Event              |  |
|  |    |  +-- Delete Event            |  |
|  |    |  +-- Publish/Unpublish       |  |
|  |    +-- Category Management         |  |
|  |    |  +-- CRUD Categories         |  |
|  |    +-- Participant Management      |  |
|  |    |  +-- View Participants       |  |
|  |    |  +-- Manage Status           |  |
|  |    |  +-- Export Data             |  |
|  |    +-- Notification Management     |  |
|  |    |  +-- Send Reminders          |  |
|  |    |  +-- Custom Notifications    |  |
|  |    +-- Analytics & Reporting       |  |
|  |    |  +-- View Statistics         |  |
|  |    |  +-- Generate Reports       |  |
|  |    +-- User Management             |  |
|  |    |  +-- View All Users          |  |
|  |    |  +-- Manage User Status      |  |
|  |                                     |  |
|  +-------------------------------------+  |
|                                           |
+-------------------------------------------+
```

## Business Rules

1. Admin dapat mengakses semua fitur sistem tanpa batasan
2. Event harus memiliki kategori yang valid
3. Penghapusan event hanya dapat dilakukan jika tidak ada peserta aktif
4. Reminder event dikirim otomatis H-1 sebelum tanggal event
5. Admin dapat melihat dan export semua data sistem
6. Perubahan status event mempengaruhi notifikasi otomatis
7. Kategori yang digunakan tidak dapat dihapus

## Non-Functional Requirements

- **Performance**: Admin dashboard load dalam <2 detik
- **Security**: Admin session timeout 30 menit
- **Audit Trail**: Semua aksi admin tercatat di log
- **Data Integrity**: Validasi data sebelum penyimpanan
- **Scalability**: Support hingga 10,000 concurrent users
- **Backup**: Automatic backup harian untuk data penting

## Implementation Notes

- Menggunakan PHP 8+ dengan MySQL database
- Frontend menggunakan Bootstrap 5 dengan AdminLTE theme
- Real-time update dengan WebSocket untuk notifications
- Chart.js untuk visualisasi data analytics
- Export functionality dengan PHPSpreadsheet
- Email queue system untuk notifikasi massal
- Role-based access control (RBAC) untuk keamanan
- API endpoints untuk integrasi dengan external systems
