<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Category.php';

$auth = new Auth();
$db = new Database();
$categoryModel = new Category($db->getConnection());

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->getCurrentUser();

// Handle CRUD operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];

        if (empty($name)) {
            $error = 'Nama kategori tidak boleh kosong.';
        } else {
            if ($categoryModel->create($name, $description, $status)) {
                $message = 'Kategori berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan kategori.';
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];

        if (empty($name)) {
            $error = 'Nama kategori tidak boleh kosong.';
        } else {
            if ($categoryModel->update($id, $name, $description, $status)) {
                $message = 'Kategori berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui kategori.';
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = $_POST['id'];
        try {
            if ($categoryModel->delete($id)) {
                $message = 'Kategori berhasil dihapus.';
            } else {
                $error = 'Gagal menghapus kategori.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$categories = $categoryModel->getAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Dashboard Konter Pulsa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            <main>
                <div class="page-header">
                    <h1>Manajemen Kategori</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Daftar Kategori</h2>
                        <button class="btn btn-primary" onclick="openModal('addCategoryModal')">Tambah Kategori</button>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="5">Tidak ada kategori.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($category['id']) ?></td>
                                                <td><?= htmlspecialchars($category['name']) ?></td>
                                                <td><?= htmlspecialchars($category['description']) ?></td>
                                                <td>
                                                    <span class="badge <?= $category['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                                        <?= ucfirst($category['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= htmlspecialchars(json_encode($category)) ?>)">Edit</button>
                                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= htmlspecialchars($category['id']) ?>)">Hapus</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('addCategoryModal')">&times;</span>
            <h2>Tambah Kategori Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="categoryName">Nama Kategori:</label>
                    <input type="text" id="categoryName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="categoryDescription">Deskripsi:</label>
                    <textarea id="categoryDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="categoryStatus">Status:</label>
                    <select id="categoryStatus" name="status">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('editCategoryModal')">&times;</span>
            <h2>Edit Kategori</h2>
            <form method="POST">
                <input type="hidden" id="editCategoryId" name="id">
                <div class="form-group">
                    <label for="editCategoryName">Nama Kategori:</label>
                    <input type="text" id="editCategoryName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editCategoryDescription">Deskripsi:</label>
                    <textarea id="editCategoryDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="editCategoryStatus">Status:</label>
                    <select id="editCategoryStatus" name="status">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <button type="submit" name="edit_category" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div id="deleteCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('deleteCategoryModal')">&times;</span>
            <h2>Hapus Kategori</h2>
            <p>Anda yakin ingin menghapus kategori ini?</p>
            <form method="POST">
                <input type="hidden" id="deleteCategoryId" name="id">
                <button type="submit" name="delete_category" class="btn btn-danger">Hapus</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteCategoryModal')">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openEditModal(category) {
            document.getElementById('editCategoryId').value = category.id;
            document.getElementById('editCategoryName').value = category.name;
            document.getElementById('editCategoryDescription').value = category.description;
            document.getElementById('editCategoryStatus').value = category.status;
            openModal('editCategoryModal');
        }

        function openDeleteModal(id) {
            document.getElementById('deleteCategoryId').value = id;
            openModal('deleteCategoryModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>

