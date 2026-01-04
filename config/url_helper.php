<?php
/**
 * URL Helper - Untuk menghasilkan URL yang kompatibel dengan berbagai server
 * Mendukung MAMP, XAMPP, Laragon tanpa konfigurasi manual
 */

class UrlHelper
{
    private static $basePath = null;

    /**
     * Mendapatkan base path proyek secara otomatis
     */
    public static function getBasePath()
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }

        // Deteksi base path dari script yang sedang berjalan
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        // Cari posisi /public/ dalam path
        $publicPos = strpos($scriptName, '/public/');
        if ($publicPos !== false) {
            self::$basePath = substr($scriptName, 0, $publicPos + 8); // +8 untuk include '/public/'
        } else {
            // Fallback: gunakan direktori dari script
            self::$basePath = dirname($scriptName) . '/';
        }

        return self::$basePath;
    }

    /**
     * Membuat URL lengkap dari path relatif
     * @param string $path Path relatif (contoh: 'login.php' atau 'admin/dashboard.php')
     * @return string URL lengkap
     */
    public static function url($path = '')
    {
        $basePath = self::getBasePath();

        // Hapus leading slash jika ada
        $path = ltrim($path, '/');

        return $basePath . $path;
    }

    /**
     * Redirect ke halaman tertentu
     * @param string $path Path relatif
     */
    public static function redirect($path = '')
    {
        $url = self::url($path);
        header('Location: ' . $url);
        exit;
    }
}
