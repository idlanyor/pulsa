<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/User.php';

$auth = new Auth();
$db = new Database();
$userModel = new User($db->getConnection());

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$auth->requireAdmin(); // Only admin can access this page
$user = $auth->getCurrentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $data = [
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'full_name' => $_POST['full_name'],
            'role' => $_POST['role'],
            'status' => $_POST['status']
        ];
        if ($userModel->create($data)) {
            $message = 'Pengguna berhasil ditambahkan.';
        } else {
            $message = 'Gagal menambahkan pengguna.';
        }
    } elseif (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $data = [
            'username' => $_POST['username'],
            'full_name' => $_POST['full_name'],
            'role' => $_POST['role'],
            'status' => $_POST['status']
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        if ($userModel->update($id, $data)) {
            $message = 'Pengguna berhasil diperbarui.';
        } else {
            $message = 'Gagal memperbarui pengguna.';
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['id'];
        if ($userModel->delete($id)) {
            $message = 'Pengguna berhasil dihapus.';
        } else {
            $message = 'Gagal menghapus pengguna.';
        }
    }
}

$users = $userModel->getAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengguna - Dashboard Konter Pulsa</title>
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
                    <h1>Manajemen Pengguna</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Daftar Pengguna</h2>
                        <button class="btn btn-primary" onclick="openAddUserModal()">Tambah Pengguna</button>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= $message ?></div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="6">Tidak ada pengguna.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($u['id']) ?></td>
                                                <td><?= htmlspecialchars($u['username']) ?></td>
                                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                                <td><?= htmlspecialchars($u['role']) ?></td>
                                                <td><?= htmlspecialchars($u['status']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="openEditUserModal(<?= htmlspecialchars($u['id']) ?>, '<?= htmlspecialchars($u['username']) ?>', '<?= htmlspecialchars($u['full_name']) ?>', '<?= htmlspecialchars($u['role']) ?>', '<?= htmlspecialchars($u['status']) ?>')">Edit</button>
                                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= htmlspecialchars($u['id']) ?>)">Hapus</button>
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

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('userModal')">&times;</span>
            <h2><span id="modalTitle">Tambah Pengguna Baru</span></h2>
            <form method="POST">
                <input type="hidden" id="userId" name="id">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password">
                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                </div>
                <div class="form-group">
                    <label for="fullName">Nama Lengkap:</label>
                    <input type="text" id="fullName" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="operator">Operator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <button type="submit" name="add_user" id="addUserBtn" class="btn btn-primary">Tambah</button>
                <button type="submit" name="edit_user" id="editUserBtn" class="btn btn-primary" style="display:none;">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('deleteUserModal')">&times;</span>
            <h2>Hapus Pengguna</h2>
            <p>Anda yakin ingin menghapus pengguna ini?</p>
            <form method="POST">
                <input type="hidden" id="deleteUserId" name="id">
                <button type="submit" name="delete_user" class="btn btn-danger">Hapus</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteUserModal')">Batal</button>
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

        function openAddUserModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Pengguna Baru';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('password').setAttribute('required', 'required');
            document.getElementById('addUserBtn').style.display = 'block';
            document.getElementById('editUserBtn').style.display = 'none';
            openModal('userModal');
        }

        function openEditUserModal(id, username, fullName, role, status) {
            document.getElementById('modalTitle').textContent = 'Edit Pengguna';
            document.getElementById('userId').value = id;
            document.getElementById('username').value = username;
            document.getElementById('fullName').value = fullName;
            document.getElementById('role').value = role;
            document.getElementById('status').value = status;
            document.getElementById('password').removeAttribute('required');
            document.getElementById('addUserBtn').style.display = 'none';
            document.getElementById('editUserBtn').style.display = 'block';
            openModal('userModal');
        }

        function openDeleteModal(id) {
            document.getElementById('deleteUserId').value = id;
            openModal('deleteUserModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>

