# 🏗️ Dokumen 3: OOP Architecture & Code Design
## Detail Teknis Pemrograman Berorientasi Objek (OOP)

Sistem EventKu dibangun menggunakan konsep OOP yang solid untuk memastikan kode reusable (bisa dipakai ulang) dan scalable (mudah dikembangkan). Tidak ada kode "spaghetti" (campur aduk) di sini; semua logika dibungkus dalam Class.

### 1. Pola Desain (Design Principles)
*   **Service Layer Pattern**: Logika bisnis tidak ditulis langsung di file tampilan (`public/*.php`), melainkan dipisah ke dalam Class "Service" di folder `modules/`.
    *   *Contoh*: Logika "Daftar Event" ada di `RegistrationService`, bukan di `register-event.php`.
*   **Dependency Injection (Connectors)**: Class Service menerima dependensi (seperti koneksi Database) melalui constructor atau membuatnya saat inisialisasi.
*   **Encapsulation**: Properti sensitif dilindungi (private), hanya bisa diakses lewat method public.
*   **Single Responsibility**: Satu class hanya mengurus satu hal. `Auth` hanya mengurus login, tidak mengurus event.

---

### 2. Class Reference (Kamus Class)

Berikut adalah daftar Class utama dan method (fungsi) penting di dalamnya:

#### A. Core Classes (`config/`)

**1. Class `Database`**
Pondasi utama aplikasi. Bertugas mengelola koneksi ke MySQL.
*   `__construct()`: Membaca file `.env` dan menyiapkan parameter koneksi.
*   `getConnection()`: **Fungsi Paling Penting!** Mengembalikan objek `PDO` yang aktif. Semua Service lain memanggil ini.

#### B. Service Classes (`modules/`)

**2. Class `Auth` (`modules/users/Auth.php`)**
Penjaga pintu gerbang keamanan.
*   `requireUser()` / `requireAdmin()`: **Middleware**. Mengecek apakah user sudah login dan punya hak akses. Jika tidak, tendang ke login page.
*   `register()`: Hash password user baru dan simpan ke DB.
*   `login($email, $password)`: Verifikasi password user.
*   `getCurrentUser()`: Mengambil data user yang sedang login dari Session.

**3. Class `EventService` (`modules/events/EventService.php`)**
Manajer data event.
*   `createEvent($data)`: Validasi input admin dan INSERT event baru.
*   `getAllEvents()`: Mengambil list event (bisa difilter).
*   `getEventById($id)`: Mengambil detail satu event lengkap.
*   `updateCalendarEventId()`: Menyambungkan ID event lokal dengan ID event di Google Calendar.
*   `getEventStats()`: (Admin) Menghitung total pendaftar untuk Dashboard.

**4. Class `RegistrationService` (`modules/registrations/RegistrationService.php`)**
Mesin transaksi pendaftaran. Logikanya paling kompleks.
*   `registerForEvent($userId, $eventId)`:
    1.  Cek apakah user sudah daftar?
    2.  Cek apakah kuota masih ada? (**Atomicity/Locking** penting di sini agar tidak oversold).
    3.  Kurangi kuota event.
    4.  Simpan data pendaftaran.
*   `verifyPayment($userId, $eventId, $status)`: (Admin) Mengubah status dari 'pending' ke 'confirmed' atau 'rejected'.
*   `getHistoryByUser($userId)`: Mengambil riwayat event user untuk halaman "Tiket Saya".

**5. Class `AnalyticsService` (`modules/analytics/AnalyticsService.php`)**
Data Scientist-nya admin.
*   `getMonthlyStats()`: Mengolah data timestamp pendaftaran menjadi grafik per bulan.
*   `getCategoryDistribution()`: Menghitung persentase popularitas kategori.
*   `getExportData()`: Menyiapkan raw data untuk dijadikan file CSV/Excel.

**6. Class `ApiClientCalendar` (`api/ApiClientCalendar.php`)**
Penerjemah bahasa Google.
*   `pushEvent($data)`: Mengirim data event kita ke server Google.
*   `deleteEvent($calendarId)`: Menghapus event di Google jika di admin dihapus.

---

### 3. Contoh Alur Data (Data Flow)

Mari kita bedah apa yang terjadi secara OOP saat Admin **Membuat Event Baru**:

1.  **Input**: Admin mengisi form di `public/admin/events.php`.
2.  **Instantiation**: File `events.php` membuat objek baru:
    ```php
    $eventService = new EventService();
    $calendarApi = new ApiClientCalendar();
    ```
3.  **Process 1 (Database Lokal)**:
    `$eventService->createEvent($data)` dipanggil.
    -> Class ini membuka koneksi DB.
    -> Melakukan Query `INSERT INTO events...`.
    -> Mengembalikan ID event baru (misal: ID 50).
4.  **Process 2 (Google API)**:
    `$calendarApi->pushEvent($data)` dipanggil.
    -> Mengirim Request HTTP ke Google API.
    -> Google membalas dengan ID Kalender (misal: "abc123google").
5.  **Process 3 (Sync)**:
    `$eventService->updateCalendarEventId(50, "abc123google")` dipanggil.
    -> Menyimpan ID Google tadi ke database lokal.

Dengan struktur OOP ini, jika suatu saat kita ingin mengganti Google Calendar dengan Outlook Calendar, kita hanya perlu mengubah kode di Process 2 tanpa merusak Process 1. Ini disebut **Loose Coupling** (Keterkaitan Longgar).
