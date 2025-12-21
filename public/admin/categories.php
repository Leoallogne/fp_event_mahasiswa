<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../modules/users/Auth.php';
require_once __DIR__ . '/../../modules/events/CategoryService.php';

$auth = new Auth();
$auth->requireAdmin();

$categoryService = new CategoryService();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $result = $categoryService->createCategory($_POST['nama'], $_POST['deskripsi'] ?? '');
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } elseif ($action === 'update') {
        $result = $categoryService->updateCategory($_POST['id'], $_POST['nama'], $_POST['deskripsi'] ?? '');
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } elseif ($action === 'delete') {
        $result = $categoryService->deleteCategory($_POST['id']);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

$categories = $categoryService->getAllCategories();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-tags"></i> Manajemen Kategori</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
                <i class="bi bi-plus-circle"></i> Tambah Kategori
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['nama']) ?></td>
                            <td><?= htmlspecialchars($cat['deskripsi']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah/Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="categoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="categoryId">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" name="nama" id="categoryNama" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="categoryDeskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById('categoryForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('categoryId').value = '';
        }

        function editCategory(cat) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('categoryNama').value = cat.nama;
            document.getElementById('categoryDeskripsi').value = cat.deskripsi || '';
            
            const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

