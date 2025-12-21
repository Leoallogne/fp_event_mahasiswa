# Daftar Endpoint - Event Management System

## Public Endpoints (User)

### Authentication
- `GET /public/login.php` - Halaman login
- `POST /public/login.php` - Proses login
- `GET /public/register.php` - Halaman registrasi
- `POST /public/register.php` - Proses registrasi
- `GET /public/logout.php` - Logout user
- `GET /public/setup.php` - Halaman setup admin pertama (hanya jika belum ada admin)
- `POST /public/setup.php` - Proses pembuatan admin pertama

### Events
- `GET /public/index.php` - Daftar semua event
- `GET /public/event-detail.php?id={id}` - Detail event
- `GET /public/register-event.php?id={id}` - Daftar ke event
- `GET /public/cancel-registration.php?id={id}` - Batalkan pendaftaran
- `GET /public/my-events.php` - Event yang diikuti user
- `GET /public/export-calendar.php?id={id}` - Export ke Google Calendar

## Admin Endpoints

### Dashboard
- `GET /public/admin/dashboard.php` - Dashboard admin dengan statistik dan grafik

### Event Management
- `GET /public/admin/events.php` - Daftar semua event (CRUD)
- `POST /public/admin/events.php` - Create/Update/Delete event
  - `action=create` - Buat event baru
  - `action=update` - Update event
  - `action=delete` - Hapus event
- `GET /public/admin/event-participants.php?id={id}` - Daftar peserta event

### Category Management
- `GET /public/admin/categories.php` - Daftar kategori (CRUD)
- `POST /public/admin/categories.php` - Create/Update/Delete kategori
  - `action=create` - Buat kategori baru
  - `action=update` - Update kategori
  - `action=delete` - Hapus kategori

### Analytics
- `GET /public/admin/analytics.php` - Halaman analitik
- `GET /public/admin/analytics.php?export=category` - Export kategori ke CSV
- `GET /public/admin/analytics.php?export=monthly` - Export tren bulanan ke CSV

### Notifications
- `GET /public/admin/notifications.php` - Halaman notifikasi
- `POST /public/admin/notifications.php` - Kirim reminder event
  - `send_reminder=1` - Trigger pengiriman reminder
  - `event_id={id}` - ID event yang akan dikirim reminder

## API Endpoints (Internal)

### Google Calendar API
- `ApiClientCalendar::fetch()` - Ambil event dari Google Calendar
- `ApiClientCalendar::pushEvent()` - Tambah event ke Google Calendar
- `ApiClientCalendar::updateEvent()` - Update event di Google Calendar
- `ApiClientCalendar::deleteEvent()` - Hapus event dari Google Calendar

## Request/Response Format

### Login Request
```php
POST /public/login.php
Content-Type: application/x-www-form-urlencoded

email=user@example.com
password=password123
```

### Register Request
```php
POST /public/register.php
Content-Type: application/x-www-form-urlencoded

nama=John Doe
email=user@example.com
password=password123
confirm_password=password123
```

### Create Event Request
```php
POST /public/admin/events.php
Content-Type: application/x-www-form-urlencoded

action=create
title=Event Title
kategori=Seminar
tanggal=2024-12-20T10:00
lokasi=Auditorium
deskripsi=Event Description
kuota=100
```

### Update Event Request
```php
POST /public/admin/events.php
Content-Type: application/x-www-form-urlencoded

action=update
id=1
title=Updated Title
kategori=Workshop
tanggal=2024-12-21T14:00
lokasi=Lab Komputer
deskripsi=Updated Description
kuota=50
```

### Delete Event Request
```php
POST /public/admin/events.php
Content-Type: application/x-www-form-urlencoded

action=delete
id=1
```

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation successful"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message"
}
```

## Authentication

Semua endpoint admin memerlukan:
- Session dengan `user_role = 'admin'`
- Login terlebih dahulu

Semua endpoint user memerlukan:
- Session dengan `user_role = 'user'`
- Login terlebih dahulu (kecuali view event)

## Query Parameters

### Event Detail
- `id` (required) - ID event

### Register Event
- `id` (required) - ID event

### Cancel Registration
- `id` (required) - ID event

### Export Calendar
- `id` (required) - ID event

### Analytics Export
- `export=category` - Export kategori event
- `export=monthly` - Export tren bulanan

### Event Participants
- `id` (required) - ID event

## Status Codes

- `200` - Success
- `302` - Redirect (after login/logout)
- `404` - Not Found
- `500` - Server Error

## Notes

- Semua form menggunakan `application/x-www-form-urlencoded`
- Redirect dilakukan setelah POST request
- Session digunakan untuk authentication
- CSRF protection dapat ditambahkan untuk production

