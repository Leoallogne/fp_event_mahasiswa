<?php

require_once __DIR__ . '/../../config/database.php';

class CategoryService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getAllCategories() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY nama ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Get Categories Error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCategoryById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Get Category Error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createCategory($nama, $deskripsi = '') {
        try {
            $stmt = $this->db->prepare("INSERT INTO categories (nama, deskripsi) VALUES (?, ?)");
            $stmt->execute([$nama, $deskripsi]);
            
            return ['success' => true, 'id' => $this->db->lastInsertId(), 'message' => 'Kategori berhasil dibuat'];
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => false, 'message' => 'Kategori sudah ada'];
            }
            error_log("Create Category Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membuat kategori'];
        }
    }
    
    public function updateCategory($id, $nama, $deskripsi = '') {
        try {
            $stmt = $this->db->prepare("UPDATE categories SET nama = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$nama, $deskripsi, $id]);
            
            return ['success' => true, 'message' => 'Kategori berhasil diperbarui'];
        } catch(PDOException $e) {
            error_log("Update Category Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memperbarui kategori'];
        }
    }
    
    public function deleteCategory($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Kategori berhasil dihapus'];
        } catch(PDOException $e) {
            error_log("Delete Category Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus kategori'];
        }
    }
}

