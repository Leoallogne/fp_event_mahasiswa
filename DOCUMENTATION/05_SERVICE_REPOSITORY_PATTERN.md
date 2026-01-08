# 🏗️ Dokumen 5: Repo-Service Pattern (The Core Architecture)

> **Navigasi Dokumentasi**:
> [🏠 Home](../README.md) | [⚙️ Setup](00_SETUP_AND_INSTALLATION.md) | [📘 Overview](01_PROJECT_OVERVIEW.md) | [📂 Folder](02_FOLDER_STRUCTURE.md) | [🏗️ OOP](03_OOP_ARCHITECTURE.md) | [🗄️ Database](06_DATABASE_SCHEMA.md)

---

## 🧠 Apa itu Repo-Service Pattern?

Seringkali programmer pemula menulis kode PHP dengan cara **"Spaghetti Code"**: mencampur logika database, HTML, dan validasi form dalam satu file yang panjang.

Di EventKu, kami menggunakan **Repo-Service Pattern** (atau lebih tepatnya *Service-Layer Pattern* yang disederhanakan).

**Intinya:**
1.  **View (`public/`)**: Hanya MENAMPILKAN data. Tidak boleh ada query SQL di sini.
2.  **Service (`modules/`)**: Otak aplikasi. Melakukan verifikasi, hitung-hitungan, dan panggil database.
3.  **Database**: Tempat penyimpanan data.

---

## 🚫 The Problem: Spaghetti Code (Cara Lama)

Bayangkan jika Anda menulis kode seperti ini di `public/register-event.php`:

```php
// CONTOH KODE BURUK (JANGAN DITIRU)
if ($_POST) {
    $conn = mysqli_connect(...);
    $kuota = mysqli_query($conn, "SELECT kuota FROM events WHERE id = " . $_POST['id']); // Bahaya SQL Injection!
    
    if ($kuota > 0) {
        mysqli_query($conn, "INSERT INTO registrations ...");
        echo "Berhasil daftar!";
    } else {
        echo "Penuh!";
    }
}
```

**Kenapa ini buruk?**
1.  Susah dibaca.
2.  Tidak aman (rawan di-hack).
3.  Susah di-test.
4.  Jika logika pendaftaran berubah, Anda harus ubah di semua file yang pakai kode ini.

---

## ✅ The Solution: Service Layer (Cara EventKu)

Kami memisahkan logika tersebut ke dalam Class.

### 1. The Controller/View (`public/register-event.php`)
File ini bersih. Tugasnya cuma terima input dan panggil Service.

```php
// public/register-event.php
require_once '../modules/registrations/RegistrationService.php';

try {
    $service = new RegistrationService();
    $service->registerUser($_SESSION['user_id'], $_POST['event_id']);
    
    // Redirect jika sukses
    header("Location: success.php");
} catch (Exception $e) {
    // Tampilkan error jika gagal
    $error = $e->getMessage();
}
```

### 2. The Service (`modules/registrations/RegistrationService.php`)
Di sinilah "Otak" bekerja.

```php
// modules/registrations/RegistrationService.php
class RegistrationService {
    
    public function registerUser($userId, $eventId) {
        // 1. Cek apakah user sudah daftar?
        if ($this->isRegistered($userId, $eventId)) {
            throw new Exception("Anda sudah terdaftar!");
        }

        // 2. Cek Kuota (Pakai Transaction biar aman dari Race Condition)
        $this->db->beginTransaction();
        
        $kuota = $this->eventModel->getQuota($eventId);
        if ($kuota <= 0) {
            $this->db->rollBack();
            throw new Exception("Kuota penuh!");
        }

        // 3. Kurangi Kuota & Simpan Data
        $this->eventModel->decrementQuota($eventId);
        $this->registrationModel->create($userId, $eventId, 'pending');

        $this->db->commit();
    }
}
```

---

## 🔄 Workflow Diagram

```
[ USER ]
   ⬇️
[ VIEW / PUBLIC ]  <-- (Hanya HTML & Pemanggilan Fungsi)
   ⬇️
[ SERVICE LAYER ]  <-- (Validasi, Logika Bisnis, Transaksi)
   ⬇️
[ DATABASE ]       <-- (Penyimpanan Data)
```

---

## 💎 Benefits (Keuntungan)

1.  **Code Lebih Rapi**: Anda tahu persis di mana harus mencari kode. Masalah tampilan? Cek `public`. Masalah logika? Cek `modules`.
2.  **Mudah Dirawat (Maintainable)**: Ingin mengubah aturan "Minimal umur pendaftar"? Cukup ubah 1 file Service, dan semua halaman yang pakai fitur itu otomatis berubah.
3.  **Aman**: Karena query SQL terisolasi di dalam class dan menggunakan PDO Prepared Statements, risiko SQL Injection hampir 0%.
4.  **Reusability**: Fungsi `registerUser()` bisa dipanggil dari Website, dari API Mobile App, atau dari CLI tanpa perlu menulis ulang kodenya.

---

**Dokumentasi Selanjutnya**:
[-> Lihat Setup & Instalasi](00_SETUP_AND_INSTALLATION.md)
