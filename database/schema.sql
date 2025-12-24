-- Database Schema for Event Management System

CREATE DATABASE IF NOT EXISTS event_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE event_management;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFA📢 *PENGUMUMAN*
*PENGUMPULAN LAPORAN FINAL PROJECT*

Diberitahukan kepada seluruh mahasiswa bahwa pengumpulan laporan Final Project (FP) dilaksanakan dengan ketentuan sebagai berikut:

🗓 Deadline Pengumpulan
*Tanggal 21 Desember 2025*
Pukul 23.59 WIB

📂 Berkas yang Wajib Dikumpulkan:

1. Video penjelasan aplikasi/sistem
2. Laporan Final Project
3. Link GitHub project
4. Database (DB)

🎥 Ketentuan Video:

1. Durasi maksimal 3 menit
2. Tidak perlu pembukaan dan perkenalan
3. Fokus pada penjelasan fitur dan alur system , contoh video  ( https://www.youtube.com/watch?v=6dHmu1GALmA )

⏰ Pengumpulan melewati batas waktu yang ditentukan *tidak akan diterima.*

Demikian pengumuman ini disampaikan. *Harap diperhatikan dan dilaksanakan dengan sebaik-baiknya.*ULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    kategori VARCHAR(255) NOT NULL,
    tanggal DATETIME NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kuota INT NOT NULL DEFAULT 0,
    calendar_event_id VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tanggal (tanggal),
    INDEX idx_kategori (kategori),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registrations Table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    daftar_waktu DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id),
    INDEX idx_user_id (user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    sent_time DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- CATATAN: Jika ingin membuat admin sendiri, gunakan halaman setup.php
-- atau hapus baris INSERT ini dan akses: http://localhost/ptojrct_putra/public/setup.php
INSERT INTO users (nama, email, password, role) VALUES 
('Administrator', 'admin@event.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Calendar Cache Table (optional, for caching Google Calendar events)
CREATE TABLE IF NOT EXISTS calendar_cache (
    event_id VARCHAR(255) PRIMARY KEY,
    event_data TEXT,
    cached_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cached_at (cached_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample categories
INSERT INTO categories (nama, deskripsi) VALUES 
('Seminar', 'Kegiatan seminar dan workshop'),
('Olahraga', 'Event olahraga dan kompetisi'),
('Kesenian', 'Event seni dan budaya'),
('Teknologi', 'Event teknologi dan IT'),
('Sosial', 'Kegiatan sosial dan kemasyarakatan');

